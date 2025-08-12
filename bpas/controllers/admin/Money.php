<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Money extends MY_Controller
{   
    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            redirect('login');
        }
        if ($this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->load->admin_model('sales_model');
        $this->load->admin_model('accounts_model');
        $this->load->admin_model('money_model');
        $this->load->admin_model('settings_model');
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
        $this->data['alert_id']         = isset($_GET['alert_id']) ? ($_GET['alert_id'] !== '' ? $_GET['alert_id'] : 'empty')  : null;
        $this->data['users']            = $this->site->getStaff();
        $this->data['products']         = $this->site->getProducts();
        $this->data['warehouses']       = $this->site->getAllWarehouses();
        $this->data['count_warehouses'] = explode(',', $this->session->userdata('warehouse_id'));
        $this->data['drivers']          = $this->site->getDriver();

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('sales')]];
        $meta = ['page_title' => lang('sales'), 'bc' => $bc];
        $this->page_construct('money/index', $meta, $this->data);
    }
    public function getChange($biller_id = null)
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
        $view_receipt       = anchor('admin/pos/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_receipt'));
        $view_logo            = anchor('admin/sales/modal_view/$1/logo', '<i class="fa fa-money"></i> ' . lang('print_with_logo'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $detail_link          = anchor('admin/sales/modal_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');        
        $return_detail_link   = anchor('admin/sales/return_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('return_sale') . ' ' . lang('details'));
        $duplicate_link       = anchor('admin/sales/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link        = anchor('admin/sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_payment_link     = anchor('admin/sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $email_link           = anchor('admin/sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link            = anchor('admin/sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'));
        $request_edit_link    = anchor('admin/sales/add_request_edit_sale/$1', '<i class="fa fa-file-text"></i> ' . lang('request_edit_sale'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $pdf_link             = anchor('admin/sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link          = anchor('admin/sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        $delete_link          = "<a href='#' class='po' title='<b>" . lang('delete_sale') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $view_receipt . '</li>
            <li>' . $view_logo . '</li> 
                <li>' . $duplicate_link . '</li>
                <li>' . $payments_link . '</li>
                <li class="add_payment">' . $add_payment_link . '</li>
                <li class="edit">' . $edit_link . '</li>
                <li>' . $pdf_link . '</li>
                <li>' . $email_link . '</li>
                <li>' . $return_link . '</li>
                <li class="delete">' . $delete_link . '</li>
            </ul>
        </div></div>';
      
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('money_exchange')}.id as id, 
                DATE_FORMAT({$this->db->dbprefix('money_exchange')}.date, '%Y-%m-%d %T') as date,
                {$this->db->dbprefix('money_exchange')}.reference_no,
                {$this->db->dbprefix('money_exchange')}.biller, 
                {$this->db->dbprefix('money_exchange')}.customer, 
                {$this->db->dbprefix('money_exchange')}.sale_status, 
                {$this->db->dbprefix('money_exchange')}.grand_total, 
                {$this->db->dbprefix('money_exchange')}.paid, 
                ({$this->db->dbprefix('money_exchange')}.grand_total - {$this->db->dbprefix('money_exchange')}.paid) as balance,
                {$this->db->dbprefix('money_exchange')}.payment_status, 
                {$this->db->dbprefix('money_exchange')}.return_id")
            ->join('users', 'money_exchange.saleman_by = users.id', 'left')
            ->order_by('money_exchange.id', 'desc')
            ->from('money_exchange');

        if ($biller_id) {
            $this->datatables->where_in('money_exchange.biller_id', $biller_id);
        }
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("FIND_IN_SET(bpas_sales.created_by, '" . $this->session->userdata('user_id') . "')");
        }
        if ($this->input->get('attachment') == 'yes') {
            $this->datatables->where('payment_status !=', 'paid')->where('attachment !=', null);
        }
        if ($payment_status) {
            $get_status = explode('_', $payment_status);
            $this->datatables->where_in('money_exchange.payment_status', $get_status);
        }
        if ($reference_no) {
            $this->datatables->where('money_exchange.reference_no', $reference_no);
        }
        // if ($product_id) {
        //     $this->datatables->where('money_exchange.product_id', $product_id);
        // }
        if ($biller) {
            $this->datatables->where('money_exchange.biller_id', $biller);
        }
        if ($customer) {
            $this->datatables->where('money_exchange.customer_id', $customer);
        }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('money_exchange') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        $this->datatables->where($this->db->dbprefix('money_exchange') . '.pos !=', 1);
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }

    public function getChange_()
    {
        $this->load->library('datatables');
        $edit_link = anchor('admin/money/edit_exchange/$1', '<i class="fa fa-edit"></i> ' . lang('edit_exchange'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $delete_link = "<a href='#' class='delete_exchange po' title='<b>" . $this->lang->line("delete_exchange") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('money/delete_exchange/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_exchange') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
        $this->datatables->select("
                id, 
                DATE_FORMAT(".$this->db->dbprefix('money_exchange').".date, '%Y-%m-%d %T') as date, 
                from_currency,
                amount,
                exchange_rate, 
                received_amount
            ")
            ->from('money_exchange');
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
    public function add_exchange_()
    {
        $this->form_validation->set_rules('type', lang('type'), 'trim|required');
        $this->form_validation->set_rules('from_currency', lang('from_currency'), 'required');
        $this->form_validation->set_rules('kh_rate', lang('exchange_rate'), 'required|numeric');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data = [
                'date'          => $date,
                'biller_id'     => $this->Settings->default_biller,
                'type'          => $this->input->post('type'),
                'customer_id'   => $this->input->post('customer'),
                'from_currency' => $this->input->post('from_currency'),
                'to_currency'   => $this->input->post('to_currency'),
                'exchange_rate' => $this->input->post('kh_rate'),
                'amount'        => $this->bpas->formatDecimalRaw($this->input->post('amount')),
                'paid_by'       => $this->input->post('paid_by'),
                'note'          => $this->bpas->clear_tags($this->input->post('note')),
                'created_by'    => $this->session->userdata('user_id')
            ];
        } elseif ($this->input->post('add_exchange')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('money');
        }
        if ($this->form_validation->run() == true && $this->money_model->addExchangeCurrency($data)) {
            $this->session->set_flashdata('message', lang('currency_added'));
            admin_redirect('money');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['from_currencies'] = $this->site->getAllCurrencies();
            $this->data['to_currencies']   = $this->site->getAllCurrencies();
            $this->data['modal_js']   = $this->site->modal_js();
            $this->data['page_title'] = lang('new_currency');
            $this->load->view($this->theme . 'money/add_exchange', $this->data);
        }
    }
    public function edit_exchange($id = NULL)
    {
        $this->form_validation->set_rules('type', lang('type'), 'trim|required');
        $this->form_validation->set_rules('from_currency', lang('from_currency'), 'required');
        $this->form_validation->set_rules('kh_rate', lang('exchange_rate'), 'required|numeric');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data = [
                'date'          => $date,
                'biller_id'     => $this->Settings->default_biller,
                'type'          => $this->input->post('type'),
                'customer_id'   => $this->input->post('customer'),
                'from_currency' => $this->input->post('from_currency'),
                'to_currency'   => $this->input->post('to_currency'),
                'exchange_rate' => $this->input->post('kh_rate'),
                'amount'        => $this->bpas->formatDecimalRaw($this->input->post('amount')),
                'paid_by'       => $this->input->post('paid_by'),
                'note'          => $this->bpas->clear_tags($this->input->post('note')),
                'updated_by'    => $this->session->userdata('user_id')
            ];
        } elseif ($this->input->post('edit_exchange')) {
            $this->session->set_flashdata('error', validation_errors());
            //redirect("system_settings/currencies");
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->money_model->updateExchange($id, $data)) { //check to see if we are updateing the customer
            $this->session->set_flashdata('message', lang("currency_updated"));
            //redirect("system_settings/currencies");
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['exchange'] = $this->money_model->getExchangeByID($id);
            $this->data['from_currencies'] = $this->site->getAllCurrencies();
            $this->data['to_currencies']   = $this->site->getAllCurrencies();
            $this->data['id'] = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'money/edit_exchange', $this->data);
        }
    }

    public function delete_exchange($id = NULL)
    {
        if ($this->money_model->delete_exchange($id)) {
            $this->bpas->send_json(array('msg' => lang("exchange_deleted")));
        }
    }

    public function ExchangeSuggestions($pos = 0)
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
        $rows           = $this->money_model->getProductNames($sr, $warehouse_id, $pos);
        if ($rows) {
            $r = 0; $pr = array();
            foreach ($rows as $row) {
                $c = uniqid(mt_rand(), true);
             
                $cate_id = $row->subcategory_id?$row->subcategory_id:$row->category_id;
                unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $option               = false;
                $row->qty             = 1;
                $row->quantity        = 0;
                $row->base_quantity   = 1;
                $row->discount        = 0;
                $row->item_tax_method = $row->tax_method;      
                if ($this->Settings->customer_group_discount == 2 && !empty($customer_group)) {
                    $row->discount    = (-1 * $customer_group->percent) . "%";
                }
                $row->serial          = '';
                $options              = $this->sales_model->getProductOptions($row->id, $warehouse_id);
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
                $set_price = $this->site->getUnitByProId($row->id);
                if ($customer->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
                        $set_price = $this->site->getUnitByProId_PG($row->id, $customer->price_group_id);
                    }
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
                $categories           = $this->site->getCategoryByID($cate_id);
                $fiber_type           = $this->sales_model->getFiberTypeById($row->id);
                $combo_items          = $row->type == 'combo' ? $this->sales_model->getProductComboItems($row->id, $warehouse_id) : false;
                $units                = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate             = $this->site->getTaxRateByID($row->tax_rate);
                $fibers               = array('fiber' => $categories, 'type' => $fiber_type);
                $stock_items          = $this->site->getStockMovementByProductID($row->id, $warehouse_id, $row->option);
                $from_currencies           = $this->money_model->getAllDiffCurrencies();
                $to_currency          = $this->money_model->getAllDiffCurrencies();
                //$row->currency        = $this->site->getCurrencyByCode($row->currency)->rate;
                $row->exchange_rate      = 0;
                $row->from_currency_code = 'USD';
                $row->to_currency_code   = 'KHR';

                //if ($row->type != 'standard' || ($row->type == 'standard' && $this->Settings->overselling == 1 && $warehouse->overselling == 1)) {
                    $pr[] = [
                            'id'          => sha1($c . $r), 
                            'item_id'     => $row->id, 
                            'label'       => $row->name . ' (' . $row->code . ')', 
                            'category'    => $row->category_id, 
                            'row'         => $row, 
                            'combo_items' => $combo_items, 
                            'tax_rate'    => $tax_rate, 
                            'units'       => $units,  
                            'set_price'   => $set_price, 
                            'options'     => $options, 
                            'fiber'       => $fibers,
                            'exchange_rate'  => $row->exchange_rate,
                            'currencies'  => $from_currencies,
                            'to_currency' => $to_currency,
                            'expiry'      => null
                    ];
                    $r++;
                //}
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    public function add_exchange($sale_id=null)
    {   
        $this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_invoiceNo']) {
                $reference = $this->site->CheckedSaleReference($this->input->post('reference_no'), $this->site->getReference('so'));
            } else {
                $reference = $this->site->getReference('so');
            }
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $project_id           = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $warehouse_id         = $this->input->post('warehouse');
            $customer_id          = $this->input->post('customer');
            $biller_id            = $this->input->post('biller');
            $total_items          = $this->input->post('total_items');
            $sale_status          = $this->input->post('sale_status');
            $payment_status       = $this->input->post('payment_status');
            $payment_term         = $this->input->post('payment_term');
            $payment_term_details = $this->site->getAllPaymentTermByID($payment_term);
            if ($this->Settings->payment_term) {
                $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            } else {
                $due_date = (isset($payment_term_details[0]->id) ? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);
            }
            $shipping           = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $surcharge          = $this->input->post('surcharge') ? $this->input->post('surcharge') : 0;
            $customer_details   = $this->site->getCompanyByID($customer_id);
            $customer           = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company.'/'.$customer_details->name : $customer_details->name;
            $biller_details     = $this->site->getCompanyByID($biller_id);
            $biller             = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company.'/'.$biller_details->name : $biller_details->name;
            $note               = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note         = $this->bpas->clear_tags($this->input->post('staff_note'));
            $quote_id           = $this->input->post('quote_id') ? $this->input->post('quote_id') : null;
            $commission_product = 0;
            
            $total              = 0;
            $product_tax        = 0;
            $product_discount   = 0;
            $digital            = false;
            $stockmoves         = null;
            $gst_data           = [];
            $total_cgst         = $total_sgst       = $total_igst       = 0;
            $i                  = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];


                $from_currency    = isset($_POST['from_currency[]'][$r]) ? $_POST['exchange_rate'][$r] :null;
                $to_currency      = isset($_POST['to_currency[]'][$r]) ? $_POST['to_currency'][$r] :null;
                $exchange_rate      = isset($_POST['exchange_rate'][$r]) ? $_POST['exchange_rate'][$r] :0;
                
                $item_detail        = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : ''; 
                $saleman_item       = isset($_POST['saleman_item'][$r]) ? $_POST['saleman_item'][$r] : '';

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByID($item_id) : null;
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
                    $product_tax       += $pr_item_tax;
                    $subtotal           = (($exchange_rate * $item_unit_quantity));
                    $unit               = $this->site->getUnitByID($item_unit);
                  
                    $purchase_unit_cost = $product_details->cost;
                    $saleman = $this->site->getuser($this->input->post('saleman_by'));
                    
                    if ($unit->id != $product_details->unit) {
                        $cost = $this->site->convertCostingToBase($purchase_unit_cost, $unit);
                    } else {
                        $cost = $cost;
                    }
              
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
                                    'quantity'       => $cost_item['quantity'] * (-1),
                                    'unit_quantity'  => !empty($unit->operation_value) ? $unit->operation_value : 1,
                                    'expiry'         => $item_expiry,
                                    'unit_code'      => $unit->code,
                                    'unit_id'        => $item_unit,
                                    'warehouse_id'   => $warehouse_id,
                                    'date'           => $date,
                                    'real_unit_cost' => $cost_item['cost'],
                                
                                    'reference_no'   => $reference,
                                    'user_id'        => $this->session->userdata('user_id'),
                                );
                                //========accounting=========//
                                if ($this->Settings->module_account == 1 && $item_type != 'manual' && ($sale_status == 'completed')) {
                                    $productAcc = $this->site->getProductAccByProductId($item_id);
                                    $accTrans[] = array(
                                        'tran_type'     => 'Sale',
                                        'tran_date'     => $date,
                                        'reference_no'  => $reference,
                                        'account_code'  => $this->accounting_setting->default_stock,
                                        'amount'        => -($cost_item['cost'] * $cost_item['quantity']),
                                        'narrative'     => $this->site->getAccountName($this->accounting_setting->default_stock),
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
                                        'account_code'  => $this->accounting_setting->default_cost,
                                        'amount'        => ($cost_item['cost'] * $cost_item['quantity']),
                                        'narrative'     => $this->site->getAccountName($this->accounting_setting->default_cost),
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
                            $cost = $item_cost_total / $item_cost_qty;
                        } else {
                            $stockmoves[] = array(
                                'transaction'    => 'Sale',
                                'product_id'     => $item_id,
                                'product_type'   => $item_type,
                                'product_code'   => $item_code,
                                'product_name'   => $item_name,
                                'quantity'       => $item_quantity * (-1),
                                'unit_quantity'  => !empty($unit->operation_value) ? $unit->operation_value : 1,
                                'unit_code'      => $unit->code,
                                'unit_id'        => $item_unit,
                                'warehouse_id'   => $warehouse_id,
                                'date'           => $date,
                                'real_unit_cost' => ($item_quantity < 0 ? (($item_net_price + $item_tax) / (!empty($unit->operation_value) ? $unit->operation_value : 1)) : $cost),
                     
                                'reference_no'   => $reference,
                                'user_id'        => $this->session->userdata('user_id'),
                            );
                            //========accounting=========//
                            if ($this->Settings->module_account == 1 && $item_type != 'manual' && ($sale_status == 'completed' || $sale_status == 'consignment')) {
                                $productAcc = $this->site->getProductAccByProductId($item_id);
                                $accTrans[] = array(
                                    'tran_type'     => 'Sale',
                                    'tran_date'     => $date,
                                    'reference_no'  => $reference,
                                    'account_code'  => $this->accounting_setting->default_stock,
                                    'amount'        => -($cost * $item_unit_quantity),
                                    'narrative'     => $this->site->getAccountName($this->accounting_setting->default_stock),
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
                                    'account_code'  => $this->accounting_setting->default_cost,
                                    'amount'        => ($cost * $item_unit_quantity),
                                    'narrative'     => $this->site->getAccountName($this->accounting_setting->default_cost),
                                    'description'   => $note,
                                    'biller_id'     => $biller_id,
                                    'project_id'    => $project_id,
                                    'customer_id'   => $customer_id,
                                    'created_by'    => $this->session->userdata('user_id'),
                                    'activity_type' => $this->site->get_activity($this->accounting_setting->default_cost)
                                );
                            }
                            //============end accounting=======//
                        }
                    
                    if ($this->Settings->module_account == 1) {
                        $getproduct    = $this->site->getProductByID($item_id);
                        $default_sale  = ($item_type == 'standard') ? $this->accounting_setting->default_sale : $this->accounting_setting->other_income;
                        $inventory_acc = $this->accounting_setting->default_stock;
                        $costing_acc   = $this->accounting_setting->default_cost;        
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

                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'cost'              => $cost,
                        'purchase_unit_cost'=> $purchase_unit_cost ? $purchase_unit_cost : NULL,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $this->bpas->formatDecimal($item_net_price + $item_tax),
                        'quantity'          => $item_quantity,
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'from_currency'     => $from_currency,
                        'to_currency'       => $to_currency,
                        'exchange_rate'     => $exchange_rate,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal)
                    ];
         
                    $products[] = ($product);
                    $total += $this->bpas->formatDecimal(($exchange_rate * $item_unit_quantity), 4);
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
            $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount + $this->bpas->formatDecimal($surcharge)), 4);
            $saleman_award_points = 0;
            $user  = $this->site->getUser($this->session->userdata('user_id'));
            $staff = $this->site->getUser($this->input->post('saleman_by'));

      
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
                        'tran_type'     => 'Sale',
                        'tran_date'     => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_freight,
                        'amount'        => -$shipping,
                        'narrative'     => 'Shipping',
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'customer_id'   => $customer_id,
                        'created_by'    => $this->session->userdata('user_id'),
                    );
                }
            }
            //============end accounting=======//
            $data      = [
                'date'                 => $date,
                'project_id'           => $this->input->post('project'),
                'reference_no'         => $reference,
                'customer_id'          => $customer_id,
                'customer'             => $customer,
                'biller_id'            => $biller_id,
                'biller'               => $biller,
                'warehouse_id'         => $warehouse_id,
                'note'                 => $note,
                'staff_note'           => $staff_note,
                'total'                => $total,
                'product_discount'     => $product_discount,
                'order_discount_id'    => $this->input->post('order_discount'),
                'order_discount'       => $order_discount,
                'total_discount'       => $total_discount,
                'product_tax'          => $product_tax,
                'order_tax_id'         => $this->input->post('order_tax'),
                'order_tax'            => $order_tax,
                'total_tax'            => $total_tax,
                'shipping'             => $this->bpas->formatDecimal($shipping),
                'grand_total'          => $grand_total,
                'total_items'          => $total_items,
                'sale_status'          => $sale_status,
                'payment_status'       => $payment_status,
                'payment_term'         => $payment_term,
 
                'paid'                 => 0, 
                'created_by'           => $this->session->userdata('user_id'),
                'hash'                 => hash('sha256', microtime() . mt_rand()),
                'saleman_by'           => $this->input->post('saleman_by')
            ];
            if ($payment_status == 'partial' || $payment_status == 'paid') {
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
                    'type'         => 'received',
                ];
                $amount_paying = $this->bpas->formatDecimal($this->input->post('amount-paid'));
                //=====add accountig=====//
                if ($this->Settings->module_account == 1) {
                    if ($this->input->post('paid_by') == 'deposit') {
                        $payment['bank_account'] = $this->accounting_setting->default_sale_deposit;
                        $paying_to = $this->accounting_setting->default_sale_deposit;
                    } else {
                        $payment['bank_account'] = $this->input->post('bank_account');
                        $paying_to = $this->input->post('bank_account');
                    }
                    if ($amount_paying < $grand_total) {
                        $accTranPayments[] = array(
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
                    $accTranPayments[] = array(
                        'tran_type'    => 'Payment',
                        'tran_date'    => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'account_code' => $paying_to,
                        'amount'       => $amount_paying,
                        'narrative'    => $this->site->getAccountName($paying_to),
                        'description'  => $this->input->post('payment_note'),
                        'biller_id'    => $biller_id,
                        'project_id'   => $project_id,
                        'customer_id'  => $customer_id,
                        'created_by'   => $this->session->userdata('user_id'),
                    );
                }
                //=====end accountig=====//
            } else {
                $accTranPayments = [];
                $payment    = [];
                $accTrans[] = array(
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
        if ($this->form_validation->run() == true && $this->money_model->addExchange($data, $products, $stockmoves, $payment, $si_return = array(), $accTrans, $accTranPayments, null, $commission_product)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang('exchange_has_been_added'));
            admin_redirect('money');
        } else {
            if ($sale_id) {
                $getSaleslist = $this->sales_model->getInvoiceByID($sale_id);
                $items        = $this->sales_model->getAllInvoiceItems($sale_id);
                $sale_items   = [];
                $q_id         = $sale_id;
                krsort($items);
                $c = rand(100000, 9999999);
                foreach ($items as $item) {
                    $b = false;
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
                    $row->serial_no       = (isset($row->serial_no) ? $row->serial_no : '');
                    $row->option          = $item->option_id;
                    $row->expiry          = $item->expiry;
                    $row->details         = (isset($item->comment) ? $item->comment : '');
                    $options              = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);
                    $combo_items          = $row->type == 'combo' ? $this->sales_model->getProductComboItems($row->id, $item->warehouse_id) : false;
                    $units                = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate             = $this->site->getTaxRateByID($row->tax_rate);
                    $ri                   = $this->Settings->item_addition ? $row->id : $c;
                    $set_price            = $this->site->getUnitByProId($row->id);
                    $stock_items          = $this->site->getStockMovementByProductID($row->id, $item->warehouse_id, $row->option);
                    if ($sale_id) {
                        if (!empty($set_price)) {
                            foreach ($set_price as $key => $p) {
                                if ($p->unit_id == $row->unit) {
                                    $set_price[$key]->price = $row->real_unit_price;
                                }
                            }
                        }
                    }
                    $pr[$ri] = [
                        'id' => $ri, 'item_id' => $row->id, 'label'    => $row->name . ' (' . $row->code . ')' . ($row->expiry != null ? ' (' . $row->expiry . ')' : ''),
                        'category' => $row->category_id, 'row'=> $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'set_price' => $set_price, 'options' => $options, 'pitems' => $stock_items, 'expiry' => $row->expiry 
                    ];
                    $c++;
                }
                $this->data['quote_items'] = json_encode($pr);
                $this->data['quote']       = $getSaleslist;
                $this->data['inv']         = $getSaleslist;
                $this->data['quote_id']    = $q_id;
            }
            $this->data['projects']        = $this->site->getAllProject();
            $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers']         = $this->site->getAllCompanies('biller');
            $this->data['data']            = $this->site->getBillerByUser($this->session->userdata('user_id'));
            $this->data['mbillers']        = $this->site->getAllCompaniesByBiller('biller', explode(',', $this->data['data']->multi_biller));
            $this->data['agencies']        = $this->site->getAllUsers();
            $this->data['payment_term']    = $this->site->getAllPaymentTerm();
            $this->data['warehouses']      = $this->site->getAllWarehouses();
            $this->data['tax_rates']       = $this->site->getAllTaxRates();
            $this->data['units']           = $this->site->getAllBaseUnits();
            $this->data['group_price']     = json_encode($this->site->getAllGroupPrice());
            $this->data['salemans']        = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['slnumber']        = $this->site->getReference('so');
            $this->data['sltaxnumber']     = $this->site->getReference('st');
            $this->data['payment_ref']     = ''; //$this->site->getReference('pay');
            $this->data['currencies']      = $this->site->getAllCurrencies();

            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('add_sale')]];
            $meta = ['page_title' => lang('add_sale'), 'bc' => $bc];
            $this->page_construct('money/add', $meta, $this->data);
        }
    }
    public function transfer_companies()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('transfer_companies')]];
        $meta = ['page_title' => lang('transfer_companies'), 'bc' => $bc];
        $this->page_construct('money/transfer_companies', $meta, $this->data);
    }
    public function getTransferCompanies()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('id, name')
            ->from('price_groups')
            ->where('type','transfer_money')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('money/transfer_range/$1') . "' class='tip' title='" . lang('group_product_prices') . "'><i class=\"fa fa-eye\"></i></a>  <a href='" . admin_url('money/edit_transfer_company/$1') . "' class='tip' title='" . lang('edit_transfer_company') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_transfer_company') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('money/delete_transfer_company/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        //->unset_column('id');

        echo $this->datatables->generate();
    }
    public function add_transfer_company()
    {
        $this->form_validation->set_rules('name', lang('group_name'), 'trim|is_unique[price_groups.name]|required|alpha_numeric_spaces');
        if ($this->form_validation->run() == true) {
        
            $data = [
                'name' => $this->input->post('name'),
                'type' => 'transfer_money'
            ];
        } elseif ($this->input->post('add_company')) {
            $this->session->set_flashdata('error', validation_errors());
           redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addPriceGroup($data)) {
            $this->session->set_flashdata('message', lang('transfer_company_added'));
            admin_redirect('money/transfer_companies');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'money/add_transfer_company', $this->data);
        }
    }
    public function edit_transfer_company($id = null)
    {
        $this->form_validation->set_rules('name', lang('group_name'), 'trim|required|alpha_numeric_spaces');
        $pg_details = $this->settings_model->getPriceGroupByID($id);
        if ($this->input->post('name') != $pg_details->name) {
            $this->form_validation->set_rules('name', lang('group_name'), 'required|is_unique[price_groups.name]');
        }

        if ($this->form_validation->run() == true) {
            $data = [
                'name' => $this->input->post('name')
            ];
        } elseif ($this->input->post('edit')) {
            $this->session->set_flashdata('error', validation_errors());

            admin_redirect('money/transfer_companies');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updatePriceGroup($id, $data)) {
            $this->session->set_flashdata('message', lang('transfer_company_updated'));
            admin_redirect('money/transfer_companies');
            
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['price_group'] = $pg_details;
            $this->data['id']          = $id;
            $this->data['modal_js']    = $this->site->modal_js();
            $this->load->view($this->theme . 'money/edit_transfer_company', $this->data);
        }
    }
    public function delete_transfer_company($id = null)
    {
        if ($this->settings_model->deletePriceGroup($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('transfer_company_deleted')]);
        }
    }
    function transfer_range($company_id = NULL)
    {
        $this->form_validation->set_rules('company_id', lang("company_id"), 'required');
        if ($this->form_validation->run() == true) {

            $company_id = $this->input->post('company_id');
            $i = isset($_POST['from_amount']) ? sizeof($_POST['from_amount']) : 0;

            for ($r = 0; $r < $i; $r++) {
                $from_amount    = $_POST['from_amount'][$r];
                $to_amount      = $_POST['to_amount'][$r];
                $commission     = $_POST['commission'][$r];
                $fee            = $_POST['fee'][$r];
                $data[] = array(
                        'company_id'    => $company_id,
                        'from_amount'   => $from_amount,
                        'to_amount'     => $to_amount,
                        'commission'    => $commission,
                        'transfer_fee'  => $fee,
                    );
            }
        }
        if ($this->form_validation->run() == true && $this->money_model->addTransferRange($data, $company_id)) {
            $this->session->set_flashdata('message', lang("transfer_range"));
            admin_redirect('money/transfer_range/'.$company_id);
        }else{
            $this->data['company'] = $this->settings_model->getPriceGroupByID($company_id);

            $transfer_ranges = $this->money_model->getTransferRange($company_id);
            if($transfer_ranges){
                $this->data['transfer_ranges'] = $transfer_ranges;
            }else{
                $transfer_ranges[] = '';
                $this->data['transfer_ranges'] = $transfer_ranges;
            }

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('money'), 'page' => lang('system_settings')),  array('link' => admin_url('money/transfer_range'), 'page' => lang('transfer_range')), array('link' => admin_url('money/transfer_range/'.$company_id.''), 'page' => lang('transfer_range')), array('link' => '#', 'page' => lang('transfer_range')));

            $meta = array('page_title' => lang('transfer_range'), 'bc' => $bc);
            $this->page_construct('money/transfer_range', $meta, $this->data);
            
        }
    }
    function suggestions_rate()
    {
        $term = $this->input->get('term', TRUE);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $rows = $this->money_model->getCurrencyName($term);
        if ($rows) {
            foreach ($rows as $row) {
                $pr[] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name);
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
    function exchange_rate()
    {  
        $this->form_validation->set_rules('rate', lang("rate"), 'required');
        if ($this->form_validation->run() == true) {

            $i = isset($_POST['from_currency']) ? sizeof($_POST['from_currency']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $from_currency    = $_POST['from_currency'][$r];
                $to_currency      = $_POST['to_currency'][$r];
                $exchange_rate    = $_POST['exchange_rate'][$r];
                $data[] = array(
                        'from_currency'   => $from_currency,
                        'to_currency'     => $to_currency,
                        'exchange_rate'   => $exchange_rate,
                        'created_by'      => $this->session->userdata('user_id')
                    );
            }
        }
        if ($this->form_validation->run() == true && $this->money_model->addMoneyExchangeRate($data)) {
            $this->session->set_flashdata('message', lang("exchange_rate_updated"));
            admin_redirect('money/exchange_rate');
        }else{

            $exchange_rates = $this->money_model->getMoneyExchangeRate();
            if($exchange_rates){
                $this->data['exchange_rates'] = $exchange_rates;
            }else{
                $exchange_rates[] = '';
                $this->data['exchange_rates'] = $exchange_rates;
            }

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('money'), 'page' => lang('system_settings')),  array('link' => admin_url('money/transfer_range'), 'page' => lang('transfer_range')), array('link' => admin_url('money/transfer_range'), 'page' => lang('transfer_range')), array('link' => '#', 'page' => lang('transfer_range')));

            $meta = array('page_title' => lang('transfer_range'), 'bc' => $bc);
            $this->page_construct('money/money_exchange_rate', $meta, $this->data);
            
        }
    }

    public function get_exchange_rate()
    {
        $from_currency = $this->input->get('from_currency') ? $this->input->get('from_currency') : null;
        $to_currency = $this->input->get('to_currency') ? $this->input->get('to_currency') : null;
        if ($data['getexchangerate'] = $this->money_model->getMoney_exchange_rate($from_currency, $to_currency)) {
            $this->bpas->send_json($data['getexchangerate']);
        }
        $this->bpas->send_json(false);
    }
}