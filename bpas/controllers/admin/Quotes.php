<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Quotes extends MY_Controller
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
        $this->lang->admin_load('quotations', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('quotes_model');
        $this->digital_upload_path = 'files/';
        $this->digital_file_types  = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size   = '1024';
        $this->data['logo']        = true;
    }

    
    public function quotes_by_csv()
    {
        $this->bpas->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');
        $this->form_validation->set_rules('project', lang('project'), 'required');
        
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') :  $this->site->getReference('qu');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $warehouse_id     = $this->input->post('warehouse');
            $supplier_id      = $this->input->post('supplier');
            $customer_id      = $this->input->post('customer');
            $valid_day = $this->bpas->fsd(trim($this->input->post('valid_day')));
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $sale_status      = $this->input->post('sale_status');
            $status           = $this->input->post('status');
            $payment_status   = $this->input->post('payment_status');
            $payment_term     = $this->input->post('payment_term');
            $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days')) : null;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = $customer_details->company && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = $biller_details->company && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note       = $this->bpas->clear_tags($this->input->post('staff_note'));
            if ($supplier_id) {
                $supplier_details = $this->site->getCompanyByID($supplier_id);
                $supplier         = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            } else {
                $supplier = null;
            }

            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('quotes/quotes_by_csv');
                }
                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle    = fopen($this->digital_upload_path . $csv, 'r');
                
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                
                $titles = array_shift($arrResult);
                // $keys  = ['code', 'net_unit_price', 'quantity', 'variant', 'item_tax_rate', 'discount', 'serial'];
                $keys  = ['code', 'net_unit_price', 'quantity', 'variant', 'item_tax_rate', 'discount'];
                
                $final = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                
                $rw = 2;
                foreach ($final as $csv_pr) {
                    if (isset($csv_pr['code']) && isset($csv_pr['net_unit_price']) && isset($csv_pr['quantity'])) {
                        if ($product_details = $this->quotes_model->getProductByCode($csv_pr['code'])) {
                            if ($csv_pr['variant']) {
                                $item_option = $this->quotes_model->getProductVariantByName($csv_pr['variant'], $product_details->id);
                                if (!$item_option) {
                                    $this->session->set_flashdata('error', lang('pr_not_found') . ' ( ' . $product_details->name . ' - ' . $csv_pr['variant'] . ' ). ' . lang('line_no') . ' ' . $rw);
                                    redirect($_SERVER['HTTP_REFERER']);
                                }
                            } else {
                                $item_option     = null;
                            }

                            $item_id        = $product_details->id;
                            $item_type      = $product_details->type;
                            $item_code      = $product_details->code;
                            $item_name      = $product_details->name;
                            $item_net_price = $this->bpas->formatDecimal($csv_pr['net_unit_price']);
                            $item_quantity  = $csv_pr['quantity'];
                            $item_tax_rate  = $csv_pr['item_tax_rate'];
                            $item_discount  = $csv_pr['discount'];
                            // $item_serial    = $csv_pr['serial'];

                            if (isset($item_code) && isset($item_net_price) && isset($item_quantity)) {
                                $product_details  = $this->quotes_model->getProductByCode($item_code);
                                $pr_discount      = $this->site->calculateDiscount($item_discount, $item_net_price);
                                $item_net_price   = $this->bpas->formatDecimal(($item_net_price - $pr_discount), 4);
                                $pr_item_discount = $this->bpas->formatDecimal(($pr_discount * $item_quantity), 4);
                                $product_discount += $pr_item_discount;

                                $tax         = '';
                                $pr_item_tax = 0;
                                $unit_price  = $item_net_price;
                                $tax_details = ((isset($item_tax_rate) && !empty($item_tax_rate)) ? $this->quotes_model->getTaxRateByName($item_tax_rate) : $this->site->getTaxRateByID($product_details->tax_rate));
                                if ($tax_details) {
                                    $ctax     = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                                    $item_tax = $ctax['amount'];
                                    $tax      = $ctax['tax'];
                                    if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                                        $item_net_price = $unit_price - $item_tax;
                                    }
                                    $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_quantity, 4);
                                    if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($biller_details->state == $customer_details->state), $tax_details)) {
                                        $total_cgst += $gst_data['cgst'];
                                        $total_sgst += $gst_data['sgst'];
                                        $total_igst += $gst_data['igst'];
                                    }
                                }

                                $product_tax += $pr_item_tax;
                                $subtotal = $this->bpas->formatDecimal((($item_net_price * $item_quantity) + $pr_item_tax), 4);
                                $unit     = $this->site->getUnitByID($product_details->unit);

                                // $product = [
                                //     'product_id'        => $product_details->id,
                                //     'product_code'      => $item_code,
                                //     'product_name'      => $item_name,
                                //     'product_type'      => $item_type,
                                //     'option_id'         => $item_option->id,
                                //     'net_unit_price'    => $item_net_price,
                                //     'unit_price'        => $this->bpas->formatDecimal(($item_net_price + $item_tax), 4),
                                //     'quantity'          => $item_quantity,
                                //     'product_unit_id'   => $product_details->unit,
                                //     'product_unit_code' => $unit->code,
                                //     'unit_quantity'     => $item_quantity,
                                //     'warehouse_id'      => $warehouse_id,
                                //     'item_tax'          => $pr_item_tax,
                                //     'tax_rate_id'       => $tax_details ? $tax_details->id : null,
                                //     'tax'               => $tax,
                                //     'discount'          => $item_discount,
                                //     'item_discount'     => $pr_item_discount,
                                //     'subtotal'          => $subtotal,
                                //     'serial_no'         => $item_serial,
                                //     'real_unit_price'   => $this->bpas->formatDecimal(($item_net_price + $item_tax + $pr_discount), 4),
                                // ];

                                $product = [
                                    'product_id'        => $item_id,
                                    'product_code'      => $item_code,
                                    'product_name'      => $item_name,
                                    'product_type'      => $item_type,
                                    'option_id'         => $item_option,
                                    'net_unit_price'    => $item_net_price,
                                    'unit_price'        => $this->bpas->formatDecimal($item_net_price + $item_tax),
                                    'quantity'          => $item_quantity,
                                    'product_unit_id'   => $product_details->unit,
                                    'product_unit_code' => $unit->code,
                                    'unit_quantity'     => $item_quantity,
                                    'warehouse_id'      => $warehouse_id,
                                    'item_tax'          => $pr_item_tax,
                                    'tax_rate_id'       => $item_tax_rate,
                                    'tax'               => $tax,
                                    'discount'          => $item_discount,
                                    'item_discount'     => $pr_item_discount,
                                    'subtotal'          => $this->bpas->formatDecimal($subtotal),
                                    'real_unit_price'   => $this->bpas->formatDecimal(($item_net_price + $item_tax + $pr_discount), 4),
                                ];
                   
                                $products[] = ($product + $gst_data);
                                $total += $this->bpas->formatDecimal(($item_net_price * $item_quantity), 4);
                            }
                        } else {
                            $this->session->set_flashdata('error', lang('pr_not_found') . ' ( ' . $csv_pr['code'] . ' ). ' . lang('line_no') . ' ' . $rw);
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                        $rw++;
                    }
                }
            }
           

            $order_discount = $this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);

            // $data           = [
            //     'date'                => $date,
            //     'reference_no'        => $reference,
            //     'project_id'          => $this->input->post('project'),
            //     'customer_id'         => $customer_id,
            //     'customer'            => $customer,
            //     'biller_id'           => $biller_id,
            //     'biller'              => $biller,
            //     'warehouse_id'        => $warehouse_id,
            //     'note'                => $note,
            //     'staff_note'          => $staff_note,
            //     'total'               => $total,
            //     'product_discount'    => $product_discount,
            //     'order_discount_id'   => $this->input->post('order_discount'),
            //     'order_discount'      => $order_discount,
            //     'total_discount'      => $total_discount,
            //     'product_tax'         => $product_tax,
            //     'order_tax_id'        => $this->input->post('order_tax'),
            //     'order_tax'           => $order_tax,
            //     'total_tax'           => $total_tax,
            //     'shipping'            => $this->bpas->formatDecimal($shipping),
            //     'grand_total'         => $grand_total,
            //     'total_items'         => $total_items,
            //     'sale_status'         => $sale_status,
            //     'payment_status'      => $payment_status,
            //     'payment_term'        => $payment_term,
            //     'due_date'            => $due_date,
            //     'paid'                => 0,
            //     'created_by'          => $this->session->userdata('user_id'),
            // ];

        $data = [
            'date'                => $date,
            'reference_no'        => $reference,
            'customer_id'         => $customer_id,
            'customer'            => $customer,
            'biller_id'           => $biller_id,
            'biller'              => $biller,
            'supplier_id'         => $supplier_id,
            'supplier'            => $supplier,
            'warehouse_id'        => $warehouse_id,
            'note'                => $note,
            'total'               => $total,
            'product_discount'    => $product_discount,
            'order_discount_id'   => $this->input->post('discount'),
            'order_discount'      => $order_discount,
            'total_discount'      => $total_discount,
            'product_tax'         => $product_tax,
            'order_tax_id'        => $this->input->post('order_tax'),
            'order_tax'           => $order_tax,
            'total_tax'           => $total_tax,
            'shipping'            => $this->bpas->formatDecimal($shipping),
            'grand_total'         => $grand_total,
            'status'              => $status,
            'created_by'          => '',
            'hash'                => hash('sha256', microtime() . mt_rand()),
            'valid_day'           => $valid_day
        ];

            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

            if ($payment_status == 'paid') {
                $payment = [
                    'date'         => $date,
                    'reference_no' => $this->site->getReference('pay'),
                    'amount'       => $grand_total,
                    'paid_by'      => 'cash',
                    'cheque_no'    => '',
                    'cc_no'        => '',
                    'cc_holder'    => '',
                    'cc_month'     => '',
                    'cc_year'      => '',
                    'cc_type'      => '',
                    'created_by'   => $this->session->userdata('user_id'),
                    'note'         => lang('auto_added_for_sale_by_csv') . ' (' . lang('sale_reference_no') . ' ' . $reference . ')',
                    'type'         => 'received',
                ];
            } else {
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

            // $this->bpas->print_arrays($data, $products, $payment);
        }

        if ($this->form_validation->run() == true && 
            $this->quotes_model->addQuote($data, $products)) {
            $this->session->set_userdata('remove_slls', 1);
        $this->session->set_flashdata('message', lang('quotes_added'));
        admin_redirect('quotes');
        } else {
            $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['billers']    = $this->site->getAllCompanies('biller');
            $this->data['qunumber']   = $this->site->getReference('qu');
            $this->data['projects']         = $this->site->getAllProject();
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('quote'), 'page' => lang('quotes')], ['link' => '#', 'page' => lang('add_quotes_by_csv')]];
            $meta = ['page_title' => lang('add_quotes_by_csv'), 'bc' => $bc];
            $this->page_construct('quotes/quotes_by_csv', $meta, $this->data);
        }
    }

    public function combine_pdf($quotes_id)
    {
        $this->bpas->checkPermissions('pdf');

        foreach ($quotes_id as $quote_id) {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv                 = $this->quotes_model->getQuoteByID($quote_id);
            if (!$this->session->userdata('view_right')) {
                $this->bpas->view_rights($inv->created_by);
            }
            $this->data['rows']      = $this->quotes_model->getAllQuoteItems($quote_id);
            $this->data['customer']  = $this->site->getCompanyByID($inv->customer_id);
            $this->data['biller']    = $this->site->getCompanyByID($inv->biller_id);
            $this->data['user']      = $this->site->getUser($inv->created_by);
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['inv']       = $inv;

            $html[] = [
                'content' => $this->load->view($this->theme . 'quotes/pdf', $this->data, true),
                'footer'  => '',
            ];
        }

        $name = lang('quotes') . '.pdf';
        $this->bpas->generate_pdf($html, $name);
    }
    public function email($quote_id = null)
    {
        $this->bpas->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $quote_id = $this->input->get('id');
        }
        $inv = $this->quotes_model->getQuoteByID($quote_id);
        $this->form_validation->set_rules('to', $this->lang->line('to') . ' ' . $this->lang->line('email'), 'trim|required|valid_email');
        $this->form_validation->set_rules('subject', $this->lang->line('subject'), 'trim|required');
        $this->form_validation->set_rules('cc', $this->lang->line('cc'), 'trim|valid_emails');
        $this->form_validation->set_rules('bcc', $this->lang->line('bcc'), 'trim|valid_emails');
        $this->form_validation->set_rules('note', $this->lang->line('message'), 'trim');

        if ($this->form_validation->run() == true) {
            if (!$this->session->userdata('view_right')) {
                $this->bpas->view_rights($inv->created_by);
            }
            $to      = $this->input->post('to');
            $subject = $this->input->post('subject');
            if ($this->input->post('cc')) {
                $cc = $this->input->post('cc');
            } else {
                $cc = null;
            }
            if ($this->input->post('bcc')) {
                $bcc = $this->input->post('bcc');
            } else {
                $bcc = null;
            }
            $customer = $this->site->getCompanyByID($inv->customer_id);
            $biller   = $this->site->getCompanyByID($inv->biller_id);
            $this->load->library('parser');
            $parse_data = [
                'reference_number' => $inv->reference_no,
                'contact_person'   => $customer->name,
                'company'          => $customer->company,
                'site_link'        => base_url(),
                'site_name'        => $this->Settings->site_name,
                'logo'             => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company && $biller->company != '-' ? $biller->company : $biller->name) . '"/>',
            ];
            $msg        = $this->input->post('note');
            $message    = $this->parser->parse_string($msg, $parse_data);
            $attachment = $this->pdf($quote_id, null, 'S');

            try {
                if ($this->bpas->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
                    delete_files($attachment);
                    $this->db->update('quotes', ['status' => 'sent'], ['id' => $quote_id]);
                    $this->session->set_flashdata('message', $this->lang->line('email_sent'));
                    admin_redirect('quotes');
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
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            if (file_exists('./themes/' . $this->Settings->theme . '/admin/views/email_templates/quote.html')) {
                $quote_temp = file_get_contents('themes/' . $this->Settings->theme . '/admin/views/email_templates/quote.html');
            } else {
                $quote_temp = file_get_contents('./themes/default/admin/views/email_templates/quote.html');
            }

            $this->data['subject'] = ['name' => 'subject',
                'id'                         => 'subject',
                'type'                       => 'text',
                'value'                      => $this->form_validation->set_value('subject', lang('quote') . ' (' . $inv->reference_no . ') ' . lang('from') . ' ' . $this->Settings->site_name),
            ];
            $this->data['note'] = ['name' => 'note',
                'id'                      => 'note',
                'type'                    => 'text',
                'value'                   => $this->form_validation->set_value('note', $quote_temp),
            ];
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);

            $this->data['id']       = $quote_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'quotes/email', $this->data);
        }
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

        $this->data['warehouse_id'] = null;
        $this->data['warehouses']   = $this->site->getAllWarehouses();
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('quotes')]];
        $meta = ['page_title' => lang('quotes'), 'bc' => $bc];
        $this->page_construct('quotes/index', $meta, $this->data);
    }
    public function getQuotes($biller_id = null)
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
        $duplicate_link       = anchor('admin/quotes/add?quote_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_quote'));
        $detail_link  = anchor('admin/quotes/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('quote_details'));
        $quote_service  = anchor('admin/quotes/view_service/$1', '<i class="fa fa-file-text-o"></i> ' . lang('quote_service'));
        $email_link   = anchor('admin/quotes/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_quote'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link    = anchor('admin/quotes/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_quote'));
        $add_sale_link = anchor('admin/sales/add/0/$1', '<i class="fa fa-heart"></i> ' . lang('create_sale'), ' class="create_sale"');
        
        $so_link     = '';
        if($this->Settings->sale_order){
            $so_link = anchor('admin/sales_order/add/$1', '<i class="fa fa-heart"></i> ' . lang('create_sale_order'), ' class="create_sale_order"');
        }

        $pc_link      = anchor('admin/purchases/add/0/$1', '<i class="fa fa-star"></i> ' . lang('create_purchase'));
        $pdf_link     = anchor('admin/quotes/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $delete_link  = "<a href='#' class='po' title='<b>" . $this->lang->line('delete_quote') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('quotes/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_quote') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $detail_link . '</li>
                        <li>' . $quote_service . '</li>
                        <li>' . $duplicate_link . '</li>
                        <li>' . $edit_link . '</li>
                        <li>' . $so_link . '</li>
                        <li>' . $add_sale_link . '</li>
                        <li>' . $pc_link . '</li>
                        <li>' . $pdf_link . '</li>
                        <li>' . $email_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        $this->datatables->select("
                {$this->db->dbprefix('quotes')}.id as id, 
                date, reference_no, 
                biller, 
                customer, 
                CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as saleman_by, 
                grand_total, 
                status, attachment")
        ->from('quotes')
        ->join('users','users.id = quotes.saleman_by','left');

        if ($biller_id) {
            $this->datatables->where_in('biller_id', $biller_id);
        }
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
    public function add()
    {
        $this->bpas->checkPermissions();
        $quote_id         = $this->input->get('quote_id') ? $this->input->get('quote_id') : null;
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('customer', $this->lang->line('customer'), 'required');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('qu');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $valid_day = $this->bpas->fsd(trim($this->input->post('valid_day')));
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $supplier_id      = $this->input->post('supplier');
            $status           = $this->input->post('status');
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = $customer_details->company && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = $biller_details->company && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            if ($supplier_id) {
                $supplier_details = $this->site->getCompanyByID($supplier_id);
                $supplier         = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            } else {
                $supplier = null;
            }
            $note = $this->bpas->clear_tags($this->input->post('note'));

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
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_comment       = $_POST['product_comment'][$r];
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->quotes_model->getProductByCode($item_code) : null;
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

                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $this->bpas->formatDecimal($item_net_price + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'real_unit_price'   => $real_unit_price,
                        'comment'           => $item_comment,
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

            $order_discount = $this->site->calculateDiscount($this->input->post('discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            $data           = [
                'date' => $date,
                'reference_no'        => $reference,
                'customer_id'         => $customer_id,
                'customer'            => $customer,
                'biller_id'           => $biller_id,
                'biller'              => $biller,
                'supplier_id'         => $supplier_id,
                'supplier'            => $supplier,
                'warehouse_id'        => $warehouse_id,
                'note'                => $note,
                'total'               => $total,
                'product_discount'    => $product_discount,
                'order_discount_id'   => $this->input->post('discount'),
                'order_discount'      => $order_discount,
                'total_discount'      => $total_discount,
                'product_tax'         => $product_tax,
                'order_tax_id'        => $this->input->post('order_tax'),
                'order_tax'           => $order_tax,
                'total_tax'           => $total_tax,
                'shipping'            => $this->bpas->formatDecimal($shipping),
                'grand_total'         => $grand_total,
                'status'              => $status,
                'created_by'          => $this->session->userdata('user_id'),
                'hash'                => hash('sha256', microtime() . mt_rand()),
                'valid_day'           => $valid_day,
                'payment_note'        => $this->bpas->clear_tags($this->input->post('payment_note')),
                'saleman_by'          => $this->input->post('saleman_by'),
                'service'             => $this->input->post('service'),
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
        }

        if ($this->form_validation->run() == true && $this->quotes_model->addQuote($data, $products)) {
            $this->session->set_userdata('remove_quls', 1);
            $this->session->set_flashdata('message', $this->lang->line('quote_added'));
            admin_redirect('quotes');
        } else {
            if ($quote_id) {
                if ($quote_id) {
                    $getSaleslist        = $this->quotes_model->getQuoteByID($quote_id);
                    $items               = $this->quotes_model->getAllQuoteItems($quote_id);
                    $sale_items          = [];
                    $q_id                = $quote_id;
                }
                krsort($items);
                $c = rand(100000, 9999999);
                foreach ($items as $item) {
                    $b = false;
                  
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
                    
                    $row = $this->site->getProductByID($item->product_id);

                    if (!$row) {
                        $row             = json_decode('{}');
                        $row->tax_method = 0;
                    } else {
                        unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                    }
                    $row->quantity = 1;
                    
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

                    $option_quantity = 0;

                    $combo_items = false;
                    if ($row->type == 'combo') {
                        $combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
                    }
                    $units     = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate  = $this->site->getTaxRateByID($row->tax_rate);
                    $ri        = $this->Settings->item_addition ? $row->id : $c;
                    $set_price = $this->site->getUnitByProId($row->id);
                    if ($quote_id) {
                        if (!empty($set_price)) {
                            foreach ($set_price as $key => $p) {
                                if ($p->unit_id == $row->unit) {
                                    $set_price[$key]->price = $row->real_unit_price;
                                }
                            }
                        }
                    }
                    $options ='';
                        $pr[$ri] = ['id' => $ri, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                            'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units,  'set_price' => $set_price, 'options' => $options,  'expiry'=>"0000-00-00" ];
                        $c++;
                    
                }
                $this->data['quote_items'] = json_encode($pr);
                $this->data['quote']       = $getSaleslist;
                $this->data['inv']         = $getSaleslist;
                $this->data['quote_id']    = $q_id;
            }


            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['billers'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            //$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['count'] = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['units']      = $this->site->getAllBaseUnits();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['qunumber']   = $this->site->getReference('qu');
            $this->data['salemans']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;

            $bc                       = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('quotes'), 'page' => lang('quotes')], ['link' => '#', 'page' => lang('add_quote')]];
            $meta                     = ['page_title' => lang('add_quote'), 'bc' => $bc];
            $this->page_construct('quotes/add', $meta, $this->data);
        }
    }

    public function edit($id = null)
    {
        $this->bpas->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->quotes_model->getQuoteByID($id);
        if (!$this->session->userdata('edit_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('reference_no', $this->lang->line('reference_no'), 'required');
        $this->form_validation->set_rules('customer', $this->lang->line('customer'), 'required');
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $valid_day = $this->bpas->fsd(trim($this->input->post('valid_day')));
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $supplier_id      = $this->input->post('supplier');
            $status           = $this->input->post('status');
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = $customer_details->company && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = $biller_details->company && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            if ($supplier_id) {
                $supplier_details = $this->site->getCompanyByID($supplier_id);
                $supplier         = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            } else {
                $supplier = null;
            }
            $note = $this->bpas->clear_tags($this->input->post('note'));

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
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_comment       = $_POST['product_comment'][$r];
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->quotes_model->getProductByCode($item_code) : null;
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
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'real_unit_price'   => $real_unit_price,
                        'comment'           => $item_comment,
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
            $order_discount = $this->site->calculateDiscount($this->input->post('discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            $data           = [
                'date'                => $date,
                'reference_no'        => $reference,
                'customer_id'         => $customer_id,
                'customer'            => $customer,
                'biller_id'           => $biller_id,
                'biller'              => $biller,
                'supplier_id'         => $supplier_id,
                'supplier'            => $supplier,
                'warehouse_id'        => $warehouse_id,
                'note'                => $note,
                'total'               => $total,
                'product_discount'    => $product_discount,
                'order_discount_id'   => $this->input->post('discount'),
                'order_discount'      => $order_discount,
                'total_discount'      => $total_discount,
                'product_tax'         => $product_tax,
                'order_tax_id'        => $this->input->post('order_tax'),
                'order_tax'           => $order_tax,
                'total_tax'           => $total_tax,
                'shipping'            => $shipping,
                'grand_total'         => $grand_total,
                'status'              => $status,
                'updated_by'          => $this->session->userdata('user_id'),
                'updated_at'          => date('Y-m-d H:i:s'),
                'valid_day'           => $valid_day,
                'payment_note'        => $this->bpas->clear_tags($this->input->post('payment_note')),
                'saleman_by'          => $this->input->post('saleman_by'),
                'service'             => $this->input->post('service'),
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

        if ($this->form_validation->run() == true && $this->quotes_model->updateQuote($id, $data, $products)) {
            $this->session->set_userdata('remove_quls', 1);
            $this->session->set_flashdata('message', $this->lang->line('quote_added'));
            admin_redirect('quotes');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $this->quotes_model->getQuoteByID($id);
            $inv_items         = $this->quotes_model->getAllQuoteItems($id);
            // krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $row             = json_decode('{}');
                    $row->tax_method = 0;
                } else {
                    unset($row->details, $row->product_details, $row->cost, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price);
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
                $row->base_quantity   = $item->quantity;
                $row->base_unit       = isset($row->unit) ? $row->unit : $item->product_unit_id;
                $row->base_unit_price = isset($row->price) ? $row->price : $item->unit_price;
                $row->unit            = $item->product_unit_id;
                $row->qty             = $item->unit_quantity;
                $row->discount        = $item->discount ? $item->discount : '0';
                $row->item_tax        = $item->item_tax      > 0 ? $item->item_tax           / $item->quantity : 0;
                $row->item_discount   = $item->item_discount > 0 ? $item->item_discount / $item->quantity : 0;
                $row->price           = $this->bpas->formatDecimal($item->net_unit_price + $this->bpas->formatDecimal($row->item_discount));
                $row->unit_price      = $row->tax_method ? $item->unit_price + $this->bpas->formatDecimal($row->item_discount) + $this->bpas->formatDecimal($row->item_tax) : $item->unit_price + ($row->item_discount);
                $row->real_unit_price = $item->real_unit_price;
                $row->tax_rate        = $item->tax_rate_id;
                $row->option          = $item->option_id;
                $options              = $this->quotes_model->getProductOptions($row->id, $item->warehouse_id);
                $row->comment         = $item->comment;
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
                    $combo_items = $this->quotes_model->getProductComboItems($row->id, $item->warehouse_id);
                    foreach ($combo_items as $combo_item) {
                        $combo_item->quantity = $combo_item->qty * $item->quantity;
                    }
                }
                $units    = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri       = $this->Settings->item_addition ? $row->id : $c;
                $set_price = $this->site->getUnitByProId($row->id);
                $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'set_price'=>$set_price,'units' => $units, 'options' => $options];
                $c++;
            }
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id']        = $id;
            //$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['count'] = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['billers']    = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            $this->data['units']      = $this->site->getAllBaseUnits();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['salemans']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('quotes'), 'page' => lang('quotes')], ['link' => '#', 'page' => lang('edit_quote')]];
            $meta = ['page_title' => lang('edit_quote'), 'bc' => $bc];
            $this->page_construct('quotes/edit', $meta, $this->data);
        }
    }
    public function delete($id = null)
    {
        $this->bpas->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->quotes_model->deleteQuote($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('quote_deleted')]);
            }
            $this->session->set_flashdata('message', lang('quote_deleted'));
            admin_redirect('welcome');
        }
    }
    public function modal_view($quote_id = null)
    {
        $this->bpas->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $quote_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->quotes_model->getQuoteByID($quote_id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by, true);
        }
        
        $this->data['rows']       = $this->quotes_model->getAllQuoteItems($quote_id);
        $this->data['customer']   = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller']     = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']  = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']        = $inv;

        $this->load->view($this->theme . 'quotes/modal_view', $this->data);
    }

    public function pdf($quote_id = null, $view = null, $save_bufffer = null)
    {
        $this->bpas->checkPermissions();

        if ($this->input->get('id')) {
            $quote_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->quotes_model->getQuoteByID($quote_id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->data['rows']       = $this->quotes_model->getAllQuoteItems($quote_id);
        $this->data['customer']   = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller']     = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse']  = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']        = $inv;
        $name                     = $this->lang->line('quote') . '_' . str_replace('/', '_', $inv->reference_no) . '.pdf';
        $html                     = $this->load->view($this->theme . 'quotes/pdf', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'quotes/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->bpas->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->bpas->generate_pdf($html, $name);
        }
    }

    public function quote_actions()
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
                        $this->quotes_model->deleteQuote($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line('quotes_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'combine') {
                    $html = $this->combine_pdf($_POST['val']);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('quotes'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('total'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $qu = $this->quotes_model->getQuoteByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($qu->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $qu->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $qu->biller);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $qu->customer);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $qu->total);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $qu->status);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'quotations_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line('no_quote_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function suggestions()
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
        $customer       = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $rows           = $this->quotes_model->getProductNames($sr, $warehouse_id);
        if ($rows) {
            $r = 0;
            foreach ($rows as $row) {
                $c = uniqid(mt_rand(), true);
                unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $option               = false;
                $row->quantity        = 0;
                $row->item_tax_method = $row->tax_method;
                $row->qty             = 1;
                $row->discount        = '0';
                $options              = $this->quotes_model->getProductOptions($row->id, $warehouse_id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->quotes_model->getProductOptionByID($option_id) : $options[0];
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
                $cost_price_by_unit    = $this->site->getProductCostPriceByUnit($row->id, $row->sale_unit);
                $row->price            = ($cost_price_by_unit ? $cost_price_by_unit->price : $row->price);
                if ($row->promotion) {
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
                $row->price           = $row->price + (($row->price * $customer_group->percent) / 100);
                $row->real_unit_price = $row->price;
                $row->base_quantity   = 1;
                $row->base_unit       = $row->unit;
                $row->base_unit_price = $row->price;
                $row->unit            = $row->sale_unit ? $row->sale_unit : $row->unit;
                $row->comment         = '';
                $combo_items          = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->quotes_model->getProductComboItems($row->id, $warehouse_id);
                }
                $units     = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate  = $this->site->getTaxRateByID($row->tax_rate);
                $set_price = $this->site->getUnitByProId($row->id);
                $pr[] = [
                    'id'  => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'category' => $row->category_id,
                    'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'set_price' => $set_price, 'units' => $units, 'options' => $options, ];
                $r++;
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    public function update_status($id)
    {
        $this->form_validation->set_rules('status', lang('status'), 'required');

        if ($this->form_validation->run() == true) {
            $status = $this->input->post('status');
            $note   = $this->bpas->clear_tags($this->input->post('note'));
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'sales');
        }

        if ($this->form_validation->run() == true && $this->quotes_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'sales');
        } else {
            $this->data['inv']      = $this->quotes_model->getQuoteByID($id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'quotes/update_status', $this->data);
        }
    }

    public function view($quote_id = null)
    {
        $this->bpas->checkPermissions('index');

        if ($this->input->get('id')) {
            $quote_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->quotes_model->getQuoteByID($quote_id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->data['rows']       = $this->quotes_model->getAllQuoteItems($quote_id);
        $this->data['customer']   = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller']     = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']  = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']        = $inv;

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('quotes'), 'page' => lang('quotes')], ['link' => '#', 'page' => lang('view')]];
        $meta = ['page_title' => lang('view_quote_details'), 'bc' => $bc];
        $this->page_construct('quotes/view', $meta, $this->data);
    }
    function quote_alerts($warehouse_id = NULL)
    {  
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['users'] = $this->site->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['products'] = $this->site->getProducts();
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('quotes'), 'page' => lang('quotes')), array('link' => '#', 'page' => lang('list_quote_alerts')));
        $meta = array('page_title' => lang('list_quote_alerts'), 'bc' => $bc);
        $this->page_construct('quotes/quote_alerts', $meta, $this->data);
    }
    function getQuoteAlerts($warehouse_id = NULL)
    {
        $this->bpas->checkPermissions('index',null,'quotes');
        if($warehouse_id){
            $warehouse_ids = $warehouse_id;
        }

        $user_query   = $this->input->get('user') ? $this->input->get('user') : null;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : null;
        $customer     = $this->input->get('customer') ? $this->input->get('customer') : null;
        $product_id   = $this->input->get('product_id') ? $this->input->get('product_id') : null;
        $biller       = $this->input->get('biller') ? $this->input->get('biller') : null;
        $warehouse    = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $start_date   = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date     = $this->input->get('end_date') ? $this->input->get('end_date') : null;
        
        if ($start_date) {
            $start_date = $this->bpas->fld($start_date);
            $end_date = $this->bpas->fld($end_date);
        }
    
        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("quotes.id, quotes.date, quotes.reference_no, quotes.biller, quotes.customer, quotes.supplier, quotes.grand_total, quotes.status")
                ->from('quotes')
                ->where('quotes.status','pending')
                ->where('quotes.warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select("
                    quotes.id, quotes.date, quotes.reference_no, quotes.biller, 
                    IF(bpas_companies.company = '', bpas_quotes.customer, bpas_companies.company) AS customer, 
                    quotes.supplier, quotes.grand_total, quotes.status")
                ->from('quotes')
                ->join('companies', 'quotes.customer_id = companies.id', 'left')
                ->where('quotes.status','pending');
        }
        
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('quotes.created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        
        if ($user_query) {
            $this->datatables->where('quotes.created_by', $user_query);
        }
        if ($product_id) {
            $this->datatables->join('quote_items', 'quote_items.quote_id = quotes.id', 'left');
            $this->datatables->where('quote_items.product_id', $product_id);
        }
        
        if ($reference_no) {
            $this->datatables->where('quotes.reference_no', $reference_no);
        }
        if ($biller) {
            $this->datatables->where('quotes.biller_id', $biller);
        }
        if ($customer) {
            $this->datatables->where('quotes.customer_id', $customer);
        }
        if ($warehouse) {
            $this->datatables->where('quotes.warehouse_id', $warehouse);
        }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('quotes').'.date BETWEEN "' . $start_date . ' 00:00:00" and "' . $end_date . ' 23:59:00"');
        }
        
        echo $this->datatables->generate();
    }
    public function view_service($quote_id = null)
    {
        $this->bpas->checkPermissions('index');

        if ($this->input->get('id')) {
            $quote_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->quotes_model->getQuoteByID($quote_id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->data['rows']       = $this->quotes_model->getAllQuoteItems($quote_id);
        $this->data['customer']   = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller']     = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']  = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']        = $inv;

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('quotes'), 'page' => lang('quotes')], ['link' => '#', 'page' => lang('view')]];
        $meta = ['page_title' => lang('view_quote_details'), 'bc' => $bc];
        $this->page_construct('quotes/view_service', $meta, $this->data);
    }
    //-------compare----------
    public function comparison($biller_id = null)
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

        $this->data['warehouse_id'] = null;
        $this->data['warehouses']   = $this->site->getAllWarehouses();
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('quotes')]];
        $meta = ['page_title' => lang('comparison'), 'bc' => $bc];
        $this->page_construct('quotes/comparison', $meta, $this->data);
    }
}
