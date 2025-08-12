<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sales_order extends MY_Controller
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
        $this->load->admin_model('table_model');
        $this->load->admin_model('sales_order_model');
        $this->load->admin_model('approved_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path         = 'assets/uploads/';
        $this->thumbs_path         = 'assets/uploads/thumbs/';
        $this->image_types         = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types  = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size   = '1024';
        $this->data['logo']        = true;
    }

    /* ------------------------------------------------------------------ */

    public function add($quote_id = null)
    {
        $this->bpas->checkPermissions();
        $getexchange_khm = $this->bpas->getExchange_rate('KHR');
        $exchange_khm    = $getexchange_khm->rate;
        $sale_id = $this->input->get('sale_id') ? $this->input->get('sale_id') : null;
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('sr');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $project_id =$this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $sale_status      = $this->input->post('sale_status');
            $payment_status   = $this->input->post('payment_status');
            $payment_term     = $this->input->post('payment_term');
            $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
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
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $item_expiry        = isset($_POST['product_expiry'][$r]) && !empty($_POST['product_expiry'][$r]) && $_POST['product_expiry'][$r] != 'false' && $_POST['product_expiry'][$r] != 'undefined' && $_POST['product_expiry'][$r] != 'null' && $_POST['product_expiry'][$r] != 'NULL' && $_POST['product_expiry'][$r] != '00/00/0000' && $_POST['product_expiry'][$r] != '' ? $_POST['product_expiry'][$r] : null; 
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_max_serial    = isset($_POST['max_serial'][$r]) ? $_POST['max_serial'][$r] : '';
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_detail        = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : '';
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_order_model->getProductByCode($item_code) : null;
                    // $unit_price = $real_unit_price;
                    if ($item_type == 'digital') {
                        $digital = true;
                    }
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
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($biller_details->state == $customer_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }
                    $product_tax += $pr_item_tax;
                    $subtotal     = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit         = $this->site->getUnitByID($item_unit);
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
                        'max_serial'        => $item_max_serial,
                        'real_unit_price'   => $real_unit_price,
                        'comment'           => $item_detail,
                        'expiry'            => $item_expiry,
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
            $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            $diagnosis      = implode(",", $this->input->post('diagnosis[]'));
            $data           = [
                'date'                => $date,
                'project_id'          => $project_id,
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
                'order_status'        => 'pending',
                'payment_status'      => $payment_status,
                'payment_term'        => $payment_term,
                'due_date'            => $due_date,
                'paid'                => 0,
                'saleman_by'          => $this->input->post('saleman_by'),
                'created_by'          => $this->session->userdata('user_id'),
                'hash'                => hash('sha256', microtime() . mt_rand()),
                'currency_rate_kh'    => $exchange_khm,
                'diagnosis_id'        => $diagnosis ? $diagnosis:null,
                'patience_type'       => $this->input->post('patience_type'),
                'bed_id'              => $this->input->post('bed'),
            ];
            $payment = [];
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
            // $this->bpas->print_arrays($data, $products, $payment);
        }
        if ($this->form_validation->run() == true && $this->sales_order_model->addSale($data, $products, $payment)) {
            $this->session->set_userdata('remove_slls', 1);
            if ($quote_id) {
                $this->db->update('quotes', ['status' => 'completed'], ['id' => $quote_id]);
            }
            $this->session->set_flashdata('message', lang('sale_added'));
            admin_redirect('sales_order');
        } else {
            if ($quote_id || $sale_id) {
                if ($quote_id) {
                    $this->data['quote'] = $this->sales_order_model->getQuoteByID($quote_id);
                    $items               = $this->sales_order_model->getAllQuoteItems($quote_id);
                } elseif ($sale_id) {
                    $this->data['quote'] = $this->sales_order_model->getInvoiceByID($sale_id);
                    $items               = $this->sales_order_model->getAllInvoiceItems($sale_id);
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
                    $pis = $this->site->getStockMovement_ProductBalanceQuantity($item->product_id, $item->warehouse_id, $item->option_id);
                    if ($pis) {
                        $row->quantity = $pis->quantity_balance;
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
                    $row->serial_no       = $row->serial_no;
                    $row->option          = $item->option_id;
                    $row->details         = $item->comment;
                    $options              = $this->sales_order_model->getProductOptions($row->id, $item->warehouse_id);
                    $combo_items          = $row->type == 'combo' ? $this->sales_order_model->getProductComboItems($row->id, $item->warehouse_id) : false;
                    $units                = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate             = $this->site->getTaxRateByID($row->tax_rate);
                    $ri                   = $this->Settings->item_addition ? $row->id : $c;
                    $set_price            = $this->site->getUnitByProId($row->id);
                    $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                        'row'        => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'set_price'=>$set_price, 'units' => $units, 'options' => $options, ];
                    $c++;
                }
                $this->data['quote_items'] = json_encode($pr);
            }
            $this->data['count']       = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['projects']    = $this->site->getAllProject();
            $this->data['error']       = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['quote_id']    = $quote_id ? $quote_id : $sale_id;
            $this->data['billers']     = $this->site->getAllCompanies('biller');
            $this->data['warehouses']  = $this->site->getAllWarehouses();
            $this->data['tax_rates']   = $this->site->getAllTaxRates();
            $this->data['units']       = $this->site->getAllBaseUnits();
            $this->data['tables']      = $this->table_model->getsuspend_note();
            $this->data['slnumber']    = $this->site->getReference('sr');
            $this->data['payment_ref'] = ''; //$this->site->getReference('pay');
            $this->data['salemans']    = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales_order'), 'page' => lang('sales_order')], ['link' => '#', 'page' => lang('add_sale_order')]];
            $meta = ['page_title' => lang('add_sale_order'), 'bc' => $bc];
            $this->page_construct('sales_order/add', $meta, $this->data);
        }
    }

    public function edit($id = null)
    {
        $this->bpas->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->sales_order_model->getInvoiceByID($id);
        if ($inv->sale_status == 'returned' || $inv->return_id || $inv->return_sale_ref) {
            $this->session->set_flashdata('error', lang('sale_x_action'));
            admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
        }
        if (!$this->session->userdata('edit_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');
        $project_id =$this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $sale_status      = $this->input->post('sale_status');
            $payment_status   = $this->input->post('payment_status');
            $payment_term     = $this->input->post('payment_term');
            $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note       = $this->bpas->clear_tags($this->input->post('staff_note'));
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
                $item_expiry        = isset($_POST['product_expiry'][$r]) && !empty($_POST['product_expiry'][$r]) && $_POST['product_expiry'][$r] != 'false' && $_POST['product_expiry'][$r] != 'undefined' && $_POST['product_expiry'][$r] != 'null' && $_POST['product_expiry'][$r] != 'NULL' && $_POST['product_expiry'][$r] != '00/00/0000' && $_POST['product_expiry'][$r] != '' ? $_POST['product_expiry'][$r] : null; 
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_max_serial        = isset($_POST['max_serial'][$r]) ? $_POST['max_serial'][$r] : '';
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_detail      = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : '';
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_order_model->getProductByCode($item_code) : null;
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
                    $product  = [
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
                        'max_serial'        => $item_max_serial,
                        'real_unit_price'   => $real_unit_price,
                        'comment'           => $item_detail,
                        'expiry'            => $item_expiry,
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
            $order_discount  = $this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax));
            $total_discount  = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax       = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax       = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total     = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            $diagnosis       = implode(",", $this->input->post('diagnosis[]'));
            $getexchange_khm = $this->bpas->getExchange_rate('KHR');
            $data = [
                'date' => $date,
                'project_id'        => $project_id,
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
                'shipping'          => $this->bpas->formatDecimal($shipping),
                'grand_total'       => $grand_total,
                'total_items'       => $total_items,
                'sale_status'       => $sale_status,
                'payment_status'    => $payment_status,
                'payment_term'      => $payment_term,
                'due_date'          => $due_date,
                'saleman_by'        => $this->input->post('saleman_by'),
                'updated_by'        => $this->session->userdata('user_id'),
                'updated_at'        => date('Y-m-d H:i:s'),
                'currency_rate_kh'  => !empty($getexchange_khm) ? $getexchange_khm->rate : 1,
                'diagnosis_id'      => $diagnosis ? $diagnosis:null,
                'patience_type'     => $this->input->post('patience_type'),
                'bed_id'            => $this->input->post('bed'),
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
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
            // $this->bpas->print_arrays($data, $products);
        }
        if ($this->form_validation->run() == true && $this->sales_order_model->updateSale($id, $data, $products)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang('sale_updated'));
            admin_redirect($inv->pos ? 'pos/sales' : 'sales_order');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $this->sales_order_model->getInvoiceByID($id);
            $this->bpas->deadlineDayEditing($this->data['inv']->date);
            $inv_items = $this->sales_order_model->getAllInvoiceItems($id);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->sales_order_model->getWarehouseProduct($item->product_id, $item->warehouse_id);
                if (!$row) {
                    $row             = json_decode('{}');
                    $row->tax_method = 0;
                    $row->quantity   = 0;
                } else {
                    unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                }
                $row->quantity = 0;
                $pis = $this->site->getStockMovement_ProductBalanceQuantity($item->product_id, $item->warehouse_id, $item->option_id);
                if ($pis) {
                    $row->quantity = $pis->quantity_balance;
                }
                $row->qoh = $this->site->getStockMovement_ProductQty($item->product_id, $item->warehouse_id, $item->option_id, $item->expiry);
                $row->id              = $item->product_id;
                $row->code            = $item->product_code;
                $row->name            = $item->product_name;
                $row->type            = $item->product_type;
                $row->base_quantity   = $item->quantity;
                $row->expiry          = $item->expiry;
                $row->base_unit       = !empty($row->unit) ? $row->unit : $item->product_unit_id;
                $row->base_unit_price = !empty($row->price) ? $row->price : $item->unit_price;
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
                $row->details         = $item->comment;
                $row->option          = $item->option_id;
                $options              = $this->sales_order_model->getProductOptions($row->id, $item->warehouse_id);
                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->sales_order_model->getProductComboItems($row->id, $item->warehouse_id);
                    $te          = $combo_items;
                    foreach ($combo_items as $combo_item) {
                        $combo_item->quantity = $combo_item->qty * $item->quantity;
                    }
                }
                $units     = !empty($row->base_unit) ? $this->site->getUnitsByBUID($row->base_unit) : null;
                $tax_rate  = $this->site->getTaxRateByID($row->tax_rate);
                $ri        = $this->Settings->item_addition ? $row->id : $c;
                $set_price = $this->site->getUnitByProId($row->id);
                $qoh       = $this->site->getStockMovement_ProductQty($item->product_id, $item->warehouse_id, $item->option_id, $item->expiry);
                $pr[$ri]   = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name. ' (' . $row->code . ')' . ($row->expiry != null ?  ' (' . $row->expiry . ')' : ''), 
                    'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'set_price' => $set_price, 'units' => $units, 'options' => $options, 'expiry' => $row->expiry, 'qoh' => (!empty($qoh) ? $qoh : 0)];
                $c++;
            }
            $this->data['count']      = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['projects']   = $this->site->getAllProject();
            $this->data['inv_items']  = json_encode($pr);
            $this->data['id']         = $id;
            $this->data['tables']     = $this->table_model->getsuspend_note();
            $this->data['billers']    = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            $this->data['units']      = $this->site->getAllBaseUnits();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['salemans']   = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales_order'), 'page' => lang('sales_order')], ['link' => '#', 'page' => lang('edit_sale_order')]];
            $meta = ['page_title' => lang('edit_sale_order'), 'bc' => $bc];
            $this->page_construct('sales_order/edit', $meta, $this->data);
        }
    }
    /* ------------------------------- */

    public function delete($id = null)
    {
        $this->bpas->checkPermissions(null, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->sales_order_model->getInvoiceByID($id);
        if ($inv->sale_status == 'returned') {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('sale_x_action')]);
        }
        if ($this->sales_order_model->deleteSale($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('sale_deleted')]);
            }
            $this->session->set_flashdata('message', lang('sale_deleted'));
            admin_redirect('welcome');
        }
    }
    /* ------------------------------------------------------------------------ */
    
    public function getSalesOrder($biller_id = null)
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
        $detail_link        = anchor('admin/sales_order/view/$1', '<i class="fa fa-file-text-o"></i>' . lang('sale_details'));
        $view_group_items_link = anchor('admin/sales_order/view_group_items/$1', '<i class="fa fa-money"></i> ' . lang('view_group_items'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="view_group_items" data-target="#myModal"');
        $view_deposit_link  = anchor('admin/sales_order/deposits/$1', '<i class="fa fa-money"></i> ' . lang('view_deposits'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="view_deposit" data-target="#myModal"');
        $add_deposit_link   = anchor('admin/sales_order/add_deposit/$1', '<i class="fa fa-money"></i> ' . lang('add_deposit'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="add_deposit" data-target="#myModal"');
        $return_detail_link = anchor('admin/sales_order/return_view/$1', '<i class="fa fa-file-text-o"></i>' . lang('return_sale').' '. lang('details'));
        $detail_link_clinic = anchor('admin/clinic/dental_clinic/$1', '<i class="fa fa-file-text-o"></i>'.lang('invoice_dental_clinic'));
        $add_draw_link      = anchor('admin/clinic/add_draw/$1', '<i class="fa fa-pencil"></i>' . lang('add_draw'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $duplicate_link     = anchor('admin/sales_order/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link      = anchor('admin/sales_order/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_payment_link   = anchor('admin/sales_order/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $packagink_link     = anchor('admin/sales_order/packaging/$1', '<i class="fa fa-archive"></i> ' . lang('packaging'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');

        $add_delivery_link='';
        if ($this->Settings->delivery) {
            //$add_delivery_link    = anchor('admin/deliveries/add/0/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'));
            $add_delivery_link  = anchor('admin/sales_order/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
            $add_delivery_link  = anchor('admin/deliveries/add/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'));
         }

        $email_link         = anchor('admin/sales_order/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link          = anchor('admin/sales_order/edit/$1', '<i class="fa fa-edit"></i>' . lang('edit_sale_order'),'class="sledit"');
        $pdf_link           = anchor('admin/sales_order/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link        = anchor('admin/sales_order/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        
        $add_sale           = anchor('admin/sales/add/$1', '<i class="fa fa-money"></i>' . lang('add_sale'));
        $authorization      = anchor('admin/sales_order/getAuthorization/$1', '<i class="fa fa-check"></i>' . lang('approved'), '');
        $unapproved         = anchor('admin/sales_order/getunapproved/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('unapproved'), '');
        $rejected           = anchor('admin/sales_order/getrejected/$1', '<i class="fa fa-times"></i> ' . lang('rejected'), '');
        $delete_link        = "<a href='#' class='po' title='<b>" . lang('delete_sale_order') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales_order/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $detail_link . '</li>
                <li>' . $view_group_items_link . '</li>
                <li class="view_deposit">' . $view_deposit_link . '</li>
                <li class="add_deposit">' . $add_deposit_link . '</li>';
                if ($this->Settings->module_clinic) {
                    $action .= '  
                        <li>' . $detail_link_clinic . '</li>
                        <li>' . $add_draw_link . '</li>';
                }
            $action .= 
                (($this->Owner || $this->Admin) ? '<li class="approved">'.$authorization.'</li>' : ($this->GP['sales_order-approved'] ? '<li class="approved">'.$authorization.'</li>' : '')).
                (($this->Owner || $this->Admin) ? '<li class="unapproved">'.$unapproved.'</li>' : ($this->GP['sales_order-rejected'] ? '<li class="unapproved">'.$unapproved.'</li>' : '')).
                (($this->Owner || $this->Admin) ? '<li class="reject">'.$rejected.'</li>' : ($this->GP['sales_order-rejected'] ? '<li class="reject">'.$rejected.'</li>' : '')).
                (($this->Owner || $this->Admin) ? '<li class="edit">'.$edit_link.'</li>' : ($this->GP['sales_order-edit'] ? '<li class="edit">'.$edit_link.'</li>' : '')).
                (($this->Owner || $this->Admin) ? '<li class="delete">'.$delete_link.'</li>' : ($this->GP['sales_order-delete'] ? '<li class="delete">'.$delete_link.'</li>' : '')).
                (($this->Owner || $this->Admin) ? '<li class="add">'.$add_sale.'</li>' : ($this->GP['sales-add'] ? '<li class="add">'.$add_sale.'</li>' : '')).
                (($this->Owner || $this->Admin) ? '<li class="add_delivery">'.$add_delivery_link.'</li>' : ($this->GP['sales-add'] ? '<li class="add_delivery">'.$add_delivery_link.'</li>' : '')).
            '</ul>
        </div></div>';
        $this->load->library('datatables');
        $this->datatables
            ->select("
                {$this->db->dbprefix('sales_order')}.id as id, 
                DATE_FORMAT({$this->db->dbprefix('sales_order')}.date, '%Y-%m-%d %T') as date, 
                project_name,
                reference_no, 
                biller, 
                {$this->db->dbprefix('sales_order')}.customer, 
                sale_status, 
                grand_total, 
                IFNULL(payments.deposit,0) as deposit,
                (grand_total-(IFNULL(payments.deposit,0))) as balance,
                order_status,
                delivery_status, 
                {$this->db->dbprefix('sales_order')}.attachment, 
                return_id")
            ->join('projects', 'sales_order.project_id = projects.project_id', 'left')
            ->join('(select sum(amount) as deposit,sale_order_id from '.$this->db->dbprefix('payments').' where sale_order_id > 0 GROUP BY sale_order_id) as payments','payments.sale_order_id = sales_order.id','left')
            ->where('sales_order.store_sale !=', 1)
            ->from('sales_order');
        if ($biller_id) {
            $this->datatables->where_in('sales_order.biller_id', $biller_id);
        } 
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
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
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('sales_order')]];
        $meta = ['page_title' => lang('sales_order'), 'bc' => $bc];
        $this->page_construct('sales_order/index', $meta, $this->data);
    }

    public function modal_view($id = null)
    {
        $this->bpas->checkPermissions('index', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_order_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by, true);
        }
        $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by']  = $this->site->getUser($inv->created_by);
        $this->data['updated_by']  = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']         = $inv;
        $this->data['rows']        = $this->sales_order_model->getAllInvoiceItems($id);
        $this->data['sold_by']  = $this->site->getsaleman($inv->saleman_by);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_order_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_order_model->getAllInvoiceItems($inv->return_id) : null;

        $this->load->view($this->theme . 'sales_order/modal_view', $this->data);
    }

    public function view_group_items($id = null)
    {
        $this->bpas->checkPermissions('index', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_order_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by, true);
        }
        $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by']  = $this->site->getUser($inv->created_by);
        $this->data['updated_by']  = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']         = $inv;
        $this->data['rows']        = $this->sales_order_model->getAllInvoiceItems_GroupProduct($id);
        $this->data['sold_by']     = $this->site->getsaleman($inv->saleman_by);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_order_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_order_model->getAllInvoiceItems($inv->return_id) : null;

        $this->load->view($this->theme . 'sales_order/view_group_items', $this->data);
    }

    public function pdf($id = null, $view = null, $save_bufffer = null)
    {
        $this->bpas->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_order_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->data['barcode']     = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments']    = $this->sales_order_model->getPaymentsForSale($id);
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
        $this->data['user']        = $this->site->getUser($inv->created_by);
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']         = $inv;
        $this->data['rows']        = $this->sales_order_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_order_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_order_model->getAllInvoiceItems($inv->return_id) : null;
        //$this->data['paypal'] = $this->sales_order_model->getPaypalSettings();
        //$this->data['skrill'] = $this->sales_order_model->getSkrillSettings();
        $name = lang('sale') . '_' . str_replace('/', '_', $inv->reference_no) . '.pdf';
        $html = $this->load->view($this->theme . 'sales/pdf', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'sales/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->bpas->generate_pdf($html, $name, $save_bufffer, $this->data['biller']->invoice_footer);
        } else {
            $this->bpas->generate_pdf($html, $name, false, $this->data['biller']->invoice_footer);
        }
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
                    foreach ($_POST['val'] as $id) {
                        $this->sales_order_model->deleteSale($id);
                    }
                    $this->session->set_flashdata('message', lang('sales_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('sales'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('grand_total'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('paid'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('payment_status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sale = $this->sales_order_model->getInvoiceByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($sale->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sale->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sale->biller);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sale->customer);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sale->grand_total);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, lang($sale->paid));
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, lang($sale->payment_status));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'sales_order_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
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

    public function suggestions($pos = 0)
    {
        $term         = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);
        $customer_id  = $this->input->get('customer_id', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed  = $this->bpas->analyze_term($term);
        $sr        = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $warehouse      = $this->site->getWarehouseByID($warehouse_id);
        $customer       = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $rows           = $this->sales_order_model->getProductNames($sr, $warehouse_id, $pos);
        if ($rows) {
            $r = 0;
            foreach ($rows as $row) {
                $c = uniqid(mt_rand(), true);
                unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $option               = false;
                $row->qty             = 1;
                $row->quantity        = 0;
                $row->base_quantity   = 1;
                $row->item_tax_method = $row->tax_method;
                $row->discount        = '0';
                $row->serial          = '';
                $options              = $this->sales_order_model->getProductOptions($row->id, $warehouse_id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->site->getProductOptionByID($option_id) : $options[0];
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt        = json_decode('{}');
                    $opt->price = 0;
                    $option_id  = false;
                }
                $row->option = $option_id;
                $pis = $this->site->getStockMovement_ProductBalanceQuantity($row->id, $warehouse_id, $row->option);
                if ($pis) {
                    $row->quantity = $pis->quantity_balance;
                }
                $cost_price_by_unit = $this->site->getProductCostPriceByUnit($row->id, $row->sale_unit);
                $row->price         = ($cost_price_by_unit ? $cost_price_by_unit->price : $row->price);
                if ($this->bpas->isPromo($row)) {
                    $row->price = $row->promo_price;
                } elseif ($customer->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
                        $row->price = $pr_group_price->price;
                    }
                } elseif ($warehouse->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $warehouse->price_group_id)) {
                        $row->price = $pr_group_price->price;
                    }
                }
                $row->real_unit_price = $row->price;
                $row->base_unit_price = $row->price;
                $row->base_unit       = $row->unit;
                $row->unit            = $row->sale_unit ? $row->sale_unit : $row->unit;
                $row->comment         = '';
                $combo_items          = $row->type == 'combo' ? $this->sales_order_model->getProductComboItems($row->id, $warehouse_id) : false;
                $units                = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate             = $this->site->getTaxRateByID($row->tax_rate);
                $set_price            = $this->site->getUnitByProId($row->id);
                if ($customer->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
                        $set_price = $this->site->getUnitByProId_PG($row->id, $customer->price_group_id);
                    }
                }

                if($this->Settings->stok_sale_order){
                    $stock_items = $this->site->getStockMovementByProductID($row->id, $warehouse_id, $row->option);
                    if ($stock_items) {
                        foreach ($stock_items as $pi) {
                            if ($this->Settings->overselling != 1 || ($this->Settings->overselling == 1 && $warehouse->overselling != 1)) {
                                if ($pi->quantity_balance > 0) {
                                    $pr[] = [
                                        'id'  => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name. ' (' . $row->code . ')' .($pi->expiry != null ?  ' ('.$pi->expiry .')' : ''), 'category' => $row->category_id,
                                        'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'set_price' => $set_price, 'options' => $options, 'fiber' => null, 'pitems' => $stock_items, 'expiry' => $pi->expiry, 'qoh' => $pi->quantity_balance
                                    ];
                                    $r++;
                                }
                            } else {
                               $pr[] = [
                                    'id'  => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name. ' (' . $row->code . ')' .($pi->expiry != null ?  ' ('.$pi->expiry .')' : ''), 'category' => $row->category_id,
                                    'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'set_price' => $set_price, 'options' => $options, 'fiber' => null, 'pitems' => $stock_items, 'expiry' => $pi->expiry, 'qoh' => $pi->quantity_balance
                                ];
                                $r++; 
                            }
                        }
                    } elseif ($row->type != 'standard' || ($row->type == 'standard' && $this->Settings->overselling == 1 && $warehouse->overselling == 1)) {
                        $pr[] = ['id' => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'category' => $row->category_id, 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units,  'set_price' => $set_price, 'options' => $options, 'fiber' => null, 'expiry' => null, 'qoh' => 0];
                        $r++;
                    } else {
                        $this->bpas->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
                    }
                }else{
                    $pr[] = [
                        'id'  => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name. ' (' . $row->code . ')' .($pi->expiry != null ?  ' ('.$pi->expiry .')' : ''), 'category' => $row->category_id,
                        'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'set_price' => $set_price, 'options' => $options, 'fiber' => null, 'pitems' => $stock_items, 'expiry' => $pi->expiry, 'qoh' => $pi->quantity_balance
                    ];
                    $r++; 
                }

            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json([['id' => 0, 'label' => lang('out_of_stock'), 'value' => $term]]);
        }
    }

    public function topup_gift_card($card_id)
    {
        $this->bpas->checkPermissions('add_gift_card', true);
        $card = $this->site->getGiftCardByID($card_id);
        $this->form_validation->set_rules('amount', lang('amount'), 'trim|integer|required');

        if ($this->form_validation->run() == true) {
            $data = ['card_id' => $card_id,
                'amount'       => $this->input->post('amount'),
                'date'         => date('Y-m-d H:i:s'),
                'created_by'   => $this->session->userdata('user_id'),
            ];
            $card_data['balance'] = ($this->input->post('amount') + $card->balance);
            // $card_data['value'] = ($this->input->post('amount')+$card->value);
            if ($this->input->post('expiry')) {
                $card_data['expiry'] = $this->bpas->fld(trim($this->input->post('expiry')));
            }
        } elseif ($this->input->post('topup')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('sales/gift_cards');
        }

        if ($this->form_validation->run() == true && $this->sales_order_model->topupGiftCard($data, $card_data)) {
            $this->session->set_flashdata('message', lang('topup_added'));
            admin_redirect('sales/gift_cards');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']   = $this->site->modal_js();
            $this->data['card']       = $card;
            $this->data['page_title'] = lang('topup_gift_card');
            $this->load->view($this->theme . 'sales/topup_gift_card', $this->data);
        }
    }

    public function update_status($id)
    {
        $this->form_validation->set_rules('status', lang('sale_status'), 'required');

        if ($this->form_validation->run() == true) {
            $status = $this->input->post('status');
            $note   = $this->bpas->clear_tags($this->input->post('note'));
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'sales');
        }

        if ($this->form_validation->run() == true && $this->sales_order_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'sales');
        } else {
            $this->data['inv']      = $this->sales_order_model->getInvoiceByID($id);
            $this->data['returned'] = false;
            if ($this->data['inv']->sale_status == 'returned' || $this->data['inv']->return_id) {
                $this->data['returned'] = true;
            }
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/update_status', $this->data);
        }
    }

    public function validate_gift_card($no)
    {
        //$this->bpas->checkPermissions();
        if ($gc = $this->site->getGiftCardByNO($no)) {
            if ($gc->expiry) {
                if ($gc->expiry >= date('Y-m-d')) {
                    $this->bpas->send_json($gc);
                } else {
                    $this->bpas->send_json(false);
                }
            } else {
                $this->bpas->send_json($gc);
            }
        } else {
            $this->bpas->send_json(false);
        }
    }

    public function view($id = null)
    {
        $this->bpas->checkPermissions('index');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_order_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->data['barcode']        = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer']       = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments']       = $this->sales_order_model->getPaymentsForSale($id);
        $this->data['biller']         = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by']     = $this->site->getUser($inv->created_by);
        $this->data['updated_by']     = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']      = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']            = $inv;
        $this->data['rows']           = $this->sales_order_model->getAllInvoiceItems($id);
        $this->data['return_sale']    = $inv->return_id ? $this->sales_order_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows']    = $inv->return_id ? $this->sales_order_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['paypal']         = $this->sales_order_model->getPaypalSettings();
        $this->data['skrill']         = $this->sales_order_model->getSkrillSettings();

        $group_pr = $this->site->getGroupPermission('sale_order');
        $this->data['getSignbox']     = isset($group_pr->id) ? $this->approved_model->getSignbox($group_pr->id) : null;
        $this->data['group_id']       = isset($group_pr->id) ? $group_pr->id : null; 

        /*$this->data['approved__']     = $this->approved_model->getApprovedStatus(['sale_order_id' => $id]);
        $this->data['approved']       = $this->approved_model->getApprovedByID(['sale_order_id' => $id]);
        $this->data['approved_']      = $this->approved_model->getApprovedByID_(['sale_order_id' => $id]);
        $this->data['PersonApproved'] = $this->site->getMultiApproved(0,'so');*/

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('view')]];
        $meta = ['page_title' => lang('view_sales_details'), 'bc' => $bc];
        $this->page_construct('sales_order/view', $meta, $this->data);
    }
    //----approved-----
    function getAuthorization($id) {
        $this->bpas->checkPermissions('approved', NULL, 'sales_order');

        $data['approved_date'] = date('Y-m-d H:i:s');
        $data['approved_by'] = $this->session->userdata('user_id');
        $data['sale_order_id'] = $id;
        $request = ['sale_order_id' => $id];
        $aprroved['sale_order_id'] = $id;
        $col = "sale_order_id";
        $this->approved_model->changeStatus($id, $col, $request, $data);
        
        if($this->sales_order_model->getAuthorizeSaleOrder($id)){
            $this->session->set_flashdata('message', $this->lang->line("sale_order_approved"));
            redirect($_SERVER["HTTP_REFERER"]); 
        }else{
            $this->session->set_flashdata('error', validation_errors());
            die();
        }
    }
    function getunapproved($id=NULL) {
        $this->bpas->checkPermissions('approved', NULL, 'sales_order');

        $data['unapproved_date'] = date('Y-m-d H:i:s');
        $data['unapproved_by'] = $this->session->userdata('user_id');
        $data['sale_order_id'] = $id;
        $request = ['sale_order_id' => $id];
        $aprroved['sale_order_id'] = $id;
        $col = "sale_order_id";
        $this->approved_model->changeStatus($id, $col, $request, $data);
        
        if(($this->sales_order_model->getSaleOrder($id)->sale_status != 'order' && $this->sales_order_model->getSaleOrder($id)->sale_status != 'delivery')) {
            $this->session->set_flashdata('error', lang("sale_order_has_been_created"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if($this->sales_order_model->getunapproved($id)){
            $this->session->set_flashdata('message', $this->lang->line("sale_order_unapproved"));
            redirect($_SERVER["HTTP_REFERER"]); 
        }else{
            $this->session->set_flashdata('error', validation_errors());
            die();
        }
    }
    function getrejected($id = null) {
        $this->bpas->checkPermissions('approved', NULL, 'sales_order');

        $data['rejected_date'] = date('Y-m-d H:i:s');
        $data['rejected_by'] = $this->session->userdata('user_id');
        $data['sale_order_id'] = $id;
        $request = ['sale_order_id' => $id];
        $aprroved['sale_order_id'] = $id;
        $col = "sale_order_id";
        $this->approved_model->changeStatus($id, $col, $request, $data);
        
        if(($this->sales_order_model->getSaleOrder($id)->sale_status) != 'order'){
            $this->session->set_flashdata('error', lang("sale_order_has_been_created"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if($this->sales_order_model->getrejected($id)){
            $this->session->set_flashdata('message', $this->lang->line("sale_order_rejected"));
            redirect($_SERVER["HTTP_REFERER"]); 
        }else{
            $this->session->set_flashdata('error', validation_errors());
            die();
        }
    }
    function sale_order_alerts($warehouse_id = NULL)
    {  
        $Settings = $this->site->getSettings();
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['users']        = $this->site->getStaff();
        $this->data['products']     = $this->site->getProducts();
        $this->data['warehouses']   = $this->site->getAllWarehouses();
        $this->data['billers']      = $this->site->getAllCompanies('biller');
        $this->data['agencies']     = $this->site->getAllSalemans($Settings->group_saleman_id);

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales_order'), 'page' => lang('sales_order')), array('link' => '#', 'page' => lang('list_sale_order_alerts')));
        $meta = array('page_title' => lang('list_sale_order_alerts'), 'bc' => $bc);
        
        $this->page_construct('sales_order/list_sale_order_alerts', $meta, $this->data);
    }
    
    function getSaleOrderAlerts($warehouse_id = NULL)
    {
        if($warehouse_id){
            $warehouse_ids = $warehouse_id;
        }

        $user_query   = $this->input->get('user') ? $this->input->get('user') : null;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : null;
        $customer     = $this->input->get('customer') ? $this->input->get('customer') : null;
        $saleman      = $this->input->get('saleman') ? $this->input->get('saleman') : null;
        $product_id   = $this->input->get('product_id') ? $this->input->get('product_id') : null;
        $biller       = $this->input->get('biller') ? $this->input->get('biller') : null;
        $warehouse    = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $start_date   = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date     = $this->input->get('end_date') ? $this->input->get('end_date') : null;

        if ($start_date) {
            $start_date = $this->bpas->fld($start_date . ' 00:00:00');
            $end_date   = $this->bpas->fld($end_date . ' 23:59:00');
        }

        $biller_id = $this->session->userdata('biller_id');
        $this->load->library('datatables');
        
        if (isset($warehouse_id)) {
            $this->datatables
                //->select("sale_order.id, sale_order.customer_id, sale_order.date,quotes.reference_no as qref, sale_order.reference_no, sale_order.biller, companies.name as customer, users.username as saleman, sale_order.sale_status, sale_order.grand_total, sale_order.order_status")
                ->select("
                            bpas_sales_order.id,
                            bpas_sales_order.date,
                            bpas_sales_order.reference_no,
                            bpas_sales_order.biller,
                            bpas_sales_order.customer,
                            CONCAT_WS(' ', bpas_users.first_name, bpas_users.last_name) as saleman_by,
                            bpas_sales_order.sale_status,
                            bpas_sales_order.grand_total,
                            COALESCE(SUM(bpas_deposits.amount), 0) as deposit,
                            bpas_sales_order.grand_total - COALESCE(SUM(bpas_deposits.amount), 0) as balance,
                            bpas_sales_order.order_status
                        ")
                ->from('bpas_sales_order')
                ->join('companies', 'companies.id = sales_order.customer_id', 'left')
                ->join('bpas_users', 'users.id = sales_order.saleman_by', 'left')
                ->join('users bill', 'bill.id = sales_order.created_by', 'left')
                ->join('companies as delivery', 'delivery.id = sales_order.delivery_by', 'left')
                ->join('deliveries', 'deliveries.sale_id = sales_order.id', 'left')
                ->join('bpas_deposits', 'bpas_deposits.so_id = sales_order.id', 'left')
                ->where('(DATE_SUB(due_date , INTERVAL(SELECT alert_day FROM bpas_settings) DAY) < CURDATE()) OR (due_date IS NULL AND DATE_ADD(bpas_sales_order.date, INTERVAL(SELECT alert_day - 1 FROM bpas_settings) DAY) < CURDATE())')
                ->where('bpas_sales_order.order_status', 'pending')
                // ->where('sales_order.biller_id', $biller_id)
                ->group_by('bpas_sales_order.id');

                if (count($warehouse_id)) {
                    $this->datatables->where('bpas_sales_order.warehouse_id', $warehouse_id);
                }
                // if (count($warehouse_ids) > 1) {
                //     $this->datatables->where_in('sales_order.warehouse_id', $warehouse_ids);
                // } else {
                //     $this->datatables->where('bpas_sales_order.warehouse_id', $warehouse_id);
                // }
                
        } else {
            $this->datatables
                //->select("sale_order.id, sale_order.date, sale_order.reference_no, quotes.reference_no as qref, sale_order.biller, companies.name AS customer, users.username AS saleman,delivery.name as delivery_man,bpas_sale_order.grand_total, sale_order.paid,(bpas_sale_order.grand_total-bpas_sale_order.paid) as balance, sale_order.sale_status")
                ->select("
                            bpas_sales_order.id,
                            bpas_sales_order.date,
                            bpas_sales_order.reference_no,
                            bpas_sales_order.biller,
                            bpas_sales_order.customer,
                            CONCAT_WS(' ', bpas_users.first_name, bpas_users.last_name) as saleman_by,
                            bpas_sales_order.sale_status,
                            bpas_sales_order.grand_total,
                            COALESCE(SUM(bpas_deposits.amount), 0) as deposit,
                            bpas_sales_order.grand_total - COALESCE(SUM(bpas_deposits.amount), 0) as balance,
                            bpas_sales_order.order_status
                    ")
                ->from('bpas_sales_order')
                ->join('companies', 'companies.id = sales_order.customer_id', 'left')
                ->join('bpas_users', 'users.id = sales_order.saleman_by', 'left')
                ->join('companies as delivery', 'delivery.id = sales_order.delivery_by', 'left')
                ->join('deliveries', 'deliveries.sale_id = sales_order.id', 'left')
                ->join('bpas_deposits', 'bpas_deposits.so_id = sales_order.id', 'left')
                ->where('(DATE_SUB(due_date , INTERVAL(SELECT alert_day FROM bpas_settings) DAY) < CURDATE()) OR (due_date IS NULL AND DATE_ADD(bpas_sales_order.date, INTERVAL(SELECT alert_day - 1 FROM bpas_settings) DAY) < CURDATE())')
                ->where('bpas_sales_order.order_status', 'pending')
                // ->where('sales_order.biller_id', $biller_id)
                ->group_by('bpas_sales_order.id');

            if(isset($_REQUEST['d'])){
                $date = $_GET['d'];
                $date1 = str_replace("/", "-", $date);
                $date =  date('Y-m-d', strtotime($date1));
                
                $this->datatables
                    ->where("date >=", $date)
                    ->where('DATE_SUB(date, INTERVAL 1 DAY) <= CURDATE()')
                    ->where('sales.payment_term <>', 0);
            }
        }
        $this->datatables->where('sales_order.pos !=', 1);
        
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('sales_order.created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        if ($user_query) {
            $this->datatables->where('sales_order.created_by', $user_query);
        }
        if ($product_id) {
            $this->datatables->join('sale_order_items', 'sale_order_items.sale_id = sales_order.id', 'left');
            $this->datatables->where('sale_order_items.product_id', $product_id);
        }
        if ($reference_no) {
            $this->datatables->where('sales_order.reference_no', $reference_no);
        }
        if ($biller) {
            $this->datatables->where('sales_order.biller_id', $biller);
        }
        if ($customer) {
            $this->datatables->where('sales_order.customer_id', $customer);
        }
        if($saleman){
            $this->datatables->where('sales_order.saleman_by', $saleman);
        }
        if ($warehouse) {
            $this->datatables->where('sales_order.warehouse_id', $warehouse);
        }
        if ($start_date) {
            $this->datatables->where('bpas_sales_order.date BETWEEN "' . $start_date . ' 00:00:00" and "' . $end_date . ' 23:59:00"', null, false);
        }

        // $this->datatables->add_column("Actions", $action, "sale_order.id");
        // $this->datatables->add_column("Actions", $action, "sales_order.id, sales_order.customer_id");
        // $this->datatables->unset_column('sales_order.customer_id');
        
        echo $this->datatables->generate();
    }

    public function approved_status($id){
        $this->form_validation->set_rules('update', lang("update"), 'required');
        if ($this->form_validation->run() == true) {
            // $note    ​=​​​  $this->bpas->clear_tags($this->input->post('note'));
            // $col     ​=​​​   'sale_order_id';
            // $request ​=​​​  ['sale_order_id' => $id];
            // $req     ​=​​​  $this->approved_model->getApprovedStatus($request);
            // foreach ($req as $key_ => $value_) {
            //     foreach($_POST as $key => $value){
            //         $d = explode("_by", $key);
            //         $m = $d[0] . '_status';
            //         if(($key != 'update') && ($key != 'note') && ($m == $key_) && ($value != $value_)){
            //             $data[]  = array(
            //                 $d[0] . '_status'   => $value,
            //                 $key                => $this->session->userdata('user_id'),
            //                 $d[0] . '_date'     => date('Y-m-d h:i:s')
            //             );
            //         }
            //     }
            // }
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $table          = "sales_order";
            $col            =  'sale_order_id';
            $request        = ['sale_order_id' => $id];
            $req            = $this->approved_model->getApprovedStatus($request);
            if($req){
            foreach ($req as $key_ => $value_) {
                foreach($_POST as $key => $value){
                    $d = explode("_by", $key);
                    $m = $d[0] . '_status';
                    if(($key != 'update') && ($key != 'note') && ($m == $key_) && ($value != $value_)){
                        $data[]  = array(
                            $d[0] . '_status'   => $value,
                            $key                => $this->session->userdata('user_id'),
                            $d[0] . '_date'     => date('Y-m-d h:i:s'));
                        }
                     }
                }
            }else{
                foreach($_POST as $key => $value){
                    $d = explode("_by", $key);
                    $m = $d[0] . '_status';
                    if(($key != 'update') && ($key != 'note')){
                        $data[]  = array(
                            $d[0] . '_status'   => $value,
                            $key                => $this->session->userdata('user_id'),
                            $d[0] . '_date'     => date('Y-m-d h:i:s'));
                    }
                }
            }
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales_order');
        }
        if ($this->form_validation->run() == true && $this->approved_model->change_Status($id, $col, $data, $request, $note, $_POST, $table)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            admin_redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales_order');
        } else {
            $this->data['inv']             = $this->sales_order_model->getInvoiceByID($id);
            $this->data['PersonApproved']  = $this->site->getMultiApproved(0,'so');
            $this->data['approved']        = $this->approved_model->getApprovedByID(['sale_order_id'=>$id]);

            $this->data['returned']        = FALSE;
            // if ($this->data['inv']->status == 'returned' || $this->data['inv']->return_id) {
            //     $this->data['returned']    = TRUE;
            // }
            $this->data['modal_js']        = $this->site->modal_js();
            $this->load->view($this->theme.'sales_order/approved_status', $this->data);
        }
    }
    //--------------
    public function deposits($id = null)
    {
        $this->bpas->checkPermissions('deposits', true, 'customers');
        $this->data['deposits'] = $this->sales_order_model->getSODeposits($id);
        $this->data['saleorder'] = $this->sales_order_model->getSaleOrderByID($id);
        $this->load->view($this->theme . 'sales_order/deposits', $this->data);
    }
    
    public function deposit_note($id = null)
    {
        $this->bpas->checkPermissions('deposits', true, 'customers');
        $deposit = $this->sales_order_model->getDepositByID($id);
        $sale_order = $this->sales_order_model->getSaleOrderByID($deposit->sale_order_id);
        $this->data['biller'] = $this->site->getCompanyByID($sale_order->biller_id);
        $this->data['customer'] = $this->site->getCompanyByID($sale_order->customer_id);
        $this->data['sale_order'] = $sale_order;
        $this->data['deposit'] = $deposit;
        $this->data['page_title'] = lang("deposit_note");
        $this->data['print'] = $this->site->Assgin_Print('Deposit Note',$deposit->id);
        $this->load->view($this->theme . 'sales_order/deposit_note', $this->data);
    }
    
    public function add_deposit($id = null)
    {
        $this->bpas->checkPermissions('deposits', true, 'customers');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale_order = $this->sales_order_model->getSaleOrderByID($id);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        
        if ($this->form_validation->run() == true) {
            if ($this->input->post('paid_by') == 'deposit') {
                $sale_order = $this->sales_order_model->getSaleOrderByID($this->input->post('sale_order_id'));
                $customer_id = $sale_order->customer_id;
                if ( ! $this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                    $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin  || $this->bpas->GP['sale_orders-date']) {
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
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay',$sale_order->biller_id);
            $paymentAcc = $this->site->getAccountSettingByBiller($sale_order->biller_id);
            if($this->input->post('paid_by')=='deposit' || $this->input->post('paid_by')=='gift_card'){
                $paying_to = $paymentAcc->customer_deposit_acc;
            }else{
                $cash_account = $this->site->getCashAccountByCode($this->input->post('paid_by'));
                $paying_to = $cash_account->account_code;
                if($cash_account->type=="bank"){
                    $bank_name = $cash_account->name;
                    $account_name = $this->input->post('account_name');
                    $account_number = $this->input->post('account_number');
                }else if($cash_account->type=="cheque"){
                    $bank_name = $this->input->post('bank_name');
                    $cheque_number = $this->input->post('cheque_number');
                    $cheque_date = $this->bpas->fsd($this->input->post('cheque_date'));
                }
            }
            $data = array(
                'date'          => $date,
                'customer_id'   => $sale_order->customer_id,
                'sale_order_id' => $this->input->post('sale_order_id'),
                'reference_no'  => $reference_no,
                'amount'        => $this->input->post('amount-paid'),
                'note'          => $this->input->post('note'),
                'created_by'    => $this->session->userdata('user_id'),
                'type'          => 'received',
            );

            $payment = array(
                'date' => $date,
                'sale_order_id' => $this->input->post('sale_order_id'),
                'transaction' => "SODeposit",
                'reference_no' => $reference_no,
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'cc_no' => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : '',
                'note' => $this->input->post('note'),
                'created_by' => $this->session->userdata('user_id'),
                'type' => 'received',
                'currencies' => json_encode($currencies),
                'account_code' => $paying_to,
                'bank_name' => $bank_name,
                'account_name' => $account_name,
                'account_number' => $account_number,
                'cheque_number' => $cheque_number,
                'cheque_date' => $cheque_date,
            );

            //=====accountig=====//
            if($this->Settings->module_account == 1){
                $accTranPayments[] = array(
                        'tran_type'     => 'SODeposit',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $paymentAcc->customer_deposit_acc,
                        'amount'        => -($this->input->post('amount-paid')+$this->input->post('discount')),
                        'narrative'     => 'Sale Order Deposit '.$sale_order->reference_no,
                        'description'   => $this->input->post('note'),
                        'biller_id'     => $sale_order->biller_id,
                        'project_id'    => $sale_order->project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'customer_id'   => $sale_order->customer_id,
                    );
                $accTranPayments[] = array(
                        'tran_type'     => 'SODeposit',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $paying_to,
                        'amount'        => $this->input->post('amount-paid'),
                        'narrative'     => 'Sale Order Deposit '.$sale_order->reference_no,
                        'description'   => $this->input->post('note'),
                        'biller_id'     => $sale_order->biller_id,
                        'project_id'    => $sale_order->project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'customer_id'   => $sale_order->customer_id,
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

        if ($this->form_validation->run() == true && $this->sales_order_model->addDeposit($payment, $customer_id, $accTranPayments)) {
            $this->session->set_flashdata('message', lang("deposit_added"));
            admin_redirect('sales_order');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['sale_order'] = $sale_order;
            $this->data['payment_ref'] = '';
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'sales_order/add_deposit', $this->data);
        }
    }
    
    public function edit_deposit($id = null)
    {
        $this->bpas->checkPermissions('deposits', true, 'customers');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $deposit = $this->sales_order_model->getDepositByID($id);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        
        if ($this->form_validation->run() == true) {
            $sale_order = $this->sales_order_model->getSaleOrderByID($this->input->post('sale_order_id'));
            if ($this->input->post('paid_by') == 'deposit') {
                $customer_id = $sale_order->customer_id;
                if ( ! $this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                    $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin  || $this->bpas->GP['sale_orders-date']) {
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
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay',$sale_order->biller_id);
            $paymentAcc = $this->site->getAccountSettingByBiller($sale_order->biller_id);
            if($this->input->post('paid_by')=='deposit' || $this->input->post('paid_by')=='gift_card'){
                $paying_to = $paymentAcc->customer_deposit_acc;
            }else{
                $cash_account = $this->site->getCashAccountByCode($this->input->post('paid_by'));
                $paying_to = $cash_account->account_code;
                if($cash_account->type=="bank"){
                    $bank_name = $cash_account->name;
                    $account_name = $this->input->post('account_name');
                    $account_number = $this->input->post('account_number');
                }else if($cash_account->type=="cheque"){
                    $bank_name = $this->input->post('bank_name');
                    $cheque_number = $this->input->post('cheque_number');
                    $cheque_date = $this->bpas->fsd($this->input->post('cheque_date'));
                }
            }
            $payment = array(
                'date' => $date,
                'sale_order_id' => $this->input->post('sale_order_id'),
                'transaction' => "SODeposit",
                'reference_no' => $reference_no,
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'cc_no' => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : '',
                'note' => $this->input->post('note'),
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date("Y-m-d H:i"),
                'type' => 'received',
                'currencies' => json_encode($currencies),
                'account_code' => $paying_to,
                'bank_name' => $bank_name,
                'account_name' => $account_name,
                'account_number' => $account_number,
                'cheque_number' => $cheque_number,
                'cheque_date' => $cheque_date,
            );

            //=====accountig=====//
                    if($this->Settings->module_account == 1){
                        $accTranPayments[] = array(
                                'tran_no'       => $id,
                                'tran_type'     => 'SODeposit',
                                'tran_date'     => $date,
                                'reference_no'   => $reference_no,
                                'account_code'  => $paymentAcc->customer_deposit_acc,
                                'amount'        => -($this->input->post('amount-paid')+$this->input->post('discount')),
                                'narrative'     => 'Sale Order Deposit '.$sale_order->reference_no,
                                'description'   => $this->input->post('note'),
                                'biller_id'     => $sale_order->biller_id,
                                'project_id'    => $sale_order->project_id,
                                'created_by'    => $this->session->userdata('user_id'),
                                'customer_id'   => $sale_order->customer_id,
                            );
                        $accTranPayments[] = array(
                                'tran_no'       => $id,
                                'tran_type'     => 'SODeposit',
                                'tran_date'     => $date,
                                'reference_no'  => $reference_no,
                                'account_code'  => $paying_to,
                                'amount'        => $this->input->post('amount-paid'),
                                'narrative'     => 'Sale Order Deposit '.$sale_order->reference_no,
                                'description'   => $this->input->post('note'),
                                'biller_id'     => $sale_order->biller_id,
                                'project_id'    => $sale_order->project_id,
                                'created_by'    => $this->session->userdata('user_id'),
                                'customer_id'   => $sale_order->customer_id,
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

        if ($this->form_validation->run() == true && $this->sales_order_model->updateDeposit($id, $payment, $customer_id, $accTranPayments)) {
            $this->session->set_flashdata('message', lang("deposit_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->data['sale_order'] = $this->sales_order_model->getSaleOrderByID($deposit->sale_order_id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['deposit'] = $deposit;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'sales_order/edit_deposit', $this->data);
        }
    }
    
    public function delete_deposit($id = null)
    {
        $this->bpas->checkPermissions('delete_deposit', true, 'customers');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $opay = $this->sales_order_model->getDepositByID($id);
        if ($this->sales_order_model->deleteDeposit($id)) {
            $this->session->set_flashdata('message', lang("deposit_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    
}
