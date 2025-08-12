<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Pos extends MY_Controller
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
        $this->load->admin_model('table_model');
        $this->load->admin_model('products_model');
        $this->load->admin_model('promos_model');
        $this->load->admin_model('pos_model');
        $this->load->admin_model('sales_model');
        $this->load->helper('text');
        $this->pos_settings           = $this->pos_model->getSetting();
        $this->pos_settings->pin_code = $this->pos_settings->pin_code ? md5($this->pos_settings->pin_code) : null;
        $this->data['pos_settings']   = $this->pos_settings;
        $this->session->set_userdata('last_activity', now());
        $this->lang->admin_load('pos', $this->Settings->user_language);
        $this->load->library('form_validation');
    }

    public function active()
    {
        $this->session->set_userdata('last_activity', now());
        if ((now() - $this->session->userdata('last_activity')) <= 20) {
            die('Successfully updated the last activity.');
        }
        die('Failed to update last activity.');
    }

    public function add_payment($id = null)
    {
        $this->bpas->checkPermissions('payments', true, 'sales');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('amount-paid', lang('amount'), 'required');
        $this->form_validation->set_rules('paid_by', lang('paid_by'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $sale = $this->pos_model->getInvoiceByID($this->input->post('sale_id'));
            if ($this->input->post('paid_by') == 'deposit') {
                $customer_id = $sale->customer_id;
                if (!$this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
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
            $currencies = $this->site->getAllCurrencies();
            $csize = sizeof($currencies); 
            foreach ($currencies as $key => $currency) {
                if($key < $csize - 1){
                    $currency_rate .= $this->bpas->formatDecimal($currency->rate).","  ;
                }else{
                    $currency_rate .= $this->bpas->formatDecimal($currency->rate)  ;
                }
            } 
            $payment = [
                'date'         => $date,
                'sale_id'      => $this->input->post('sale_id'),
                'reference_no' => $this->input->post('reference_no'),
                'amount'       => $this->input->post('amount-paid'),
                'paid_by'      => $this->input->post('paid_by'),
                'cheque_no'    => $this->input->post('cheque_no'),
                'currency_rate' => $currency_rate,
                'cc_no'        => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                'cc_holder'    => $this->input->post('pcc_holder'),
                'cc_month'     => $this->input->post('pcc_month'),
                'cc_year'      => $this->input->post('pcc_year'),
                'cc_type'      => $this->input->post('pcc_type'),
                'cc_cvv2'      => $this->input->post('pcc_ccv'),
                'note'         => $this->input->post('note'),
                'created_by'   => $this->session->userdata('user_id'),
                'type'         => $sale->sale_status == 'returned' ? 'returned' : 'received',
            ];

            //=====add accounting=====//
            $reference_no = $this->input->post('reference_no');
            if($this->Settings->module_account == 1){
            //    $paymentAcc = $this->site->getAccountSettingByBiller($sale->biller_id);
                $paymentAcc = $this->site->getAccountSettingByBiller();
                $accTranPayments[] = array(
                        'tran_type'    => 'Payment',
                        'tran_date'    => $date,
                        'reference_no' => $reference_no,
                        'account_code' => $paymentAcc->default_receivable,
                        'amount'       => -($this->input->post('amount-paid')),
                        'narrative'    => $this->site->getAccountName($paymentAcc->default_receivable),
                        'description'  => $this->input->post('note'),
                        'biller_id'    => $sale->biller_id,
                        'project_id'   => $sale->project_id,
                        'customer_id'  => $sale->customer_id,
                        'created_by'   => $this->session->userdata('user_id'),
                    );
                if($this->input->post('paid_by') == 'deposit'){
                    $paying_to = $paymentAcc->default_sale_deposit;
                }else{
                    $paying_to = ($this->input->post('bank_account') !=0) ? $this->input->post('bank_account'): $paymentAcc->default_cash ;
                }
                $accTranPayments[] = array(
                        'tran_type'    => 'Payment',
                        'tran_date'    => $date,
                        'reference_no' => $reference_no,
                        'account_code' => $paying_to,
                        'amount'       => $this->input->post('amount-paid'),
                        'narrative'    => $this->site->getAccountName($paying_to),
                        'description'  => $this->input->post('note'),
                        'biller_id'    => $sale->biller_id,
                        'project_id'   => $sale->project_id,
                        'customer_id'  => $sale->customer_id,
                        'created_by'   => $this->session->userdata('user_id'),
                    );
             
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

            //$this->bpas->print_arrays($payment);
        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $msg = $this->pos_model->addPayment($payment, $customer_id, $accTranPayments)) {
            if ($msg) {
                if ($msg['status'] == 0) {
                    unset($msg['status']);
                    $error = '';
                    foreach ($msg as $m) {
                        if (is_array($m)) {
                            foreach ($m as $e) {
                                $error .= '<br>' . $e;
                            }
                        } else {
                            $error .= '<br>' . $m;
                        }
                    }
                    $this->session->set_flashdata('error', '<pre>' . $error . '</pre>');
                } else {
                    $this->session->set_flashdata('message', lang('payment_added'));
                }
            } else {
                $this->session->set_flashdata('error', lang('payment_failed'));
            }
            admin_redirect('pos/sales');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $sale                      = $this->pos_model->getInvoiceByID($id);
            $this->data['inv']         = $sale;
            $this->data['payment_ref'] = $this->site->getReference('pay');
            $this->data['modal_js']    = $this->site->modal_js();

            $this->load->view($this->theme . 'pos/add_payment', $this->data);
        }
    }
    public function add_printer()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('pos');
        }
        $this->form_validation->set_rules('title', $this->lang->line('title'), 'required');
        $this->form_validation->set_rules('type', $this->lang->line('type'), 'required');
        $this->form_validation->set_rules('profile', $this->lang->line('profile'), 'required');
        $this->form_validation->set_rules('char_per_line', $this->lang->line('char_per_line'), 'required');
        if ($this->input->post('type') == 'network') {
            $this->form_validation->set_rules('ip_address', $this->lang->line('ip_address'), 'required|is_unique[printers.ip_address]');
            $this->form_validation->set_rules('port', $this->lang->line('port'), 'required');
        } else {
            $this->form_validation->set_rules('path', $this->lang->line('path'), 'required|is_unique[printers.path]');
        }

        if ($this->form_validation->run() == true) {
            $data = ['title'    => $this->input->post('title'),
                     'type'          => $this->input->post('type'),
                     'stock_type'          => $this->input->post('stock_type'),
                     'profile'       => $this->input->post('profile'),
                     'char_per_line' => $this->input->post('char_per_line'),
                     'path'          => $this->input->post('path'),
                     'ip_address'    => $this->input->post('ip_address'),
                     'port'          => ($this->input->post('type') == 'network') ? $this->input->post('port') : null,
            ];
        }

        if ($this->form_validation->run() == true && $cid = $this->pos_model->addPrinter($data)) {
            $this->session->set_flashdata('message', $this->lang->line('printer_added'));
            admin_redirect('pos/printers');
        } else {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['status' => 'failed', 'msg' => validation_errors()]);
                die();
            }
            $this->data['stock_types'] = $this->site->getAllStockTypes();
            $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('add_printer');
            $bc                       = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('pos'), 'page' => lang('pos')], ['link' => admin_url('pos/printers'), 'page' => lang('printers')], ['link' => '#', 'page' => lang('add_printer')]];
            $meta                     = ['page_title' => lang('add_printer'), 'bc' => $bc];
            $this->page_construct('pos/add_printer', $meta, $this->data);
        }
    }

    public function getCustomerByID()
    {
        $this->bpas->checkPermissions('index');
        if ($this->input->get('customer_id')) {
            $customer_id = $this->input->get('customer_id');
        }
        $customer_group_id = $this->site->getGroupCustomerByCustomerID($customer_id);
        $this->bpas->send_json($customer_group_id);
    }

    public function ajaxbranddata($brand_id = null,  $warehouse_id = null)
    {
        $this->bpas->checkPermissions('index');
        if ($this->input->get('brand_id')) {
            $brand_id  = $this->input->get('brand_id');
        }
        $warehouse_id  = $this->input->get('warehouse_id') ? $this->input->get('warehouse_id') : null;
        $products      = $this->ajaxproducts(false, $brand_id, $warehouse_id);
        if (!($tcp = $this->pos_model->products_count(false, false, $brand_id))) {
            $tcp = 0;
        }
        $this->bpas->send_json(['products' => $products, 'tcp' => $tcp]);
    }

    public function ajaxcategorydata($category_id = null, $warehouse_id = null)
    {
        $this->bpas->checkPermissions('index');
        $warehouse_id  = $this->input->get('warehouse_id') ? $this->input->get('warehouse_id') : null;
        $category_id   = $this->input->get('category_id') ? $this->input->get('category_id') : $this->pos_settings->default_category;
        $subcategories = $this->site->getSubCategories($category_id);
        $scats         = '';
        if ($subcategories) {
            foreach ($subcategories as $category) {
                $scats .= '<button id="subcategory-' . $category->id . "\" type=\"button\" value='" . $category->id . "' class=\"btn-prni subcategory\" ><img src=\"" . base_url() . 'assets/uploads/thumbs/' . ($category->image ? $category->image : 'no_image.png') . "\" class='img-rounded img-thumbnail' /><span>" . $category->name . '</span></button>';
            }
        }
        $products  = $this->ajaxproducts($category_id, null, $warehouse_id);
        if (!($tcp = $this->pos_model->products_count($category_id))) {
            $tcp = 0;
        }
        $this->bpas->send_json(['products' => $products, 'subcategories' => $scats, 'tcp' => $tcp]);
    }

    public function ajaxproducts($category_id = null, $brand_id = null, $warehouse_id = null, $term_product = null, $term_category = null)
    {
        $this->bpas->checkPermissions('index');
        if ((!empty($this->input->get('term_product')) && $this->input->get('term_product') != '') || (!empty($this->input->get('term_category')) && $this->input->get('term_category') != '')) {
            $term_product   = $this->input->get('term_product');
            $term_category  = $this->input->get('term_category');
            $brand_id       = null;
            $category_id    = null;
            $subcategory_id = null;
        } else {
            if ($this->input->get('brand_id')) {
                $brand_id = $this->input->get('brand_id');
            }
            if ($this->input->get('category_id')) {
                $category_id = $this->input->get('category_id');
            } else {
                $category_id = $this->pos_settings->default_category;
            }
            if ($this->input->get('subcategory_id')) {
                $subcategory_id = $this->input->get('subcategory_id');
            } else {
                $subcategory_id = null;
            }
        }
        $warehouse_id = ($this->input->get('warehouse_id') ? $this->input->get('warehouse_id') : $warehouse_id);
        if (empty($this->input->get('per_page')) || $this->input->get('per_page') == 'n') {
            $page = 0;
        } else {
            $page = $this->input->get('per_page');
        }
        $this->load->library('pagination');
        $config                  = [];
        $config['base_url']      = base_url() . 'pos/ajaxproducts';
        $config['total_rows']    = $this->pos_model->products_count($category_id, $subcategory_id, $brand_id, $warehouse_id, $term_product, $term_category);
        $config['per_page']      = $this->pos_settings->pro_limit;
        $config['prev_link']     = false;
        $config['next_link']     = false;
        $config['display_pages'] = false;
        $config['first_link']    = false;
        $config['last_link']     = false;
        $this->pagination->initialize($config);
        $products = $this->pos_model->fetch_products($category_id, $config['per_page'], $page, $subcategory_id, $brand_id, $warehouse_id, $term_product, $term_category);
        $pro      = 1;
        $prods    = '<div>';
        if (!empty($products)) {
            foreach ($products as $product) {
                $count = $product->id;
                if ($count < 10) {
                    $count = '0' . ($count / 100) * 100;
                }
                if ($category_id < 10) {
                    $category_id = '0' . ($category_id / 100) * 100;
                }
                if($this->pos_settings->show_item == 1) {
                    $prods .= '<button id="product-' . $category_id . $count . '" value="' . $product->code . '" alt="' . $product->name . '" class="product pos-tip" data-container="body"
                                    style="width:150px;border:1px solid #dddddd;border-radius:5px; margin-right:5px;margin-bottom:3px;background: #ffffff;">
                                    <div style="width:150px;margin-right:5px;">
                                        <img width="100%" height="145" src="' . base_url() . 'assets/uploads/thumbs/' . $product->image . '" class="rounded" />
                                    </div>
                                    <div style="width:150px;">
                                        <div class="text-center">
                                            <span>' . character_limiter($product->name, 15) . '</span>
                                        </div>';
                                        if ($this->pos_settings->show_qty == 1 ) {
                                            $prods .= '<div class="text-center bold">QOH: ' . $product->quantity . '</div>';
                                        }
                                        $prods .= '<div class="text-center bold">' . $this->Settings->default_currency . $this->bpas->formatDecimal($product->price) . '</div>
                                    </div>
                                </button>';
                } elseif ($product->quantity > 0) {
                    $prods .= '<button id="product-' . $category_id . $count . '" value="' . $product->code . '" alt="' . $product->name . '" class="product pos-tip" data-container="body"
                                    style="width:150px;border:1px solid #dddddd;border-radius:5px; margin-right:5px;margin-bottom:3px;background: #ffffff;position: relative;">
                                    <div>
                                        <img width="100%" height="145" src="' . base_url() . 'assets/uploads/thumbs/' . $product->image . '" class="rounded" />
                                    </div>
                                    <div class="text-center">
                                        <span>'. character_limiter($product->name, 15) . '</span>
                                    </div>';
                                    if($this->pos_settings->show_qty == 1 ){
                                        $prods .= '<div class="text-center bold">QOH: ' . $product->quantity . '</div>';
                                    }   
                    $prods .= '<div class="text-center bold" style="position: absolute;top: 0;right: 0;background: gray;color: white; padding: 0 3px;">
                                ' . $this->Settings->default_currency . $this->bpas->formatDecimal($product->price) . '
                            </div>
                        </button>';
                }
                $pro++; 
            }
        }
        $prods .= '</div>';
        if ($this->input->get('per_page')) {
            echo $prods;
        } else {
            return $prods;
        }
    }

    public function barcode($text = null, $bcs = 'code128', $height = 50)
    {
        return admin_url('products/gen_barcode/' . $text . '/' . $bcs . '/' . $height);
    }

    public function check_pin()
    {
        $pin = $this->input->post('pw', true);
        if ($pin == $this->pos_pin) {
            $this->bpas->send_json(['res' => 1]);
        }
        $this->bpas->send_json(['res' => 0]);
    }

    public function close_register($user_id = null)
    {
        $this->bpas->checkPermissions('index');
        if (!$this->Owner && !$this->Admin) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->form_validation->set_rules('total_cash', lang('total_cash'), 'trim|required|numeric');
        $this->form_validation->set_rules('total_cheques', lang('total_cheques'), 'trim|required|numeric');
        $this->form_validation->set_rules('total_cc_slips', lang('total_cc_slips'), 'trim|required|numeric');
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['brands']     = $this->site->getAllBrands();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $warehouse = $this->site->getAllWarehouses();
        
        $start_date = $this->session->userdata('register_open_time');
        $end_date = $this->bpas->hrld(date('Y-m-d H:i:s'));
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $user_register = $user_id ? $this->pos_model->registerData($user_id) : null;
                $rid           = $user_register ? $user_register->id : $this->session->userdata('register_id');
                $user_id       = $user_register ? $user_register->user_id : $this->session->userdata('user_id');
            } else {
                $rid     = $this->session->userdata('register_id');
                $user_id = $this->session->userdata('user_id');
            }
            $data = [
                'closed_at'                => date('Y-m-d H:i:s'),
                'total_cash'               => $this->input->post('total_cash'),
                'total_cheques'            => $this->input->post('total_cheques'),
                'total_cc_slips'           => $this->input->post('total_cc_slips'),
                'total_cash_submitted'     => $this->input->post('total_cash_submitted'),
                'total_cheques_submitted'  => $this->input->post('total_cheques_submitted'),
                'total_cc_slips_submitted' => $this->input->post('total_cc_slips_submitted'),
                'note'                     => $this->input->post('note'),
                'status'                   => 'close',
                'transfer_opened_bills'    => $this->input->post('transfer_opened_bills'),
                'closed_by'                => $this->session->userdata('user_id'),
            ];
        } elseif ($this->input->post('close_register')) {
            $this->session->set_flashdata('error', (validation_errors() ? validation_errors() : $this->session->flashdata('error')));
            admin_redirect('pos');
        }

        if ($this->form_validation->run() == true && $this->pos_model->closeRegister($rid, $user_id, $data)) {
            $this->session->set_flashdata('message', lang('register_closed'));
            admin_redirect('welcome');
        } else {
            if ($this->Owner || $this->Admin) {
                $user_register                    = $user_id ? $this->pos_model->registerData($user_id) : null;
                $register_open_time               = $user_register ? $user_register->date : null;
                $this->data['cash_in_hand']       = $user_register ? $user_register->cash_in_hand : null;
                $this->data['register_open_time'] = $user_register ? $register_open_time : null;
            } else {
                $register_open_time               = $this->session->userdata('register_open_time');
                $this->data['cash_in_hand']       = null;
                $this->data['register_open_time'] = null;
            }
            $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['getcategoryInOut'] = $this->pos_model->getCategoryInOuts($start_date,$end_date);
             $this->data['get_warehouse'] = $warehouse;
            $this->data['settings'] = $this->site->getSettings();
            $this->data['ccsales']         = $this->pos_model->getRegisterCCSales($register_open_time, $user_id);
            $this->data['cashsales']       = $this->pos_model->getRegisterCashSales($register_open_time, $user_id);
            $this->data['chsales']         = $this->pos_model->getRegisterChSales($register_open_time, $user_id);
            $this->data['gcsales']         = $this->pos_model->getRegisterGCSales($register_open_time);
            $this->data['pppsales']        = $this->pos_model->getRegisterPPPSales($register_open_time, $user_id);
            $this->data['stripesales']     = $this->pos_model->getRegisterStripeSales($register_open_time, $user_id);
            $this->data['authorizesales']  = $this->pos_model->getRegisterAuthorizeSales($register_open_time, $user_id);
            $this->data['totalsales']      = $this->pos_model->getRegisterSales($register_open_time, $user_id);

            $this->data['abasales']         = $this->pos_model->getRegisterABASales($register_open_time,null,'ABA');
            $this->data['acledasales']      = $this->pos_model->getRegisterABASales($register_open_time,null,'Acleda');
            $this->data['alipay']           = $this->pos_model->getRegisterABASales($register_open_time,null,'Alipay');
            $this->data['pipay']            = $this->pos_model->getRegisterABASales($register_open_time,null,'PiPay');
            $this->data['wing']             = $this->pos_model->getRegisterABASales($register_open_time,null,'Wing');
            $this->data['other']             = $this->pos_model->getRegisterABASales($register_open_time,null,'other');

            $this->data['payments']        = $this->pos_model->getCashAccountByBank($register_open_time,null);


            $this->data['totalreceipt']     = $this->pos_model->getRegisterTotalTrans($register_open_time);
            
            $this->data['refunds']         = $this->pos_model->getRegisterRefunds($register_open_time, $user_id);
            $this->data['returns']         = $this->pos_model->getRegisterReturns($register_open_time, $user_id);
            $this->data['cashrefunds']     = $this->pos_model->getRegisterCashRefunds($register_open_time, $user_id);
            $this->data['expenses']        = $this->pos_model->getRegisterExpenses($register_open_time, $user_id);
            $this->data['users']           = $this->pos_model->getUsers($user_id);
            $this->data['suspended_bills'] = $this->pos_model->getSuspendedsales($user_id);
            $this->data['user_id']         = $user_id;

            $this->data['modal_js']        = $this->site->modal_js();
            $this->load->view($this->theme . 'pos/close_register', $this->data);
        }
    }

    public function delete($id =null, $sus_id = null)
    {
        $this->bpas->checkPermissions('index');
        $sus_id = $this->input->get('room');
        if ($this->pos_model->deleteBill($id, $sus_id)) {
            if ($this->input->get('room') != null) {
                $data2 = array(
                    'booking'   => "",
                    'description' => '',
                    'customer_qty' => 1 );
                $this->db->update('suspended_note', $data2, array('note_id' => $this->input->get('room')));
            }
            $this->bpas->send_json(['success' => 0, 'msg' => lang('suspended_sale_deleted')]);
        } else {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('table');
        }
    }

    public function delete_printer($id = null)
    {
        if (DEMO) {
            $this->session->set_flashdata('error', $this->lang->line('disabled_in_demo'));
            $this->bpas->md();
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->bpas->md();
        }
        if ($this->input->get('id')) {
            $id = $this->input->get('id', true);
        }
        if ($this->pos_model->deletePrinter($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('printer_deleted')]);
        }
    }

    public function edit_printer($id = null)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('pos');
        }
        if ($this->input->get('id')) {
            $id = $this->input->get('id', true);
        }

        $printer = $this->pos_model->getPrinterByID($id);
        $this->form_validation->set_rules('title', $this->lang->line('title'), 'required');
        $this->form_validation->set_rules('type', $this->lang->line('type'), 'required');
        $this->form_validation->set_rules('profile', $this->lang->line('profile'), 'required');
        $this->form_validation->set_rules('char_per_line', $this->lang->line('char_per_line'), 'required');
        if ($this->input->post('type') == 'network') {
            $this->form_validation->set_rules('ip_address', $this->lang->line('ip_address'), 'required');
            if ($this->input->post('ip_address') != $printer->ip_address) {
                $this->form_validation->set_rules('ip_address', $this->lang->line('ip_address'), 'is_unique[printers.ip_address]');
            }
            $this->form_validation->set_rules('port', $this->lang->line('port'), 'required');
        } else {
            $this->form_validation->set_rules('path', $this->lang->line('path'), 'required');
            if ($this->input->post('path') != $printer->path) {
                $this->form_validation->set_rules('path', $this->lang->line('path'), 'is_unique[printers.path]');
            }
        }

        if ($this->form_validation->run() == true) {
            $data = ['title'    => $this->input->post('title'),
                'type'          => $this->input->post('type'),
                'profile'       => $this->input->post('profile'),
                'stock_type'          => $this->input->post('stock_type'),
                'char_per_line' => $this->input->post('char_per_line'),
                'path'          => $this->input->post('path'),
                'ip_address'    => $this->input->post('ip_address'),
                'port'          => ($this->input->post('type') == 'network') ? $this->input->post('port') : null,
            ];
        }

        if ($this->form_validation->run() == true && $this->pos_model->updatePrinter($id, $data)) {
            $this->session->set_flashdata('message', $this->lang->line('printer_updated'));
            admin_redirect('pos/printers');
        } else {
            $this->data['printer']    = $printer;
            $this->data['stock_types'] = $this->site->getAllStockTypes();
            $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('edit_printer');
            $bc                       = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('pos'), 'page' => lang('pos')], ['link' => admin_url('pos/printers'), 'page' => lang('printers')], ['link' => '#', 'page' => lang('edit_printer')]];
            $meta                     = ['page_title' => lang('edit_printer'), 'bc' => $bc];
            $this->page_construct('pos/edit_printer', $meta, $this->data);
        }
    }

    public function email_receipt($sale_id = null, $view = null)
    {
        $this->bpas->checkPermissions('index');
        if ($this->input->post('id')) {
            $sale_id = $this->input->post('id');
        }
        if (!$sale_id) {
            die('No sale selected.');
        }
        if ($this->input->post('email')) {
            $to = $this->input->post('email');
        }
        $this->data['error']   = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');

        $this->data['rows']            = $this->pos_model->getAllInvoiceItems($sale_id);
        $inv                           = $this->pos_model->getInvoiceByID($sale_id);
        $biller_id                     = $inv->biller_id;
        $customer_id                   = $inv->customer_id;
        $this->data['biller']          = $this->pos_model->getCompanyByID($biller_id);
        $this->data['customer']        = $this->pos_model->getCompanyByID($customer_id);
        $this->data['payments']        = $this->pos_model->getInvoicePayments($sale_id);
        $this->data['pos']             = $this->pos_model->getSetting();
        $this->data['barcode']         = $this->barcode($inv->reference_no, 'code128', 30);
        $this->data['return_sale']     = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows']     = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : null;
        $this->data['inv']             = $inv;
        $this->data['sid']             = $sale_id;
        $this->data['created_by']      = $this->site->getUser($inv->created_by);
        $this->data['page_title']      = $this->lang->line('invoice');

        $receipt = $this->load->view($this->theme . 'pos/email_receipt', $this->data, true);
        if ($view) {
            echo $receipt;
            die();
        }

        if (!$to) {
            $to = $this->data['customer']->email;
        }
        if (!$to) {
            $this->bpas->send_json(['msg' => $this->lang->line('no_meil_provided')]);
        }

        try {
            if ($this->bpas->send_email($to, lang('receipt_from') . ' ' . $this->data['biller']->company, $receipt)) {
                $this->bpas->send_json(['msg' => $this->lang->line('email_sent')]);
            } else {
                $this->bpas->send_json(['msg' => $this->lang->line('email_failed')]);
            }
        } catch (Exception $e) {
            $this->bpas->send_json(['msg' => $e->getMessage()]);
        }
    }

    public function get_printers()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->bpas->md();
        }

        $this->load->library('datatables');
        $this->datatables
        ->select('id, title, type, profile, path, ip_address, port')
        ->from('printers')
        ->add_column('Actions', "<div class='text-center'> <a href='" . admin_url('pos/edit_printer/$1') . "' class='btn-warning btn-xs tip' title='" . lang('edit_printer') . "'><i class='fa fa-edit'></i></a> <a href='#' class='btn-danger btn-xs tip po' title='<b>" . lang('delete_printer') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('pos/delete_printer/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id')
        ->unset_column('id');
        echo $this->datatables->generate();
    }

    public function getProductDataByCode($code = null, $warehouse_id = null)
    {
        $this->bpas->checkPermissions('index');
        if ($this->input->get('code')) {
            $code = $this->input->get('code', true);
        }
        if ($this->input->get('warehouse_id')) {
            $warehouse_id = $this->input->get('warehouse_id', true);
        }
        if ($this->input->get('customer_id')) {
            $customer_id = $this->input->get('customer_id', true);
        }
        if (!$code) {
            echo null;
            die();
        }
        $warehouse      = $this->site->getWarehouseByID($warehouse_id);
        $customer       = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $row            = $this->pos_model->getWHProduct($code, $warehouse_id);
        $option         = false;
        $discount_promotion = 0;
        if ($row) {
            $promotions = $this->promos_model->getPromotionByProduct($warehouse_id, $row->category_id, $row->id);
            if ($promotions) {
                foreach ($promotions as $promotion) {
                    $discount_promotion = $promotion->discount;
                }
            }
            unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
            $row->item_tax_method = $row->tax_method;
            $row->qty             = 1;
            $row->discount        = 0;          
            if ($discount_promotion) {
                $row->discount    = $discount_promotion;
            } else if ($this->Settings->customer_group_discount == 2 && !empty($customer_group)) {
                $row->discount    = (-1 * $customer_group->percent) . "%";
            }
            $row->serial          = '';
            $options              = $this->pos_model->getProductOptions($row->id, $warehouse_id);
            if ($options) {
                $opt = current($options);
                if (!$option) {
                    $option = $opt->id;
                }
            } else {
                $opt        = json_decode('{}');
                $opt->price = 0;
            }
            $row->option   = $option;
            $row->quantity = 0;
            $pis = $this->site->getStockMovement_ProductBalanceQuantity($row->id, $warehouse_id, $row->option);
            if ($pis) {
                $row->quantity = $pis->quantity_balance;
            }
            if ($row->type == 'standard' && (!$this->Settings->overselling && $row->quantity < 1)) {
                echo null;
                die();
            }
            if ($options) {
                $option_quantity = 0;
                foreach ($options as $option) {
                    $pis = $this->site->getStockMovement_ProductBalanceQuantity($row->id, $warehouse_id, $row->option);
                    if ($pis) {
                        $option_quantity = $pis->quantity_balance;
                    }
                    if ($option->quantity > $option_quantity) {
                        $option->quantity = $option_quantity;
                    }
                }
            }
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
            // $row->price          = $row->price + (($row->price * $customer_group->percent) / 100);
            $row->price           = $row->price;
            $row->real_unit_price = $row->price;
            $row->base_quantity   = 1;
            $row->base_unit       = $row->unit;
            $row->base_unit_price = $row->price;
            $row->unit            = $row->sale_unit ? $row->sale_unit : $row->unit;
            $row->comment         = '';
            $combo_items          = false;
            if ($row->type == 'combo') {
                $combo_items = $this->pos_model->getProductComboItems($row->id, $warehouse_id);
            }
            $units     = $this->site->getUnitsByBUID($row->base_unit);
            $tax_rate  = $this->site->getTaxRateByID($row->tax_rate);
            $set_price = $this->site->getUnitByProId($row->id);
            $pr = ['id' => sha1(uniqid(mt_rand(), true)), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'category' => $row->category_id, 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'set_price'=> $set_price, 'units' => $units, 'options' => $options];
            $this->bpas->send_json($pr);
        } else {
            echo null;
        }
    }

     public function getProductAddOnDataByCode($code = null, $warehouse_id = null)
    {
        $this->bpas->checkPermissions('index');
        if ($this->input->get('code')) {
            $code = $this->input->get('code', true);
        }
        if ($this->input->get('warehouse_id')) {
            $warehouse_id = $this->input->get('warehouse_id', true);
        }
        if ($this->input->get('customer_id')) {
            $customer_id = $this->input->get('customer_id', true);
        }
         if ($this->input->get('addon_id')) {
            $addon_id = $this->input->get('addon_id', true);
        }
        if (!$code) {
            echo null;
            die();
        }
        $warehouse      = $this->site->getWarehouseByID($warehouse_id);
        $customer       = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $row            = $this->pos_model->getWHProduct($code, $warehouse_id);
        $option         = false;
        $discount_promotion = 0;
        if ($row) {
            $item_addOn = $this->products_model->getProductAddOnItem($addon_id);
            $promotions = $this->promos_model->getPromotionByProduct($warehouse_id, $row->category_id,$row->id);
            // $addon_item = $this->products_model->getProductsAddOnItem
            if($promotions){
                foreach ($promotions as $promotion) {
                    $discount_promotion = $promotion->discount;
                }
            }
            unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
            $row->item_tax_method = $row->tax_method;
            $row->qty             = 1;
            $row->discount        =  $discount_promotion ?  $discount_promotion : '0';
            $row->serial          = '';
            $options              = $this->pos_model->getProductOptions($row->id, $warehouse_id);
            if ($options) {
                $opt = current($options);
                if (!$option) {
                    $option = $opt->id;
                }
            } else {
                $opt        = json_decode('{}');
                $opt->price = 0;
            }
            $row->option   = $option;
            $row->quantity = 0;
            $pis = $this->site->getStockMovement_ProductBalanceQuantity($row->id, $warehouse_id, $row->option);
            if ($pis) {
                $row->quantity = $pis->quantity_balance;
            }
            if ($row->type == 'standard' && (!$this->Settings->overselling && $row->quantity < 1)) {
                echo null;
                die();
            }
            if ($options) {
                $option_quantity = 0;
                foreach ($options as $option) {
                    $pis = $this->site->getStockMovement_ProductBalanceQuantity($row->id, $warehouse_id, $row->option);
                    if ($pis) {
                        $option_quantity = $pis->quantity_balance;
                    }
                    if ($option->quantity > $option_quantity) {
                        $option->quantity = $option_quantity;
                    }
                }
            }
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
            $row->price           = $item_addOn->price + (($item_addOn->price * $customer_group->percent) / 100);
            $row->real_unit_price = $item_addOn->price;
            $row->base_quantity   = 1;
            $row->base_unit       = $row->unit;
            $row->base_unit_price = $item_addOn->price;
            $row->unit            = $row->sale_unit ? $row->sale_unit : $row->unit;
            $row->comment         = '';
            $combo_items          = false;
            if ($row->type == 'combo') {
                $combo_items = $this->pos_model->getProductComboItems($row->id, $warehouse_id);
            }
            $units     = $this->site->getUnitsByBUID($row->base_unit);
            $tax_rate  = $this->site->getTaxRateByID($row->tax_rate);
            $set_price = $this->site->getUnitByProId($row->id);
            $pr = ['id' => sha1(uniqid(mt_rand(), true)), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'category' => $row->category_id, 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'set_price'=> $set_price, 'units' => $units, 'options' => $options];
            $this->bpas->send_json($pr);
        } else {
            echo null;
        }
    }

    public function getProductToBuy($pId = null, $warehouse_id = null,$qty = null,$positems = null)
    {
        $this->bpas->checkPermissions('index');
        $this->load->admin_model('promos_model');
        if ($this->input->get('qty')) {
            $qty = $this->input->get('qty', true);
        }
        $positems = $this->input->get('positems');
         $promosBypos = $this->promos_model->getPromosItemByID($positems['id'], $qty);
        $this->bpas->send_json($promosBypos);

        if ($this->input->get('product_id')) {
            $pId = $this->input->get('product_id', true);
        }
        if ($this->input->get('warehouse_id')) {
            $warehouse_id = $this->input->get('warehouse_id', true);
        }
        $promos = $this->promos_model->getPromosItemByID($pId, $qty);
        if ($promos) {
            $c = rand(100000, 9999999);
            foreach ($promos as $promo) {
                $warehouse = $this->site->getWarehouseByID($warehouse_id);
                // $array2get = explode(',',$promo->product2get);
                // $sizeof = sizeof($array2get);
                // foreach ($array2get as $ID2get) {
                // for($i=1; $i<= $sizeof; $i++){
                $row       = $this->pos_model->getWHProductById($promo->product_id, $warehouse_id);
                $option    = false;
                if ($row) {
                    unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                    $row->item_tax_method = $row->tax_method;
                    $row->qty             = $promo->qty;
                    $row->price           = 0;
                    $row->discount        = '0';
                    $row->serial          = '';
                    $options              = $this->pos_model->getProductOptions($row->id, $warehouse_id);
                    if ($options) {
                        $opt = current($options);
                        if (!$option) {
                            $option = $opt->id;
                        }
                    }
                    $row->option          = $option;
                    $row->real_unit_price = $row->price;
                    $row->base_quantity   = $promo->qty;
                    $row->base_unit       = $row->unit;
                    $row->base_unit_price = $row->price;
                    $row->unit            = $row->sale_unit ? $row->sale_unit : $row->unit;
                    $row->comment         = '';
                    $combo_items          = false;
                    // if ($row->type == 'combo') {
                    //     $combo_items = $this->pos_model->getProductComboItems($row->id, $warehouse_id);
                    // }
                    $units    = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate = false; // 
                    $this->site->getTaxRateByID($row->tax_rate);
                    $ri = $this->Settings->item_addition ? $row->id : $c;
                    $set_price = $this->site->getUnitByProId($row->id);
                    $pr[] = ['id' => sha1(uniqid(mt_rand(), true)), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'category' => $row->category_id, 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'set_price'=>$set_price, 'units' => $units, 'options' => $options];
                    
                    // $this->bpas->send_json2($pr);
                } else {
                    echo null;
                } 
                $this->bpas->send_json($pr);
                // }                 
            }
        } else {
            echo null;
        }
    }  

    public function getProductPromo($pId = null, $warehouse_id = null,$qty = null)
    {
        $this->bpas->checkPermissions('index');
        if ($this->input->get('product_id')) {
            $pId = $this->input->get('product_id', true);
        }
        if ($this->input->get('qty')) {
            $qty = $this->input->get('qty', true);
        }
        if ($this->input->get('warehouse_id')) {
            $warehouse_id = $this->input->get('warehouse_id', true);
        }
        $this->load->admin_model('promos_model');
        $promos = $this->promos_model->getPromosByProduct($pId, $qty);
        if ($promos) {
            $c = rand(100000, 9999999);
            foreach ($promos as $promo) {
                $warehouse = $this->site->getWarehouseByID($warehouse_id);
                $row       = $this->pos_model->getWHProductById($promo->product_id, $warehouse_id);
                $option    = false;
                if ($row) {
                    unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                    $row->item_tax_method = $row->tax_method;
                    $row->qty             = $promo->qty;
                    $row->price           = 0;
                    $row->discount        = '0';
                    $row->serial          = '';
                    $options              = $this->pos_model->getProductOptions($row->id, $warehouse_id);
                    if ($options) {
                        $opt = current($options);
                        if (!$option) {
                            $option = $opt->id;
                        }
                    }
                    $row->currency        = $row->currency;
                    $row->option          = $option;
                    $row->real_unit_price = $row->price;
                    $row->base_quantity   = $promo->qty;
                    $row->base_unit       = $row->unit;
                    $row->base_unit_price = $row->price;
                    $row->unit            = $row->sale_unit ? $row->sale_unit : $row->unit;
                    $row->comment         = '';
                    $combo_items          = false;
                    $tax_rate             = false; 
                    $units                = $this->site->getUnitsByBUID($row->base_unit);
                    $ri = $this->Settings->item_addition ? $row->id : $c;
                    $set_price = $this->site->getUnitByProId($row->id);
                    $pr[] = ['id' => sha1(uniqid(mt_rand(), true)), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'category' => $row->category_id, 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'set_price'=>$set_price, 'units' => $units, 'options' => $options];
                } else {
                    echo null;
                }
            }
            $this->bpas->send_json($pr);

        } else {
            echo null;
        }
    }     

    public function getSalesPrinting($warehouse_id = null)
    {
        $this->bpas->checkPermissions('index');

        $product_id   = $this->input->get('product_id') ? $this->input->get('product_id') : null;
        $user_query   = $this->input->get('user') ? $this->input->get('user') : null;
        $customer     = $this->input->get('customer') ? $this->input->get('customer') : null;
        $biller       = $this->input->get('biller') ? $this->input->get('biller') : null;
        $warehouse    = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : null;
        $start_date   = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date     = $this->input->get('end_date') ? $this->input->get('end_date') : null;
        $serial       = $this->input->get('serial') ? $this->input->get('serial') : null;
        $project      = $this->input->get('project') ? $this->input->get('project') : null;
        if ($start_date) {
            $start_date = $this->bpas->fld($start_date . ' 00:00:00');
            $end_date   = $this->bpas->fld($end_date . ' 23:59:00');
        }
        if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
            $user         = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $duplicate_link    = anchor('admin/pos/?duplicate=$1', '<i class="fa fa-plus-square"></i> ' . lang('duplicate_sale'), 'class="duplicate_pos"');
        $detail_link       = anchor('admin/pos/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_receipt'));
        $detail_link2      = anchor('admin/sales/modal_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details_modal'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $detail_link3      = anchor('admin/sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        $payments_link     = anchor('admin/sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_payment_link  = anchor('admin/pos/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $packagink_link    = anchor('admin/sales/packaging/$1', '<i class="fa fa-archive"></i> ' . lang('packaging'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_delivery_link = anchor('admin/sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $email_link        = anchor('admin/#', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'class="email_receipt" data-id="$1" data-email-address="$2"');
        $edit_link         = anchor('admin/sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $return_link       = anchor('admin/sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        $delete_link       = "<a href='#' class='po' title='<b>" . lang('delete_sale') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_sale') . '</a>';

        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $duplicate_link . '</li>
                <li>' . $detail_link . '</li>
                <li>' . $detail_link2 . '</li>
                <li>' . $detail_link3 . '</li>
                <li>' . $payments_link . '</li>
                <li>' . $add_payment_link . '</li>
                <li>' . $packagink_link . '</li>
                <li>' . $add_delivery_link . '</li>
                <li>' . $edit_link . '</li>
                <li>' . $email_link . '</li>
                <li>' . $return_link . '</li>
                <li>' . $delete_link . '</li>
            </ul>
        </div></div>';
        $action = "";
        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select($this->db->dbprefix('sales') . ".id as id, DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date, reference_no,(grand_total+COALESCE(rounding, 0)),total_discount,CONCAT(grand_total, '__', rounding, '__', paid) as balance")
                ->from('sales')
                ->join('companies', 'companies.id=sales.customer_id', 'left')
                ->where('sales.warehouse_id', $warehouse_id)
                ->group_by('sales.id');
        } else {
            $this->datatables
                ->select($this->db->dbprefix('sales') . ".id as id,
                 DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date, 
                 reference_no,
                 (grand_total+COALESCE(rounding, 0)),
                 total_discount,
                 CONCAT(grand_total, '__', rounding, '__', paid) as balance")
                ->from('sales')
                ->join('companies', 'companies.id=sales.customer_id', 'left')
                ->group_by('sales.id');
        }
        $this->datatables->where('pos', 1);
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('bpas_sales.created_by', $this->session->userdata('user_id'));
            // $this->datatables->or_where("FIND_IN_SET(bpas_sales.warehouse_id, '" . $warehouse_id . "')");
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        if ($user_query) {
            $this->datatables->where('sales.created_by', $user_query);
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
        if ($warehouse) {
            $this->datatables->where('sales.warehouse_id', $warehouse);
        }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('sales') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        $this->datatables->add_column('Actions', $action, 'id, cemail')->unset_column('cemail');

        echo $this->datatables->generate();
    }

    function saveimage()
    {
        $data = ['note'=>$this->input->get('titile'),];
        $this->db->insert('atest', $data);
        return "te3st";

    }
    public function add_making($id) 
    {
        if ($this->pos_model->addMarking($id)) {
            $this->session->set_flashdata('message', lang('created_marking_successfully'));
            admin_redirect('pos/making');
        }
    }

    public function loadRecord($rowno = 0)
    {
        $this->load->library('pagination');
         $setting = $this->pos_model->getSetting();
        $rowperpage = $setting->show_category;
        // $rowperpage = 5 ;
        // Row position
        if ($rowno != 0) {
            $rowno = ($rowno - 1) * $rowperpage;
            // $rowno = 4;
        }
        // All records count
        $allcount =  $this->site->countCategories();
        // Get records
        $users_record = $this->site->fetch_categories($rowno, $rowperpage);
        $categories   = $this->site->getNestedCategories($rowno, $rowperpage);
        // Pagination Configuration
        $config['base_url'] = admin_url("pos/index");
        $config['use_page_numbers'] = TRUE;
        $config['total_rows'] = $allcount;
        $config['per_page'] = $rowperpage;

        // Initialize
        $this->pagination->initialize($config);

        // Initialize $data Array
        $data['pagination'] = $this->pagination->create_links1();
        $data['categories'] = $categories;
        $data['result'] = $users_record;
        $data['row'] = $rowno;

        echo json_encode($data);
    }

    public function index($sid = NULL,$card_id=null) 
    {    
        $user=$this->session->userdata();
        $this->bpas->checkPermissions();
        $getexchange_khm = $this->bpas->getExchange_rate('KHR');
        $getexchange_bat = $this->bpas->getExchange_rate('THB');
        $exchange_khm    = isset($getexchange_khm->rate) ? $getexchange_khm->rate : 1; 
        $exchange_bat    = isset($getexchange_bat->rate) ? $getexchange_bat->rate : 1;
        if (!$this->pos_settings->default_biller || !$this->pos_settings->default_customer || !$this->pos_settings->default_category) {
            $this->session->set_flashdata('warning', lang('please_update_settings'));
            admin_redirect('pos/settings');
        }
        if ($register = $this->pos_model->registerData($this->session->userdata('user_id'))) {
            $register_data = array('register_id' => $register->id, 'cash_in_hand' => $register->cash_in_hand, 'register_open_time' => $register->date);
            $this->session->set_userdata($register_data);
        } else {
            $this->session->set_flashdata('error', lang('register_not_open'));
            admin_redirect('pos/open_register');
        }
        $this->data['sid'] = $this->input->get('suspend_id') ? $this->input->get('suspend_id') : $sid;
        $did            = $this->input->post('delete_id') ? $this->input->post('delete_id') : NULL;
        $suspend        = $this->input->post('suspend') ? TRUE : FALSE;
        $count          = $this->input->post('count') ? $this->input->post('count') : NULL;
        $duplicate_sale = $this->input->get('duplicate') ? $this->input->get('duplicate') : NULL;
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'trim|required');
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required');
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
        if ($this->form_validation->run() == TRUE) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->input->post('date') ? $this->bpas->fld(trim($this->input->post('date'))) : date('Y-m-d H:i:s');
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id         = $this->input->post('warehouse');
            $customer_id          = $this->input->post('customer');
            $biller_id            = $this->input->post('biller');
            $total_items          = $this->input->post('total_items');
            $project_id           = $this->input->post('project_1');
            $saleman              = $this->input->post('saleman_1');
            $delivery_by          = $this->input->post('delivery_by_1');
            $customer_qty         = $this->input->post('customer_qty');
            $sale_status          = 'completed';
            $payment_status       = 'due';
            $payment_term         = 0;
            $due_date             = date('Y-m-d', strtotime('+' . $payment_term . ' days'));
            $shipping             = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details     = $this->site->getCompanyByID($customer_id);
            $customer             = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $biller_details       = $this->site->getCompanyByID($biller_id);
            $biller               = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note                 = $this->bpas->clear_tags($this->input->post('pos_note'));
            $staff_note           = $this->bpas->clear_tags($this->input->post('staff_note'));
            $reference            = $this->site->getReference('pos', $biller_details->code);
            $total_original_price = 0;
            $total                = 0;
            $product_tax          = 0;
            $item_price_original  = 0;
            $default_total_price  = 0;
            $product_discount     = 0;
            $digital              = FALSE;
            $stockmoves           = null;
            $total_weight         = 0;
            $gst_data             = [];
            $accTranPayments      = [];
            $text_items           = '';
            $total_cgst           = $total_sgst = $total_igst = 0;
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_code = $_POST['product_code'][$r];

                    $item_id        = $_POST['product_id'][$r];
                    $item_type      = $_POST['product_type'][$r];
                    $item_code      = $_POST['product_code'][$r];
                    $item_name      = $_POST['product_name'][$r];
                    if ($item_id != 0) {
                        $item_original = $this->site->getProductByID($item_id);
                    }
                    $item_comment        = $_POST['product_comment'][$r];
                    $item_option         = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : NULL;
                    $real_unit_price     = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                    $unit_price          = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                  
                    $item_unit_quantity  = $_POST['quantity'][$r];
                    $item_serial         = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                    $item_tax_rate       = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : NULL;
                    $item_discount       = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : NULL;
                    $item_unit           = $_POST['product_unit'][$r];
                    $item_quantity       = $_POST['product_base_quantity'][$r];
                    $item_weight         = isset($_POST['product_weight'][$r])? $_POST['product_weight'][$r] : 0;
                    $saleman_item        = isset($_POST['saleman_item'][$r]) ? $_POST['saleman_item'][$r] : '';

                    if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                        $product_details    = $this->pos_model->getProductByCode($item_code);
                        // $unit_price      = $real_unit_price;
                        if ($product_details->cost) {
                           $cost = $product_details->cost; 
                        }
                        if ($item_type == 'digital') {
                            $digital = TRUE;
                        }
                        $pr_discount        = $this->site->calculateDiscount($item_discount, $unit_price);
                        $pr_discount        = $this->bpas->formatDecimal($pr_discount);
                        $unit_price         = $this->bpas->formatDecimal($unit_price - $pr_discount);
                        $item_net_price     = $unit_price;
                        $pr_item_discount   = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                        $product_discount   += $pr_item_discount;
                        $pr_item_tax        = $item_tax = 0;
                        $tax                = "";
                        if (isset($item_tax_rate) && $item_tax_rate != 0) {
                            $tax_details    = $this->site->getTaxRateByID($item_tax_rate);
                            $ctax           = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                            $item_tax       = $ctax['amount'];
                            $tax            = $ctax['tax'];
                            if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                                $item_net_price = $unit_price - $item_tax;
                            }
                            $pr_item_tax = $this->bpas->formatDecimal(($item_tax * $item_unit_quantity));
                        }
                        $product_tax   += $pr_item_tax;
                        $subtotal       = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                        $total_weight   = $item_weight * $item_unit_quantity;
                        $unit           = $this->site->getUnitByID($item_unit);
                        $purchase_unit_cost = $product_details->cost;
                        if ($unit->id != $product_details->unit) {
                            $cost = $this->site->convertCostingToBase($purchase_unit_cost, $unit);
                        } else {
                            $cost = $cost;
                        }
                        $addon_products = [];
                        $combo_products = json_decode($_POST['product_combo'][$r]);
                        if ($product_details->type == 'combo' && $combo_products) {
                            $price_combo = 0;
                            $qty_combo   = count($combo_products);
                            $dicount     = 0;
                            foreach ($combo_products as $combo_product) {
                                $price_combo += $combo_product->price * $combo_product->qty;
                            }
                            if ($this->bpas->formatDecimal($price_combo) <> $this->bpas->formatDecimal($item_net_price)) {
                                $dicount = (($price_combo - $item_net_price) * 100) / $price_combo;
                            }
                            $product_combo_cost = 0;
                            foreach ($combo_products as $combo_product) {
                                $combo_id    = $combo_product->id;
                                $combo_code  = $combo_product->code;
                                $combo_name  = $combo_product->name;
                                $combo_qty   = $combo_product->qty * $item_quantity;
                                $combo_price = $combo_product->price;
                                if ($dicount > 0) {
                                    $combo_price = $combo_price - (($combo_price * $dicount) / 100);
                                } else if ($dicount < 0) {
                                    $combo_price = $combo_price + (($combo_price * abs($dicount)) / 100);
                                }
                                if ($price_combo == 0 && $item_net_price > 0) {
                                    $combo_price = $item_net_price / $qty_combo;
                                }
                                $combo_detail = $this->site->getProductByID($combo_id);
                                if ($combo_detail) {
                                    $combo_unit = $this->site->getProductUnit($combo_id, $combo_detail->unit);
                                    if ($this->Settings->accounting_method == '0') {
                                        $costs = $this->site->getFifoCost($combo_id, $combo_qty, $stockmoves);
                                    } else if ($this->Settings->accounting_method == '1') {
                                        $costs = $this->site->getLifoCost($combo_id, $combo_qty, $stockmoves);
                                    } else if ($this->Settings->accounting_method == '3') {
                                        $costs = $this->site->getProductMethod($combo_id, $combo_qty, $stockmoves);
                                    }
                                    if (isset($costs) && $costs) {
                                        $item_cost_qty   = 0;
                                        $item_cost_total = 0;
                                        $item_costs      = '';
                                        foreach ($costs as $cost_item) {
                                            $item_cost_qty   += $cost_item['quantity'];
                                            $item_cost_total += $cost_item['cost'] * $cost_item['quantity'];
                                            $stockmoves[] = array(
                                                'transaction'    => 'Sale',
                                                'product_id'     => $combo_detail->id,
                                                'product_type'   => $combo_detail->type,
                                                'product_code'   => $combo_detail->code,
                                                'product_name'   => $combo_detail->name,
                                                'quantity'       => $cost_item['quantity'] * (-1),
                                                'expiry'         => null,
                                                'unit_quantity'  => $combo_unit->unit_qty,
                                                'weight'         => $total_weight * (-1),
                                                'unit_code'      => $combo_unit->code,
                                                'unit_id'        => $combo_detail->unit,
                                                'warehouse_id'   => $warehouse_id,
                                                'date'           => $date,
                                                'real_unit_cost' => $cost_item['cost'],
                                                'reference_no'   => $reference,
                                                'user_id'        => $this->session->userdata('user_id'),
                                            );
                                            //========accounting=========//
                                            if ($this->Settings->module_account == 1 && $sale_status == 'completed') {
                                                $getproduct    = $this->site->getProductByID($combo_detail->id);
                                                $productAcc    = $this->site->getProductAccByProductId($combo_detail->id);
                                                $default_sale  = $this->site->AccountByBiller('default_sale', $biller_id);
                                                $inventory_acc = $this->site->AccountByBiller('default_stock', $biller_id);
                                                $costing_acc   = $this->site->AccountByBiller('default_cost', $biller_id);
                                                $accTrans[] = array(
                                                    'tran_type'     => 'Sale',
                                                    'tran_date'     => $date,
                                                    'reference_no'  => $reference,
                                                    'account_code'  => $inventory_acc,
                                                    'amount'        => -($cost_item['cost'] * $cost_item['quantity']),
                                                    'narrative'     => $this->site->getAccountName($inventory_acc),
                                                    'description'   => $note,
                                                    'biller_id'     => $biller_id,
                                                    'project_id'    => $project_id,
                                                    'customer_id'   => $customer_id,
                                                    'created_by'    => $this->session->userdata('user_id'),
                                                );
                                                $accTrans[] = array(
                                                    'tran_type'     => 'Sale',
                                                    'tran_date'     => $date,
                                                    'reference_no'  => $reference,
                                                    'account_code'  => $costing_acc,
                                                    'amount'        => ($cost_item['cost'] * $cost_item['quantity']),
                                                    'narrative'     => $this->site->getAccountName($costing_acc),
                                                    'description'   => $note,
                                                    'biller_id'     => $biller_id,
                                                    'project_id'    => $project_id,
                                                    'customer_id'   => $customer_id,
                                                    'created_by'    => $this->session->userdata('user_id'),
                                                    'activity_type' => $this->site->get_activity($this->accounting_setting->default_cost)
                                                );
                                            }
                                            //============end accounting=======//
                                            $item_costs .='#'.$cost_item['cost'].'='.$cost_item['quantity'];
                                        }
                                        $productAcc = $this->site->getProductAccByProductId($combo_id);
                                        $default_sale  = ($combo_detail->type == 'standard') ? $this->accounting_setting->default_sale : $this->accounting_setting->other_income;
                                        $accTrans[] = array(
                                            'tran_type'     => 'Sale',
                                            'tran_date'     => $date,
                                            'reference_no'  => $reference,
                                            'account_code'  => $default_sale,
                                            'amount'        => -($combo_price * $combo_qty),
                                            'narrative'     => $this->site->getAccountName($default_sale),
                                            'description'   => $note,
                                            'biller_id'     => $biller_id,
                                            'project_id'    => $project_id,
                                            'customer_id'   => $customer_id,
                                            'created_by'    => $this->session->userdata('user_id'),
                                        );
                                        $product_combo_cost += ($item_cost_total / $item_cost_qty);
                                    } else {
                                        $product_combo_cost += ($combo_qty * $combo_detail->cost);
                                        $stockmoves[] = array(
                                            'transaction'    => 'Sale',
                                            'product_id'     => $combo_detail->id,
                                            'product_type'   => $combo_detail->type,
                                            'product_code'   => $combo_detail->code,
                                            'product_name'   => $combo_detail->name,
                                            'quantity'       => $combo_qty * -1,
                                            'unit_quantity'  => $combo_unit->unit_qty,
                                            'weight'         => $total_weight,
                                            'expiry'         => null,
                                            'unit_code'      => $combo_unit->code,
                                            'unit_id'        => $combo_detail->unit,
                                            'warehouse_id'   => $warehouse_id,
                                            'date'           => $date,
                                            'real_unit_cost' => $combo_detail->cost,
                                            'reference_no'   => $reference,
                                            'user_id'        => $this->session->userdata('user_id'),
                                        );
                                        //=======accounting=========//
                                        if ($this->Settings->module_account == 1 && $sale_status == 'completed') {
                                            $getproduct    = $this->site->getProductByID($combo_detail->id);
                                            $productAcc    = $this->site->getProductAccByProductId($combo_detail->id);
                                            $default_sale  = $this->site->AccountByBiller('default_sale', $biller_id);
                                            $inventory_acc = $this->site->AccountByBiller('default_stock', $biller_id);
                                            $costing_acc   = $this->site->AccountByBiller('default_cost', $biller_id);
                                            $accTrans[] = array(
                                                'tran_type'     => 'Sale',
                                                'tran_date'     => $date,
                                                'reference_no'  => $reference,
                                                'account_code'  => $inventory_acc,
                                                'amount'        => -($combo_detail->cost * $combo_qty),
                                                'narrative'     => $this->site->getAccountName($inventory_acc),
                                                'description'   => $note,
                                                'biller_id'     => $biller_id,
                                                'project_id'    => $project_id,
                                                'customer_id'   => $customer_id,
                                                'created_by'    => $this->session->userdata('user_id'),
                                            );
                                            $accTrans[] = array(
                                                'tran_type'     => 'Sale',
                                                'tran_date'     => $date,
                                                'reference_no'  => $reference,
                                                'account_code'  => $costing_acc,
                                                'amount'        => ($combo_detail->cost * $combo_qty),
                                                'narrative'     => $this->site->getAccountName($costing_acc),
                                                'description'   => $note,
                                                'biller_id'     => $biller_id,
                                                'project_id'    => $project_id,
                                                'customer_id'   => $customer_id,
                                                'created_by'    => $this->session->userdata('user_id'),
                                                'activity_type' => $this->site->get_activity($costing_acc)
                                            );
                                            $accTrans[] = array(
                                                'tran_type'     => 'Sale',
                                                'tran_date'     => $date,
                                                'reference_no'  => $reference,
                                                'account_code'  => $default_sale,
                                                'amount'        => -($combo_price * $combo_qty),
                                                'narrative'     => $this->site->getAccountName($default_sale),
                                                'description'   => $note,
                                                'biller_id'     => $biller_id,
                                                'project_id'    => $project_id,
                                                'customer_id'   => $customer_id,
                                                'created_by'    => $this->session->userdata('user_id'),
                                            );
                                        }
                                        //============end accounting=======//
                                    }
                                    $raw_materials[] = array(
                                        "product_id" => $combo_detail->id,
                                        "quantity"   => $combo_qty
                                    );
                                }
                            }
                            $cost  = $product_combo_cost;
                        } else {
                            if ($this->Settings->accounting_method == '0') {
                                $costs = $this->site->getFifoCost($item_id, $item_quantity, $stockmoves);
                            } else if ($this->Settings->accounting_method == '1') {
                                $costs = $this->site->getLifoCost($item_id, $item_quantity, $stockmoves);
                            } else if ($this->Settings->accounting_method == '3') {
                                $costs = $this->site->getProductMethod($item_id, $item_quantity, $stockmoves);
                            }
                            if (isset($costs) && $costs && $item_quantity > 0) {
                                $item_cost_qty   = 0;
                                $item_cost_total = 0;
                                $item_costs      = '';
                                foreach ($costs as $cost_item) {
                                    $item_cost_qty   += $cost_item['quantity'];
                                    $item_cost_total += $cost_item['cost'] * $cost_item['quantity'];
                                    $stockmoves[] = array(
                                        'transaction'    => 'Sale',
                                        'product_id'     => $item_id,
                                        'product_type'   => $item_type,
                                        'product_code'   => $item_code,
                                        'product_name'   => $item_name,
                                        'option_id'      => $item_option,
                                        'quantity'       => $cost_item['quantity'] * (-1),
                                        'unit_quantity'  => !empty($unit->operation_value) ? $unit->operation_value : 1,
                                        'weight'         => $total_weight * (-1),
                                        'expiry'         => $item_expiry,
                                        'unit_code'      => $unit->code,
                                        'unit_id'        => $item_unit,
                                        'warehouse_id'   => $warehouse_id,
                                        'date'           => $date,
                                        'real_unit_cost' => $cost_item['cost'],
                                        'serial_no'      => $item_serial,
                                        'reference_no'   => $reference,
                                        'user_id'        => $this->session->userdata('user_id'),
                                    );
                                    //========accounting=========//
                                    if ($this->Settings->module_account == 1 && $item_type != 'manual' && $sale_status == 'completed') {
                                        $getproduct    = $this->site->getProductByID($item_id);
                                        $productAcc    = $this->site->getProductAccByProductId($item_id);
                                        $default_sale  = $this->site->AccountByBiller('default_sale', $biller_id);
                                        $inventory_acc = $this->site->AccountByBiller('default_stock', $biller_id);
                                        $costing_acc   = $this->site->AccountByBiller('default_cost', $biller_id);
                                        $accTrans[] = array(
                                            'tran_type'     => 'Sale',
                                            'tran_date'     => $date,
                                            'reference_no'  => $reference,
                                            'account_code'  => $inventory_acc,
                                            'amount'        => -($cost_item['cost'] * $cost_item['quantity']),
                                            'narrative'     => $this->site->getAccountName($inventory_acc),
                                            'description'   => $note,
                                            'biller_id'     => $biller_id,
                                            'project_id'    => $project_id,
                                            'customer_id'   => $customer_id,
                                            'created_by'    => $this->session->userdata('user_id'),
                                        );
                                        $accTrans[] = array(
                                            'tran_type'     => 'Sale',
                                            'tran_date'     => $date,
                                            'reference_no'  => $reference,
                                            'account_code'  => $costing_acc,
                                            'amount'        => ($cost_item['cost'] * $cost_item['quantity']),
                                            'narrative'     => $this->site->getAccountName($costing_acc),
                                            'description'   => $note,
                                            'biller_id'     => $biller_id,
                                            'project_id'    => $project_id,
                                            'customer_id'   => $customer_id,
                                            'created_by'    => $this->session->userdata('user_id'),
                                            'activity_type' => $this->site->get_activity($costing_acc)
                                        );
                                    }
                                    //============end accounting=======//
                                    $item_costs .='#'.$cost_item['cost'].'='.$cost_item['quantity'];
                                }
                                $cost = $item_cost_total / $item_cost_qty;
                            } else {
                                $stockmoves[] = array(
                                    'transaction'    => 'Sale',
                                    'product_id'     => $item_id,
                                    'product_type'   => $item_type,
                                    'product_code'   => $item_code,
                                    'product_name'   => $item_name,
                                    'option_id'      => $item_option,
                                    'quantity'       => $item_quantity * (-1),
                                    'unit_quantity'  => !empty($unit->operation_value) ? $unit->operation_value : 1,
                                    'weight'         => $total_weight * (-1),
                                    'expiry'         => $item_expiry,
                                    'unit_code'      => $unit->code,
                                    'unit_id'        => $item_unit,
                                    'warehouse_id'   => $warehouse_id,
                                    'date'           => $date,
                                    'real_unit_cost' => ($item_quantity < 0 ? (($item_net_price + $item_tax) / (!empty($unit->operation_value) ? $unit->operation_value : 1)) : $cost),
                                    'serial_no'      => $item_serial,
                                    'reference_no'   => $reference,
                                    'user_id'        => $this->session->userdata('user_id'),
                                );
                                //========accounting=========//
                                if ($this->Settings->module_account == 1 && $item_type != 'manual' && $sale_status == 'completed') {
                                    $getproduct    = $this->site->getProductByID($item_id);
                                    $productAcc    = $this->site->getProductAccByProductId($item_id);
                                    $default_sale  = $this->site->AccountByBiller('default_sale', $biller_id);
                                    $inventory_acc = $this->site->AccountByBiller('default_stock', $biller_id);
                                    $costing_acc   = $this->site->AccountByBiller('default_cost', $biller_id);
                                    $accTrans[] = array(
                                        'tran_type'     => 'Sale',
                                        'tran_date'     => $date,
                                        'reference_no'  => $reference,
                                        'account_code'  => $inventory_acc,
                                        'amount'        => -($cost * $item_unit_quantity),
                                        'narrative'     => $this->site->getAccountName($inventory_acc),
                                        'description'   => $note,
                                        'biller_id'     => $biller_id,
                                        'project_id'    => $project_id,
                                        'customer_id'   => $customer_id,
                                        'created_by'    => $this->session->userdata('user_id'),
                                    );
                                    $accTrans[] = array(
                                        'tran_type'     => 'Sale',
                                        'tran_date'     => $date,
                                        'reference_no'  => $reference,
                                        'account_code'  => $costing_acc,
                                        'amount'        => ($cost * $item_unit_quantity),
                                        'narrative'     => $this->site->getAccountName($costing_acc),
                                        'description'   => $note,
                                        'biller_id'     => $biller_id,
                                        'project_id'    => $project_id,
                                        'customer_id'   => $customer_id,
                                        'created_by'    => $this->session->userdata('user_id'),
                                        'activity_type' => $this->site->get_activity($costing_acc)
                                    );
                                }
                                //============end accounting=======//
                            }
                        }
                        if ($this->Settings->module_account == 1 && $sale_status == 'completed') {
                            $getproduct    = $this->site->getProductByID($item_id);
                            $productAcc    = $this->site->getProductAccByProductId($item_id);
                            $default_sale  = $this->site->AccountByBiller('default_sale', $biller_id);
                            $inventory_acc = $this->site->AccountByBiller('default_stock', $biller_id);
                            $costing_acc   = $this->site->AccountByBiller('default_cost', $biller_id);
                            $product_tax   = $this->bpas->formatDecimal($product_tax);
                            if ($product_details->type != 'combo') {
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
                            }
                            if ($product_tax > 0) {
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
                        }
                        $product = array(
                            'product_id'        => $item_id,
                            'product_code'      => $item_code,
                            'product_name'      => $item_name,
                            'product_type'      => $item_type,
                            'option_id'         => $item_option,
                            'net_unit_price'    => $item_net_price,
                            'unit_price'        => $this->bpas->formatDecimal($item_net_price + $item_tax),
                            'quantity'          => $item_quantity,
                            'product_unit_id'   => $unit ? $unit->id : NULL,
                            'product_unit_code' => $unit ? $unit->code : NULL,
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
                            'weight'            => $item_weight,
                            'total_weight'      => $total_weight,
                            'comment'           => $item_comment,
                            'saleman_by'        => $saleman_item,
                            'item_row_id'       => $_POST['item_row_id'][$r] != "undefined" ? $_POST['item_row_id'][$r] : rand(10000000000000, 99999999999999),
                            'row_id'            => $_POST['row_id'][$r],
                            'cost'              => $cost,
                        );
                        $text_items .= $r+1 . "/ " . $item_name . "(".$item_code.")" ." | ". $item_quantity." | ".$this->bpas->formatDecimal($real_unit_price) ." | ". $pr_item_discount ." | ". $this->bpas->formatDecimal($subtotal)."\n";
                        $products[] = ($product + $gst_data);
                        $total_original_price += $this->bpas->formatDecimal(($item_price_original * $item_unit_quantity));
                        $total += $this->bpas->formatDecimal(($item_net_price * $item_unit_quantity));
                    }
                
            }

            $extractCost = 0;
            $i = isset($_POST['addon_product_code']) ? sizeof($_POST['addon_product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $pro_id = "";
                $addon_subtotal = (($_POST['addon_product_price'][$r] * $_POST['addon_product_qty'][$r]));
                foreach ($products as $key => $product) {
                    if ($product['row_id'] == $_POST['addon_row_id'][$r]) {
                        $pro_id = $product['product_id'];
                        $products[$key]['subtotal'] = $this->bpas->formatDecimal($product['subtotal'] + $addon_subtotal);
                    }
                }
                $extra_details = $this->site->getProductByID($_POST['addon_product_id'][$r]);
                if ($extra_details) {
                    $extraUnit = $this->site->getProductUnit($extra_details->id, $extra_details->unit);
                    $extractProductID = $extra_details->id;
                    $extractQuantity  = $_POST['addon_product_qty'][$r];
                    if ($this->Settings->accounting_method == '0') {
                        $extraCosts = $this->site->getFifoCost($extractProductID, $extractQuantity, $stockmoves);
                    } else if ($this->Settings->accounting_method == '1') {
                        $extraCosts = $this->site->getLifoCost($extractProductID, $extractQuantity, $stockmoves);
                    } else if ($this->Settings->accounting_method == '3') {
                        $extraCosts = $this->site->getProductMethod($extractProductID, $extractQuantity, $stockmoves);
                    }
                    if (isset($extraCosts) && !empty($extraCosts)) {
                        $item_cost_qty   = 0;
                        $item_cost_total = 0;
                        foreach ($extraCosts as $extraCost) {
                            $item_cost_qty   += $extraCost['quantity'];
                            $item_cost_total += $extraCost['cost'] * $extraCost['quantity'];
                            $stockmoves[] = array(
                                'transaction'    => 'Sale',
                                'product_id'     => $extractProductID,
                                'product_type'   => $extra_details->type,
                                'product_code'   => $extra_details->code,
                                'product_name'   => $extra_details->name,
                                'quantity'       => $extraCost['quantity'] * (-1),
                                'unit_quantity'  => $extraUnit->unit_qty,
                                'weight'         => $total_weight * (-1),
                                'unit_code'      => $extraUnit->code,
                                'unit_id'        => $extra_details->unit,
                                'warehouse_id'   => $warehouse_id,
                                'date'           => $date,
                                'real_unit_cost' => $extraCost['cost'],
                                'reference_no'   => $reference,
                                'user_id'        => $this->session->userdata('user_id'),
                            );
                            if ($this->Settings->module_account == 1) {
                                $getproduct    = $this->site->getProductByID($extractProductID);
                                $productAcc    = $this->site->getProductAccByProductId($extractProductID);
                                $default_sale  = $this->site->AccountByBiller('default_sale', $biller_id);
                                $inventory_acc = $this->site->AccountByBiller('default_stock', $biller_id);
                                $costing_acc   = $this->site->AccountByBiller('default_cost', $biller_id);
                                $accTrans[] = array(
                                    'tran_type'    => 'Sale',
                                    'tran_date'    => $date,
                                    'reference_no' => $reference,
                                    'account_code' => $inventory_acc,
                                    'amount'       => -($extraCost['cost'] * $extraCost['quantity']),
                                    'narrative'    => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extraCost['quantity'].'#'.'Cost: '.$extraCost['cost'],
                                    'description'  => $note,
                                    'biller_id'    => $biller_id,
                                    'project_id'   => $project_id,
                                    'customer_id'  => $customer_id,
                                    'created_by'   => $this->session->userdata('user_id'),
                                );
                                $accTrans[] = array(
                                    'tran_type'    => 'Sale',
                                    'tran_date'    => $date,
                                    'reference_no' => $reference,
                                    'account_code' => $costing_acc,
                                    'amount'       => ($extraCost['cost'] * $extraCost['quantity']),
                                    'narrative'    => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extraCost['quantity'].'#'.'Cost: '.$extraCost['cost'],
                                    'description'  => $note,
                                    'biller_id'    => $biller_id,
                                    'project_id'   => $project_id,
                                    'customer_id'  => $customer_id,
                                    'created_by'   => $this->session->userdata('user_id'),
                                );
                            }
                        }
                        $extra_details->cost = $item_cost_total / $item_cost_qty;
                    } else {
                        $stockmoves[] = array(
                            'transaction'    => 'Sale',
                            'product_id'     => $extractProductID,
                            'product_type'   => $extra_details->type,
                            'product_code'   => $extra_details->code,
                            'product_name'   => $extra_details->name,
                            'quantity'       => $extractQuantity * (-1),
                            'unit_quantity'  => $extraUnit->unit_qty,
                            'weight'         => $total_weight * (-1),
                            'unit_code'      => $extraUnit->code,
                            'unit_id'        => $extra_details->unit,
                            'warehouse_id'   => $warehouse_id,
                            'date'           => $date,
                            'real_unit_cost' => $extra_details->cost,
                            'reference_no'   => $reference,
                            'user_id'        => $this->session->userdata('user_id'),
                        );
                        if ($this->Settings->module_account == 1) {
                            $getproduct    = $this->site->getProductByID($extractProductID);
                            $productAcc    = $this->site->getProductAccByProductId($extractProductID);
                            $default_sale  = $this->site->AccountByBiller('default_sale', $biller_id);
                            $inventory_acc = $this->site->AccountByBiller('default_stock', $biller_id);
                            $costing_acc   = $this->site->AccountByBiller('default_cost', $biller_id);
                            $accTrans[] = array(
                                'tran_type'    => 'Sale',
                                'tran_date'    => $date,
                                'reference_no' => $reference,
                                'account_code' => $inventory_acc,
                                'amount'       => -($extra_details->cost * $extractQuantity),
                                'narrative'    => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extractQuantity.'#'.'Cost: '.$extra_details->cost,
                                'description'  => $note,
                                'biller_id'    => $biller_id,
                                'project_id'   => $project_id,
                                'customer_id'  => $customer_id,
                                'created_by'   => $this->session->userdata('user_id'),
                            );
                            $accTrans[] = array(
                                'tran_type'    => 'Sale',
                                'tran_date'    => $date,
                                'reference_no' => $reference,
                                'account_code' => $costing_acc,
                                'amount'       => ($extra_details->cost * $extractQuantity),
                                'narrative'    => 'Product Code: '.$extra_details->code.'#'.'Qty: '.$extractQuantity.'#'.'Cost: '.$extra_details->cost,
                                'description'  => $note,
                                'biller_id'    => $biller_id,
                                'project_id'   => $project_id,
                                'customer_id'  => $customer_id,
                                'created_by'   => $this->session->userdata('user_id'),
                            );
                        }
                    }
                    $addon_products[] = array(
                        'sale_product_id' => $pro_id,
                        'addon_row_id'    => $_POST['addon_row_id'][$r],
                        'product_id'      => $_POST['addon_product_id'][$r],
                        'product_code'    => $_POST['addon_product_code'][$r],
                        'product_name'    => $_POST['addon_product_name'][$r],
                        'product_type'    => $_POST['addon_product_type'][$r],
                        'warehouse_id'    => $warehouse_id,
                        'quantity'        => $_POST['addon_product_qty'][$r],
                        'net_unit_price'  => $_POST['addon_product_unit_price'][$r],
                        'unit_price'      => $_POST['addon_product_unit_price'][$r],
                        'currency'        => $_POST['addon_product_currency'][$r],
                        'tax_rate'        => $_POST['addon_product_tax_rate'][$r],
                        'option_id'       => isset($_POST['addon_product_option'][$r]) && $_POST['addon_product_option'][$r] != 'false' ? $_POST['addon_product_option'][$r] : NULL,
                        'subtotal'        => $this->bpas->formatDecimal($addon_subtotal),
                    );
                    $total = $this->bpas->formatDecimal($total + $addon_subtotal);
                    $default_total_price = $this->bpas->formatDecimal($default_total_price + $addon_subtotal);
                    $total_original_price = $this->bpas->formatDecimal($total_original_price + $addon_subtotal);
                }
            } 
            if (empty($products)) { 
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } elseif ($this->pos_settings->item_order == 1) {
                krsort($products);
            }
            $cur_rate       = $this->pos_model->getExchange_rate('KHR');
            $order_discount = $this->site->calculateDiscount($this->input->post('discount'), ($total + $product_tax));
            $order_discount = $this->bpas->formatDecimal($order_discount);
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount));
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->bpas->formatDecimal(($product_tax + $order_tax));
            $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount));
            $rounding       = 0;
            if ($this->pos_settings->rounding) {
                $round_total = $this->bpas->roundNumber($grand_total, $this->pos_settings->rounding);
                $rounding    = $this->bpas->formatMoney($round_total - $grand_total);
            }
            // $currency     = $this->input->post('kh_currenncy') == "" ? $this->input->post('en_currenncy') : $this->input->post('kh_currenncy');
            $currency        = $this->Settings->default_currency;
            $currency_rate   = ($currency == "usd") ? $cur_rate->rate : 1;
            // -------check_payby--------
            $p = isset($_POST['amount']) ? sizeof($_POST['amount']) : 0;
            $pos_type  = 1;
            //=======acounting=========//
            if ($this->Settings->module_account == 1) {
                if ($order_discount != 0) {
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
                if ($order_tax != 0) {
                    $accTrans[] = array(
                        'tran_type'     => 'Sale',
                        'tran_date'     => $date,
                        'reference_no'  => $reference,
                        'account_code'  => $this->accounting_setting->default_sale_tax,
                        'amount'        => -$order_tax,
                        'narrative'     => 'Order Tax',
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'customer_id'   => $customer_id,
                        'created_by'    => $this->session->userdata('user_id'),
                    );
                }
                if ($shipping != 0) {
                    $accTrans[] = array(
                        'tran_type'    => 'Sale',
                        'tran_date'    => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_freight,
                        'amount'       => -$shipping,
                        'narrative'    => 'Shipping',
                        'description'  => $note,
                        'biller_id'    => $biller_id,
                        'project_id'   => $project_id,
                        'customer_id'  => $customer_id,
                        'created_by'   => $this->session->userdata('user_id'),
                    );
                }
            }
            //============end accounting=======//
            $data = array(
                'date'                => $date,
                'date_in'             => $this->input->post('start_time'),
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
                'order_discount_id'   => $this->input->post('discount'),
                'order_discount'      => $this->bpas->formatDecimal($order_discount),
                'total_discount'      => $total_discount,
                'product_tax'         => $product_tax,
                'order_tax_id'        => $this->input->post('order_tax'),
                'membership_code'     => $this->input->post('membership_code'),
                'order_tax'           => $order_tax,
                'total_tax'           => $total_tax,
                'default_total_price' => $default_total_price,
                'shipping'            => $this->bpas->formatDecimal($shipping),
                'grand_total'         => $grand_total,
                'total_items'         => $total_items,
                'sale_status'         => $sale_status,
                'payment_status'      => $payment_status,
                'payment_term'        => $payment_term,
                'rounding'            => $rounding,
                'original_price'      => $total_original_price,
                'suspend_note'        => $this->input->post('suspend_note') ? $this->input->post('suspend_note') : null,
                'currency'            => $currency,
                'customer_qty'        => $customer_qty,
                'pos'                 => $pos_type,
                'paid'                => $this->input->post('amount-paid') ? $this->input->post('amount-paid') : 0,
                'created_by'          => $this->session->userdata('user_id'),
                'hash'                => hash('sha256', microtime() . mt_rand()),
                'saleman_by'          => $saleman,
                'delivery_by'         => $delivery_by,
                'currency_rate_kh'    => $exchange_khm,
                'currency_rate_bat'   => $exchange_bat,
            );
            if (!$suspend) {
                $p = isset($_POST['amount']) ? sizeof($_POST['amount']) : 0;
                $paid = 0;
                for ($r = 0; $r < $p; $r++) { 
                    if (isset($_POST['amount'][$r]) && ($_POST['amount'][$r] > 0 || $grand_total == 0) && isset($_POST['paid_by'][$r]) && !empty($_POST['paid_by'][$r])) {
                        $amount = $this->bpas->formatDecimal($_POST['balance_amount'][$r] > 0 ? $_POST['amount'][$r] - $_POST['balance_amount'][$r] : $_POST['amount'][$r]);
                        if ($_POST['paid_by'][$r] == 'deposit') {
                            if (!$this->site->check_customer_deposit($customer_id, $amount)) {
                                $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                                redirect($_SERVER["HTTP_REFERER"]);
                            }
                        }
                        if ($_POST['paid_by'][$r] == 'gift_card') {
                            $gc = $this->site->getGiftCardByNO($_POST['paying_gift_card_no'][$r]);
                            $amount_paying = $_POST['amount'][$r] >= $gc->balance ? $gc->balance : $_POST['amount'][$r];
                            $gc_balance    = $gc->balance - $amount_paying;
                            $payment[]     = array(
                                'date'         => $date,
                                // 'reference_no' => $this->site->getReference('pay'),
                                'amount'       => $amount,
                                'paid_amount'  => $_POST['paid_amount'][$r],
                                'currency_rate'=> $_POST['currency_rate'][$r],
                                'paid_by'      => $_POST['paid_by'][$r],
                                'cheque_no'    => $_POST['cheque_no'][$r],
                                'cc_no'        => $_POST['paying_gift_card_no'][$r],
                                'cc_holder'    => $_POST['cc_holder'][$r],
                                'cc_month'     => $_POST['cc_month'][$r],
                                'cc_year'      => $_POST['cc_year'][$r],
                                'cc_type'      => $_POST['cc_type'][$r],
                                'cc_cvv2'      => $_POST['cc_cvv2'][$r],
                                'created_by'   => $this->session->userdata('user_id'),
                                'type'         => 'received',
                                'note'         => $_POST['payment_note'][$r],
                                'pos_paid'     => $_POST['amount'][$r],
                                'pos_balance'  => $_POST['balance_amount'][$r],
                                'gc_balance'   => $gc_balance
                            );
                        } else { 
                            $payment[] = array(
                                'date'         => $date,
                                // 'reference_no' => $this->site->getReference('pay'),
                                'amount'       => $amount,
                                'paid_amount'  => implode(',', array($_POST['paid_amount'][$r], $_POST['paid_amount_kh'][$r], $_POST['paid_amount_bat'][$r],)),
                                'currency_rate'=> $_POST['currency_rate'][$r],
                                'paid_by'      => $_POST['paid_by'][$r],
                                'cheque_no'    => $_POST['cheque_no'][$r],
                                'cc_no'        => $_POST['cc_no'][$r],
                                'cc_holder'    => $_POST['cc_holder'][$r],
                                'cc_month'     => $_POST['cc_month'][$r],
                                'cc_year'      => $_POST['cc_year'][$r],
                                'cc_type'      => $_POST['cc_type'][$r],
                                'cc_cvv2'      => $_POST['cc_cvv2'][$r],
                                'created_by'   => $this->session->userdata('user_id'),
                                'type'         => 'received',
                                'note'         => $_POST['payment_note'][$r],
                                'pos_paid'     => $_POST['amount'][$r],
                                'pos_balance'  => $_POST['balance_amount'][$r]
                            );
                        }
                    }
                    //=====add accountig=====//
                    if ($this->Settings->module_account == 1) {
                        if ($_POST['amount'][$r]) {        
                            if ($this->input->post('paid_by') == 'deposit') {
                                $paying_to = $this->accounting_setting->default_sale_deposit;
                            } else {
                                $paid_by = $this->site->getCashAccountByCode($_POST['paid_by'][$r]);
                                $paid_by_account = $paid_by->account_code;
                                $paying_to = isset($paid_by_account)?$paid_by_account:$this->accounting_setting->default_payment_pos;
                            }
                            $accTrans[] = array(
                                'tran_type'    => 'Payment',
                                'tran_date'    => $date,
                                'reference_no' => $reference,
                                'account_code' => $paying_to,
                                'amount'       => $amount,
                                'narrative'    => $this->site->getAccountName($paying_to),
                                'description'  => $_POST['payment_note'][$r],
                                'biller_id'    => $biller_id,
                                'project_id'   => $project_id,
                                'customer_id'  => $customer_id,
                                'created_by'   => $this->session->userdata('user_id'),
                            );
                        }
                    }
                    //=====end accountig=====//
                }
            }
            if (!isset($payment) || empty($payment)) {
                $payment = array();
            }
            if ($this->Settings->module_account != 1) {
                $accTrans = array();
                $accTranPayments = array();
            }
            if ($this->Settings->product_expiry == '1' && $stockmoves && $products) {
                $checkExpiry = $this->site->checkExpiry($stockmoves, $products, 'POS');
                // var_dump($checkExpiry['expiry_items']);
                $stockmoves  = $checkExpiry['expiry_stockmoves'];
                $products    = $checkExpiry['expiry_items'];
            }
            // $this->bpas->print_arrays($data, $products, $addon_products, $stockmoves, $payment);
        }
        if ($this->form_validation->run() == TRUE && !empty($products) && !empty($data)) {
            if ($suspend) {
                if (!empty($addon_products)) {
                    foreach($addon_products as $key => &$addon_product){
                        $addon_products[$key]['suspend_product_id'] = $addon_product['sale_product_id'];
                        unset($addon_product['sale_product_id']);
                    }    
                } else {
                    $addon_products = null;
                }
                if ($this->pos_model->suspendSale($data, $products, $addon_products, $did)) {
                    $this->session->set_flashdata('message', $this->lang->line("sale_suspended"));
                    if ($this->pos_settings->pos_type == "table" || $this->pos_settings->pos_type == "room") {
                        // admin_redirect("table");
                        admin_redirect("pos/index/".$did."", 'refresh');
                        // $this->session->set_flashdata('suspend', $did);
                    } else {
                        //$this->session->set_userdata('remove_posls', 1);
                        admin_redirect("pos");
                    }
                }
            } else {
                if ($sale = $this->pos_model->addSale($data, $products, $addon_products, $stockmoves, $payment, $did, $accTrans, $accTranPayments, $this->input->post('suspend_note'))) {
                    $this->session->set_userdata('remove_posls', 1);
                    $msg = $this->lang->line("sale_added");
                    if (!empty($sale['message'])) {
                        foreach ($sale['message'] as $m) {
                            $msg .= '<br>' . $m;
                        }
                    }
                    
                    $dynamic_date = $this->pos_settings->dynamic_date;
                    $current_date = date("Y-m-d");
                    $no_number = $this->pos_settings->wait_number;
                    if ($dynamic_date == $current_date) {
                        $this->db->update('pos_settings',['wait_number'=>  $no_number + 1]);
                        $this->db->update('sales',['wait_number'=>  $no_number + 1],['id'=>$sale['sale_id']]);
                    } else {
                        $this->db->update('pos_settings', ['wait_number'=>  1]);
                        $this->db->update('pos_settings', ['dynamic_date'=>  $current_date]);
                        $this->db->update('sales',['wait_number'=>  1], ['id' => $sale['sale_id']]);
                    }
                    $this->session->set_flashdata('message', $msg);
                    $redirect_to = $this->pos_settings->after_sale_page ? "pos" : "pos/view/" . $sale['sale_id'];
                    if ($this->pos_settings->auto_print) {
                        if ($this->Settings->remote_printing != 1) {
                            $redirect_to .= '?print='.$sale['sale_id'];
                        }
                    }
                    admin_redirect($redirect_to);
                }
            }
        } else {
            $this->data['old_sale'] = NULL;
            $this->data['oid']      = NULL;
            if ($duplicate_sale) {
                if ($old_sale  = $this->pos_model->getInvoiceByID($duplicate_sale)) {
                    $inv_items = $this->pos_model->getSaleItems($duplicate_sale);
                    $this->data['oid']      = $duplicate_sale;
                    $this->data['old_sale'] = $old_sale;
                    $this->data['message']  = lang('old_sale_loaded');
                    $this->data['customer'] = $this->pos_model->getCompanyByID($old_sale->customer_id);
                } else {
                    $this->session->set_flashdata('error', lang("bill_x_found"));
                    if ($this->pos_settings->pos_type == "room" || 
                        $this->pos_settings->pos_type == "table") {
                        admin_redirect("table");
                    } else {
                        admin_redirect("pos");
                    }
                }
            }
            $this->data['suspend_sale'] = NULL;
            if ($sid) {
                if ($suspended_sale = $this->pos_model->getOpenBillByID($sid)) {
                    $inv_items                     = $this->pos_model->getSuspendedSaleItems($sid);
                    $this->data['sid']             = $sid;
                    $this->data['suspend_sale']    = $suspended_sale;
                    $inv_addon_items               = $this->pos_model->getSuspendedSaleAddOnItems($sid);
                    $this->data['inv_addon_items'] = $inv_addon_items;
                    $audit_bill     = null;
                    if (!empty($this->pos_model->check_biller($suspended_sale->refer, $this->session->userdata('user_id')))) {
                        $audit_bill = $this->pos_model->check_biller($suspended_sale->refer, $this->session->userdata('user_id'));
                    } else {
                        $audit_bill = $this->pos_model->check_biller_order($suspended_sale->refer, $this->session->userdata('user_id'));
                    }
                    $this->data['biller_audit'] = $audit_bill;
                    $this->data['biller_audit_refer']     = $this->pos_model->check_biller($suspended_sale->refer,$this->session->userdata('user_id'));
                    $this->data['message']        = lang('suspended_sale_loaded');
                    $this->data['customer']       = $this->pos_model->getCompanyByID($suspended_sale->customer_id);
                    $this->data['reference_note'] = $suspended_sale->suspend_note;
                } else {
                    $this->session->set_flashdata('error', lang("bill_x_found"));
                    if ($this->pos_settings->pos_type == "room") {
                        admin_redirect("table");
                    }else{
                        admin_redirect("pos");
                    }
                }
            }
            if (($sid || $duplicate_sale) && $inv_items) {
                    $this->pos_model->updateRoomPriceMinutely($sid);
                    $c = rand(100000, 9999999);
                    foreach ($inv_items as $item) {
                        $row = $this->site->getProductByID($item->product_id);
                        if (!$row) {
                            $row                = json_decode('{}');
                            $row->tax_method    = 0;
                            $row->quantity      = 0;
                        } else {
                            $category           = $this->site->getCategoryByID($row->category_id);
                            $row->category_name = $category->name;
                            unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                        }
                        $pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                        if ($pis) {
                            foreach ($pis as $pi) {
                                $row->quantity += $pi->quantity_balance;
                            }
                        }
                        $row->id                = $item->product_id;
                        $row->item_row_id       = $item->item_row_id;
                        $row->code              = $item->product_code;
                        $row->name              = $item->product_name;
                        $row->type              = $item->product_type;
                        $row->quantity         += $item->quantity ? $item->quantity : 1;
                        $row->discount          = $item->discount ? $item->discount : '0';
                        if ($item->quantity == 0 ) {
                            $item->quantity     = 1 ;
                        }
                        $row->price             = $this->bpas->formatDecimal($item->net_unit_price + $this->bpas->formatDecimal($item->item_discount / $item->quantity));
                        $row->unit_price        = $row->tax_method ? $item->unit_price + $this->bpas->formatDecimal($item->item_discount / $item->quantity) + $this->bpas->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
                        $row->real_unit_price   = $item->real_unit_price;
                        $row->base_quantity     = $item->quantity;
                        $row->base_unit         = isset($row->unit) ? $row->unit : $item->product_unit_id;
                        $row->base_unit_price   = $row->price ? $row->price : $item->unit_price;
                        $row->unit              = $item->product_unit_id;
                        $row->qty               = $item->unit_quantity;
                        $row->tax_rate          = $item->tax_rate_id;
                        $row->serial            = $item->serial_no;
                        $row->option            = $item->option_id;
                        $row->weight            = $item->weight;
                        $options                = $this->pos_model->getProductOptions($row->id, $item->warehouse_id);
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
                        $row->comment    = isset($item->comment) ? $item->comment : '';
                        $row->ordered    = '1';
                        $combo_items     = false;
                        if ($row->type == 'combo') {
                            $combo_items = $this->pos_model->getProductComboItems($row->id, $item->warehouse_id);
                        }
                        $units           = $this->site->getUnitsByBUID($row->base_unit);
                        $tax_rate        = $this->site->getTaxRateByID($row->tax_rate);
                        $ri              = $this->Settings->item_addition ? $row->id : $c;
                        $set_price       = $this->site->getUnitByProId($row->id);
                        $addOn_items     = array();
                        if ($inv_addon_items) {
                            foreach ($inv_addon_items as $key => $inv_addon_item) {
                                if ($inv_addon_item->suspend_item_id == $item->id) {
                                    $addOn_item_row = $this->site->getProductByID($inv_addon_item->product_id);
                                    if ($addOn_item_row) {
                                        $addOn_item_row->id               = $inv_addon_item->product_id;
                                        $addOn_item_row->name             = $inv_addon_item->product_name;
                                        $addOn_item_row->code             = $inv_addon_item->product_code;
                                        $addOn_item_row->product_type     = $inv_addon_item->product_type;
                                        $addOn_item_row->warehouse_id     = $inv_addon_item->warehouse_id;
                                        $addOn_item_row->real_unit_price  = $inv_addon_item->unit_price;
                                        $addOn_item_row->price            = $this->bpas->formatDecimal($inv_addon_item->unit_price);
                                        $addOn_item_row->qty              = $inv_addon_item->quantity;
                                        $addOn_item_row->currency         = $inv_addon_item->currency;
                                        $addOn_item_row->tax_rate         = $inv_addon_item->tax_rate;
                                        $addOn_item_row->option           = $inv_addon_item->option_id;
                                        $addOn_items[$key]['row']         = $addOn_item_row;
                                        $addOn_items[$key]['tax_rate']    = $this->site->getTaxRateByID($addOn_item_row->tax_rate);
                                    }
                                }
                            }
                        }
                        $pr[$ri] = array(
                            'id' => $c, 'item_id' => $row->id, 'item_row_id' => $row->item_row_id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'combo_items' => $combo_items,
                            'tax_rate' => $tax_rate, 'set_price' => $set_price, 'units' => $units,'options' => $options,'addOn_items' => $addOn_items);
                        $c++;
                    }
                    $this->data['items'] = json_encode($pr);
            } else {
                $this->data['customer'] = $this->pos_model->getCompanyByID($this->pos_settings->default_customer);
                $this->data['reference_note'] = NULL;
            }
            $this->data['error']         = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['message']       = isset($this->data['message']) ? $this->data['message'] : $this->session->flashdata('message');
            // $this->data['biller']     = $this->site->getCompanyByID($this->pos_settings->default_biller);
            $this->data['suspend_note']  = $this->table_model->available_room();
            $this->data['agencies']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['deliveries']    = $this->site->getDriver();
            $this->data['billers']       = $this->site->getAllCompanies('biller');
            $this->data['customers']     = $this->site->getAllCompanies('customer');
            $this->data['warehouses']    = $this->site->getAllWarehouses();
            $this->data['tax_rates']     = $this->site->getAllTaxRates();
            $this->data['user']          = $this->site->getUser();
            $this->data["tcp"]           = $this->pos_model->products_count($this->pos_settings->default_category);
            $this->data['products']      = $this->ajaxproducts($this->pos_settings->default_category);
            $this->data['categories']    = $this->site->getAllCategories();
            $this->data['brands']        = $this->site->getAllBrands();
            $this->data['subcategories'] = $this->site->getSubCategories($this->pos_settings->default_category);
            $this->data['printer']       = $this->pos_model->getPrinterByID($this->pos_settings->printer);
            $order_printers = json_decode($this->pos_settings->order_printers);
            $printers = array();
            if (!empty($order_printers)) {
                foreach ($order_printers as $printer_id) {
                    $printers[] = $this->pos_model->getPrinterByID($printer_id);
                }
            }
            $this->data['order_printers'] = $printers;
            $this->data['pos_settings'] = $this->pos_settings;
            if ($this->pos_settings->after_sale_page && $saleid = $this->input->get('print', true)) {
                if ($inv = $this->pos_model->getInvoiceByID($saleid)) {
                    $this->load->helper('pos');
                    if (!$this->session->userdata('view_right')) {
                        $this->bpas->view_rights($inv->created_by, true);
                    }
                    $this->data['rows']            = $this->pos_model->getAllInvoiceItems($inv->id);
                    $this->data['biller']          = $this->pos_model->getCompanyByID($inv->biller_id);
                    $this->data['customer']        = $this->pos_model->getCompanyByID($inv->customer_id);
                    $this->data['payments']        = $this->pos_model->getInvoicePayments($inv->id);
                    $this->data['return_sale']     = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : NULL;
                    $this->data['return_rows']     = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : NULL;
                    $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : NULL;
                    $this->data['inv']             = $inv;
                    $this->data['print']           = $inv->id;
                    $this->data['created_by']      = $this->site->getUser($inv->created_by);
                }
            }
            $room_number =$this->table_model->get_room_number($sid);
            if (isset($room_number->name)) {
                $this->data['customer_qty'] = $room_number->customer_qty;
                $this->data['room_number']  = $room_number->name;
                $this->data['room_n']       = $room_number->note_id;
                $this->data['room_tmp']     = $room_number->tmp;
                $this->data['room_id']      = $sid;
            }
            if(isset($card_id)){
                $this->data['get_card']  =$this->table_model->getMemberCardByID($card_id);
            }
            $user_id     = $this->session->userdata('user_id');
            $currency_id = $this->site->getCurrencyWarehouseByUserID($user_id);
            $curr        = $this->site->getCurrencyByID($currency_id);
            $this->data['biller_adr'] = $this->pos_model->get_biller_by_user($user_id);
            if ($this->Owner || $this->Admin) {
                $this->data['projects'] = $this->site->getAllProject();
            } else {
                $this->data['projects'] = $this->site->getAllProject($this->session->userdata('warehouse_id'));
            }
            $this->data['currency_code']     = (isset($curr->code) ? $curr->code : null);
            $this->data['salemans']          = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['exchange_rate']     = $this->pos_model->getExchange_rate('KHR');
            $this->data['exchange_rate_bat'] = $this->pos_model->getExchange_rate('THB');
            $this->data['language']          = $this->Settings->language;
            $this->data['group_price']       = json_encode($this->site->getAllGroupPrice());
            //$this->data['exchange_rate_bat_out'] = $this->pos_model->getExchange_rate('THB');
            if (isset($inv_items)) {
                $this->data['inv_items']  = $inv_items;
            }
            $this->data['suspend_note_tmp'] = $this->table_model->getAll_suspend_notetmp();
            $this->data['permission'] = $this->site->getPermission();
            //if($user['warehouse_id'] != 1){
                $this->load->view($this->theme . 'pos/add', $this->data);
             //  $this->load->view($this->theme . 'pos/add_auto_currencires', $this->data);
            //}else{
                //$this->load->view($this->theme . 'pos/add_mart', $this->data);
            //}
        }
    }

    public function split_bill()
    {
        $this->form_validation->set_rules('amount', lang("amount"), 'trim|numeric');
        if ($this->form_validation->run() == true) {
            if(!empty($this->input->post('split_id'))){
                $i = isset($_POST['split_id']) ? sizeof($_POST['split_id']) : 0;
                $room_id = $this->input->post('suspend_id');
                $option_bill = $this->input->post('option_bill');
                if ($option_bill == 1) {
                    //============create temporary suspended note=======//
                    $suspended_note = $this->table_model->getSuspendnoteById($this->input->post('suspend_note'));
                    $data_room = array(
                        'name'          => $suspended_note->name.' ('.$this->input->post('note_name').')',
                        'type'          => 'table',
                        'price'         => '0',
                        'tmp'           => '1',
                        'floor'         => '0',
                        'warehouse_id'  => $this->input->post('warehouse'),
                        'description'   => 'tmp',
                        'create_date'   => date('Y-m-d H:i:s')
                    );
                    $this->table_model->addRoom($data_room);
                    $suspend_note_id = $this->db->insert_id();
                    $this->db->select();
                        $this->db->from('suspended_bills');
                        $this->db->where('id', $room_id);
                        $query = $this->db->get();
                        if($query->num_rows()) {   
                            $new_author = $query->result_array();
                            foreach ($new_author as $row) {
    		                $product_room = array(
		                        'refer'      	    => $row['refer'],
		        	            'date'      	    => $row['date'],
		        	            'start_date'        => $row['start_date'],
		        	            'customer_id'       => $row['customer_id'],
		        	            'customer'   	    => $row['customer'],
		        	            'count'    		    => $row['count'],
		        	            'order_discount_id' => $row['order_discount_id'],
		        	            'order_tax_id'      => $row['order_tax_id'],
		        	            'total'      	    => $row['total'],
		        	            'biller_id'         => $row['biller_id'],
		        	            'warehouse_id' 	    => $row['warehouse_id'],
		        	            'created_by' 	    => $row['created_by'],
		        	            'suspend_note'      => $suspend_note_id
                            );
                            $this->db->insert('suspended_bills',$product_room); 
                        } 
                    }
                }
                    $bill_id = $this->db->insert_id();
                    $total_item = 0;
                    for ($r = 0; $r < $i; $r++) {
                        $item_id = $_POST['split_id'][$r];
                        $dataExplode = explode("-space_explode-",$item_id);
                        $id_item = $dataExplode[0];
                        $pp_id[] = $id_item;
                        $code_item = $dataExplode[1];
                        $id = $dataExplode[2];
                        $qty_item = 1;

                        $this->db->select();
                        $this->db->from('suspended_items');
                        $this->db->where('id', $id);
                        $this->db->where('suspend_id', $room_id);
                        $this->db->where('product_id', $id_item);
                        $this->db->where('product_code', $code_item);
                        $query = $this->db->get();
                        if($query->num_rows()) {   
                            $new_author = $query->result_array();
                            foreach ($new_author as $row) {
                                $suspended_items[] = $row;
                                $datas = array(
                                    'suspend_id'          => $bill_id,
                                    'item_row_id'         => $row['item_row_id'],
                                    'product_id'          => $row['product_id'],
                                    'product_code'        => $row['product_code'],
                                    'product_name'        => $row['product_name'],
                                    'quantity'            => $qty_item,
                                    'net_unit_price'      => $row['net_unit_price'],
                                    'unit_price'          => $row['unit_price'],
                                    'subtotal'            => $row['subtotal'],
                                    'real_unit_price'     => $row['real_unit_price'],
                                    'unit_quantity'       => $qty_item,
                                    'comment'             => $row['comment'],
                                    'product_unit_code'   => $row['product_unit_code'],
                                    'product_unit_id'     => $row['product_unit_id'],
                                    'product_type'        => $row['product_type'],
                                    'option_id'           => $row['option_id'],
                                    'serial_no'           => $row['serial_no'],
                                    'item_discount'       => $row['item_discount'],
                                    'discount'            => $row['discount'],
                                    'tax'                 => $row['tax'],
                                    'tax_rate_id'         => $row['tax_rate_id'],
                                    'free'                => $row['free'],
                                    'item_tax'            => $row['item_tax'],
                                    'warehouse_id'        => $row['warehouse_id'],
                                    'product_second_name' => $row['product_second_name'],
                                    'gst'                 => $row['gst'],
                                    'cgst'                => $row['cgst'],
                                    'sgst'                => $row['sgst'],
                                    'igst'                => $row['igst'],
                                    'weight'              => $row['weight'],
                                    'total_weight'        => ($row['weight'] * $qty_item),
                                    'row_id'              => $row['row_id'],
                                );
                                krsort($datas);
                                $data_array[] = $datas;
                                $this->db->insert('split_items', $datas);
                                $this->db->update('suspended_items', 
                                    ['unit_quantity'=> ($row['unit_quantity'] - 1), 'quantity'=> ($row['quantity'] - 1), 'total_weight' => (($row['quantity'] - 1) * $row['weight'])], ['suspend_id' => $room_id , 'product_id'=>$id_item, 'id'=>$id]);
                                $this->db->delete('suspended_items',  ['suspend_id' => $room_id , 'product_id'=> $id_item, 'unit_quantity'=> 0, 'quantity'=> 0, 'id'=>$id]);
                            }
                        }
                    } 
            } else {
                $this->session->set_flashdata('error', validation_errors());
                redirect($_SERVER['HTTP_REFERER']);
            }
        } elseif ($this->input->post('add_room')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->pos_model->addTmpSuspendItem($data_array, $suspended_items)) { 
            $this->session->set_flashdata('message', lang("split_bill ".$suspended_note->name));
            // admin_redirect('pos/index/'. $bill_id);
            redirect($_SERVER['HTTP_REFERER']);

        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'pos/', $this->data);
        }
    }

    public function install_update($file, $m_version, $version)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $this->load->helper('update');
        save_remote_file($file . '.zip');
        $this->bpas->unzip('./files/updates/' . $file . '.zip');
        if ($m_version) {
            $this->load->library('migration');
            if (!$this->migration->latest()) {
                $this->session->set_flashdata('error', $this->migration->error_string());
                admin_redirect('pos/updates');
            }
        }
        $this->db->update('pos_settings', ['version' => $version], ['pos_id' => 1]);
        unlink('./files/updates/' . $file . '.zip');
        $this->session->set_flashdata('success', lang('update_done'));
        admin_redirect('pos/updates');
    }

    public function open_drawer()
    {
        $data = json_decode($this->input->get('data'));
        $this->load->library('escpos');
        $this->escpos->load($data->printer);
        $this->escpos->open_drawer();
    }

    public function open_register()
    {
        $this->bpas->checkPermissions('index');
        $this->form_validation->set_rules('cash_in_hand', lang('cash_in_hand'), 'trim|required|numeric');
        if ($register = $this->pos_model->registerData($this->session->userdata('user_id'))) {
            $register_data = ['register_id' => $register->id, 'cash_in_hand' => $register->cash_in_hand, 'register_open_time' => $register->date];
            $this->session->set_userdata($register_data);
            if ($this->pos_settings->pos_type == 'pos') {
                admin_redirect('pos');
            } else {
                admin_redirect('table');
            }
        }

        if ($this->form_validation->run() == true) {
            $data = [
                'date'         => date('Y-m-d H:i:s'),
                'cash_in_hand' => $this->input->post('cash_in_hand'),
                'user_id'      => $this->session->userdata('user_id'),
                'status'       => 'open',
            ];
        }
        if ($this->form_validation->run() == true && $this->pos_model->openRegister($data)) {
            $this->session->set_flashdata('message', lang('welcome_to_pos'));
            if ($this->pos_settings->pos_type == 'pos') {
                admin_redirect('pos');
            } else {
                admin_redirect('table');
            }
        } else {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('open_register')]];
            $meta                = ['page_title' => lang('open_register'), 'bc' => $bc];
            $this->page_construct('pos/open_register', $meta, $this->data);
        }
    }

    public function opened_bills($per_page = 0)
    {
        $this->load->library('pagination');

        //$this->table->set_heading('Id', 'The Title', 'The Content');
        if ($this->input->get('per_page')) {
            $per_page = $this->input->get('per_page');
        }

        $config['base_url']   = admin_url('pos/opened_bills');
        $config['total_rows'] = $this->pos_model->bills_count();
        $config['per_page']   = 6;
        $config['num_links']  = 3;
        $config['full_tag_open']   = '<ul class="pagination pagination-sm">';
        $config['full_tag_close']  = '</ul>';
        $config['first_tag_open']  = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open']   = '<li>';
        $config['last_tag_close']  = '</li>';
        $config['next_tag_open']   = '<li>';
        $config['next_tag_close']  = '</li>';
        $config['prev_tag_open']   = '<li>';
        $config['prev_tag_close']  = '</li>';
        $config['num_tag_open']    = '<li>';
        $config['num_tag_close']   = '</li>';
        $config['cur_tag_open']    = '<li class="active"><a>';
        $config['cur_tag_close']   = '</a></li>';
        $this->pagination->initialize($config);
        $data['r'] = true;
        $bills     = $this->pos_model->fetch_bills($config['per_page'], $per_page);
        if (!empty($bills)) {
            $html = '';
            $html .= '<ul class="ob">';
            foreach ($bills as $bill) {
                $html .= '<li><button type="button" class="btn btn-info sus_sale" id="' . $bill->id . '"><p>' . $bill->suspend_note . '</p><strong>' . $bill->customer . '</strong><br>' . lang('date') . ': ' . $bill->date . '<br>' . lang('items') . ': ' . $bill->count . '<br>' . lang('total') . ': ' . $this->bpas->formatMoney($bill->total) . '</button></li>';
            }
            $html .= '</ul>';
        } else {
            $html      = '<h3>' . lang('no_opeded_bill') . '</h3><p>&nbsp;</p>';
            $data['r'] = false;
        }

        $data['html'] = $html;

        $data['page'] = $this->pagination->create_links();
        echo $this->load->view($this->theme . 'pos/opened', $data, true);
    }

    public function p()
    {
        $data = json_decode($this->input->get('data'));
        $this->load->library('escpos');
        $this->escpos->load($data->printer);
        $this->escpos->print_receipt($data);
    }
    // public function ps()
    // {
    //     $data = json_decode($this->input->get('data'));
    //     $data2 = $this->input->get('data2');
    //     $this->load->library('escpos');
    //     $this->escpos->load($data->printer);
    //     $this->escpos->print_receipt($data);
    // }
    public function ps()
    {
        $data = json_decode($this->input->get('data'));//from printer
        $item = json_decode($this->input->get('item'));//from json
        $table_id = json_decode($this->input->get('table_id'));//from json
        $stock_type = json_decode($this->input->get('stock_type'));//from json
        
        $this->db->insert("print_order_items",["printer"=>json_encode($data->printer), "text"=>json_encode($data), "item"=>$item->item, "stock_type"=>$stock_type,  "suspend_note"=>$table_id]);
        // $this->load->library('escpos');
        // $this->escpos->load($data->printer);
        // $this->escpos->print_receipt_order($data, $data2);
    }
    public function paypal_balance()
    {
        if (!$this->Owner) {
            return false;
        }
        $this->load->admin_model('paypal_payments');

        return $this->paypal_payments->get_balance();
    }

    public function printers()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('pos');
        }
        $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = lang('printers');
        $bc                       = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('pos'), 'page' => lang('pos')], ['link' => '#', 'page' => lang('printers')]];
        $meta                     = ['page_title' => lang('list_printers'), 'bc' => $bc];
        $this->page_construct('pos/printers', $meta, $this->data);
    }

    public function register_details()
    {
        $this->bpas->checkPermissions('index');
        $register_open_time           = $this->session->userdata('register_open_time');
        $this->data['error']          = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['ccsales']        = $this->pos_model->getRegisterCCSales($register_open_time);
        $this->data['cashsales']      = $this->pos_model->getRegisterCashSales($register_open_time);
        $this->data['chsales']        = $this->pos_model->getRegisterChSales($register_open_time);
        $this->data['gcsales']        = $this->pos_model->getRegisterGCSales($register_open_time);
        $this->data['pppsales']       = $this->pos_model->getRegisterPPPSales($register_open_time);
        $this->data['stripesales']    = $this->pos_model->getRegisterStripeSales($register_open_time);
        $this->data['authorizesales'] = $this->pos_model->getRegisterAuthorizeSales($register_open_time);
        $this->data['totalsales']     = $this->pos_model->getRegisterSales($register_open_time);
        $this->data['refunds']        = $this->pos_model->getRegisterRefunds($register_open_time);
        $this->data['returns']        = $this->pos_model->getRegisterReturns($register_open_time);
        $this->data['expenses']       = $this->pos_model->getRegisterExpenses($register_open_time);
        $this->load->view($this->theme . 'pos/register_details', $this->data);
    }

    public function registers()
    {
        $this->bpas->checkPermissions();

        $this->data['error']     = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['registers'] = $this->pos_model->getOpenRegisters();
        $bc                      = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('pos'), 'page' => lang('pos')], ['link' => '#', 'page' => lang('open_registers')]];
        $meta                    = ['page_title' => lang('open_registers'), 'bc' => $bc];
        $this->page_construct('pos/registers', $meta, $this->data);
    }
    public function sales($warehouse_id = null)
    {
        $this->bpas->checkPermissions('index');
        $count = explode(',', $this->session->userdata('warehouse_id'));

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $user                       = $this->site->getUser();
            if (count($count)>1) {
                $this->data['warehouses']   = $this->site->getAllWarehouses();
            } else {
                $this->data['warehouses']   = null;
            }
            $this->data['count_warehouses'] = $count;
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        }
        $this->data['users'] = $this->site->getStaff();
        $this->data['products'] = $this->site->getProducts();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['agencies'] = $this->site->getAllUsers();
        $this->data['drivers']  = $this->site->getDriver();
        

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('pos'), 'page' => lang('pos')], ['link' => '#', 'page' => lang('pos_sales')]];
        $meta = [
                'page_title' => lang('pos_sales'), 
                'bc' => $bc
            ];
        $this->page_construct('pos/sales', $meta, $this->data);
    }


    public function sales_page($biller_id = null)
    {
        $this->bpas->checkPermissions('index');
        $this->load->library("pagination");

        $user = $this->site->getUser($this->session->userdata('user_id'));
        if ($this->Settings->multi_biller) {
            $count = $user->multi_biller ? explode(',', $user->multi_biller) : null;         
        } else {
            $count = $user->biller_id ? ((array) $user->biller_id) : null;
        }
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

        $product_id      = $this->input->get('product_id') ? $this->input->get('product_id') : null;
        $user_query         = $this->input->get('user') ? $this->input->get('user') : null;
        $customer     = $this->input->get('customer') ? $this->input->get('customer') : null;
        $biller       = $this->input->get('biller') ? $this->input->get('biller') : null;
        $warehouse    = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $delivered_by    = $this->input->get('delivered_by') ? $this->input->get('delivered_by') : null;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : null;
        $start_date   = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date     = $this->input->get('end_date') ? $this->input->get('end_date') : null;
        $serial       = $this->input->get('serial') ? $this->input->get('serial') : null;
        $project       = $this->input->get('project') ? $this->input->get('project') : null;
        if ($start_date) {
            $start_date = $this->bpas->fld($start_date.' 00:00:00');
            $end_date   = $this->bpas->fld($end_date.' 23:59:00');
        }
        if ($start_date) {
            $start_date = $this->bpas->fld($start_date.' 00:00:00');
            $end_date   = $this->bpas->fld($end_date.' 23:59:59');
        }
        $start  = "";
        $end = "";
        $str ="";
        $warehouse_id ='';
        $possale = $this->db->get_where("sales",['pos'=>1])
        ->num_rows();
        $config = array();
        $config['suffix'] = "?v=1".$str;
        $config["base_url"] = admin_url("pos/sales");
        $config["total_rows"] = $possale;
        $config["ob_set"] = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
        $config["per_page"] = $this->Settings->rows_per_page; 
        $config["uri_segment"] = 4;
        $config['full_tag_open'] = '<ul class="pagination pagination-sm">';
        $config['full_tag_close'] = '</ul>';
        $config['next_tag_open'] = '<li class="next">';
        $config['next_tag_close'] = '<li>';
        $config['prev_tag_open'] = '<li class="prev">';
        $config['prev_tag_close'] = '<li>';
        $config['cur_tag_open'] = '<li class="active"><a href="#">';
        $config['cur_tag_close'] = '</a><li>';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '<li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '<li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';

        $this->pagination->initialize($config);
        $this->data["pagination"] = $this->pagination->create_links();
        
        
         $ds = "( SELECT sale_id,delivered_by, status FROM {$this->db->dbprefix('deliveries')} ) FSI";
        $this->db->where('sales.hide', 1);
            $this->db
                ->select($this->db->dbprefix('sales') . ".id as id, 
                    DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date, reference_no, {$this->db->dbprefix('sales')}.biller, 
                    {$this->db->dbprefix('sales')}.customer,
                    companies.name as driver, 
                    (grand_total+COALESCE(rounding, 0)) as grand_total,
                    COALESCE(paid, 0) as paid, 
                    ( (grand_total + COALESCE(rounding, 0)) - COALESCE( paid, 0) ) as balance , 
                    sale_status, 
                    produce_status, 
                    payment_status
                ")
                ->from('sales')
                //->join('companies', 'companies.id=sales.customer_id', 'left')
                ->join('deliveries', 'deliveries.sale_id=sales.id', 'left')
                ->join('companies', 'deliveries.delivered_by = companies.id', 'left')
                ->group_by('sales.id');
        $this->db->limit($config["per_page"],$config["ob_set"]);

        $this->db->where('pos', 1);
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->db->where('bpas_sales.created_by', $this->session->userdata('user_id'));
            // $this->datatables->or_where("FIND_IN_SET(bpas_sales.warehouse_id, '".$warehouse_id."')");
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        if ($user_query) {
            $this->db->where('sales.created_by', $user_query);
        }
        if ($reference_no) {
            $this->db->where('sales.reference_no', $reference_no);
        }
        if ($biller) {
            $this->db->where('sales.biller_id', $biller);
        }
        if ($customer) {
            $this->db->where('sales.customer_id', $customer);
        }
        if ($warehouse) {
            $this->db->where('sales.warehouse_id', $warehouse);
        }
        if ($delivered_by) {
            $this->db->where('deliveries.delivered_by', $delivered_by);
        }
        if ($start_date) {
            $this->db->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }

        $q = $this->db->get();
        $getSales = $q->result();

        $output ="";
        $i=1;
        $grand_total=0;$paid=0;$balance=0;
        foreach ($getSales as  $row) {
            $duplicate_link    = anchor('admin/pos/?duplicate='.$row->id, '<i class="fa fa-plus-square"></i> ' . lang('duplicate_sale'), 'class="duplicate_pos"');
            $detail_link       = anchor('admin/pos/view/'.$row->id, '<i class="fa fa-file-text-o"></i> ' . lang('view_receipt'));
            $detail_link2      = anchor('admin/sales/modal_view/'.$row->id, '<i class="fa fa-file-text-o"></i> ' . lang('sale_details_modal'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
            $detail_link3      = anchor('admin/sales/view/'.$row->id, '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
            $payments_link     = anchor('admin/sales/payments/'.$row->id, '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
            $add_payment_link  = anchor('admin/pos/add_payment/'.$row->id, '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
            $packagink_link    = anchor('admin/sales/packaging/'.$row->id, '<i class="fa fa-archive"></i> ' . lang('packaging'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
            $add_delivery_link ='';
            if ($this->Settings->delivery) {
                $add_delivery_link = anchor('admin/deliveries/add/0/'.$row->id, '<i class="fa fa-truck"></i> ' . lang('add_delivery'));
            }
            $email_link        = anchor('admin/#', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'class="email_receipt" data-id="$1" data-email-address="$2"');
            $edit_link         = anchor('admin/sales/edit/'.$row->id, '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
            $return_link       = anchor('admin/sales/return_sale/'.$row->id, '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
            $making_link       = anchor('admin/pos/add_making/'.$row->id, '<i class="fa fa-gavel" aria-hidden="true"></i> ' . lang('add_making'));
            $delete_link       = "<a href='#' class='po' title='<b>" . lang('delete_sale') . "</b>' data-content=\"<p>"
                . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales/delete/'.$row->id) . "'>"
                . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
                . lang('delete_sale') . '</a>';
            $action = '<div class="text-center"><div class="btn-group text-left">'
                . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
                . lang('actions') . ' <span class="caret"></span></button>
                <ul class="dropdown-menu pull-right" role="menu">
                    <li>' . $duplicate_link . '</li>
                    <li>' . $detail_link . '</li>
                    <li>' . $detail_link2 . '</li>
                    <li>' . $detail_link3 . '</li>
                    <li>' . $payments_link . '</li>
                    <li>' . $add_payment_link . '</li>
                    <li class="hide">' . $packagink_link . '</li>
                    <li>' . $add_delivery_link . '</li>
                
                    <li>' . $email_link . '</li>
                    <li>' . $return_link . '</li>
                    <li>' . $delete_link . '</li>
                    <li class="making hide">' . $making_link . '</li>
                </ul>
            </div></div>';

            $output .='<tr class="receipt_link" id="'.$row->id.'">';
            
                $output .='<td><input class="checkbox multi-select input-xs" value="'.$row->id.'" type="checkbox" name="val[]"></td>';
                $output .='<td>'.$this->bpas->hrld($row->date).'</td>';
                $output .='<td>'.$row->reference_no.'</td>';
                $output .='<td>'.$row->biller.'</td>';
                $output .='<td>'.$row->customer.'</td>';
                $output .='<td>'.$row->driver.'</td>';
                $output .='<td>'.$this->bpas->formatDecimal($row->grand_total).'</td>';
                $output .='<td>'.$this->bpas->formatDecimal($row->paid).'</td>';
                $output .='<td>'.$this->bpas->formatDecimal($row->balance).'</td>';
                $output .='<td>'.$this->bpas->row_status($row->sale_status).'</td>';
                $output .='<td>'.$this->bpas->pay_status($row->payment_status).'</td>';
                $output .='<td>'.$action.'</td>';
                    
                
            $output .='</tr>';
            $i++;
            $grand_total +=$row->grand_total;
            $paid       +=$row->paid;
            $balance    +=$row->balance;
        }
        $output .='<tr class="active">';
            $output .='<td></td>';
            $output .='<td></td>';
            $output .='<td></td>';
            $output .='<td></td>';
            $output .='<td></td>';
            $output .='<td></td>';
            $output .='<td>'.$grand_total.'</td>';
            $output .='<td>'.$paid.'</td>';
            $output .='<td>'.$balance.'</td>';
            $output .='<td></td>';
        $output .='</tr>';

        $this->data['datas'] = $output;

        $this->data['users'] = $this->site->getStaff();
        $this->data['products'] = $this->site->getProducts();
        $this->data['agencies'] = $this->site->getAllUsers();
        $this->data['drivers']  = $this->site->getDriver();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('pos'), 'page' => lang('pos')], ['link' => '#', 'page' => lang('pos_sales')]];
        $meta = [
                'page_title' => lang('pos_sales'), 
                'bc' => $bc,
                'links' => $this->pagination->create_links()
            ];
        $this->page_construct('pos/sales_pagination', $meta, $this->data);
    }

    public function getSales($bill_id = null)
    {
        $this->bpas->checkPermissions('index');
        $product_id      = $this->input->get('product_id') ? $this->input->get('product_id') : null;
        $user_query         = $this->input->get('user') ? $this->input->get('user') : null;
        $customer     = $this->input->get('customer') ? $this->input->get('customer') : null;
        $biller       = $this->input->get('biller') ? $this->input->get('biller') : null;
        $warehouse    = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $delivered_by    = $this->input->get('delivered_by') ? $this->input->get('delivered_by') : null;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : null;
        $start_date   = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date     = $this->input->get('end_date') ? $this->input->get('end_date') : null;
        $serial       = $this->input->get('serial') ? $this->input->get('serial') : null;
        $project       = $this->input->get('project') ? $this->input->get('project') : null;
        if ($start_date) {
            $start_date = $this->bpas->fld($start_date.' 00:00:00');
            $end_date   = $this->bpas->fld($end_date.' 23:59:00');
        }
        if ((!$this->Owner && !$this->Admin) && !$bill_id) {
            $user         = $this->site->getUser();
            $bill_id = $user->bill_id;
        }
        $duplicate_link    = anchor('admin/pos/?duplicate=$1', '<i class="fa fa-plus-square"></i> ' . lang('duplicate_sale'), 'class="duplicate_pos"');
        $detail_link       = anchor('admin/pos/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_receipt'));
        $detail_link2      = anchor('admin/sales/modal_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details_modal'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $detail_link3      = anchor('admin/sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        $payments_link     = anchor('admin/sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_payment_link  = anchor('admin/pos/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $packagink_link    = anchor('admin/sales/packaging/$1', '<i class="fa fa-archive"></i> ' . lang('packaging'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_delivery_link = anchor('admin/deliveries/add/0/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'));
        $email_link        = anchor('admin/#', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'class="email_receipt" data-id="$1" data-email-address="$2"');
        $edit_link         = anchor('admin/sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $return_link       = anchor('admin/sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        $making_link       = anchor('admin/pos/add_making/$1', '<i class="fa fa-gavel" aria-hidden="true"></i> ' . lang('add_making'));
        $delete_link       = "<a href='#' class='po' title='<b>" . lang('delete_sale') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_sale') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $duplicate_link . '</li>
                <li>' . $detail_link . '</li>
                <li>' . $detail_link2 . '</li>
                <li>' . $detail_link3 . '</li>
                <li>' . $payments_link . '</li>
                <li>' . $add_payment_link . '</li>
                <li class="hide">' . $packagink_link . '</li>
                <li>' . $add_delivery_link . '</li>
            
                <li>' . $email_link . '</li>
                <li>' . $return_link . '</li>
                <li>' . $delete_link . '</li>
                <li class="making hide">' . $making_link . '</li>
            </ul>
        </div></div>';
        $this->load->library('datatables');
        $ds = "( SELECT sale_id,delivered_by, status FROM {$this->db->dbprefix('deliveries')} ) FSI";
        $this->datatables->where('sales.hide', 1);
        
            $this->datatables
                ->select($this->db->dbprefix('sales') . ".id as id, DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date, reference_no, {$this->db->dbprefix('sales')}.biller, {$this->db->dbprefix('sales')}.customer,companies.name, (grand_total+COALESCE(rounding, 0)),
                COALESCE(paid, 0), 
                ( (grand_total + COALESCE(rounding, 0)) - COALESCE( paid, 0) ) as balance , 
                 sale_status, produce_status, payment_status")
                ->from('sales')
                //->join('companies', 'companies.id=sales.customer_id', 'left')
                ->join('deliveries', 'deliveries.sale_id=sales.id', 'left')
                ->join('companies', 'deliveries.delivered_by = companies.id', 'left')
                ->group_by('sales.id');
        if ($bill_id) {
            $this->datatables->where('sales.bill_id', $bill_id);
        }
        $this->datatables->where('pos', 1);
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('bpas_sales.created_by', $this->session->userdata('user_id'));
            // $this->datatables->or_where("FIND_IN_SET(bpas_sales.warehouse_id, '".$warehouse_id."')");
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        if ($user_query) {
            $this->datatables->where('sales.created_by', $user_query);
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
        if ($warehouse) {
            $this->datatables->where('sales.warehouse_id', $warehouse);
        }
        if ($delivered_by) {
            $this->datatables->where('deliveries.delivered_by', $delivered_by);
        }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        $this->datatables->add_column('Actions', $action, 'id, cemail')->unset_column('cemail');
        echo $this->datatables->generate();
    }
    public function settings()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('pro_limit', $this->lang->line('pro_limit'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('pin_code', $this->lang->line('delete_code'), 'numeric');
        $this->form_validation->set_rules('category', $this->lang->line('default_category'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('customer', $this->lang->line('default_customer'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('biller', $this->lang->line('default_biller'), 'required|is_natural_no_zero');

        if ($this->form_validation->run() == true) {
            $print_product = implode(",", $this->input->post('print_product[]'));
            $data = [
                'pro_limit'                 => $this->input->post('pro_limit'),
                'pin_code'                  => $this->input->post('pin_code') ? $this->input->post('pin_code') : null,
                'default_category'          => $this->input->post('category'),
                'default_customer'          => $this->input->post('customer'),
                'default_biller'            => $this->input->post('biller'),
                'display_time'              => $this->input->post('display_time'),
                'receipt_printer'           => $this->input->post('receipt_printer'),
                'cash_drawer_codes'         => $this->input->post('cash_drawer_codes'),
                'cf_title1'                 => $this->input->post('cf_title1'),
                'cf_title2'                 => $this->input->post('cf_title2'),
                'cf_value1'                 => $this->input->post('cf_value1'),
                'cf_value2'                 => $this->input->post('cf_value2'),
                'focus_add_item'            => $this->input->post('focus_add_item'),
                'password_reason'           => $this->input->post('password_reason'),
                'add_manual_product'        => $this->input->post('add_manual_product'),
                'customer_selection'        => $this->input->post('customer_selection'),
                'add_customer'              => $this->input->post('add_customer'),
                'toggle_category_slider'    => $this->input->post('toggle_category_slider'),
                'toggle_subcategory_slider' => $this->input->post('toggle_subcategory_slider'),
                'toggle_brands_slider'      => $this->input->post('toggle_brands_slider'),
                'cancel_sale'               => $this->input->post('cancel_sale'),
                'suspend_sale'              => $this->input->post('suspend_sale'),
                'print_items_list'          => $this->input->post('print_items_list'),
                'finalize_sale'             => $this->input->post('finalize_sale'),
                'today_sale'                => $this->input->post('today_sale'),
                'open_hold_bills'           => $this->input->post('open_hold_bills'),
                'close_register'            => $this->input->post('close_register'),
                'tooltips'                  => $this->input->post('tooltips'),
                'keyboard'                  => $this->input->post('keyboard'),
                'pos_printers'              => $this->input->post('pos_printers'),
                'java_applet'               => $this->input->post('enable_java_applet'),
                'product_button_color'      => $this->input->post('product_button_color'),
                'paypal_pro'                => $this->input->post('paypal_pro'),
                'stripe'                    => $this->input->post('stripe'),
                'authorize'                 => $this->input->post('authorize'),
                'rounding'                  => $this->input->post('rounding'),
                'show_categories'           => $this->input->post('show_categories'),
                'item_order'                => $this->input->post('item_order'),
                'after_sale_page'           => $this->input->post('after_sale_page'),
                'printer'                   => $this->input->post('receipt_printer'),
                'order_printers'            => json_encode($this->input->post('order_printers')),
                'auto_print'                => $this->input->post('auto_print'),
                'remote_printing'           => DEMO ? 1 : $this->input->post('remote_printing'),
                'customer_details'          => $this->input->post('customer_details'),
                'local_printers'            => $this->input->post('local_printers'),
                'pos_type'                  => $this->input->post('pos_type'), 
                'separate'                  => $this->input->post('separate'),
                'show_category'             => $this->input->post('show_category'),
                'show_qty'                  => $this->input->post('show_qty'),
                'show_item'                 => $this->input->post('show_item'),
                'sale_due'                  => $this->input->post('sale_due'),
                'member_card'               => $this->input->post('member_card'),
                'coupon_card'               => $this->input->post('coupon_card'),
                'print_product'             => $print_product,
                'default_warehouse'         => $this->input->post('warehouse'),
                'show_close_register_products' => $this->input->post('show_close_register_products'),
            ];
            $payment_config = [
                'APIUsername'            => $this->input->post('APIUsername'),
                'APIPassword'            => $this->input->post('APIPassword'),
                'APISignature'           => $this->input->post('APISignature'),
                'stripe_secret_key'      => $this->input->post('stripe_secret_key'),
                'stripe_publishable_key' => $this->input->post('stripe_publishable_key'),
                'api_login_id'           => $this->input->post('api_login_id'),
                'api_transaction_key'    => $this->input->post('api_transaction_key'),
            ];
        } elseif ($this->input->post('update_settings')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('pos/settings');
        }

        if ($this->form_validation->run() == true && $this->pos_model->updateSetting($data)) {
            if (DEMO) {
                $this->session->set_flashdata('message', $this->lang->line('pos_setting_updated'));
                admin_redirect('pos/settings');
            }
            if ($this->write_payments_config($payment_config)) {
                $this->session->set_flashdata('message', $this->lang->line('pos_setting_updated'));
                admin_redirect('pos/settings');
            } else {
                $this->session->set_flashdata('error', $this->lang->line('pos_setting_updated_payment_failed'));
                admin_redirect('pos/settings');
            }
        } else {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['pos']        = $this->pos_model->getSetting();
            $this->data['stocktype'] = $this->site->getAllStockTypes();
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['customer'] = $this->pos_model->getCompanyByID($this->pos_settings->default_customer);
            $this->data['billers'] = $this->pos_model->getAllBillerCompanies();
            $this->config->load('payment_gateways');
            $this->data['stripe_secret_key']      = $this->config->item('stripe_secret_key');
            $this->data['stripe_publishable_key'] = $this->config->item('stripe_publishable_key');
            $authorize                            = $this->config->item('authorize');
            $this->data['api_login_id']           = $authorize['api_login_id'];
            $this->data['api_transaction_key']    = $authorize['api_transaction_key'];
            $this->data['APIUsername']            = $this->config->item('APIUsername');
            $this->data['APIPassword']            = $this->config->item('APIPassword');
            $this->data['APISignature']           = $this->config->item('APISignature');
            $this->data['printers']               = $this->pos_model->getAllPrinters();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['paypal_balance']         = null; // $this->pos_settings->paypal_pro ? $this->paypal_balance() : NULL;
            $this->data['stripe_balance']         = null; // $this->pos_settings->stripe ? $this->stripe_balance() : NULL;
            $bc                                   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('pos_settings')]];
            $meta                                 = ['page_title' => lang('pos_settings'), 'bc' => $bc];
            $this->page_construct('pos/settings', $meta, $this->data);
        }
    }

    public function stripe_balance()
    {
        if (!$this->Owner) {
            return false;
        }
        $this->load->admin_model('stripe_payments');

        return $this->stripe_payments->get_balance();
    }

    public function today_sale()
    {
        if (!$this->Owner && !$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->bpas->md();
        }

        $this->data['error']          = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['ccsales']        = $this->pos_model->getTodayCCSales();
        $this->data['cashsales']      = $this->pos_model->getTodayCashSales();
        $this->data['chsales']        = $this->pos_model->getTodayChSales();
        $this->data['pppsales']       = $this->pos_model->getTodayPPPSales();
        $this->data['stripesales']    = $this->pos_model->getTodayStripeSales();
        $this->data['authorizesales'] = $this->pos_model->getTodayAuthorizeSales();
        $this->data['totalsales']     = $this->pos_model->getTodaySales();
        $this->data['refunds']        = $this->pos_model->getTodayRefunds();
        $this->data['returns']        = $this->pos_model->getTodayReturns();
        $this->data['expenses']       = $this->pos_model->getTodayExpenses();
        $this->load->view($this->theme . 'pos/today_sale', $this->data);
    }

    public function updates()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $this->form_validation->set_rules('purchase_code', lang('purchase_code'), 'required');
        $this->form_validation->set_rules('envato_username', lang('envato_username'), 'required');
        if ($this->form_validation->run() == true) {
            $this->db->update('pos_settings', ['purchase_code' => $this->input->post('purchase_code', true), 'envato_username' => $this->input->post('envato_username', true)], ['pos_id' => 1]);
            admin_redirect('pos/updates');
        } else {
            $fields = ['version' => $this->pos_settings->version, 'code' => $this->pos_settings->purchase_code, 'username' => $this->pos_settings->envato_username, 'site' => base_url()];
            $this->load->helper('update');
            $protocol              = is_https() ? 'https://' : 'http://';
            $updates               = get_remote_contents($protocol . 'api.tecdiary.com/v1/update/', $fields);
            $this->data['updates'] = json_decode($updates);
            $bc                    = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('updates')]];
            $meta                  = ['page_title' => lang('updates'), 'bc' => $bc];
            $this->page_construct('pos/updates', $meta, $this->data);
        }
    }

    /* ------------------------------------------------------------------------------------ */

    public function view($sale_id = null, $modal = null,$tax_view=null)
    {
        $this->bpas->checkPermissions('index');
        $user_id = $this->session->userdata('user_id');
        $currency_id=$this->site->getCurrencyWarehouseByUserID($user_id);
        $curr=$this->site->getCurrencyByID($currency_id);
        
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }
        $this->load->helper('pos');
        $this->data['error']   = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        $inv                   = $this->pos_model->getInvoiceByIDTax($sale_id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by, true);
        }
        $this->data['rows']            = $this->pos_model->getAllInvoiceItemsGroup($sale_id);
        $this->data['rows_addon']      = $this->pos_model->getAllInvoiceItemsAddon($sale_id);
        $biller_id                     = $inv->biller_id;
        $customer_id                   = $inv->customer_id;
        $this->data['biller']          = $this->pos_model->getCompanyByID($biller_id);
        $this->data['customer']        = $this->pos_model->getCompanyByID($customer_id);
        $this->data['payments']        = $this->pos_model->getInvoicePayments($sale_id);
        $this->data['pos']             = $this->pos_model->getSetting();
        $this->data['barcode']         = $this->barcode($inv->reference_no, 'code128', 30);
        $this->data['return_sale']     = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows']     = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : null;
        $this->data['inv']             = $inv;
        $this->data['sid']             = $sale_id;
        $this->data['modal']           = $modal;
        $this->data['tax_view']        = $tax_view ? $this->site->getTaxItem('sale',$tax_view):'';
        $this->data['created_by']      = $this->site->getUser($inv->created_by);
        $this->data['printer']         = $this->pos_model->getPrinterByID($this->pos_settings->printer);
        $this->data['page_title']      = $this->lang->line('invoice');
        $this->data['exchange_rate_bat_in'] = $this->pos_model->getExchange_rate('THB');
        $this->data['exchange_rate_bat_out'] = $this->pos_model->getExchange_rate('BAT_o');
        if(!empty($curr) && ($curr->code =="THB" || $curr->code =="BAT_o")) {
            $this->load->view($this->theme . 'pos/view_bath_default', $this->data);
        } else {
            //$this->load->view($this->theme . 'pos/view_2_currency', $this->data);
            //$this->load->view($this->theme . 'pos/view_3_currency_time', $this->data);
            $this->load->view($this->theme . 'pos/view_3_currency', $this->data);
        }
    }
    
    public function view_express($sale_id = null, $modal = null)
    {
        $this->bpas->checkPermissions('index');
        $user_id = $this->session->userdata('user_id');
        $currency_id=$this->site->getCurrencyWarehouseByUserID($user_id);
        $curr=$this->site->getCurrencyByID($currency_id);
        
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }
        $this->load->helper('pos');
        $this->data['error']   = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        $inv                   = $this->pos_model->getInvoiceByID($sale_id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by, true);
        }
        $this->data['rows']            = $this->pos_model->getAllInvoiceItems($sale_id);
        $biller_id                     = $inv->biller_id;
        $customer_id                   = $inv->customer_id;
        $this->data['biller']          = $this->pos_model->getCompanyByID($biller_id);
        $this->data['customer']        = $this->pos_model->getCompanyByID($customer_id);
        $this->data['payments']        = $this->pos_model->getInvoicePayments($sale_id);
        $this->data['pos']             = $this->pos_model->getSetting();
        $this->data['barcode']         = $this->barcode($inv->reference_no, 'code128', 30);
        $this->data['return_sale']     = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows']     = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : null;
        $this->data['inv']             = $inv;
        $this->data['sid']             = $sale_id;
        $this->data['modal']           = $modal;
        $this->data['created_by']      = $this->site->getUser($inv->created_by);
        $this->data['printer']         = $this->pos_model->getPrinterByID($this->pos_settings->printer);
        $this->data['page_title']      = $this->lang->line('invoice');
        //    $this->load->view($this->theme . 'pos/view', $this->data);
        $this->data['exchange_rate_bat_in'] = $this->pos_model->getExchange_rate('THB');
        $this->data['exchange_rate_bat_out'] = $this->pos_model->getExchange_rate('BAT_o');

        $this->load->view($this->theme . 'pos/view_express', $this->data);
        
    }
    public function view_multi_invoices($sale_id = null, $modal = null)
    {
        $this->bpas->checkPermissions('index');
        $user_id = $this->session->userdata('user_id');
        $currency_id=$this->site->getCurrencyWarehouseByUserID($user_id);
        $curr=$this->site->getCurrencyByID($currency_id);
        
        if ($this->input->get('data')) {
            $sale_ids = $this->input->get('data');
        }

        $arr_sales_id=explode(",", $sale_ids);
   
        foreach($arr_sales_id as $sale_id)
        {

            $this->load->helper('pos');
            $this->data['error']   = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['message'] = $this->session->flashdata('message');
            $inv                           = $this->pos_model->getInvoiceByID($sale_id);
            if (!$this->session->userdata('view_right')) {
                $this->bpas->view_rights($inv->created_by, true);
            }
            $biller_id                     = $inv->biller_id;
            $customer_id                   = $inv->customer_id;

            $sale= array(
                        'rows'             => $this->pos_model->getAllInvoiceItems($sale_id),
                        'biller'           => $this->pos_model->getCompanyByID($biller_id),
                        'customer'         => $this->pos_model->getCompanyByID($customer_id),
                        'payments'         => $this->pos_model->getInvoicePayments($sale_id),
                        'pos'              => $this->pos_model->getSetting(),
                        'barcode'          => $this->barcode($inv->reference_no, 'code128', 30),
                        'return_sale'      => $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : null,
                        'return_rows'      => $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : null,
                        'return_payments'  => $inv->return_id  ? $this->pos_model->getInvoicePayments($this->pos_model->getInvoiceByID($inv->return_id)->id) : null,
                        'inv'              => $inv,
                        'sid'              => $sale_id,
                        'modal'            => $modal,
                        'created_by'       => $this->site->getUser($inv->created_by),
                        'printer'          => $this->pos_model->getPrinterByID($this->pos_settings->printer),
                        'page_title'       => $this->lang->line('invoice'),
                        'exchange_rate_bat_in'  => $this->pos_model->getExchange_rate('THB'),
                        'exchange_rate_bat_out' => $this->pos_model->getExchange_rate('BAT_o'),
             );
             $sales[] = $sale;
        }
  
        $this->data['sales'] = $sales;
        // $this->bpas->print_arrays($sales);

        if($curr->code =="THB" || $curr->code =="BAT_o"){
            $this->load->view($this->theme . 'pos/view_bath_default', $this->data);
        }else{
            $this->load->view($this->theme . 'pos/view_multi_invoices', $this->data);
        }
    }

    public function view_bill()
    {
        $this->bpas->checkPermissions('index');
        $this->data['tax_rates'] = $this->site->getAllTaxRates();
        $this->load->view($this->theme . 'pos/view_bill', $this->data);
    }

    public function write_payments_config($config)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        if (DEMO) {
            return true;
        }
        $file_contents = file_get_contents('./assets/config_dumps/payment_gateways.php');
        $output_path   = APPPATH . 'config/payment_gateways.php';
        $this->load->library('parser');
        $parse_data = [
            'APIUsername'            => $config['APIUsername'],
            'APIPassword'            => $config['APIPassword'],
            'APISignature'           => $config['APISignature'],
            'stripe_secret_key'      => $config['stripe_secret_key'],
            'stripe_publishable_key' => $config['stripe_publishable_key'],
            'api_login_id'           => $config['api_login_id'],
            'api_transaction_key'    => $config['api_transaction_key'],
        ];
        $new_config = $this->parser->parse_string($file_contents, $parse_data);

        $handle = fopen($output_path, 'w+');
        @chmod($output_path, 0777);

        if (is_writable($output_path)) {
            if (fwrite($handle, $new_config)) {
                @chmod($output_path, 0644);
                return true;
            }
            @chmod($output_path, 0644);
            return false;
        }
        @chmod($output_path, 0644);
        return false;
    }
	public function sales_tax($warehouse_id = null)
    {
        $this->bpas->checkPermissions('index');

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $user                       = $this->site->getUser();
            $this->data['warehouses']   = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse']    = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('pos'), 'page' => lang('pos')], ['link' => '#', 'page' => lang('pos_sales')]];
        $meta = ['page_title' => lang('pos_sales'), 'bc' => $bc];
        $this->page_construct('pos/sales_tax', $meta, $this->data);
    }
     public function getSalesTax($warehouse_id = null)
    {
        $this->bpas->checkPermissions('index');

        if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
            $user         = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        if ($this->input->get('user')) {
            $user_query = $this->input->get('user');
        } else {
            $user_query = NULL;
        }
        if ($this->input->get('reference_no')) {
            $reference_no = $this->input->get('reference_no');
        } else {
            $reference_no = NULL;
        }
        if ($this->input->get('customer')) {
            $customer = $this->input->get('customer');
        } else {
            $customer = NULL;
        }
        if ($this->input->get('saleman')) {
            $saleman = $this->input->get('saleman');
        } else {
            $saleman = NULL;
        }
        if ($this->input->get('product_id')) {
            $product_id = $this->input->get('product_id');
            
            //$this->erp->print_arrays($product_id);
        } else {
            $product_id = NULL;
        }
        if ($this->input->get('biller')) {
            $biller = $this->input->get('biller');
        } else {
            $biller = NULL;
        }
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        } else {
            $warehouse = NULL;
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
        
        if ($start_date) {
            $start_date = $this->erp->fld($start_date);
            $end_date = $this->erp->fld($end_date);
        }
        $duplicate_link    = anchor('admin/pos/?duplicate=$1', '<i class="fa fa-plus-square"></i> ' . lang('duplicate_sale'), 'class="duplicate_pos"');
        $detail_link       = anchor('admin/pos/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_receipt'));
        $detail_link2      = anchor('admin/sales/modal_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details_modal'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $detail_link3      = anchor('admin/sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        $payments_link     = anchor('admin/sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_payment_link  = anchor('admin/pos/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $packagink_link    = anchor('admin/sales/packaging/$1', '<i class="fa fa-archive"></i> ' . lang('packaging'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_delivery_link = anchor('admin/sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $email_link        = anchor('admin/#', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'class="email_receipt" data-id="$1" data-email-address="$2"');
        $edit_link         = anchor('admin/sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $return_link       = anchor('admin/sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        $delete_link       = "<a href='#' class='po' title='<b>" . lang('delete_sale') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_sale') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $duplicate_link . '</li>
                <li>' . $detail_link . '</li>
                <li>' . $detail_link2 . '</li>
                <li>' . $detail_link3 . '</li>
                <li>' . $payments_link . '</li>
                <li>' . $add_payment_link . '</li>
                <li>' . $packagink_link . '</li>
                <li>' . $add_delivery_link . '</li>
                <li>' . $edit_link . '</li>
                <li>' . $email_link . '</li>
                <li>' . $return_link . '</li>
                <li>' . $delete_link . '</li>
            </ul>
        </div></div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select($this->db->dbprefix('sales') . ".id as id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, biller, customer, (grand_total+COALESCE(rounding, 0)), paid, CONCAT(grand_total, '__', rounding, '__', paid) as balance, sale_status, payment_status, companies.email as cemail")
                ->from('sales')
                ->join('companies', 'companies.id=sales.customer_id', 'left')
                ->where(array('order_tax_id !=' => 1,'warehouse_id'=> $warehouse_id))
                ->group_by('sales.id');
        } else {
            $this->datatables
                ->select($this->db->dbprefix('sales') . ".id as id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, biller, customer, (grand_total+COALESCE(rounding, 0)), paid, CONCAT(grand_total, '__', rounding, '__', paid) as balance, sale_status, payment_status, companies.email as cemail")
                ->from('sales')
                ->join('companies', 'companies.id=sales.customer_id', 'left')
                ->group_by('sales.id')
                ->where(array('order_tax_id !=' => 1));
        }
        if ($product_id) {
            $this->datatables->join('sale_items', 'sale_items.sale_id = sales.id', 'left');
            $this->datatables->where('sale_items.product_id', $product_id);
        }
        
        $this->datatables->where('pos', 1);
        
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('sales.created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        
        if ($user_query) {
            $this->datatables->where('sales.created_by', $user_query);
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
        
        if($saleman){
            $this->datatables->where('sales.saleman_by', $saleman);
        }
        
        if ($warehouse) {
            $this->datatables->where('sales.warehouse_id', $warehouse);
        }

        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . ' 00:00:00" and "' . $end_date . ' 23:59:00"');
        }
        
        $this->datatables->where('pos', 1);
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column('Actions', $action, 'id, cemail')->unset_column('cemail');
        echo $this->datatables->generate();
    }
    //-----room---
    function updateRoomPriceMinutely($sid){
        $result= $this->pos_model->updateRoomPriceMinutely($sid);
        if($result){
    /*      $this->db->select('subtotal');
            $this->db->where(array('suspend_id' => $sid, 'product_code' => 'Time'));
            $q = $this->db->get('suspended_items');
            if ($q->num_rows() > 0) {
                $data = $q->row();
                echo $data->subtotal;
            }*/
            echo 'success';
        }
    }


    function kitchen_06_12_2021() 
    {
        $bill_refer =$this->input->get('bill_refer');
        $head_print_order =$this->input->get('head_print_order');
        $table = '';
        $this->db->select('order_status')->from('audit_order_item');
        $this->db->where(array('order_status' => 1, 'reference'=> $bill_refer));
        $cquery = $this->db->get();

        $this->db
             ->select($this->db->dbprefix('audit_order_item').".*,audit_order.status")
             ->from('audit_order')
             ->join('audit_order_item', 'audit_order.audit_id = audit_order_item.audit_id', 'left')
             ->order_by('audit_order_item.stock_type');

        // if($cquery->num_rows()) {
        //     $this->db->where(array('audit_order.reference'=> $bill_refer,
        //                         'audit_order_item.order_status'=>1));
        // }else{
        //     $this->db->where('audit_order.reference', $bill_refer);
        // }

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            $i=1; $j = 0; $k = 0;
            $arrayType[] = 0;
            foreach (($q->result()) as $row) {
                $arrayType[] = $row->stock_type;
            }
            sort($arrayType);
            foreach (($q->result()) as $row) {
                if($row->item_code !='Time'){
                    if($row->status == 0){
                        if($row->stock_type == $arrayType[$j++]){
                            $table = "<table width='100%' height='0' class='prT table table-striped'>
                                <header>
                                    <td style='visibility: hidden;'>No</td>
                                    <td style='visibility: hidden;'>Code</td>
                                    <td style='visibility: hidden;'>Product Name</td>
                                    <td style='visibility: hidden;'>Qty</td>
                                </header>";
                        } else {
                            $stock = $this->pos_model->getStockTypeByID($row->stock_type);
                            $table = ($head_print_order."Stock Type:".' '. strtoupper($stock->name)."<table width='50%' class='prT table table-striped'>
                                <header>
                                    <td>No</td>
                                    <td>Code</td>
                                    <td>Product Name</td>
                                    <td>Qty</td>
                                </header>");
                        }

                        $table .="<tr>
                                    <td>".$i."</td>
                                    <td>".$row->item_code."</td>
                                    <td>".$row->item_name."</td>
                                    <td>[ ".$row->qty. " ]</td>
                                </tr>";
                        $table .= "</table>";
                        echo $table;  
                    } else {
                        if ($row->new_qty != $row->last_order) {
                            if ($row->qty > $row->new_qty){
                                $quantity = $row->new_qty - $row->last_order;
                            } else {
                                $quantity = $row->new_qty - $row->last_order;
                            }
                            // if($row->order_status <1){
                            if ($row->stock_type == $arrayType[$k++]) {
                                $table = "<table width='50%'  height='0' class='prT table table-striped'>
                                            <header>
                                            <td style='visibility: hidden;'>No</td>
                                            <td style='visibility: hidden;'>Code</td>
                                            <td style='visibility: hidden;'>Product Name</td>
                                            <td style='visibility: hidden;'>Qty</td>
                                        </header>";
                            } else {
                                $stock = $this->pos_model->getStockTypeByID($row->stock_type);
                                $table = ($head_print_order . "Stock Type:" . ' ' . strtoupper($stock->name) . 
                                    "<table width='100%' class='prT table table-striped'>
                                    <header>
                                        <td>No</td>
                                        <td>Code</td>
                                        <td>Product Name</td>
                                        <td>Qty</td>
                                       
                                    </header>");
                            }

                            $table .="<tr>
                                    <td>".$i."</td>
                                    <td>".$row->item_code."</td>
                                    <td>".$row->item_name."</td>
                                    <td>[ ".$quantity. " ]</td>            
                                </tr>";
                            // }
                            $table .= "</table>";
                            echo $table;  
                        }
                    }
                    $i++;
                }    
            }
        }
    }
    public function kitchen_ordering()
    {

        if ($this->input->get('status') == 0) {
            $order_status = $this->input->get('status');
        } else {
            $order_status = 1;

        }
        echo "<div class='container-fluid' id='form-kitchen'>";
        echo '<center><h2 style="padding: 15px;">Kitchen Ordering Display<h2> ';
        $this->db->select("{$this->db->dbprefix('audit_order')}.*,
            CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) AS created_by, {$this->db->dbprefix('companies')}.name as customer, {$this->db->dbprefix('suspended_note')}.name as suspended_note")
            ->join('users', 'audit_order.user_id=users.id', 'left')
            ->join('companies', 'audit_order.customer_id=companies.id', 'left')
            ->join('suspended_bills', 'suspended_bills.id=audit_order.suspended_id', 'left')
            ->join('suspended_note', 'suspended_note.note_id=suspended_bills.suspend_note', 'left');
            $this->db->where('order_status', 1);
        $sus_bills = $this->db->order_by('tran_date')->get('audit_order');
        if ($sus_bills->num_rows() > 0) {
            $i = 1;
            echo "<table style='width: 100%;'>";
            foreach (($sus_bills->result()) as $number => $suspended_order) {
                $this->db->select("{$this->db->dbprefix('audit_order_item')}.id as s_id, {$this->db->dbprefix('audit_order_item')}.*,
                {$this->db->dbprefix('stock_type')}.name as stock_type")
                    ->join('products', 'products.id=audit_order_item.item_id', 'left')
                    ->join('stock_type', 'stock_type.id=products.stock_type', 'left')
                    ->where('reference', $suspended_order->reference)
                    ->where('audit_order_item.item_code !=', 'Time')
                    ->where('audit_order_item.new_qty !=', 0);
                $sus_items = $this->db->get('audit_order_item');
                $audit_order_item = [];
                if ($i == 3) {
                    $i = 1;
                }
                if ($i == 1) {
                    echo "<tr>";
                }
                echo "<td style='width: 50%; vertical-align: top;'>";
                if ($sus_items->num_rows() > 0) {
                    echo "<div style='margin: 15px; border: 1px solid black; border-radius: 5px; padding: 5px;'>";
                    echo "<h3 style='margin: 5px; text-align: center; color: red;'>" . lang('order') . ' : #' . ($number + 1) . "</h3>";
                    echo "<p style='margin: 5px;'>" . lang('date') . ' : ' . $this->bpas->hrld($suspended_order->tran_date) . "</p>";
                    echo "<p style='margin: 5px;'>" . lang('customer') . ' : ' . $suspended_order->customer . "</p>";
                    echo "<p style='margin: 5px;'>" . lang('raised_by') . ' : ' . $suspended_order->created_by . "</p>";
                    echo "<p style='margin: 5px;'>" . lang('table') . ' : ' . ($suspended_order->suspended_note ? $suspended_order->suspended_note : lang('take_out')) . "</p>";
                    echo "<p style='margin: 5px;'>" . lang('reference') . ' : ' . $suspended_order->reference . "</p>";
                    echo "<div class='table-responsive'>";
                    echo "<table class='table table-hover table-striped' style='width: 100%; border: 1px solid black; margin-bottom: 10px;'>";
                    foreach (($sus_items->result()) as $key => $item) {
                        $audit_order_item[$item->stock_type][$key] = $item;
                    }
                    ksort($audit_order_item);
                    if (is_array($audit_order_item)) {
                        foreach ($audit_order_item as $type => $items) {
                            foreach ($items as $key => $row) {
                                if ($row->ordering_status == 'pending') {
                                    $btn = '<button type="button" value="' . $row->item_row_id . '" data="pending" class="btn btn-warning btn_status" style="background-color: #edb409; border-radius: 5px;">' . lang($row->ordering_status) . '</button>';
                                } else {
                                    $btn = '<button type="button" value="' . $row->item_row_id . '" data="completed" class="btn btn-success btn_status" style="background-color: #218838; border-radius: 5px;">' . lang($row->ordering_status) . '</button>';
                                }
                                $audit_order_item = $this->pos_model->getAuditOrderItemsByItemRowID($row->item_row_id);
                                if ($row === reset($items)) {
                                    echo "<tr><th colspan='6' style='padding: 5px 0; text-align: left; background-color: #5DADE2 !important;'>" . lang('type') . ' : ' . ($row->stock_type != '' ? ucfirst($row->stock_type) : ucfirst(lang('other'))) . "</th>
                                            </tr>
                                            <tr>
                                                <th style='border: 1px solid black; background-color: #DCDCDC !important;'>" . lang('no') . "</th>
                                                <th style='border: 1px solid black; background-color: #DCDCDC !important;'>" . lang('time') . "</th>
                                                <th style='border: 1px solid black; background-color: #DCDCDC !important;'>" . lang('code') . "</th>
                                                <th style='border: 1px solid black; background-color: #DCDCDC !important;'>" . lang('name') . "</th>
                                                <th style='border: 1px solid black; background-color: #DCDCDC !important;'>" . lang('quantity') . "</th>
                                                <th style='border: 1px solid black; background-color: #DCDCDC !important;'>" . lang('status') . "</th>
                                            </tr>
                                            <tr>
                                                <td style='padding: 3px 0; border: 1px solid black; text-align: center;'>" . ($key + 1) . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; padding-left: 5px;'>" . $this->bpas->convert_datetime_to_time($audit_order_item->date) . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; padding-left: 5px;'>" . $row->item_code . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; padding-left: 5px;'>" . $row->item_name . ($row->comment ? (' (' . $row->comment . ')') : '') . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; text-align: center;'>" . $row->new_qty . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; text-align: center;'>" . $btn . "</td>
                                            </tr>
                                    ";
                                } else {
                                    echo "<tr>
                                                <td style='padding: 3px 0; border: 1px solid black; text-align: center;'>" . ($key + 1) . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; padding-left: 5px;'>" . $this->bpas->convert_datetime_to_time($audit_order_item->date) . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; padding-left: 5px;'>" . $row->item_code . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; padding-left: 5px;'>" . $row->item_name . ($row->comment ? (' (' . $row->comment . ')') : '') . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; text-align: center;'>" . $row->new_qty . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; text-align: center;'>" . $btn . "</td>
                                            </tr>";
                                }
                            }
                        }
                    }
                    echo "  </table>
                        </div>
                        <button value='" . $suspended_order->reference . "' class='btn_done pull-right' style='margin:10px; padding: 5px; width: 100px; background-color: #5AAAFF; border-radius: 5px; position: relative; left: 25%;'>" . lang('done') . "</button><button value='" . $suspended_order->reference . "' class='btn_hide pull-right' style='margin:10px; padding: 5px; width: 100px; background-color: #008000; border-radius: 5px; position: relative; left: 40%;'>" . lang('hide') . "</button>
                    </div>";
                }
                echo "</td>";
                if ((count($sus_bills->result()) - 1) == $number) {
                    if (count($sus_bills->result()) % 2 == 1) {
                        echo "<td>&nbsp;</td>";
                    }

                }

                if ($i == 2) {
                    echo "</tr>";
                }

                $i++;
            }
            echo "</table>";
        }
        echo "</div>";
        echo '<script src="' . $this->data['assets'] . 'js/jquery-3.3.1.min.js"></script>';
        echo '<script type="text/JavaScript">
                    $(document).ready(function(){
                        $.ajax({
                            type: "get",
                            url: "' . admin_url('pos/getVoiceItems') . '",
                            success: function(result){
                                if(result){
                                    for (let i = 0; i < result.length; i++) {
                                        text = result[i];
                                        console.log(result[i]);
                                        textToSpeak =  text.name+ " was "+ text.status;
                                        speakData = new SpeechSynthesisUtterance();
                                        speakData.volume = 1; // From 0 to 1
                                        speakData.rate = 1; // From 0.1 to 10
                                        speakData.pitch = 2; // From 0 to 2
                                        speakData.text = textToSpeak;
                                        speakData.lang = "en";
                                        speakData.voice = getVoices()[0];
                                        speechSynthesis.speak(speakData);
                                        $.ajax({
                                            type: "get",
                                            url: "' . admin_url('pos/rmVoiceItems') . '",
                                            data: { item_row_id: text.item_row_id},
                                            // success: function(result){
                                            //     // window.location.reload();
                                            // }
                                        })
                                    }
                                }
                                function getVoices() {
                                    let voices = speechSynthesis.getVoices();
                                    if(!voices.length){
                                      let utterance = new SpeechSynthesisUtterance("");
                                      speechSynthesis.speak(utterance);
                                      voices = speechSynthesis.getVoices();
                                    }
                                    return voices;
                                  }
                            }
                        })
                        /////////////////////////////
                        // function getVoices() {
                        //     let voices = speechSynthesis.getVoices();
                        //     if(!voices.length){
                        //       // some time the voice will not be initialized so we can call spaek with empty string
                        //       // this will initialize the voices 
                        //       let utterance = new SpeechSynthesisUtterance("");
                        //       speechSynthesis.speak(utterance);
                        //       voices = speechSynthesis.getVoices();
                        //     }
                        //     return voices;
                        //   }
                        //   function speak(text, voice, rate, pitch, volume) {
                        //     // create a SpeechSynthesisUtterance to configure the how text to be spoken 
                        //     let speakData = new SpeechSynthesisUtterance();
                        //     speakData.volume = volume; // From 0 to 1
                        //     speakData.rate = rate; // From 0.1 to 10
                        //     speakData.pitch = pitch; // From 0 to 2
                        //     speakData.text = text;
                        //     speakData.lang = "en";
                        //     speakData.voice = voice;
                        //     // pass the SpeechSynthesisUtterance to speechSynthesis.speak to start speaking 
                        //     speechSynthesis.speak(speakData);
                        //   }
                        //   if ("speechSynthesis" in window) {
                        //     let voices = getVoices();
                        //     let rate = 1, pitch = 2, volume = 1;
                        //     let text = "Spaecking with volume = 1 rate =1 pitch =2 ";
                        //     speak(text, voices[5], rate, pitch, volume);
                        //     setTimeout(()=>{ // speak after 2 seconds 
                        //       rate = 0.5; pitch = 1.5, volume = 0.5;
                        //       text = "Spaecking with volume = 0.5 rate = 0.5 pitch = 1.5 ";
                        //       speak(text, voices[10], rate, pitch, volume );
                        //     }, 500);
                        //   }else{
                        //     console.log(" Speech Synthesis Not Supported 😞"); 
                        //   }
                        $(".btn_status").click(function(e){
                            e.preventDefault();
                            var btn = $(this);
                            $.ajax({
                                type: "get",
                                url: "' . admin_url('pos/updateOrderItemStatus') . '",
                                data: { item_row_id: $(this).val(), data: $(this).attr("data") },
                                success: function(result){
                                    if(result.status == "pending") {
                                        btn.attr("data", "pending");
                                        btn.text("Pending");
                                        btn.css("background-color", "#edb409");
                                    } else {
                                        btn.attr("data", "completed");
                                        btn.text("Completed");
                                        btn.css("background-color", "#218838");
                                    }
                                }
                            })
                        });
                        $(".btn_done").click(function(e){
                            e.preventDefault();
                            var btn = $(this);
                            $.ajax({
                                type: "get",
                                url: "' . admin_url('pos/updateOrderItemStatus') . '",
                                data: { reference: $(this).val() },
                                success: function(result){
                                    window.location.reload();
                                }
                            })
                        });
                        $(".btn_hide").click(function(e){
                            e.preventDefault();
                            var btn = $(this);
                            $.ajax({
                                type: "get",
                                url: "' . admin_url('pos/updateOrderItemStatus') . '",
                                data: { reference2: $(this).val() },
                                success: function(result){
                                    window.location.reload();
                                }
                            })
                        });
                        $(".btn_all").click(function(e){
                            e.preventDefault();
                            var btn = $(this);
                            $.ajax({
                                type: "get",
                                url: "' . admin_url('pos/kitchen_ordering') . '",
                                data: { status: $(this).val() },
                                success: function(result){
                                    // window.location.reload();
                                }
                            })
                        });
                        setInterval(function () {
                            window.location.reload();
                            // $("#form-kitchen").load("' . admin_url('pos/kitchen') . '");
                        }, 10000);
                    });
            </script>';
    } 
    function kitchen() 
    {
        echo "<div class='container-fluid' id='form-kitchen'>";
        echo '<center><h2 style="padding: 15px;">Kitchen Display<h2></center>';
        $sus_bills = 
            $this->db->select("
                {$this->db->dbprefix('suspended_bills')}.*, 
                CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) AS created_by,
                {$this->db->dbprefix('companies')}.name as customer
            ")
            ->join('users', 'suspended_bills.created_by=users.id', 'left')
            ->join('companies', 'suspended_bills.customer_id=companies.id', 'left')
            ->order_by('date')
            ->get('suspended_bills');

        if ($sus_bills->num_rows() > 0) {
            $i = 1;
            echo "<table style='width: 100%;'>";
            foreach (($sus_bills->result()) as $number => $suspended_bill) {
                $this->db->select("{$this->db->dbprefix('suspended_items')}.id as s_id, {$this->db->dbprefix('suspended_items')}.*, {$this->db->dbprefix('stock_type')}.name as stock_type")
                    ->join('products', 'products.id=suspended_items.product_id', 'left')
                    ->join('stock_type', 'stock_type.id=products.stock_type', 'left')
                    ->where('suspend_id', $suspended_bill->id)
                    ->where('suspended_items.product_code !=', 'Time');
                $sus_items = $this->db->get('suspended_items');
                $suspended_items = [];

                if ($i == 3) $i = 1;
                if ($i == 1) echo "<tr>";
                echo "<td style='width: 50%; vertical-align: top;'>";
                if($sus_items->num_rows() > 0) {
                    echo "<div style='margin: 15px; border: 1px solid black; border-radius: 5px; padding: 5px;'>";
                    echo "<h3 style='margin: 5px; text-align: center; color: red;'>" . lang('order') . ' : #' . ($number+1) ."</h3>";
                    echo "<p style='margin: 5px;'>" . lang('date') . ' : ' . $this->bpas->hrld($suspended_bill->date) ."</p>";
                    echo "<p style='margin: 5px;'>" . lang('customer') . ' : ' . $suspended_bill->customer . "</p>";
                    echo "<p style='margin: 5px;'>" . lang('raised_by') . ' : ' . $suspended_bill->created_by . "</p>";
                    echo "<div class='table-responsive'>";
                    echo "<table class='table table-hover table-striped' style='width: 100%; border: 1px solid black; margin-bottom: 10px;'>";
                    foreach (($sus_items->result()) as $key => $item) {
                        $suspended_items[$item->stock_type][$key] = $item;
                    }
                    ksort($suspended_items);
                    if (is_array($suspended_items)) {
                        foreach ($suspended_items as $type => $items) {
                            foreach ($items as $key => $row) {  
                                if ($row->status == 'pending') {
                                    $btn = '<button type="button" value="' . $row->s_id . '" data="pending" class="btn btn-warning btn_status" style="background-color: #edb409; border-radius: 5px;">' . lang($row->status) . '</button>';
                                } else {
                                    $btn = '<button type="button" value="' . $row->s_id . '" data="completed" class="btn btn-success btn_status" style="background-color: #218838; border-radius: 5px;">' . lang($row->status) . '</button>';
                                }
                                if ($row === reset($items)) {
                                    echo "
                                            <tr>
                                                <th colspan='5' style='padding: 5px 0; text-align: left; background-color: #5DADE2 !important;'>" . lang('type') . ' : ' . ($row->stock_type != '' ? ucfirst($row->stock_type) : ucfirst(lang('other'))) . "</th>
                                            </tr>
                                            <tr>
                                                <th style='border: 1px solid black; background-color: #DCDCDC !important;'>" . lang('no') . "</th>
                                                <th style='border: 1px solid black; background-color: #DCDCDC !important;'>" . lang('code') . "</th>
                                                <th style='border: 1px solid black; background-color: #DCDCDC !important;'>" . lang('name') . "</th>
                                                <th style='border: 1px solid black; background-color: #DCDCDC !important;'>" . lang('quantity') . "</th>
                                                <th style='border: 1px solid black; background-color: #DCDCDC !important;'>" . lang('status') . "</th>
                                            </tr>
                                            <tr>
                                                <td style='padding: 3px 0; border: 1px solid black; text-align: center;'>" . ($key+1) . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; padding-left: 5px;'>" . $row->product_code . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; padding-left: 5px;'>" . $row->product_name . ($row->comment ? (' (' . $row->comment . ')') : '') . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; text-align: center;'>" . $row->quantity . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; text-align: center;'>" . $btn . "</td>
                                            </tr>
                                    ";
                                } else {
                                    echo "      
                                            <tr>
                                                <td style='padding: 3px 0; border: 1px solid black; text-align: center;'>" . ($key+1) . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; padding-left: 5px;'>" . $row->product_code . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; padding-left: 5px;'>" . $row->product_name . ($row->comment ? (' (' . $row->comment . ')') : '') . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; text-align: center;'>" . $row->quantity . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; text-align: center;'>" . $btn . "</td>
                                            </tr>";
                                }   
                            }   
                        }
                    }

                    echo "  </table>
                        </div>
                        <button value='".$suspended_bill->id."' class='btn_done pull-right' style='padding: 5px; width: 100px; background-color: #5AAAFF; border-radius: 5px; position: relative; left: 85%;'>".lang('done')."</button>
                    </div>";        
                }

                echo "</td>";
                if ((count($sus_bills->result())-1) == $number) {
                    if(count($sus_bills->result()) % 2 == 1) echo "<td>&nbsp;</td>";
                }

                if ($i == 2) echo "</tr>";
                $i++;
            }
            echo "</table>";
        }
        echo "</div>";
        echo '<script src="' . $this->data['assets'] . 'js/jquery-3.3.1.min.js"></script>';
        echo '<script type="text/JavaScript"> 
                    $(document).ready(function(){
                        $(".btn_status").click(function(e){ 
                            e.preventDefault();
                            var btn = $(this);
                            $.ajax({
                                type: "get",
                                url: "'.admin_url('pos/updateSuspendItemStatus').'",
                                data: { id: $(this).val(), data: $(this).attr("data") },
                                success: function(result){
                                    if(result.status == "pending") {
                                        btn.attr("data", "pending");
                                        btn.text("Pending");
                                        btn.css("background-color", "#edb409");
                                    } else {
                                        btn.attr("data", "completed");
                                        btn.text("Completed");
                                        btn.css("background-color", "#218838");
                                    }
                                }
                            })
                        });

                        $(".btn_done").click(function(e){ 
                            e.preventDefault();
                            var btn = $(this);
                            $.ajax({
                                type: "get",
                                url: "'.admin_url('pos/updateSuspendItemStatus').'",
                                data: { bill_id: $(this).val() },
                                success: function(result){
                                    window.location.reload();        
                                }
                            })
                        });

                        setInterval(function () {
                            window.location.reload();
                            // $("#form-kitchen").load("'.admin_url('pos/kitchen').'");
                        }, 10000);
                    });
            </script>'
        ;       
    }

    function making() 
    {
        $this->bpas->checkPermissions('pos',true,'making');
        echo "<div class='container-fluid' id='form-kitchen'>";
        echo '<center><h2 style="padding: 15px;">Product Producing Display<h2></center>';
        $sales = 
            $this->db->select("
                {$this->db->dbprefix('sales')}.*, 
                CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) AS created_by,
                {$this->db->dbprefix('companies')}.name as customer
            ")
            ->join('users', 'sales.created_by=users.id', 'left')
            ->join('companies', 'sales.customer_id=companies.id', 'left')
            ->where('produce_status', 'making')
            ->order_by('date')
            ->get('sales');

        if ($sales->num_rows() > 0) {
            $i = 1;
            echo "<table style='width: 100%;'>";
            foreach (($sales->result()) as $number => $sale) {
                $this->db->select("{$this->db->dbprefix('sale_items')}.id as s_id, {$this->db->dbprefix('sale_items')}.*, {$this->db->dbprefix('stock_type')}.name as stock_type")
                    ->join('products', 'products.id=sale_items.product_id', 'left')
                    ->join('stock_type', 'stock_type.id=products.stock_type', 'left')
                    ->where('sale_id', $sale->id);
                $sale_items = $this->db->get('sale_items');
                $arr_items = [];

                if ($i == 3) $i = 1;
                if ($i == 1) echo "<tr>";
                echo "<td style='width: 50%; vertical-align: top;'>";
                if($sale_items->num_rows() > 0) {
                    echo "<div style='margin: 15px; border: 1px solid black; border-radius: 5px; padding: 5px;'>";
                    echo "<h3 style='margin: 5px; text-align: center; color: red;'>" . lang('order') . ' : #' . ($number+1) ."</h3>";
                    echo "<p style='margin: 5px;'>" . lang('date') . ' : ' . $this->bpas->hrld($sale->date) ."</p>";
                    echo "<p style='margin: 5px;'>" . lang('reference_no') . ' : ' . $sale->reference_no . "</p>";
                    echo "<p style='margin: 5px;'>" . lang('customer') . ' : ' . $sale->customer . "</p>";
                    echo "<p style='margin: 5px;'>" . lang('raised_by') . ' : ' . $sale->created_by . "</p>";
                    echo "<div class='table-responsive'>";
                    echo "<table class='table table-hover table-striped' style='width: 100%; border: 1px solid black; margin-bottom: 10px;'>";
                    foreach (($sale_items->result()) as $key => $item) {
                        $arr_items[$item->stock_type][$key] = $item;
                    }
                    ksort($arr_items);
                    if (is_array($arr_items)) {
                        foreach ($arr_items as $type => $items) {
                            foreach ($items as $key => $row) {  
                                if ($row->making_status == 'pending') {
                                    $btn = '<button type="button" value="' . $row->s_id . '" data="pending" class="btn btn-warning btn_status" style="background-color: #edb409; border-radius: 5px;">' . lang($row->making_status) . '</button>';
                                } else {
                                    $btn = '<button type="button" value="' . $row->s_id . '" data="completed" class="btn btn-success btn_status" style="background-color: #218838; border-radius: 5px;">' . lang($row->making_status) . '</button>';
                                }
                                if ($row === reset($items)) {
                                    echo "
                                            <tr>
                                                <th colspan='6' style='padding: 5px 0; text-align: left; background-color: #5DADE2 !important;'>" . lang('type') . ' : ' . ($row->stock_type != '' ? ucfirst($row->stock_type) : ucfirst(lang('other'))) . "</th>
                                            </tr>
                                            <tr>
                                                <th style='border: 1px solid black; background-color: #DCDCDC !important;'>" . lang('no') . "</th>
                                                <th style='border: 1px solid black; background-color: #DCDCDC !important;'>" . lang('code') . "</th>
                                                <th style='border: 1px solid black; background-color: #DCDCDC !important;'>" . lang('name') . "</th>
                                                <th style='border: 1px solid black; background-color: #DCDCDC !important;'>" . lang('quantity') . "</th>
                                                <th style='border: 1px solid black; background-color: #DCDCDC !important;'>" . lang('note') . "</th>
                                                <th style='border: 1px solid black; background-color: #DCDCDC !important;'>" . lang('status') . "</th>
                                            </tr>
                                            <tr>
                                                <td style='padding: 3px 0; border: 1px solid black; text-align: center;'>" . ($key+1) . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; padding-left: 5px;'>" . $row->product_code . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; padding-left: 5px;'>" . $row->product_name . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; text-align: center;'>" . $row->quantity . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; padding-left: 5px;'>" . strip_tags($row->comment) . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; text-align: center;'>" . $btn . "</td>
                                            </tr>
                                    ";
                                } else {
                                    echo "      
                                            <tr>
                                                <td style='padding: 3px 0; border: 1px solid black; text-align: center;'>" . ($key+1) . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; padding-left: 5px;'>" . $row->product_code . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; padding-left: 5px;'>" . $row->product_name . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; text-align: center;'>" . $row->quantity . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; padding-left: 5px;'>" . strip_tags($row->comment) . "</td>
                                                <td style='padding: 3px 0; border: 1px solid black; text-align: center;'>" . $btn . "</td>
                                            </tr>";
                                }   
                            }   
                        }
                    }

                    echo "  </table>
                        </div>
                        <button value='".$sale->id."' class='btn_done pull-right' style='padding: 5px; width: 100px; background-color: #5AAAFF; border-radius: 5px; position: relative; left: 89%;'>".lang('done')."</button>
                    </div>";        
                }

                echo "</td>";
                if ((count($sales->result())-1) == $number) {
                    if(count($sales->result()) % 2 == 1) echo "<td>&nbsp;</td>";
                }

                if ($i == 2) echo "</tr>";
                $i++;
            }

            echo "</table>";
        }
        echo "</div>";
        echo '<script src="' . $this->data['assets'] . 'js/jquery-3.3.1.min.js"></script>';
        echo '<script type="text/JavaScript"> 
                    $(document).ready(function(){
                        $(".btn_status").click(function(e){ 
                            e.preventDefault();
                            var btn = $(this);
                            $.ajax({
                                type: "get",
                                url: "'.admin_url('pos/updateSaleItemStatus').'",
                                data: { id: $(this).val(), data: $(this).attr("data") },
                                success: function(result){
                                    if(result.status == "pending") {
                                        btn.attr("data", "pending");
                                        btn.text("Pending");
                                        btn.css("background-color", "#edb409");
                                    } else if (result.status == "pending") {
                                        btn.attr("data", "completed");
                                        btn.text("Completed");
                                        btn.css("background-color", "#218838");
                                    } else {
                                        window.location.reload(); 
                                    }
                                }
                            })
                        });

                        $(".btn_done").click(function(e){ 
                            e.preventDefault();
                            var btn = $(this);
                            $.ajax({
                                type: "get",
                                url: "'.admin_url('pos/updateSaleItemStatus').'",
                                data: { sale_id: $(this).val() },
                                success: function(result){
                                    window.location.reload();        
                                }
                            })
                        });

                        setInterval(function () {
                            window.location.reload();
                            // $("#form-kitchen").load("'.admin_url('pos/kitchen').'");
                        }, 10000);
                    });
            </script>'
        ;       
    }
    public function rmVoiceItems()
    {
        if ($this->input->get('item_row_id')) {
            $item_row_id = $this->input->get('item_row_id');
                $this->db->delete('voice_items', ['item_row_id' => $item_row_id]);
            $this->bpas->send_json(1);
        }
       
    }
    public function getVoiceItems()
    {
        $this->db->select('*');
        $q = $this->db->get('voice_items');
        if ($q->num_rows() > 0) {
            $this->bpas->send_json($q->result());
        }
    }
    public function updateOrderItemStatus()
    {
        if ($this->input->get('item_row_id')) {
            $item_row_id = $this->input->get('item_row_id');
            $status = $this->input->get('data');
            if ($status == 'pending') {
                $this->db->update('audit_order_item', ['ordering_status' => 'completed'], ['item_row_id' => $item_row_id]);
            } else {
                $this->db->update('audit_order_item', ['ordering_status' => 'pending'], ['item_row_id' => $item_row_id]);
            }
            if($item = $this->pos_model->getAuditOrderItemsByItemRowID($item_row_id)){
                $this->db->insert('voice_items', ['item_row_id' => $item_row_id, 'name'=> $item->item_name, 'code'=>$item->item_code, 'status'=> ($status == 'pending' ? 'completed' : 'pending')]);
            }
            $this->bpas->send_json(['status' => ($status == 'pending' ? 'completed' : 'pending')]);
        } elseif ($this->input->get('reference')) {
            $reference = $this->input->get('reference');
            $this->db->update('audit_order_item', ['ordering_status' => 'completed'], ['reference' => $reference]);
            $this->bpas->send_json(1);
        } elseif ($this->input->get('reference2')) {
            $reference = $this->input->get('reference2');
            $this->db->update('audit_order', ['order_status' => 0], ['reference' => $reference]);
            $this->bpas->send_json(1);
        }
    }
    function updateSuspendItemStatus() 
    {
        if($this->input->get('id')) {
            $id     = $this->input->get('id');
            $status = $this->input->get('data');
            if($status == 'pending') 
                $this->db->update('suspended_items', ['status' => 'completed'], ['id' => $id]);
            else 
                $this->db->update('suspended_items', ['status' => 'pending'], ['id' => $id]);
            $this->bpas->send_json(['status' => ($status == 'pending' ? 'completed' : 'pending')]);
        } elseif ($this->input->get('bill_id')) {
            $bill_id = $this->input->get('bill_id');
            $this->db->update('suspended_items', ['status' => 'completed'], ['suspend_id' => $bill_id]);
            $this->bpas->send_json(1);
        }
    }

    function updateSaleItemStatus() 
    {
        if($this->input->get('id')) {
            $id     = $this->input->get('id');
            $status = $this->input->get('data');
            if($status == 'pending') {
                $status = 'completed';
                $this->db->update('sale_items', ['making_status' => 'completed'], ['id' => $id]);
            } else {
                $status = 'pending';
                $this->db->update('sale_items', ['making_status' => 'pending'], ['id' => $id]);
            }

            $q = $this->db->get_where('sale_items', ['id' => $id], 1);
            if ($q->num_rows() > 0) {
                $query = $this->db->get_where('sale_items', ['sale_id' => $q->row()->sale_id, 'making_status' => 'pending']);
                if ($query->num_rows() > 0) {
                    $this->db->update('sales', ['produce_status' => 'making'], ['id' => $q->row()->sale_id]);
                } else {
                    $status = 'done';
                    $this->db->update('sales', ['produce_status' => 'completed'], ['id' => $q->row()->sale_id]);
                }
            }
            $this->bpas->send_json(['status' => $status]);

        } elseif ($this->input->get('sale_id')) {
            $sale_id = $this->input->get('sale_id');
            $this->db->update('sale_items', ['making_status' => 'completed'], ['sale_id' => $sale_id]);
            $this->db->update('sales', ['produce_status' => 'completed'], ['id' => $sale_id]);
            $this->bpas->send_json(1);
        }
    }

    function kitchens()
    {
        $bill_refer = $this->input->get('bill_refer');
        $table_id = $this->input->get('table_id');
        $head_print_order = $this->input->get('head_print_order');
        $table = '';
        $this->db->select('order_status')->from('audit_order_item');
        $this->db->where('audit_order_item.user_id', $this->session->userdata('user_id'));
        $this->db->where(array('print_status' => 1, 'order_status' => 1,'suspend_note' => $table_id, 'reference' => $bill_refer));
        $cquery = $this->db->get();
        $this->db
        ->select("{$this->db->dbprefix('audit_order_item')}.* ,{$this->db->dbprefix('products')}.stock_type as stock__type, audit_order.status,products.product_details as product_details")
        ->from('audit_order')
        ->join('audit_order_item', 'audit_order.audit_id = audit_order_item.audit_id', 'left')
        ->join('products', 'products.id = audit_order_item.item_id', 'left');
        $this->db->where('audit_order_item.print_status', 1);
        $this->db->where('audit_order_item.suspend_note', $table_id);
        if ($cquery->num_rows()) {
            $this->db->where(array(
                'audit_order.reference' => $bill_refer, 
                'audit_order.suspend_note' => $table_id, 
                'audit_order_item.suspend_note' => $table_id, 
                'audit_order_item.order_status' => 1
            ));
        } else {
            $this->db->where('audit_order.reference', $bill_refer);
            $this->db->where('audit_order.suspend_note', $table_id);
        }
        $q = $this->db->get();
     
        if ($q->num_rows() > 0) { 
            $j = 1;
            $k = 0;
            $arrayType[] = 0;
            $myJSON[] = 0;   
            foreach (($q->result()) as $row) {
                if ($row->item_code != 'Time') {
                    $addon_items = $this->pos_model->getSuspendedSaleAddOnItemsByItemRowID($row->item_row_id);
                    $addon_note =  array(); 
                    if(!empty($addon_items) && $addon_items != false){
                        foreach($addon_items as $item){  
                            if($item->item_row_id == $row->item_row_id){
                                $addon_note[] = $item->product_name . ' ['. $item->quantity.']';  
                            }
                        }
                    }
                    $combo_items = $this->pos_model->getComboItems($row->item_id);
                    $combo_note =  array(); 
                    if(!empty($combo_items) && $combo_items != false){
                        foreach($combo_items as $combo_item){  
                            // if($item->item_row_id == $row->item_row_id){
                                $combo_note[] = $combo_item->product_name . ' ['.$this->bpas->formatDecimal($combo_item->quantity, 2) .']';    
                            // }
                        }
                    }
                    if ($row->status == 0) {
                        $myJSON[] = [
                            'items' => [
                                'type'    => $row->stock__type,
                                // 'product_details'  => $this->bpas->remove_tag($row->product_details),
                                'no'      => $j,
                                'code'    => $row->item_code,
                                'name'    => $row->item_name,
                                'addon'   => isset($addon_note) ? $addon_note : null,
                                'combos' => isset($combo_note) ? $combo_note : null,
                                'comment' => $row->comment,
                                'qty'     => $this->bpas->formatDecimal($row->qty, 0)
                            ]
                        ];
                        } else { 
                        if ($row->new_qty != $row->last_order) {
                            if ($row->qty > $row->new_qty) {
                                $quantity = $row->new_qty - $row->last_order;
                            } else {
                                $quantity = $row->new_qty - $row->last_order;
                            } 
                            $myJSON[] = ['items' => 
                                [
                                    'type'  => $row->stock__type,
                                    // 'product_details'  => $this->bpas->remove_tag($row->product_details),
                                    'no'    => $j,
                                    'code'  => $row->item_code,
                                    'name'  => $row->item_name,
                                    'combos' => isset($combo_note) ? $combo_note : null,
                                    'comment'  => $row->comment,
                                    'qty'   => $quantity
                                ]
                            ]; 
                        }
                    } 
                    $j++;
                }
            } 
           
            $this->bpas->send_json($myJSON); 
        }
    }
      function kitchens_popup()
        {
            $bill_refer = $this->input->get('bill_refer');
            $head_print_order = $this->input->get('head_print_order');
            $table = $head_print_order;
            $table .= "<table width='100%' class='prT table table-striped'>";
            $table .= "<header>
                        <td>No</td>
                        <td>Code</td>
                        <td>Product Name</td>
                        <td>Qty</td>
                        </header>";
            $this->db->select('order_status')->from('audit_order_item');
            $this->db->where(array('order_status' => 1, 'reference' => $bill_refer));
            $cquery = $this->db->get();
            $this->db
                ->select("{$this->db->dbprefix('audit_order_item')}.*, audit_order.status")
                ->from('audit_order')
                ->group_by('audit_order_item.item_id')
                ->join('audit_order_item', 'audit_order.audit_id = audit_order_item.audit_id', 'left');
            if ($cquery->num_rows()) {
                $this->db->where(array(
                    'audit_order.reference' => $bill_refer,
                    'audit_order_item.order_status' => 1
                ));
            } else {
                $this->db->where('audit_order.reference', $bill_refer);
            }
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                $i = 1;
                foreach (($q->result()) as $row) {
                    if ($row->item_code != 'Time') {
                        if ($row->status == 0) {
                            $table .= "<tr>
                                    <td>" . $i . "</td>
                                    <td>" . $row->item_code . "</td>
                                    <td>" . $row->item_name . "</td>
                                    <td>" . $this->bpas->formatDecimal($row->qty,0) . "</td>
                                </tr>";
                        } else {
                            if ($row->new_qty != $row->last_order) {
                                if ($row->qty > $row->new_qty) {
                                    $quantity = $row->new_qty - $row->last_order;
                                } else {
                                    $quantity = $row->new_qty - $row->last_order;
                                }
                                // if($row->order_status < 1){
                                $table .= "<tr>
                                            <td>" . $i . "</td>
                                            <td>" . $row->item_code . "</td>
                                            <td>" . $row->item_name . "</td>
                                            <td>" . $this->bpas->formatDecimal($quantity,0) . "</td>
                                        </tr>";
                                // }
                            }
                        }
                        $i++;
                    }
                }
            }
            $table .= "</table>";
            echo $table;
        }
        function kitchenss()
        {
            $bill_refer = $this->input->get('bill_refer');
            $head_print_order = $this->input->get('head_print_order');
            $table = $head_print_order;
            $table .= "<table width='100%' class='prT table table-striped'>";
            $table .= "<header>
                        <td>No</td>
                        <td>Code</td>
                        <td>Product Name</td>
                        <td>Qty</td>
                    </header>";
            $this->db->select('order_status')->from('audit_order_item');
            $this->db->where(array('order_status' => 1, 'reference' => $bill_refer));
            $cquery = $this->db->get();
            $this->db->select($this->db->dbprefix('audit_order_item') . ".*, audit_order.status")->from('audit_order')
                ->join('audit_order_item', 'audit_order.audit_id = audit_order_item.audit_id', 'left');

            if ($cquery->num_rows()) {
                $this->db->where(array(
                    'audit_order.reference' => $bill_refer,
                    'audit_order_item.order_status' => 1
                ));
            } else {
                $this->db->where('audit_order.reference', $bill_refer);
            }
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                $i = 1;
                foreach (($q->result()) as $row) {
                    if ($row->item_code != 'Time') {
                        if ($row->status == 0) {
                            $table .= "<tr>
                                    <td>" . $i . "</td>
                                    <td>" . $row->item_code . "</td>
                                    <td>" . $row->item_name . "</td>
                                    <td>[" . $row->qty . "]</td>
                                </tr>";
                        } else {
                            if ($row->new_qty != $row->last_order) {
                                if ($row->qty > $row->new_qty) {
                                    $quantity = $row->new_qty - $row->last_order;
                                } else {
                                    $quantity = $row->new_qty;
                                }
                                // if($row->order_status <1){
                                $table .= "<tr>
                                            <td>" . $i . "</td>
                                            <td>" . $row->item_code . "</td>
                                            <td>" . $row->item_name . "</td>
                                            <td>[" . $quantity . "]</td>
                                        </tr>";
                                // }
                            }
                        }
                        $i++;
                    }
                }
            }
            $table .= "</table>";
            echo $table;
        }
        public function audit_trail_order()
        {
            $bill_refer = $this->input->post('bill_refer');
            $table_id = $this->input->post('table_id');
            $suspended_id = $this->input->post('suspended_id');
            $warehouse_id = $this->input->post('warehouse_id');
            
            $this->db->select('reference')->from('audit_order')->where('reference', $bill_refer)->where('suspend_note', $table_id);
            $query = $this->db->get();
            
            if ($query->num_rows()) {
                $this->db->select('product_id, item_row_id, CAST(quantity AS DECIMAL(25,2)) as quantity')->from('suspended_bills');
                $this->db->join('suspended_items', 'suspended_bills.id = suspended_items.suspend_id');
                $this->db->where(array(
                    'refer' => $bill_refer,
                    'suspend_note' => $table_id,
                    'product_id !=' => 0,
                ));
                $this->db->order_by("product_id", "asc");
                $this->db->group_by("item_row_id");
                $sq = $this->db->get()->result_array();
                $suspended_items = ($sq);
                $this->db->select('item_id as product_id, item_row_id,  CAST(new_qty AS DECIMAL(25,2)) as quantity')->from('audit_order_item');
                $this->db->where(array(
                    'reference' => $bill_refer,
                    'suspend_note' => $table_id,
                    'new_qty !=' => 0,
                ));
                $this->db->order_by("item_id", "asc");
                $this->db->group_by("item_row_id");
                $aoi = $this->db->get()->result_array();
                $audit_order_items = ($aoi);
                // $token = '1882945178:AAHI-f7eaHDz8ryCruFeGd3pvdKR3muXeME';
                // if($token != ""){
                //     $link = 'https://api.telegram.org:443/bot'. $token .'';
                //     $getupdate = file_get_contents($link.'/getUpdates');
                //     $responsearray = json_decode($getupdate, TRUE);
                //     $chatid = '601026228';
                //     $chatname = $responsearray['result'][0]['message']['chat']['first_name'] ." " . $responsearray['result'][0]['message']['chat']['last_name'];
                //     $parameter = array('chat_id' => $chatid, 'text' => 'audit_order_items : '.json_encode($audit_order_items). 'suspended_items : '.json_encode($suspended_items));
                //     $request_url = $link.'/sendMessage?'.http_build_query($parameter); 
                //     file_get_contents($request_url);
                // }
                if (!($audit_order_items == $suspended_items)) { 
                    $this->db->where(array('reference' => $bill_refer, 'new_qty' => 0, 'suspend_note' => $table_id));
                    $this->db->update("audit_order_item", array('print_status' => 0, 'suspend_note' => $table_id));
                    $this->db->where(array('reference' => $bill_refer, 'suspend_note' => $table_id));
                    $this->db->update("audit_order", array('status' => 1, 'suspend_note' => $table_id));
                    $this->db->select('*, suspended_items.item_row_id as item_row_id, SUM(quantity) as sus_qty,suspended_bills.id as suspended_bill_id')->from('suspended_bills');
                    $this->db->join('suspended_items', 'suspended_bills.id = suspended_items.suspend_id');
                    $this->db->where(array('refer' => $bill_refer, 'suspend_note' => $table_id, 'suspended_items.product_id !=' => 0));
                    $this->db->group_by('suspended_items.item_row_id');
                    $query = $this->db->get(); 
                    if ($query->num_rows()) {
                        $new_author = $query->result_array();
                        foreach ($new_author as $row) {
                            $this->db->select('item_id, item_row_id, new_qty')->from('audit_order_item');
                            $this->db->where(array('item_id' => $row['product_id'], 'item_row_id' => $row['item_row_id'], 'reference' => $bill_refer, 'suspend_note' => $table_id, 'item_id' != 0));
                            $cquery = $this->db->get(); 
                            if ($cquery->num_rows()) {
                                $old_qty = $cquery->row();
                                $this->db->where(array('reference' => $bill_refer, 'suspend_note' => $table_id,
                                    'item_id'     => $row['product_id'],
                                    'item_row_id' => $row['item_row_id'],
                                ));
                                $this->db->update("audit_order_item", array(
                                    'new_qty'      => $row['sus_qty'],
                                    'comment'      => $row['comment'],
                                    'suspend_note' => $table_id,
                                    'last_order'   => $old_qty->new_qty,
                                    'order_status' => 0,
                                ));
                                $this->db->select('*')->from('audit_order_item');
                                $this->db->where(array('reference' => $bill_refer, 'suspend_note' => $table_id));
                                $bill_get = $this->db->get();
                                $bill_query = $bill_get->result_array();
                                foreach ($bill_query as $row1) {
                                    $this->db->select('suspended_items.item_row_id as item_row_id, comment, SUM(quantity) as sus_qty')->from('suspended_bills');
                                    $this->db->join('suspended_items', 'suspended_bills.id = suspended_items.suspend_id');
                                    $this->db->where(array(
                                        'suspend_note' => $row1['suspend_note'],
                                        'refer' => $row1['reference'],
                                        'item_row_id' => $row1['item_row_id'],
                                        'product_id' => $row1['item_id'],
                                    ));
                                    $this->db->group_by('suspended_items.item_row_id');
                                    $sus_query = $this->db->get();
                                    if (!$sus_query->num_rows()) {
                                        $this->db->where(array(
                                            'suspend_note' => $table_id,
                                            'reference' => $bill_refer,
                                            'item_row_id' => $row1['item_row_id'],
                                            'item_id' => $row1['item_id'])
                                        );
                                        $this->db->update("audit_order_item", array(
                                            'last_order' => $row1['new_qty'] != 0 ? $row1['new_qty'] : $row1['last_order'],
                                            // 'comment'     => $row['comment'],
                                            'new_qty' => 0,
                                            'suspend_note' => $table_id,
                                        ));
                                    }
                                }
                            } else {
                                $this->db->select('audit_id,reference')->from('audit_order')->where('reference', $bill_refer)->where('suspend_note', $table_id);
                                $query = $this->db->get();
                                $audit_data = $query->row();
                                $type = $this->pos_model->getStockType($row['product_id']);
                                $data = array(
                                    'audit_id' => $audit_data->audit_id,
                                    'user_id' => $this->session->userdata('user_id'),
                                    'reference' => $row['refer'],
                                    'item_id' => $row['product_id'],
                                    'date' => $row['date'],
                                    'item_code' => $row['product_code'],
                                    'item_name' => $row['product_name'],
                                    'price' => $row['net_unit_price'],
                                    'comment' => $row['comment'],
                                    'qty' => $row['quantity'],
                                    'stock_type' => $type->stock_type,
                                    'new_qty' => $row['quantity'],
                                    'description' => 'add more',
                                    'suspend_note' =>  $row['suspend_note'],
                                    'suspended_id' =>  $row['suspended_bill_id'],
                                    'warehouse_id' => $row['warehouse_id'],
                                    'item_row_id' => $row['item_row_id'],
                                ); 
                                $this->db->insert("audit_order_item", $data);
                                $this->db->insert("audit_trail_order_item", $data);
                            }
                        }
                    }
                } else {
                    $this->bpas->send_json(false); 
                }
            } else {
                $this->db->select('*,suspended_items.item_row_id as item_row_id, quantity as sus_qty,suspended_bills.id as suspended_id');
                $this->db->from('suspended_bills');
                $this->db->join('suspended_items', 'suspended_bills.id = suspended_items.suspend_id');
                $this->db->where(array('refer' => $bill_refer, 'suspend_note' => $table_id,'suspended_items.product_id !=' => 0));
                $query = $this->db->get();
               
                if ($query->num_rows()) {
                    $data_ = $query->row();
                    $new_author = $query->result_array();
                    $data = array(
                        'suspended_id' => $data_->suspended_id,
                        'reference' => $data_->refer,
                        'type' => 'Order',
                        'user_id' => $this->session->userdata('user_id'),
                        'suspend_note' => $table_id,
                        'customer_id' => $data_->customer_id,
                        'print_index' => 1,
                        'warehouse_id' => $data_->warehouse_id,
                    );
                    $this->db->insert("audit_order", $data);
                    $id = $this->db->insert_id();
                    
                    foreach ($new_author as $row) {
                        $type = $this->pos_model->getStockType($row['product_id']);
                        $data = array(
                            'audit_id' => $id,
                            'user_id' => $this->session->userdata('user_id'),
                            'reference' => $data_->refer,
                            'stock_type' => $type->stock_type,
                            'item_id' => $row['product_id'],
                            'date' => $row['date'],
                            'item_code' => $row['product_code'],
                            'item_name' => $row['product_name'],
                            'price' => $row['net_unit_price'],
                            'qty' => $row['sus_qty'],
                            'suspend_note' => $table_id,
                            'suspended_id' => $data_->suspended_id,
                            'comment' => $row['comment'],
                            'new_qty' => $row['sus_qty'],
                            'item_row_id' => $row['item_row_id'],
                            'print_index' => 1,
                            'warehouse_id' => $data_->warehouse_id,
                        );
                        $this->db->insert("audit_order_item", $data);
                        $this->db->insert("audit_trail_order_item", $data);
                    }
                }
            }
        }
    //     function audit_trail_order(){
    //         $bill_refer = $this->input->post('bill_refer');
    //         $table_id = $this->input->post('table_id');
    //         $suspended_id = $this->input->post('suspended_id');
    //         $warehouse_id = $this->input->post('warehouse_id'); 
    //         $this->db->select('reference')->from('audit_order')->where('reference', $bill_refer);
    //         $query = $this->db->get(); 
    //         if($query->num_rows()) {
    //             $this->db->select('product_id, SUM(quantity) as quantity')->from('suspended_bills');
    //             $this->db->join('suspended_items','suspended_bills.id = suspended_items.suspend_id');
    //             $this->db->where(array(
    //                 'refer'=> $bill_refer,
    //                 'product_id !=' =>0
    //                 )); 
    //             $this->db->order_by("product_id", "asc");
    //             $this->db->group_by("product_id"); 
    //             $siquery = $this->db->get()->result_array();
    //             $sus_query =  ($siquery); 
    //             $this->db->select('item_id as product_id,SUM(new_qty) as quantity')->from('audit_order_item');
    //             $this->db->where(array(
    //                 'reference'=> $bill_refer,
    //                 'new_qty !='=> 0
    //             )); 
    //             $this->db->order_by("item_id", "asc");
    //             $this->db->group_by("item_id");
    //             $aoiquery = $this->db->get()->result_array();
    //             $order_query =  ($aoiquery); 
    //             if(!($order_query == $sus_query)){ 
    //                 $this->db->where(array('reference'=> $bill_refer));
    //                 $this->db->update("audit_order", array('status'  => 1)); 
    //                 $this->db->select('*, sum(quantity) as sus_qty')->from('suspended_bills');
    //                 $this->db->join('suspended_items','suspended_bills.id = suspended_items.suspend_id');
    //                 $this->db->where(array('refer'=> $bill_refer,'suspended_items.product_id !=' =>0));
    //                 $this->db->group_by('suspended_items.product_id');
    //                 $query = $this->db->get();
    //                 if($query->num_rows()) {
    //                     $new_author = $query->result_array();
    //                     foreach ($new_author as $row) {
    //                         $this->db->select('item_id,new_qty')->from('audit_order_item');
    //                         $this->db->where(array('item_id' => $row['product_id'],
    //                                             'reference'=> $bill_refer,'item_id' !=0));
    //                         $cquery = $this->db->get();
    //                         if($cquery->num_rows()) {
    //                             $old_qty = $cquery->row(); 
    //                                 $this->db->where(array('reference'=> $bill_refer,
    //                                         'item_id'  => $row['product_id'] ));
    //                                 $this->db->update("audit_order_item", array(
    //                                         'new_qty'       => $row['sus_qty'],
    //                                         'last_order'    => $old_qty->new_qty,
    //                                         'order_status'  => 0 
    //                                     )); 
    //                                 $this->db->select('*')->from('audit_order_item');
    //                                 $this->db->where(array('reference'=>$bill_refer));
    //                                 $bill_get = $this->db->get();
    //                                 $bill_query = $bill_get->result_array();
    //                                 foreach ($bill_query as $row1) { 
    //                                     $this->db->select('sum(quantity) as sus_qty')->from('suspended_bills');
    //                                     $this->db->join('suspended_items','suspended_bills.id = suspended_items.suspend_id');
    //                                     $this->db->where(array(
    //                                         'refer'=> $row1['reference'],
    //                                         'product_id' => $row1['item_id']
    //                                         ));
    //                                     $this->db->group_by('suspended_items.product_id');
    //                                     $sus_query = $this->db->get();                                    
    //                                     if(!$sus_query->num_rows()) {
    //                                         $this->db->where(array(
    //                                             'reference'   => $bill_refer,
    //                                             'item_id'     => $row1['item_id']));
    //                                         $this->db->update("audit_order_item", array(
    //                                             'last_order'  => $row1['new_qty'],
    //                                             'new_qty'     => 0 
    //                                         ));
    //                                     } 
    //                                 }
    //                         }else{
    //                             $this->db->select('audit_id,reference')->from('audit_order')->where('reference', $bill_refer);
    //                             $query = $this->db->get();
    //                             $audit_data= $query->row();
    //                             $type = $this->pos_model->getStockType($row['product_id']);
    //                             $data = array(
    //                                 'audit_id'     => $audit_data->audit_id,
    //                                 'user_id'       => $this->session->userdata('user_id'),
    //                                 'reference'     => $row['refer'],
    //                                 'item_id'  => $row['product_id'],
    //                                 'item_code'  => $row['product_code'],
    //                                 'item_name'  => $row['product_name'],
    //                                 'price'  => $row['net_unit_price'],
    //                                 'qty'  => $row['quantity'],
    //                                 'stock_type'  => $type->stock_type,
    //                                 'new_qty'  => $row['quantity'],
    //                                 'description'  => 'add more',
    //                                 'warehouse_id'  => $row['warehouse_id']
    //                             );
    //                             $this->db->insert("audit_order_item", $data);
    //                         }  
    //                     }         
    //                 } 
    //             }
    //         }else{
    //             $this->db->select('*,sum(quantity) as sus_qty,suspended_bills.id as suspended_id');
    //             $this->db->from('suspended_bills');
    //             $this->db->join('suspended_items','suspended_bills.id = suspended_items.suspend_id');
    //             $this->db->where(array('refer'=> $bill_refer,'suspended_items.product_id !=' =>0));
    //             $this->db->group_by('suspended_items.product_id');
    //             $query = $this->db->get(); 
    //             if($query->num_rows()) {
    //                 $data_ = $query->row(); 
    //                 $new_author = $query->result_array();
    //                 $data = array(
    //                     'suspended_id'  => $data_->suspended_id,
    //                     'reference'     => $data_->refer,
    //                     'type'          => 'Order',
    //                     'user_id'       => $this->session->userdata('user_id'),
    //                     'customer_id'   => $data_->customer_id,
    //                     'print_index'  => 1,
    //                     'warehouse_id'  => $data_->warehouse_id
    //                 );
    //                 $this->db->insert("audit_order", $data);
    //                 $id = $this->db->insert_id(); 
    //                 foreach ($new_author as $row) {
    //                     $type = $this->pos_model->getStockType($row['product_id']);
    //                     $data = array(
    //                         'audit_id'     => $id,
    //                         'user_id'      => $this->session->userdata('user_id'),
    //                         'reference'    => $data_->refer,
    //                         'stock_type'   => $type->stock_type,
    //                         'item_id'      => $row['product_id'],
    //                         'item_code'    => $row['product_code'],
    //                         'item_name'    => $row['product_name'],
    //                         'price'        => $row['net_unit_price'],
    //                         'qty'          => $row['sus_qty'],
    //                         'new_qty'      => $row['sus_qty'],
    //                         'print_index'  => 1,
    //                         'warehouse_id' => $data_->warehouse_id
    //                     );
    //                     $this->db->insert("audit_order_item", $data);
    //                     $this->db->insert("audit_trail_order_item", $data);
    //                 }           
    //             }
    //         }
    // }
    // function audit_trail_order_(){
    //     $bill_refer = $this->input->post('bill_refer');
    //     $table_id = $this->input->post('table_id');
    //     $suspended_id = $this->input->post('suspended_id');
    //     $warehouse_id = $this->input->post('warehouse_id');
    //     $items = ($this->input->post('items'));
    //     var_dump($items);

    //     $this->db->select('reference')->from('audit_order')->where('reference', $bill_refer);
    //     $query = $this->db->get();

    //     if($query->num_rows()) {
    //         $this->db->select('sum(quantity) as sus_qty')->from('suspended_bills');
    //         $this->db->join('suspended_items','suspended_bills.id = suspended_items.suspend_id');
    //         $this->db->where(array(
    //             'refer'=> $bill_refer,
    //             'product_id !=' =>0
    //             ));
    //         $this->db->group_by('suspended_bills.refer');
    //         $sus_query = $this->db->get()->row();
    //         $sus_qty = $sus_query->sus_qty;
    //         $this->db->select('sum(new_qty) as order_qty')->from('audit_order_item');
    //         $this->db->where(array(
    //             'reference'=> $bill_refer
    //             ));
    //         $this->db->group_by('audit_order_item.reference');
    //         $order_query = $this->db->get()->row();
    //         $order_qty = $order_query->order_qty;
    //         if($sus_qty != $order_qty){ 
    //             $this->db->where(array('reference'=> $bill_refer));
    //             $this->db->update("audit_order", array('status'  => 1)); 
    //             $this->db->select('*, sum(quantity) as sus_qty')->from('suspended_bills');
    //             $this->db->join('suspended_items','suspended_bills.id = suspended_items.suspend_id');
    //             $this->db->where(array('refer'=> $bill_refer,'suspended_items.product_id !=' =>0));
    //             $this->db->group_by('suspended_items.product_id');
    //             $query = $this->db->get();
    //             if($query->num_rows()) {
    //                 $new_author = $query->result_array();
    //                 foreach ($new_author as $row) {
    //                     $this->db->select('item_id,new_qty')->from('audit_order_item');
    //                     $this->db->where(array('item_id' => $row['product_id'],
    //                                         'reference'=> $bill_refer,'item_id' !=0));
    //                     $cquery = $this->db->get();

    //                     if($cquery->num_rows()) {
    //                         $old_qty = $cquery->row(); 
    //                             $this->db->where(array('reference'=> $bill_refer,
    //                                     'item_id'  => $row['product_id'] ));
    //                             $this->db->update("audit_order_item", array(
    //                                     'new_qty'       => $row['sus_qty'],
    //                                     'last_order'    => $old_qty->new_qty,
    //                                     'order_status'  => 0 
    //                                 )); 
    //                             $this->db->select('*')->from('audit_order_item');
    //                             $this->db->where(array('reference'=>$bill_refer));
    //                             $bill_get = $this->db->get();
    //                             $bill_query = $bill_get->result_array();

    //                             foreach ($bill_query as $row1) {

    //                                 $this->db->select('sum(quantity) as sus_qty')->from('suspended_bills');
    //                                 $this->db->join('suspended_items','suspended_bills.id = suspended_items.suspend_id');
    //                                 $this->db->where(array(
    //                                     'refer'=> $row1['reference'],
    //                                     'product_id' => $row1['item_id']
    //                                     ));
    //                                 $this->db->group_by('suspended_items.product_id');
    //                                 $sus_query = $this->db->get();                                    
    //                                 if(!$sus_query->num_rows()) {
    //                                     $this->db->where(array(
    //                                         'reference'   => $bill_refer,
    //                                         'item_id'     => $row1['item_id']));
    //                                     $this->db->update("audit_order_item", array(
    //                                         'last_order'  => $row1['new_qty'],
    //                                         'new_qty'     => 0
                                            

    //                                     ));
    //                                 }

    //                             }
    //                     }else{
    //                         $this->db->select('audit_id,reference')->from('audit_order')->where('reference', $bill_refer);
    //                         $query = $this->db->get();
    //                         $audit_data= $query->row();
    //                         $type = $this->pos_model->getStockType($row['product_id']);
    //                         $data = array(
    //                             'audit_id'     => $audit_data->audit_id,
    //                             'user_id'       => $this->session->userdata('user_id'),
    //                             'reference'     => $row['refer'],
    //                             'item_id'  => $row['product_id'],
    //                             'item_code'  => $row['product_code'],
    //                             'item_name'  => $row['product_name'],
    //                             'price'  => $row['net_unit_price'],
    //                             'qty'  => $row['quantity'],
    //                             'stock_type'  => $type->stock_type,
    //                             'new_qty'  => $row['quantity'],
    //                             'description'  => 'add more',
    //                             'warehouse_id'  => $row['warehouse_id']
    //                         );
    //                         $this->db->insert("audit_order_item", $data);
    //                     }  
    //                 }         
    //             } 
    //         }
    //     }else{
    //         $this->db->select('*,sum(quantity) as sus_qty,suspended_bills.id as suspended_id');
    //         $this->db->from('suspended_bills');
    //         $this->db->join('suspended_items','suspended_bills.id = suspended_items.suspend_id');
    //         $this->db->where(array('refer'=> $bill_refer,'suspended_items.product_id !=' =>0));
    //         $this->db->group_by('suspended_items.product_id');
    //         $query = $this->db->get(); 
    //         if($query->num_rows()) {
    //             $data_ = $query->row(); 
    //             $new_author = $query->result_array();
    //             $data = array(
    //                 'suspended_id'  => $data_->suspended_id,
    //                 'reference'     => $data_->refer,
    //                 'type'          => 'Order',
    //                 'user_id'       => $this->session->userdata('user_id'),
    //                 'customer_id'   => $data_->customer_id,
    //                 'print_index'  => 1,
    //                 'warehouse_id'  => $data_->warehouse_id
    //             );
    //             $this->db->insert("audit_order", $data);
    //             $id = $this->db->insert_id(); 
    //             foreach ($new_author as $row) {
    //                 $type = $this->pos_model->getStockType($row['product_id']);
    //                 $data = array(
    //                     'audit_id'     => $id,
    //                     'user_id'      => $this->session->userdata('user_id'),
    //                     'reference'    => $data_->refer,
    //                     'stock_type'   => $type->stock_type,
    //                     'item_id'      => $row['product_id'],
    //                     'item_code'    => $row['product_code'],
    //                     'item_name'    => $row['product_name'],
    //                     'price'        => $row['net_unit_price'],
    //                     'qty'          => $row['sus_qty'],
    //                     'new_qty'      => $row['sus_qty'],
    //                     'print_index'  => 1,
    //                     'warehouse_id' => $data_->warehouse_id
    //                 );
    //                 $this->db->insert("audit_order_item", $data);
    //                 $this->db->insert("audit_trail_order_item", $data);
    //             }           
    //         }
    //     }
    // }
     function audit_trail_bill(){
        $bill_refer = $this->input->post('bill_refer');
        $table_id = $this->input->post('table_id');
        $warehouse_id = $this->input->post('warehouse_id');
        $suspended_id = $this->input->post('suspended_id');
        $items = ($this->input->post('items'));  
        $this->db->select('reference, print_index')->from('audit_bill')->where('reference', $bill_refer);
        $query_reference = $this->db->get(); 
        if($query_reference->num_rows()) {
            foreach ($items as $key => $value) {
                $row_data = $value['row'];
                 $this->db->select('audit_id, reference, print_index')->from('audit_bill')->where('reference', $bill_refer);
                    $query_audit_id = $this->db->get();
                    $audit_data = $query_audit_id->row();
                      $data = array(
                        'audit_id'     => $audit_data->audit_id,
                        'user_id'      => $this->session->userdata('user_id'),
                        'reference'    => $audit_data->reference,
                        'item_id'      => $row_data['id'],
                        'item_code'    => $row_data['code'],
                        'item_name'    => $row_data['name'],
                        'price'        => $row_data['base_unit_price'],
                        'qty'          => $row_data['base_quantity'],
                        'new_qty'      => $row_data['base_quantity'],
                        'description'  => 'add more',
                        'print_index'  => ($audit_data->print_index) + 1,
                        'warehouse_id' => $warehouse_id
                    );
                    $this->db->insert("audit_bill_item", $data); 
            }
            $this->db->update('audit_bill',['print_index' => ($audit_data->print_index) + 1], ['audit_id' => $audit_data->audit_id]);
                
        }else{
            $this->db->select('*, suspended_bills.id as suspended_id');
            $this->db->from('suspended_bills');
            $this->db->join('suspended_items', 'suspended_bills.id = suspended_items.suspend_id');
            $this->db->where('refer', $bill_refer);
            $query = $this->db->get();
            if($query->num_rows()) {
                $data_ = $query->row();
                $new_author = $query->result_array();
                $data = array(
                    'tran_date'     => date('Y-m-d h:i:s'),
                    'suspended_id'  => $data_->suspended_id,
                    'reference'     => $data_->refer,
                    'type'          => 'Bill',
                    'user_id'       => $this->session->userdata('user_id'),
                    'customer_id'   => $data_->customer_id,
                    'warehouse_id'  => $data_->warehouse_id,
                    'print_index'   => 1
                );
                $this->db->insert("audit_bill", $data);
                $id= $this->db->insert_id();
                foreach ($items as $key => $value) {
                $row_data = $value['row'];
                 $this->db->select('audit_id, reference, print_index')->from('audit_bill')->where('reference', $bill_refer);
                    $query_audit_id = $this->db->get();
                    $audit_data = $query_audit_id->row();
                      $data = array(
                        'audit_id'     => $id,
                        'user_id'      => $this->session->userdata('user_id'),
                        'reference'    => $data_->refer,
                        'item_id'      => $row_data['id'],
                        'item_code'    => $row_data['code'],
                        'item_name'    => $row_data['name'],
                        'price'        => $row_data['base_unit_price'],
                        'qty'          => $row_data['base_quantity'],
                        'new_qty'      => $row_data['base_quantity'],
                        'print_index'  => 1,
                        'warehouse_id' => $warehouse_id
                    );
                    $this->db->insert("audit_bill_item", $data); 
            }
               
            }
        }
        $data = array('print_status'  => 1);
        // $p = $query_reference->row();
        // if($p){
        //     $data = array('print_status'  => 1, 'print_index' => $p->print_index + 1);
        // }
        $this->db->where(array('suspend_note'=> $table_id));
        $this->db->update("suspended_bills", $data);
    }
    function audit_trail_bill_(){
        $bill_refer = $this->input->get('bill_refer');
        $table_id = $this->input->get('table_id');
        $suspended_id = $this->input->get('suspended_id'); 
        $this->db->select('reference')->from('audit_bill')->where('reference', $bill_refer);
        $query = $this->db->get(); 
        if($query->num_rows()) {
            $this->db->select()->from('suspended_bills');
            $this->db->join('suspended_items','suspended_bills.id = suspended_items.suspend_id');
            $this->db->where('refer', $bill_refer);
            $query2 = $this->db->get();
            if($query2->num_rows()) {   
                $new_author = $query2->result_array();
                foreach ($new_author as $row) {
                    $this->db->select('item_id')->from('audit_bill_item');
                    $this->db->where(array('item_id' => $row['product_id'], 'item_id' !=0));
                    $cquery = $this->db->get();
                    if($cquery->num_rows()) {
                        $data = array('new_qty'  => $row['quantity']);
                        $this->db->where(array('reference'=> $bill_refer,'item_id'  => $row['product_id']));
                        $this->db->update("audit_bill_item", $data);
                    }else{
                        $this->db->select('audit_id,reference')->from('audit_bill')->where('reference', $bill_refer);
                        $query3 = $this->db->get();
                        $audit_data= $query3->row();

                        $data = array(
                            'audit_id'      => $audit_data->audit_id,
                            'user_id'       => $this->session->userdata('user_id'),
                            'reference'     => $row['refer'],
                            'item_id'       => $row['product_id'],
                            'item_code'     => $row['product_code'],
                            'item_name'     => $row['product_name'],
                            'price'         => $row['net_unit_price'],
                            'qty'           => $row['quantity'],
                            'new_qty'       => $row['quantity'],
                            'description'   => 'add more',
                            'warehouse_id'  => $row['warehouse_id']
                        );
                        $this->db->insert("audit_bill_item", $data);
                    }
                    
                }         
            }
        }else{
            $this->db->select('*,suspended_bills.id as suspended_id');
            $this->db->from('suspended_bills');
            $this->db->join('suspended_items','suspended_bills.id = suspended_items.suspend_id');
            $this->db->where('refer', $bill_refer);
            $query1 = $this->db->get();
            if($query1->num_rows()) {
                $subdata = $query1->row();
               
                $new_author = $query1->result_array();
                $ndata = array(
                    'tran_date'     => date('Y-m-d h:i:s'),
                    'suspended_id'  => $subdata->suspended_id,
                    'reference'     => $subdata->refer,
                    'type'          => 'Bill',
                    'user_id'       => $this->session->userdata('user_id'),
                    'customer_id'   => $subdata->customer_id,
                    'warehouse_id'  => $subdata->warehouse_id
                ); 
                $this->db->insert("audit_bill", $ndata);
                $id= $this->db->insert_id();
                foreach ($new_author as $row) {
                    $mdata = array(
                        'audit_id'     => $id,
                        'user_id'      => $this->session->userdata('user_id'),
                        'reference'    => $subdata->refer,
                        'item_id'      => $row['product_id'],
                        'item_code'    => $row['product_code'],
                        'item_name'    => $row['product_name'],
                        'price'        => $row['net_unit_price'],
                        'qty'          => $row['quantity'],
                        'new_qty'      => $row['quantity'],
                        'warehouse_id' => $subdata->warehouse_id
                    );
                    $this->db->insert("audit_bill_item", $mdata);
                }           
            }
        }
        $sdata = array('print_status'  => 1);
            $this->db->where(array('suspend_note'=> $table_id));
                    $this->db->update("suspended_bills", $sdata);
    }
    function del_item(){
        $pro_id =$this->input->get('pro_id');
        $bill_refer =$this->input->get('bill_refer');
        $pos_pin =$this->input->get('pos_pin');

         $result =$this->db->update('audit_bill', 
            array( 'change_status' =>1),
            array( 'reference' => $bill_refer,'type' => 'Bill')
        );
        if($result){
            $result =$this->db->update('audit_bill_item', 
                array(
                'status' => 1,
                'description' => $pos_pin
                ),
                array(
                'reference' => $bill_refer,
                'item_id' => $pro_id,
                )
            );
            echo 'success';
        } 
    }
     function getAddOnItemByPID_ajax($id){
        $result = [];
        if(($result['addOnItems'] = $this->pos_model->getAddOnItemsByPID($id)) && ($result['p_all'] = $this->pos_model->getAllP())){
            $this->bpas->send_json($result);      
        } else {
            $this->bpas->send_json(false);
        }
    }

    function adodnitemsNote_ajax(){
        $data = $this->input->get('addon_note_', true);
        $this->db->delete('addon_items_note', ['suspend_id' => $data[0]['suspend_id'], 'row_id' => $data[0]['row_id']]);
        if($data[0]['addon_status'] != "no"){
            if ($this->db->insert_batch('addon_items_note', $data)) {
                echo json_encode(array("statusCode" => 200));
            } 
            else {
                echo json_encode(array("statusCode" => 201));
            }
        } else {
            echo json_encode(array("statusCode" => 200));
        }
    }

    function get_addonitemsNote_ajax(){
        $data = [];
        $id = $this->input->get('suspend_id', true);
        $row_id = $this->input->get('row_id', true);

        if($data = $this->pos_model->getAddonitemsNote($id, $row_id)){
            $this->bpas->send_json($data);
        } else {
            $this->bpas->send_json(false);    
        }
    }
    function pin_update_item(){
        $pro_id =$this->input->get('pro_id');
        $bill_refer =$this->input->get('bill_refer');
        $pos_pin =$this->input->get('pos_pin');
        $new_qty =$this->input->get('new_qty');
        $old_row_qty = $this->input->get('old_row_qty'); 

        // $data = $this->site->approved_edit_bill($pos_pin); 
        $result =$this->db->update('audit_bill', 
            array( 'change_status' =>1),
            array( 'reference' => $bill_refer,'type' => 'Bill')
        );
        if($result){
            $result =$this->db->update('audit_bill_item', 
                array(
                'status' => 1,
                'new_qty' => $new_qty,
                'description' => 'Updated With Pin Code'
                ),
                array(
                'reference' => $bill_refer,
                'qty'       => $old_row_qty,
                'item_id' => $pro_id,
                )
            );
            echo 'success';
        }
    }
    function update_item(){
        $pro_id = $this->input->get('pro_id');
        $bill_refer = $this->input->get('bill_refer');
        $reason = $this->input->get('reason');
        $new_qty = $this->input->get('new_qty'); 
        $old_row_qty = $this->input->get('old_row_qty'); 
        // $data = $this->site->approved_edit_bill($reason); 
        $result = $this->db->update('audit_bill', 
            array( 'change_status' =>1),
            array( 'reference' => $bill_refer,'type' => 'Bill')
        );
        if($result){
            $result =$this->db->update('audit_bill_item', 
                array(
                'status' => 1,
                'new_qty' => $new_qty,
                'description' => $reason
                ),
                array(
                'reference' => $bill_refer,
                'qty'       => $old_row_qty,
                'item_id' => $pro_id,
                )
            );
            echo 'success';
        }
    }
    
    public function getmember_card()
    {
        $id_card = $this->input->get('idcard') ? $this->input->get('idcard') : null;
        if($getIdCard = $this->pos_model->getidmembercard($id_card)){
            $this->bpas->send_json($getIdCard);   
        }
        $this->bpas->send_json(false); 
    }

    public function getcoupon_code()
    {
        $id_card = $this->input->get('idcard') ? $this->input->get('idcard') : null;
        if($getIdCard = $this->pos_model->getidcoupon($id_card)){
            $this->bpas->send_json($getIdCard);   
        }
        $this->bpas->send_json(false); 
    }

    public function get_reason () 
    {
        if ($reason = $this->site->getcustomfield('reason')) {
            $this->bpas->send_json($reason); 
        }
        $this->bpas->send_json(false); 
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
        $customer       = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $rows = $this->sales_model->getProductNames($sr, $warehouse_id, $pos);
        if ($rows) {
            $r = 0; $pr = array();
            foreach ($rows as $row) {
                // $promotions = $this->promos_model->getPromotionByProduct($warehouse_id, $row->category_id, $row->id);
                if ($promotions = $this->promos_model->getPromotionByProduct($warehouse_id, $row->category_id, $row->id)) {
                    if ($this->promos_model->getPromotionByMultiProductUnit($warehouse_id, $row->code, $row->id)) {
                        $promotions = $this->promos_model->getPromotionByMultiProductUnit($warehouse_id, $row->code, $row->id);
                    }
                } elseif($this->promos_model->getPromotionByMultiProductUnit($warehouse_id, $row->code, $row->id)) {
                    $promotions = $this->promos_model->getPromotionByMultiProductUnit($warehouse_id, $row->code, $row->id);
                }
                $discount_promotion = 0;
                if ($promotions) {
                    foreach ($promotions as $promotion) {
                        $discount_promotion = $promotion->discount;
                    }
                }
                $cate_id = $row->subcategory_id ? $row->subcategory_id : $row->category_id;
                $c = uniqid(mt_rand(), true);
                unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $option               = false;
                $row->quantity        = 0;
                $row->item_tax_method = $row->tax_method;
                $row->qty             = 1;
                $row->discount        = 0;          
                if ($discount_promotion != 0) {
                    $row->discount    = $discount_promotion;
                } else if ($this->Settings->customer_group_discount == 2 && !empty($customer_group)) {
                    $row->discount    = (-1 * $customer_group->percent) . "%";
                }
                $row->serial_no       = '';

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
                $pis = $this->site->getStockMovement_ProductBalanceQuantity($row->id, $warehouse_id, $row->option);
                if ($pis) {
                    $row->quantity = $pis->quantity_balance;
                }
                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        $pis = $this->site->getStockMovement_ProductBalanceQuantity($row->id, $warehouse_id, $row->option);
                        if ($pis) {
                            $option_quantity = $pis->quantity_balance;
                        }
                        if ($option->quantity > $option_quantity) {
                            $option->quantity = $option_quantity;
                        }
                    }
                }
                $set_price = $this->site->getUnitByProId($row->id);
                if ($customer->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
                        $set_price = $this->site->getUnitByProId_PG($row->id, $customer->price_group_id);
                    }
                }
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
                 if($this->Settings->product_serial == 1){
                    $product_serials = $this->sales_model->getProductSerialDetailsByProductId($row->id, $warehouse_id, $row->serial_no);
                }else{
                    $product_serials = false;
                }

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
                $pr[] = [
                    'id'  => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'category' => $row->category_id,
                    'row' => $row,'product_serials' => $product_serials, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'set_price' => $set_price, 'options' => $options, 'expiry' => "0000-00-00" ];
                $r++;
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    public function getProductsAjax($warehouse_id = null)
    {
        $warehouse_id = $this->input->get('warehouse_id') ? $this->input->get('warehouse_id') : null;
        $products     = $this->ajaxproducts($this->pos_settings->default_category, false, $warehouse_id);
        $this->bpas->send_json(['products' => $products]);
    }
    
    public function customer_stocks()
    {
        $this->bpas->checkPermissions("customer_stock");
        $this->pos_model->addCustomerStockExpired();
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('pos'), 'page' => lang('pos')), array('link' => '#', 'page' => lang('customer_stocks')));
        $meta = array('page_title' => lang('customer_stocks'), 'bc' => $bc);
        $this->page_construct('pos/customer_stocks', $meta, $this->data);
    }
    
    public function getCustomerStocks()
    {
        $this->bpas->checkPermissions("customer_stock");
        $this->load->library('datatables');
        $detail_link = anchor('admin/pos/view_customer_stock/$1/1', '<i class="fa fa-file-text-o"></i> ' . lang('view_customer_stock'), ' class="cs-view" data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('admin/pos/edit_customer_stock/$1', '<i class="fa fa-edit"></i> ' . lang('edit_customer_stock'), ' class="cs-edit"');
        $transfer_link = anchor('admin/pos/transfer_customer_stock/$1', '<i class="fa fa-share"></i> ' . lang('transfer_customer_stock'), ' class="cs-transfer" data-toggle="modal" data-target="#myModal"');
        
        $delete_link = "<a href='#' class='po cs-delete' title='<b>" . lang("delete_customer_stock") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('pos/delete_customer_stock/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_customer_stock') . "</a>";
        
        $return_link = "<a href='#' class='po cs-return' title='<b>" . lang("return_customer_stock") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('pos/return_customer_stock/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-reply\"></i> "
        . lang('return_customer_stock') . "</a>";

        $cancel_link = "<a href='#' class='po cs-cancel' title='<b>" . lang("cancel_customer_stock") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('pos/cancel_customer_stock/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('cancel_customer_stock') . "</a>";
        
        $action = '<div class="text-center"><div class="btn-group text-left">'
                    . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
                    . lang('actions') . ' <span class="caret"></span></button>
                        <ul class="dropdown-menu pull-right" role="menu">
                            <li>' . $detail_link . '</li>
                            <li>' . $return_link . '</li>
                            <li>' . $transfer_link . '</li>
                            <li>' . $cancel_link . '</li>
                            <li>' . $edit_link . '</li>
                            <li>' . $delete_link . '</li>
                        </ul>
                    </div></div>';
        
        $this->datatables
            ->select("
                    customer_stocks.id as id, 
                    {$this->db->dbprefix('customer_stocks')}.date, 
                    reference_no, 
                    customer, 
                    companies.phone,
                    GROUP_CONCAT(bpas_products.name) as description,
                    customer_stocks.expiry,
                    CONCAT({$this->db->dbprefix('users')}.last_name, ' ', {$this->db->dbprefix('users')}.first_name) as created_by,
                    {$this->db->dbprefix('customer_stocks')}.status", false)
            ->from("customer_stocks")
            ->join('customer_stock_items','customer_stock_items.customer_stock_id=customer_stocks.id','left')
            ->join('products','products.id=product_id','left')
            ->join('users', 'users.id=customer_stocks.created_by', 'left')
            ->join('companies', 'companies.id=customer_stocks.customer_id', 'left')
            ->group_by("customer_stocks.id")
            ->add_column("Actions", $action, "id");
            
            if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('customer_stocks.created_by', $this->session->userdata('user_id'));
            } elseif ($this->Customer) {
                $this->datatables->where('customer_stocks.customer_id', $this->session->userdata('user_id'));
            }
        
        echo $this->datatables->generate();
    }
    
    public function add_customer_stock()
    {
        $this->bpas->checkPermissions("customer_stock");
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');
        if ($this->form_validation->run() == true) {
            $biller_id = $this->input->post('biller');
            $project_id = $this->input->post('project');
            if ($this->Owner || $this->Admin || $GP['sales-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('cs',$biller_id);
            $warehouse_id = $this->input->post('warehouse');
            $note = $this->bpas->clear_tags($this->input->post('note'));
            $expiry = isset($_POST['expiry_date']) ? $this->bpas->fsd($_POST['expiry_date']) : NULL;
            $customer_id = $this->input->post("customer");
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = ($customer_details->name?$customer_details->name:$customer_details->company);
            
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                
                $product_id     = $_POST['product_id'][$r];
                $quantity       = $_POST['quantity'][$r];
                $variant        = isset($_POST['variant'][$r]) && !empty($_POST['variant'][$r]) ? $_POST['variant'][$r] : NULL;
                $item_unit      = $_POST['product_unit'][$r];
                $item_quantity  = $_POST['product_base_quantity'][$r];
                $serial         = $_POST['serial'][$r];
                $unit           = $this->site->getProductUnit($product_id,$item_unit);
                $product_details = $this->site->getProductByID($product_id);
                
                $products[] = array(
                    'product_id'        => $product_id,
                    'quantity'          => $item_quantity,
                    'option_id'         => $variant,
                    'product_unit_id'   => $item_unit,
                    'warehouse_id'      => $warehouse_id,
                    'product_unit_code' => $unit->code,
                    'unit_quantity'     => $quantity,
                    'serial_no'         => $serial,
                    );
                
                if($serial != ''){
                    $serial_detail = $this->products_model->getProductSerial($serial,$product_details->id,$warehouse_id);
                    if($serial_detail){
                        $product_details->cost = $serial_detail->cost;
                    }
                    if($item_quantity > 0){
                        $reactive = 0;
                        if($serial_detail){
                            if($serial_detail->inactive==0){
                                $this->session->set_flashdata('error', lang("serial_is_existed").' ('.$serial.') ');
                                redirect($_SERVER["HTTP_REFERER"]);
                            }else {
                                $reactive = 1;
                            }
                        }else{
                            $product_serials[] = array(
                                    'product_id'    => $product_details->id,
                                    'cost'          => $product_details->cost,
                                    'price'         => $product_details->price,
                                    'warehouse_id'  => $warehouse_id,
                                    'date'          => $date,
                                    'serial'        => $serial,
                                );
                        }
                    }
                }
                
                $stockmoves[] = array(
                        'transaction'   => 'CustomerStock',
                        'product_id'    => $product_id,
                        'product_code'  => $product_details->code,
                        'product_type'  => $product_details->type,
                        'option_id'     => $variant,
                        'quantity'      => $item_quantity,
                        'unit_quantity' => $unit->unit_qty,
                        'unit_code'     => $unit->code,
                        'unit_id'       => $item_unit,
                        'warehouse_id'  => $warehouse_id,
                        'date'          => $date,
                        'real_unit_cost' => $product_details->cost,
                        'serial_no'     => $serial,
                        'reference_no'  => $reference_no,
                        'user_id'       => $this->session->userdata('user_id')
                    );  
                    
                if($this->Settings->accounting == 1){
                    
                    $productAcc = $this->site->getProductAccByProductId($product_details->id);
                    $billerAcc  = $this->site->getAccountSettingByBiller($biller_id);
                    
                    $accTrans[] = array(
                        'tran_type'     => 'CustomerStock',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $productAcc->stock_account,
                        'amount'        => ($product_details->cost * $item_quantity),
                        'narrative'     => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'created_by'       => $this->session->userdata('user_id'),
                    );
                    
                    $accTrans[] = array(
                        'tran_type'     => 'CustomerStock',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $billerAcc->customer_stock_acc,
                        'amount'        => ($product_details->cost * $item_quantity) * (-1),
                        'narrative'     => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'created_by'       => $this->session->userdata('user_id'),
                    );
                }
            }
            
            $data = array(
                'date'          => $date,
                'reference_no'  => $reference_no,
                'warehouse_id'  => $warehouse_id,
                'biller_id'     => $biller_id,
                'expiry'        => $expiry,
                'customer_id'   => $customer_details->id,
                'customer'      => $customer,
                'note'          => $note,
                'created_by'    => $this->session->userdata('user_id'),
                );
        }
        if ($this->form_validation->run() == true && $this->pos_model->addCustomerStock($data, $products, $stockmoves, $accTrans, $product_serials)) {
            $this->session->set_userdata('remove_csls', 1);
            $this->session->set_flashdata('message', lang("customer_stock_added")." - ".$data['reference_no']);
            admin_redirect('pos/customer_stocks');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['billers'] = $this->site->getBillers();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('pos'), 'page' => lang('pos')), array('link' => '#', 'page' => lang('add_customer_stock')));
            $meta = array('page_title' => lang('add_customer_stock'), 'bc' => $bc);
            $this->page_construct('pos/add_customer_stock', $meta, $this->data);
        }
    }
    
    public function edit_customer_stock($id = NULL)
    {
        $this->bpas->checkPermissions("customer_stock");
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');
        $customer_stock = $this->pos_model->getCustomerStockByID($id);
        
        if ($this->form_validation->run() == true) {
        
            $biller_id = $this->input->post('biller');
            $project_id = $this->input->post('project');
            if ($this->Owner || $this->Admin || $GP['sales-date']) {
                $date = $this->bpas->fld($this->input->post('date'),1);
            } else {
                $date = $customer_stock->date;
            }
            
            $reference_no = $this->input->post('reference_no');
            $warehouse_id = $this->input->post('warehouse');
            $note = $this->bpas->clear_tags($this->input->post('note'));
            $expiry = isset($_POST['expiry_date']) ? $this->bpas->fsd($_POST['expiry_date']) : NULL;
            $customer_id = $this->input->post("customer");
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = ($customer_details->name?$customer_details->name:$customer_details->company);
            
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $quantity = $_POST['quantity'][$r];
                $variant = isset($_POST['variant'][$r]) && !empty($_POST['variant'][$r]) ? $_POST['variant'][$r] : NULL;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
                $serial = $_POST['serial'][$r];
                $unit = $this->site->getProductUnit($product_id,$item_unit);
                $product_details = $this->site->getProductByID($product_id);
                
                $products[] = array(
                    'product_id' => $product_id,
                    'quantity' => $item_quantity,
                    'option_id' => $variant,
                    'product_unit_id' => $item_unit,
                    'warehouse_id' => $warehouse_id,
                    'product_unit_code' => $unit->code,
                    'unit_quantity' => $quantity,
                    'serial_no' => $serial,
                    );
                
                if($serial!=''){
                    $serial_detail = $this->products_model->getProductSerial($serial,$product_details->id,$warehouse_id,$id);
                    if($serial_detail){
                        $product_details->cost = $serial_detail->cost;
                    }
                    if($item_quantity > 0){ 
                        $reactive = 0;
                        if($serial_detail){
                            if($serial_detail->inactive==0){
                                if($this->products_model->getAdjustmentItemSerial($product_details->id,$id,$serial)){
                                    $reactive = 1;
                                }else{
                                    $this->session->set_flashdata('error', lang("serial_is_existed").' ('.$serial.') ');
                                    redirect($_SERVER["HTTP_REFERER"]);
                                }
                            }else {
                                $reactive = 1;
                            }
                        }else{
                            $product_serials[] = array(
                                        'product_id'    => $product_details->id,
                                        'cost'          => $product_details->cost,
                                        'price'         => $product_details->price,
                                        'warehouse_id'  => $warehouse_id,
                                        'date'          => $date,
                                        'serial'        => $serial,
                                    );
                        }
                    }
                }
                
                $stockmoves[] = array(
                        'transaction'   => 'CustomerStock',
                        'product_id'    => $product_id,
                        'product_code'  => $product_details->code,
                        'product_type'  => $product_details->type,
                        'option_id'     => $variant,
                        'quantity'      => $item_quantity,
                        'unit_quantity' => $unit->unit_qty,
                        'unit_code'     => $unit->code,
                        'unit_id'       => $item_unit,
                        'warehouse_id'  => $warehouse_id,
                        'date'          => $date,
                        'real_unit_cost' => $product_details->cost,
                        'serial_no'     => $serial,
                        'reference_no'  => $reference_no,
                        'user_id'       => $this->session->userdata('user_id')
                    );      
                    
                if($this->Settings->accounting == 1){
                    $productAcc = $this->site->getProductAccByProductId($product_details->id);
                    $billerAcc  = $this->site->getAccountSettingByBiller($biller_id);
                    $accTrans[] = array(
                        'tran_type'     => 'CustomerStock',
                        'tran_date'     => $date,
                        'reference_no' => $reference_no,
                        'account_code' => $productAcc->stock_account,
                        'amount'        => ($product_details->cost * $item_quantity),
                        'narrative'     => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'created_by' => $this->session->userdata('user_id'),
                    );
                    $accTrans[] = array(
                        'tran_type'     => 'CustomerStock',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $billerAcc->customer_stock_acc,
                        'amount'        => ($product_details->cost * $item_quantity) * (-1),
                        'narrative'     => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'created_by'       => $this->session->userdata('user_id'),
                    );
                }
            }
            
            $data = array(
                'date'          => $date,
                'reference_no'  => $reference_no,
                'warehouse_id'  => $warehouse_id,
                'biller_id'     => $biller_id,
                'expiry'        => $expiry,
                'customer_id'   => $customer_details->id,
                'customer'      => $customer,
                'note'          => $note,
                'created_by'    => $this->session->userdata('user_id'),
                );
        }
        if ($this->form_validation->run() == true && $this->pos_model->updateCustomerStock($id, $data, $products, $stockmoves, $accTrans, $product_serials)) {
            $this->session->set_userdata('remove_csls', 1);
            $this->session->set_flashdata('message', lang("customer_stock_updated")." - ".$data['reference_no']);
            admin_redirect('pos/customer_stocks');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $inv_items = $this->pos_model->getCustomerStockItems($id);
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $product = $this->site->getProductByID($item->product_id);
                $row = json_decode('{}');
                $row->id = $item->product_id;
                $row->code = $product->code;
                $row->name = $product->name;
                $row->qty = $item->unit_quantity;
                $options = $this->site->getProductVariants($product->id);
                $row->option = $item->option_id ? $item->option_id : 0;
                $row->serial = $item->serial_no ? $item->serial_no : '';
                $ri = $this->Settings->item_addition ? $product->id : $c;
                $item->quantity = abs($item->quantity);
                $row->base_quantity = $item->quantity;
                $row->base_unit_cost = $product->cost;
                $row->base_unit = $product->unit ? $product->unit : $item->product_unit_id;
                $row->unit = $item->product_unit_id;
                $units = $this->site->getUnitbyProduct($row->id,$row->base_unit);
                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'options' => $options, 'units'=> $units);
                $c++;
            }
            $this->data['id'] = $id;
            $this->data['customer_stock'] = $customer_stock;
            $this->data['customer_stock_items'] = json_encode($pr);
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['billers'] = $this->site->getBillers();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('pos'), 'page' => lang('pos')), array('link' => '#', 'page' => lang('edit_customer_stock')));
            $meta = array('page_title' => lang('edit_customer_stock'), 'bc' => $bc);
            $this->page_construct('pos/edit_customer_stock', $meta, $this->data);
        }
    }
    
    public function delete_customer_stock($id)
    {
        $this->bpas->checkPermissions("customer_stock");
        if ($this->pos_model->deleteCustomerStock($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('customer_stock_deleted')]);
        }
        $this->session->set_flashdata('message', lang("customer_stock_deleted"));
        admin_redirect('pos/customer_stocks');
    }
    public function customer_stock_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        
        if ($this->form_validation->run() == true) {
            
            if (!empty($_POST['val'])) {
                
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $customer_stock = $this->pos_model->getCustomerStockByID($id);
                        if($customer_stock->status=='pending'){
                            $this->pos_model->deleteCustomerStock($id);
                        }else{
                            $this->session->set_flashdata('error', $this->lang->line("customer_stocks_cannot_delete"));
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    }
                    $this->session->set_flashdata('message', $this->lang->line("customer_stocks_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('customer_stocks'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('phone'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('description'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('expiry'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('created_by'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->pos_model->getCustomerStockByID($id);
                        $cs = $this->site->getCompanyByID($sc->customer_id);
                        $user = $this->site->getUser($sc->created_by);
                        
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bms->hrld($sc->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->customer);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $cs->phone);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sc->description);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bms->hrsd($sc->expiry));
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $user->last_name .' '.$user->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, lang($sc->status));
                        
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
                    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
                    
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    
                    $filename = 'customer_stocks_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            }else {
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    public function view_customer_stock($id, $modal)
    {
        $this->bpas->checkPermissions("customer_stock");
        if(!$id){
            $id = $this->input->get("id");
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        if($this->Owner || $this->Admin || $this->bpas->GP['unlimited-print']){
            $this->data['print'] = 0;
        }else{
            if($this->Settings->limit_print=='1' && $this->site->checkPrint('POS',$inv->id)){
                $this->data['print'] = 1;
            }else if($this->Settings->limit_print=='2' && $this->site->checkPrint('POS',$inv->id)){
                $this->data['print'] = 2;
            }else{
                $this->data['print'] = 0;
            }
        }
        
        $inv = $this->pos_model->getCustomerStockByID($id);
        $this->data['inv'] = $inv;
        $this->data['modal'] = $modal;
        $this->data['pos'] = $this->pos_model->getSetting();
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['printer'] = $this->pos_model->getPrinterByID($this->pos_settings->printer);
        $this->data['rows'] = $this->pos_model->getCustomerStockItems($id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['page_title'] = $this->lang->line("view_customer_stock");
        $this->load->view($this->theme . 'pos/view_customer_stock', $this->data);
    }
    public function return_customer_stock($id)
    {
        $this->bpas->checkPermissions("customer_stock");
        $customer_stock = $this->pos_model->getCustomerStockByID($id);
        $items = $this->site->getAllStockmoves("CustomerStock", $id);
        foreach($items as $item){
            if($this->Settings->accounting_method == '0'){
                $costs = $this->site->getFifoCost($item->product_id,$item->quantity,$stockmoves);
            }else if($this->Settings->accounting_method == '1'){
                $costs = $this->site->getLifoCost($item->product_id,$item->quantity,$stockmoves);
            }else if($this->Settings->accounting_method == '3'){
                $costs = $this->site->getProductMethod($item->product_id,$item->quantity,$stockmoves);
            }else{
                $costs = false;
            }
            if($costs && $item->serial_no==''){
                foreach($costs as $cost_item){
                    $stockmoves[] = array(
                                'transaction'    => 'CustomerStockReturn',
                                'product_id'     => $item->product_id,
                                'product_code'   => $item->product_code,
                                'product_type'   => $item->product_type,
                                'option_id'      => $item->option_id,
                                'quantity'       => $cost_item['quantity'] * (-1),
                                'unit_quantity'  => $item->unit_quantity,
                                'unit_code'      => $item->unit_code,
                                'unit_id'        => $item->unit_id,
                                'warehouse_id'   => $item->warehouse_id,
                                'date'           => $item->date,
                                'real_unit_cost' => $cost_item['cost'],
                                'serial_no'      => $item->serial_no,
                                'reference_no'   => $item->reference_no,
                                'user_id'        => $item->user_id
                            );
                            
                    if($this->Settings->accounting == 1){
                        $productAcc = $this->site->getProductAccByProductId($item->product_id);
                        $billerAcc  = $this->site->getAccountSettingByBiller($customer_stock->biller_id);
                        $accTrans[] = array(
                            'tran_type'     => 'CustomerStockReturn',
                            'tran_date'     => date("Y-m-d H:i"),
                            'reference_no'  => $customer_stock->reference_no,
                            'account_code'  => $productAcc->stock_account,
                            'amount'        => ($cost_item['cost'] * $item->quantity) * (-1),
                            'narrative'     => 'Product Code:'.$item->product_code.'#'.'Qty: '.$item->quantity.'#'.'Cost: '.$cost_item['cost'],
                            'description'   => $note,
                            'biller_id'     => $biller_id,
                            'created_by'    => $this->session->userdata('user_id'),
                        );
                        $accTrans[] = array(
                            'tran_type'     => 'CustomerStockReturn',
                            'tran_date'     => date("Y-m-d H:i"),
                            'reference_no'  => $customer_stock->reference_no,
                            'account_code'  => $billerAcc->customer_stock_acc,
                            'amount'        => ($cost_item['cost'] * $item->quantity),
                            'narrative'     => 'Product Code:'.$item->product_code.'#'.'Qty: '.$item->quantity.'#'.'Cost: '.$cost_item['cost'],
                            'description'   => $note,
                            'biller_id'     => $biller_id,
                            'created_by'    => $this->session->userdata('user_id'),
                        );
                    }
                }
                
            }else{
                
                $stockmoves[] = array(
                    'transaction'    => 'CustomerStockReturn',
                    'product_id'     => $item->product_id,
                    'product_code'   => $item->product_code,
                    'product_type'   => $item->product_type,
                    'option_id'      => $item->option_id,
                    'quantity'       => $item->quantity * (-1),
                    'unit_quantity'  => $item->unit_quantity,
                    'unit_code'      => $item->unit_code,
                    'unit_id'        => $item->unit_id,
                    'warehouse_id'   => $item->warehouse_id,
                    'date'           => $item->date,
                    'real_unit_cost' => $item->real_unit_cost,
                    'serial_no'      => $item->serial_no,
                    'reference_no'   => $item->reference_no,
                    'user_id'        => $item->user_id
                );
                        
                if($this->Settings->accounting == 1){
                    $productAcc = $this->site->getProductAccByProductId($item->product_id);
                    $billerAcc  = $this->site->getAccountSettingByBiller($customer_stock->biller_id);
                    $accTrans[] = array(
                        'tran_type'     => 'CustomerStockReturn',
                        'tran_date'     => date("Y-m-d H:i"),
                        'reference_no'  => $customer_stock->reference_no,
                        'account_code'  => $productAcc->stock_account,
                        'amount'        => ($item->real_unit_cost * $item->quantity) * (-1),
                        'narrative'     => 'Product Code: '.$item->product_code.'#'.'Qty: '.$item->quantity.'#'.'Cost: '.$item->real_unit_cost,
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'created_by'    => $this->session->userdata('user_id'),
                    );
                    $accTrans[] = array(
                        'tran_type'     => 'CustomerStockReturn',
                        'tran_date'     => date("Y-m-d H:i"),
                        'reference_no'  => $customer_stock->reference_no,
                        'account_code'  => $billerAcc->customer_stock_acc,
                        'amount'        => ($item->real_unit_cost * $item->quantity),
                        'narrative'     => 'Product Code: '.$item->product_code.'#'.'Qty: '.$item->quantity.'#'.'Cost: '.$item->real_unit_cost,
                        'description'    => $note,
                        'biller_id'     => $biller_id,
                        'created_by'    => $this->session->userdata('user_id'),
                    );
                }
            }
        }
        
        if ($this->pos_model->returnCustomerStock($id, $stockmoves, $accTrans)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('customer_stock_returned')." ". $item->reference_no]);
            
        }
    }
    public function transfer_customer_stock($id)
    {
        $this->bpas->checkPermissions("customer_stock");
        if(!$id){
            $id = $this->input->get("id");
        }
        $customer_stock = $this->pos_model->getCustomerStockByID($id);
        $this->form_validation->set_rules('table', lang("table"), 'required');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $GP['sales-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data = array(
                        "transfered_by" => $this->session->userdata("user_id"),
                        "transfered_at" => $date,
                        "table_id"      => $this->input->post("table"),
                        "status"        => "completed",
                    );
            
            $items = $this->site->getAllStockmoves("CustomerStock", $id);
            foreach($items as $item){
                $prod = $this->site->getProductByID($item->product_id);
                $unit = $this->site->getProductUnit($item->product_id,$item->unit_id);
                $products[] = array(
                        'product_id'        => $item->product_id,
                        'product_code'      => $item->product_code,
                        'product_name'      => $prod->name,
                        'product_type'      => $item->product_type,
                        'option_id'         => $item->option_id,
                        'quantity'          => $item->quantity,
                        'product_unit_id'   => $item->unit_id,
                        'product_unit_code' => $unit ? $unit->code : NULL,
                        'unit_quantity'     => $item->quantity,
                        'warehouse_id'      => $item->warehouse_id,
                        'unit_price'        => 0,
                        'net_unit_price'    => 0,
                        'real_unit_price'   => 0,
                        'subtotal'          => 0,
                        'ordered'           => 1,
                        'serial_no'         => $item->serial_no,
                        'cost'              => $item->real_unit_cost,
                    );
            }

        }
        if ($this->form_validation->run() == true && $this->pos_model->transferCustomerStock($id, $data, $products)) {
            $this->session->set_flashdata('message', lang("customer_stock_transfered").' '.$customer_stock->reference_no);
            redirect($_SERVER['HTTP_REFERER']); 
        }else{
            $row = $this->pos_model->getCustomerStockByID($id);
            $this->data['id'] = $id;
            $this->data['customer_stock'] = $row;
            $this->data['suspend_bills'] = $this->pos_model->getAllSuspendBills();
            $this->data['biller'] = $this->site->getCompanyByID($row->biller_id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['page_title'] = $this->lang->line("transfer_customer_stock");
            $this->load->view($this->theme . 'pos/transfer_customer_stock', $this->data);
        }
    }
    public function cancel_customer_stock($id)
    {
        $this->bpas->checkPermissions("customer_stock");
        if ($this->pos_model->cancelCustomerStock($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('customer_stock_canceled')]);
        }
    }
}