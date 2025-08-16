<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Transfers extends MY_Controller
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
        $this->lang->admin_load('transfers', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('transfers_model');
        $this->load->admin_model('accounts_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path         = 'assets/uploads/';
        $this->thumbs_path         = 'assets/uploads/thumbs/';
        $this->image_types         = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types  = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size   = '1024';
        $this->data['logo']        = true;
    }

    public function index($warehouse_id = null)
    {
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $count = explode(',', $this->session->userdata('warehouse_id'));
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id ? $warehouse_id : null;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            if (count($count) > 1) {
                $this->data['warehouses']   = $this->site->getAllWarehouses();
            } else {
                $this->data['warehouses']   = null;
            }    
            $this->data['count_warehouses'] = $count;
            $this->data['user_warehouse']   = (isset($count) && count($count) == 1) ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
            $this->data['warehouse_id']     = $warehouse_id;
            $this->data['warehouse']        = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        }

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('transfers')]];
        $meta = ['page_title' => lang('transfers'), 'bc' => $bc];
        $this->page_construct('transfers/index', $meta, $this->data);
    }

    public function getTransfers($warehouse_id = null)
    {
        $this->bpas->checkPermissions('index');
        if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
            $user         = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        $detail_link   = anchor('admin/transfers/view_detail/$1', '<i class="fa fa-file-text-o"></i> ' . lang('transfer_details'));
        $email_link    = anchor('admin/transfers/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_transfer'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link     = anchor('admin/transfers/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_transfer'));
        $pdf_link      = anchor('admin/transfers/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $print_barcode = anchor('admin/products/print_barcodes/?transfer=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
        $delete_link   = "<a href='#' class='tip po' title='<b>" . lang('delete_transfer') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' id='a__$1' href='" . admin_url('transfers/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_transfer') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $print_barcode . '</li>
            <li>' . $delete_link . '</li>
        </ul>
        </div></div>';

        $this->load->library('datatables');
        $this->datatables
            ->select('id, date, transfer_no, from_warehouse_name as fname, from_warehouse_code as fcode, to_warehouse_name as tname,to_warehouse_code as tcode, total, total_tax, grand_total, status, attachment')
            ->from('transfers')
            ->edit_column('fname', '$1 ($2)', 'fname, fcode')
            ->edit_column('tname', '$1 ($2)', 'tname, tcode');
        if ($warehouse_id) {
            $this->datatables->where('from_warehouse_id IN (' . $warehouse_id . ')');
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
        if ((!$this->Owner && !$this->Admin) && !$this->GP['products-cost']) {
            $this->datatables->unset_column('total');
            $this->datatables->unset_column('total_tax');
            $this->datatables->unset_column('grand_total');
        }

        $this->datatables->add_column('Actions', $action, 'id')
            ->unset_column('fcode')
            ->unset_column('tcode');
        echo $this->datatables->generate();
    }

    public function transfer_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->transfers_model->deleteTransfer($id);
                    }
                    $this->session->set_flashdata('message', lang('transfers_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'combine') {
                    $html = $this->combine_pdf($_POST['val']);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('transfers'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('from_warehouse'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('to_warehouse'));
                    if (($this->Owner || $this->Admin) || $this->GP['products-cost']) {
                        $this->excel->getActiveSheet()->SetCellValue('E1', lang('grand_total'));
                        $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));
                    } else {
                        $this->excel->getActiveSheet()->SetCellValue('E1', lang('status'));
                    }
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $tansfer = $this->transfers_model->getTransferByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($tansfer->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $tansfer->transfer_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $tansfer->from_warehouse_name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $tansfer->to_warehouse_name);
                        if (($this->Owner || $this->Admin) || $this->GP['products-cost']) {
                            $this->excel->getActiveSheet()->SetCellValue('E' . $row, $tansfer->grand_total);
                            $this->excel->getActiveSheet()->SetCellValue('F' . $row, $tansfer->status);
                        } else {
                            $this->excel->getActiveSheet()->SetCellValue('E' . $row, $tansfer->status);
                        }
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'tansfers_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_transfer_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function add()
    {
        $this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('to_warehouse', lang('warehouse') . ' (' . lang('to') . ')', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('from_warehouse', lang('warehouse') . ' (' . lang('from') . ')', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('biller', lang("from_biller") . ' (' . lang("to_biller") . ')', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('to_biller', lang("to_biller") . ' (' . lang("to_biller") . ')', 'required|is_natural_no_zero');
        if ($this->form_validation->run()) {
            $transfer_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('to');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $biller_id              = $this->input->post('biller');
            $to_biller              = $this->input->post('to_biller');
            $to_warehouse           = $this->input->post('to_warehouse');
            $from_warehouse         = $this->input->post('from_warehouse');
            $note                   = $this->bpas->clear_tags($this->input->post('note'));
            $shipping               = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $status                 = $this->input->post('status');
            $from_warehouse_details = $this->site->getWarehouseByID($from_warehouse);
            $from_warehouse_code    = $from_warehouse_details->code;
            $from_warehouse_name    = $from_warehouse_details->name;
            $to_warehouse_details   = $this->site->getWarehouseByID($to_warehouse);
            $to_warehouse_code      = $to_warehouse_details->code;
            $to_warehouse_name      = $to_warehouse_details->name;
            $total       = 0;
            $product_tax = 0;
            $gst_data    = [];
            $total_cgst  = $total_sgst  = $total_igst  = 0;
            $i           = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_code          = $_POST['product_code'][$r];
                $item_net_cost      = $this->bpas->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost          = $this->bpas->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost     = $this->bpas->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $item_expiry        = isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r]) && $_POST['expiry'][$r] != 'false' && $_POST['expiry'][$r] != 'undefined' && $_POST['expiry'][$r] != 'null' && $_POST['expiry'][$r] != 'NULL' && $_POST['expiry'][$r] != '00/00/0000' && $_POST['expiry'][$r] != '' ? $this->bpas->fsd($_POST['expiry'][$r]) : null; 
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->site->getProductByCode($item_code);
                    $pr_item_tax   = $item_tax   = 0;
                    $tax           = '';
                    $item_net_cost = $unit_cost;
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];
                        if (!empty($product_details) && $product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal(($item_tax * $item_unit_quantity), 4);
                    }
                    $product_tax += $pr_item_tax;
                    $subtotal = $this->bpas->formatDecimal((($item_net_cost * $item_unit_quantity) + $pr_item_tax), 4);
                    $unit     = $this->site->getProductUnit($product_details->id, $item_unit);
                    $product  = [
                        'product_id'        => $product_details->id,
                        'product_code'      => $item_code,
                        'product_name'      => $product_details->name,
                        'option_id'         => $item_option,
                        'net_unit_cost'     => $item_net_cost,
                        'unit_cost'         => $this->bpas->formatDecimal($item_net_cost + $item_tax, 4),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity'     => $item_unit_quantity,
                        'quantity_balance'  => $item_quantity,
                        'warehouse_id'      => $to_warehouse,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'expiry'            => $item_expiry,
                        'real_unit_cost'    => $real_unit_cost,
                        'date'              => date('Y-m-d', strtotime($date)),
                    ];
                    // var_dump($product);exit();
                    $reactive = 1;
                    $stock_movement[] = array(
                        'transaction'    => 'Transfer',
                        'product_id'     => $product_details->id,
                        'product_type'   => $product_details->type,
                        'product_code'   => $product_details->code,
                        'product_name'   => $product_details->name,
                        'option_id'      => $item_option,
                        'quantity'       => $item_quantity,
                        'unit_quantity'  => $unit->unit_qty,
                        'unit_code'      => $unit->code,
                        'unit_id'        => $item_unit,
                        'date'           => $date,
                        'expiry'         => $item_expiry,
                        'serial_no'      => null,
                        'real_unit_cost' => $product_details->cost,
                        'reference_no'   => $transfer_no,
                        'user_id'        => $this->session->userdata('user_id'),
                    );
                    $products[] = ($product + $gst_data);
                    $total += $this->bpas->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                    //---------accounting-------------
                    if ($this->Settings->module_account == 1 && ($biller_id != $to_biller) && $status != "pending") {
                        $getproduct       = $this->site->getProductByID($product_details->id);
                        $from_biller_name = $this->site->getCompanyByID($biller_id);
                        $to_biller_name   = $this->site->getCompanyByID($to_biller);  
                        $inventory_acc    = $this->accounting_setting->default_stock;
                        $costing_acc      = $this->accounting_setting->default_cost;
                        $accTrans[] = array(
                            'tran_type'    => 'Transfer',
                            'tran_date'    => $date,
                            'reference_no' => $transfer_no,
                            'account_code' => $inventory_acc,
                            'amount'       => ($product_details->cost * $item_quantity)  * (-1),
                            'narrative'    => 'Transfer Inventory from '.$from_biller_name->company.' To '.$to_biller_name->company,
                            'description'  => $note,
                            'biller_id'    => $biller_id,
                            'created_by'   => $this->session->userdata('user_id'),
                        );
                        if ($status == "completed") { 
                            $accTrans[] = array(
                                'tran_type'     => 'Transfer',
                                'tran_date'     => $date,
                                'reference_no'  => $transfer_no,
                                'account_code'  => $inventory_acc,
                                'amount'        => ($product_details->cost * $item_quantity),
                                'narrative'     => 'Transfer Inventory from '.$from_biller_name->company.' To '.$to_biller_name->company,
                                'description'   => $note,
                                'biller_id'     => $to_biller,
                                'created_by'    => $this->session->userdata('user_id'),
                            );
                        }
                    } else {
                        $accTrans = [];
                    }
                    //-------close Account----
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }
            $grand_total = $this->bpas->formatDecimal(($total + $shipping + $product_tax), 4);
            $data        = [
                'transfer_no'         => $transfer_no,
                'date'                => $date,
                'from_warehouse_id'   => $from_warehouse,
                'from_warehouse_code' => $from_warehouse_code,
                'from_warehouse_name' => $from_warehouse_name,
                'to_warehouse_id'     => $to_warehouse,
                'to_warehouse_code'   => $to_warehouse_code,
                'to_warehouse_name'   => $to_warehouse_name,
                'note'                => $note,
                'total_tax'           => $product_tax,
                'total'               => $total,
                'grand_total'         => $grand_total,
                'created_by'          => $this->session->userdata('user_id'),
                'status'              => $status,
                'shipping'            => $shipping,
                'from_biller'         => $biller_id,
                'to_biller'           => $to_biller,
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
        if ($this->form_validation->run() == true && $this->transfers_model->addTransfer($data, $products, $stock_movement, $accTrans)) {
            $this->session->set_userdata('remove_tols', 1);
            $this->session->set_flashdata('message', lang('transfer_added'));
            admin_redirect('transfers');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['name'] = [
                'name'  => 'name',
                'id'    => 'name',
                'type'  => 'text',
                'value' => $this->form_validation->set_value('name'),
            ];
            $this->data['quantity'] = [
                'name'  => 'quantity',
                'id'    => 'quantity',
                'type'  => 'text',
                'value' => $this->form_validation->set_value('quantity'),
            ];
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['rnumber']    = $this->site->getReference('to');
            $this->data['billers']    = $this->site->getAllCompanies('biller');
            $this->data['to_billers'] = $this->site->getAllCompanies('biller');
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('transfers'), 'page' => lang('transfers')], ['link' => '#', 'page' => lang('add_transfer')]];
            $meta = ['page_title' => lang('transfer_quantity'), 'bc' => $bc];
            $this->page_construct('transfers/add', $meta, $this->data);
        }
    }
    public function edit($id = null)
    {
        $this->bpas->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $transfer = $this->transfers_model->getTransferByID($id);
        if (!$this->session->userdata('edit_right')) {
            $this->bpas->view_rights($transfer->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('to_warehouse', lang('warehouse') . ' (' . lang('to') . ')', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('from_warehouse', lang('warehouse') . ' (' . lang('from') . ')', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('biller', lang("from_biller") . ' (' . lang("to_biller") . ')', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('to_biller', lang("to_biller") . ' (' . lang("to_biller") . ')', 'required|is_natural_no_zero');
        if ($this->form_validation->run()) {
            $transfer_no = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $biller_id = $this->input->post('biller');
            $to_biller = $this->input->post('to_biller');
            $to_warehouse           = $this->input->post('to_warehouse');
            $from_warehouse         = $this->input->post('from_warehouse');
            $note                   = $this->bpas->clear_tags($this->input->post('note'));
            $shipping               = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $status                 = $this->input->post('status');
            $from_warehouse_details = $this->site->getWarehouseByID($from_warehouse);
            $from_warehouse_code    = $from_warehouse_details->code;
            $from_warehouse_name    = $from_warehouse_details->name;
            $to_warehouse_details   = $this->site->getWarehouseByID($to_warehouse);
            $to_warehouse_code      = $to_warehouse_details->code;
            $to_warehouse_name      = $to_warehouse_details->name;
            $total       = 0;
            $product_tax = 0;
            $gst_data    = [];
            $total_cgst  = $total_sgst  = $total_igst  = 0;
            $i           = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_code          = $_POST['product_code'][$r];
                $item_net_cost      = $this->bpas->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost          = $this->bpas->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost     = $this->bpas->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $quantity_balance   = $_POST['quantity_balance'][$r];
                $ordered_quantity   = $_POST['ordered_quantity'][$r];
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $item_expiry        = isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r]) && $_POST['expiry'][$r] != 'false' && $_POST['expiry'][$r] != 'undefined' && $_POST['expiry'][$r] != 'null' && $_POST['expiry'][$r] != 'NULL' && $_POST['expiry'][$r] != '00/00/0000' && $_POST['expiry'][$r] != '' ? $this->bpas->fsd($_POST['expiry'][$r]) : null; 
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->site->getProductByCode($item_code);
                    $pr_item_tax     = $item_tax     = 0;
                    $tax             = '';
                    $item_net_cost   = $unit_cost;
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];
                        if (!empty($product_details) && $product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal(($item_tax * $item_unit_quantity), 4);
                    }
                    $product_tax += $pr_item_tax;
                    $subtotal    = $this->bpas->formatDecimal((($item_net_cost * $item_unit_quantity) + $pr_item_tax), 4);
                    $unit        = $this->site->getProductUnit($product_details->id, $item_unit);
                    $balance_qty = ($status != 'completed') ? $item_quantity : ($item_quantity - ($ordered_quantity - $quantity_balance));
                    $product = [
                        'transfer_id'       => $id,
                        'product_id'        => $product_details->id,
                        'product_code'      => $item_code,
                        'product_name'      => $product_details->name,
                        'option_id'         => $item_option,
                        'net_unit_cost'     => $item_net_cost,
                        'unit_cost'         => $this->bpas->formatDecimal(($item_net_cost + $item_tax), 4),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity'     => $item_unit_quantity,
                        'quantity_balance'  => $balance_qty,
                        'warehouse_id'      => $to_warehouse,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'expiry'            => $item_expiry,
                        'real_unit_cost'    => $real_unit_cost,
                        'date'              => date('Y-m-d', strtotime($date)),
                    ];
                    $reactive = 1;
                    $stock_movement[] = array(
                        'transaction'    => 'Transfer',
                        'transaction_id' => $id,
                        'product_id'     => $product_details->id,
                        'product_type'   => $product_details->type,
                        'product_code'   => $product_details->code,
                        'product_name'   => $product_details->name,
                        'option_id'      => $item_option,
                        'quantity'       => $item_quantity,
                        'unit_quantity'  => $unit->unit_qty,
                        'unit_code'      => $unit->code,
                        'unit_id'        => $item_unit,
                        'date'           => $date,
                        'expiry'         => $item_expiry,
                        'serial_no'      => null,
                        'real_unit_cost' => $product_details->cost,
                        'reference_no'   => $transfer_no,
                        'user_id'        => $this->session->userdata('user_id'),
                    );
                    $products[] = ($product + $gst_data);
                    $total += $this->bpas->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                    //---------accounting-------------
                    if ($this->Settings->module_account == 1 && ($biller_id != $to_biller) && $status != "pending") {
                        $getproduct = $this->site->getProductByID($product_details->id);
                        // if($getproduct->gender =='WOMEN' || $getproduct->gender =='WOMENS'){
                        //     $inventory_acc = 3001101;
                        //     $costing_acc   = 8001101;
                        // }elseif ($getproduct->gender =='MEN' || $getproduct->gender =='MENS') {
                        //     $inventory_acc = 3001102;
                        //     $costing_acc   = 8001102;
                        // }elseif ($getproduct->gender =='GIRLS' || $getproduct->gender =='GIRL') {
                        //     $inventory_acc = 3001103;
                        //     $costing_acc   = 8001103;
                        // }elseif ($getproduct->gender =='BOY' || $getproduct->gender =='BOYS') {
                        //     $inventory_acc = 3001104;
                        //     $costing_acc   = 8001104;
                        // }else{
                            $inventory_acc = $this->accounting_setting->default_stock;
                            $costing_acc   = $this->accounting_setting->default_cost;
                        //}
                        $accTrans[] = array(
                            'tran_no'       => $id,
                            'tran_type'     => 'Transfer',
                            'tran_date'     => $date,
                            'reference_no'  => $transfer_no,
                            'account_code'  => $inventory_acc,
                            'amount'        => ($product_details->cost * $item_quantity)  * (-1),
                            'narrative'     => $this->site->getAccountName($inventory_acc),
                            'description'   => $note,
                            'biller_id'     => $biller_id,
                            'created_by'    => $this->session->userdata('user_id'),
                        );
                        // $accTrans[] = array(
                        //     'tran_no'       => $id,
                        //     'tran_type'     => 'Transfer',
                        //     'tran_date'     => $date,
                        //     'reference_no'  => $transfer_no,
                        //     'account_code'  => $this->accounting_setting->default_stock_adjust,
                        //     'amount'        => ($product_details->cost * $item_quantity),
                        //     'narrative'     => $this->site->getAccountName($this->accounting_setting->default_stock_adjust),
                        //     'description'   => $note,
                        //     'biller_id'     => $biller_id,
                        //     'created_by'    => $this->session->userdata('user_id'),
                        // );
                        if($status=="completed"){
                            $accTrans[] = array(
                                'tran_no'       => $id,
                                'tran_type'     => 'Transfer',
                                'tran_date'     => $date,
                                'reference_no'  => $transfer_no,
                                'account_code'  => $inventory_acc,
                                'amount'        => ($product_details->cost * $item_quantity),
                                'narrative'     => $this->site->getAccountName($inventory_acc),
                                'description'   => $note,
                                'biller_id'     => $to_biller,
                                'created_by'    => $this->session->userdata('user_id'),
                            );
                            // $accTrans[] = array(
                            //     'tran_no'       => $id,
                            //     'tran_type'     => 'Transfer',
                            //     'tran_date'     => $date,
                            //     'reference_no'  => $transfer_no,
                            //     'account_code'  => $this->accounting_setting->default_stock_adjust,
                            //     'amount'        => ($product_details->cost * $item_quantity) * (-1),
                            //     'narrative'     => $this->site->getAccountName($this->accounting_setting->default_stock_adjust),
                            //     'description'   => $note,
                            //     'biller_id'     => $to_biller,
                            //     'created_by'    => $this->session->userdata('user_id'),
                            // );
                        }
                    } else {
                        $accTrans=[];
                    }
                    //-------close Account----
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }
            $grand_total = $this->bpas->formatDecimal(($total + $shipping + $product_tax), 4);
            $data        = [
                'transfer_no'         => $transfer_no,
                'date'                => $date,
                'from_warehouse_id'   => $from_warehouse,
                'from_warehouse_code' => $from_warehouse_code,
                'from_warehouse_name' => $from_warehouse_name,
                'to_warehouse_id'     => $to_warehouse,
                'to_warehouse_code'   => $to_warehouse_code,
                'to_warehouse_name'   => $to_warehouse_name,
                'note'                => $note,
                'total_tax'           => $product_tax,
                'total'               => $total,
                'grand_total'         => $grand_total,
                'created_by'          => $this->session->userdata('user_id'),
                'status'              => $status,
                'shipping'            => $shipping,
                'from_biller'         => $biller_id,
                'to_biller'           => $to_biller,
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
        if ($this->form_validation->run() == true && $this->transfers_model->updateTransfer($id, $data, $products, $stock_movement, $accTrans)) {
            $this->session->set_userdata('remove_tols', 1);
            $this->session->set_flashdata('message', lang('transfer_updated'));
            admin_redirect('transfers');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['transfer'] = $this->transfers_model->getTransferByID($id);
            $transfer_items         = $this->transfers_model->getAllTransferItems($id, $this->data['transfer']->status);
            krsort($transfer_items);
            $c = rand(100000, 9999999);
            foreach ($transfer_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $row = json_decode('{}');
                } else {
                    unset($row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                }
                $row->qty              = $item->unit_quantity;
                $row->quantity         = $item->quantity_balance;
                $row->base_quantity    = $item->quantity;
                $row->quantity_balance = $item->quantity_balance;
                $row->ordered_quantity = $item->quantity;
                $row->base_unit        = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_cost   = $row->cost ? $row->cost : $item->unit_cost;
                $row->unit             = $item->product_unit_id;
                $row->unit_name        = $this->site->getUnitByID($item->product_unit_id)->name;
                $row->cost             = $item->net_unit_cost;
                $row->unit_cost        = $item->net_unit_cost + ($item->item_tax / $item->quantity);
                $row->real_unit_cost   = $item->real_unit_cost;
                $row->tax_rate         = $item->tax_rate_id;
                $row->option           = $item->option_id;
                $expiry                = (($item->expiry && $item->expiry != '0000-00-00') ? $item->expiry : '');
                $row->expiry           = $expiry;
                $options               = $this->site->getProductOptions($row->id, $this->data['transfer']->from_warehouse_id, false);
                $pis = $this->site->getStockMovement_ProductBalanceQuantity($item->product_id, $this->data['transfer']->from_warehouse_id, $item->option_id);
                if ($pis) {
                    $row->quantity += $pis->quantity_balance;
                }
                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        if ($pis) {
                            $option_quantity = $pis->quantity_balance;
                        }
                        if ($option->id == $item->option_id) {
                            $option->quantity += $item->quantity;
                        }
                    }
                }
                $stock_items = $this->site->getStockMovementByProductID($item->product_id, $this->data['transfer']->from_warehouse_id, $item->option_id);
                $units       = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate    = $this->site->getTaxRateByID($row->tax_rate);
                $ri          = $this->Settings->item_addition ? $row->id : $c;
                $set_price   = $this->site->getUnitByProId($row->id);
                $pr[$ri] = [
                    'id'  => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row' => $row, 'tax_rate' => $tax_rate, 'set_price' => $set_price,'units' => $units, 'options' => $options, 'pitems' => $stock_items, 'expirys' => $stock_items, 'expiry' => $expiry ];
                $c++;
            }
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['user_warehouses'] = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->data['transfer_items']  = json_encode($pr);
            $this->data['id']              = $id;
            $this->data['warehouses']      = $this->site->getAllWarehouses();
            $this->data['tax_rates']       = $this->site->getAllTaxRates();
            $this->data['billers']         = $this->site->getAllCompanies('biller');
            $this->data['to_billers']      = $this->site->getAllCompanies('biller');
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('transfers'), 'page' => lang('transfers')], ['link' => '#', 'page' => lang('edit_transfer')]];
            $meta = ['page_title' => lang('edit_transfer_quantity'), 'bc' => $bc];
            $this->page_construct('transfers/edit', $meta, $this->data);
        }
    }
    public function delete($id = null)
    {
        $this->bpas->checkPermissions(null, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->transfers_model->deleteTransfer($id)) {
            $this->site->deleteAccTran('Transfer',$id);
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('transfer_deleted')]);
            }
            $this->session->set_flashdata('message', lang('transfer_deleted'));
            admin_redirect('welcome');
        }
    }
    public function transfer_by_csv()
    {
        $this->bpas->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('to_warehouse', lang('warehouse') . ' (' . lang('to') . ')', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('from_warehouse', lang('warehouse') . ' (' . lang('from') . ')', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');
        $this->form_validation->set_rules('biller', lang("from_biller") . ' (' . lang("to_biller") . ')', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('to_biller', lang("to_biller") . ' (' . lang("to_biller") . ')', 'required|is_natural_no_zero');

        if ($this->form_validation->run()) {
            $transfer_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('to');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $biller_id = $this->input->post('biller');
            $to_biller = $this->input->post('to_biller');
            $to_warehouse           = $this->input->post('to_warehouse');
            $from_warehouse         = $this->input->post('from_warehouse');
            $note                   = $this->bpas->clear_tags($this->input->post('note'));
            $shipping               = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $status                 = $this->input->post('status');
            $from_warehouse_details = $this->site->getWarehouseByID($from_warehouse);
            $from_warehouse_code    = $from_warehouse_details->code;
            $from_warehouse_name    = $from_warehouse_details->name;
            $to_warehouse_details   = $this->site->getWarehouseByID($to_warehouse);
            $to_warehouse_code      = $to_warehouse_details->code;
            $to_warehouse_name      = $to_warehouse_details->name;
            $total       = 0;
            $product_tax = 0;
            $gst_data    = [];
            $total_cgst  = $total_sgst  = $total_igst  = 0;
            if (isset($_FILES['userfile'])) {
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $this->load->library('upload', $config);
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('transfers/transfer_bt_csv');
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
                $keys  = ['product', 'unit_cost', 'quantity', 'variant', 'expiry','description'];
                $final = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv_pr) {
                    $item_code     = $csv_pr['product'];
                    $unit_cost     = $csv_pr['unit_cost'];
                    $item_quantity = $csv_pr['quantity'];
                    $variant       = isset($csv_pr['variant']) ? $csv_pr['variant'] : null;
                    $item_expiry   = isset($csv_pr['expiry']) ? $this->bpas->fsd($csv_pr['expiry']) : null;
                    $item_description = $csv_pr['description'];
                    if (isset($item_code) && isset($unit_cost) && isset($item_quantity)) {
                        if (!($product_details = $this->transfers_model->getProductByCode($item_code))) {
                            $this->session->set_flashdata('error', lang('pr_not_found') . ' ( ' . $csv_pr['product'] . ' ). ' . lang('line_no') . ' ' . $rw);
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                        if ($variant) {
                            $item_option = $this->transfers_model->getProductVariantByName($variant, $product_details->id);
                            if (!$item_option) {
                                $this->session->set_flashdata('error', lang('pr_not_found') . ' ( ' . $csv_pr['product'] . ' - ' . $csv_pr['variant'] . ' ). ' . lang('line_no') . ' ' . $rw);
                                redirect($_SERVER['HTTP_REFERER']);
                            }
                        } else {
                            $item_option     = json_decode('{}');
                            $item_option->id = null;
                        }
                        if (!$this->Settings->overselling) {
                            $warehouse_quantity = $this->transfers_model->getWarehouseProduct($from_warehouse_details->id, $product_details->id, $item_option->id);
                            if ($warehouse_quantity->quantity < $item_quantity) {
                                $this->session->set_flashdata('error', lang('no_match_found') . ' (' . lang('product_name') . ' <strong>' . $product_details->name . '</strong> ' . lang('product_code') . ' <strong>' . $product_details->code . '</strong>) ' . lang('line_no') . ' ' . $rw);
                                redirect($_SERVER['HTTP_REFERER']);
                            }
                        }
                        $pr_item_tax   = $item_tax   = 0;
                        $tax           = '';
                        $item_net_cost = $unit_cost;
                        if (isset($product_details->tax_rate) && $product_details->tax_rate != 0) {
                            $tax_details = $this->site->getTaxRateByID($product_details->tax_rate);
                            $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                            $item_tax    = $ctax['amount'];
                            $tax         = $ctax['tax'];
                            if (!empty($product_details) && $product_details->tax_method != 1) {
                                $item_net_cost = $unit_cost - $item_tax;
                            }
                            $pr_item_tax = $this->bpas->formatDecimal(($item_tax * $item_quantity), 4);
                            if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, false, $tax_details)) {
                                $total_cgst += $gst_data['cgst'];
                                $total_sgst += $gst_data['sgst'];
                                $total_igst += $gst_data['igst'];
                            }
                        }
                        $product_tax += $pr_item_tax;
                        $subtotal = $this->bpas->formatDecimal((($item_net_cost * $item_quantity) + $pr_item_tax), 4);
                        $unit     = $this->site->getUnitByID($product_details->unit);
                        $product = [
                            'product_id'        => $product_details->id,
                            'product_code'      => $item_code,
                            'product_name'      => $product_details->name,
                            'option_id'         => $item_option->id,
                            'net_unit_cost'     => $item_net_cost,
                            'unit_cost'         => $this->bpas->formatDecimal($unit_cost, 4),
                            'quantity'          => $item_quantity,
                            'product_unit_id'   => $unit ? $unit->id : null,
                            'product_unit_code' => $unit ? $unit->code : null,
                            'unit_quantity'     => $item_quantity,
                            'quantity_balance'  => $item_quantity,
                            'warehouse_id'      => $to_warehouse,
                            'item_tax'          => $pr_item_tax,
                            'tax_rate_id'       => $product_details->tax_rate,
                            'tax'               => $tax,
                            'subtotal'          => $subtotal,
                            'expiry'            => $item_expiry,
                            'description'            => $item_description,
                            'real_unit_cost'    => $unit_cost,
                            'date'              => date('Y-m-d', strtotime($date)),
                        ];
                        $stock_movement[] = array(
                            'transaction'    => 'Transfer',
                            'product_id'     => $product_details->id,
                            'product_code'   => $item_code,
                            'option_id'      => $item_option,
                            'quantity'       => $item_quantity,
                            'unit_quantity'  => $unit->unit_qty,
                            'unit_code'      => $unit->code,
                            'unit_id'        => $item_unit,
                            'date'           => $date,
                            'expiry'         => $item_expiry,
                            'serial_no'      => $item_serial,
                            'real_unit_cost' => $product_details->cost,
                            'reference_no'   => $transfer_no,
                            'user_id'        => $this->session->userdata('user_id'),
                        );
                        $products[] = ($product + $gst_data);
                        $total += $this->bpas->formatDecimal(($item_net_cost * $item_quantity), 4);
                        //---------accounting-------------
                        if($this->Settings->module_account == 1 && ($biller_id != $to_biller) && $status != "pending"){
                            $getproduct = $this->site->getProductByID($product_details->id);
                            $from_biller_name = $this->site->getCompanyByID($biller_id);
                            $to_biller_name = $this->site->getCompanyByID($to_biller);
                            $inventory_acc = $this->accounting_setting->default_stock;
                            $costing_acc   = $this->accounting_setting->default_cost;
                            $accTrans[] = array(
                                'tran_type'     => 'Transfer',
                                'tran_date'     => $date,
                                'reference_no'  => $transfer_no,
                                'account_code'  => $inventory_acc,
                                'amount'        => ($product_details->cost * $item_quantity)  * (-1),
                                'narrative'     => 'Transfer Inventory from '.$from_biller_name->company.' To '.$to_biller_name->company,
                                'description'   => $note,
                                'biller_id'     => $biller_id,
                                'created_by'    => $this->session->userdata('user_id'),
                            );
                            // $accTrans[] = array(
                            //     'tran_type'     => 'Transfer',
                            //     'tran_date'     => $date,
                            //     'reference_no'  => $transfer_no,
                            //     'account_code'  => $this->accounting_setting->default_stock_adjust,
                            //     'amount'        => ($product_details->cost * $item_quantity),
                            //     'narrative'     => $this->site->getAccountName($this->accounting_setting->default_stock_adjust),
                            //     'description'   => $note,
                            //     'biller_id'     => $biller_id,
                            //     'created_by'    => $this->session->userdata('user_id'),
                            // );
                            if ($status=="completed") {
                                $accTrans[] = array(
                                    'tran_type'     => 'Transfer',
                                    'tran_date'     => $date,
                                    'reference_no'  => $transfer_no,
                                    'account_code'  => $inventory_acc,
                                    'amount'        => ($product_details->cost * $item_quantity),
                                    'narrative'     => 'Transfer Inventory from '.$from_biller_name->company.' To '.$to_biller_name->company,
                                    'description'   => $note,
                                    'biller_id'     => $to_biller,
                                    'created_by'    => $this->session->userdata('user_id'),
                                );
                                // $accTrans[] = array(
                                //     'tran_type'     => 'Transfer',
                                //     'tran_date'     => $date,
                                //     'reference_no'  => $transfer_no,
                                //     'account_code'  => $this->accounting_setting->default_stock_adjust,
                                //     'amount'        => ($product_details->cost * $item_quantity) * (-1),
                                //     'narrative'     => $this->site->getAccountName($this->accounting_setting->default_stock_adjust),
                                //     'description'   => $note,
                                //     'biller_id'     => $to_biller,
                                //     'created_by'    => $this->session->userdata('user_id'),
                                // );
                            }
                        } else {
                            $accTrans=[];
                        }
                        //-------close Account----
                    }
                    $rw++;
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_item'), 'required');
            } else {
                krsort($products);
            }
            $grand_total = $total + $shipping + $product_tax;
            $data        = [
                'transfer_no'         => $transfer_no,
                'date'                => $date,
                'from_warehouse_id'   => $from_warehouse,
                'from_warehouse_code' => $from_warehouse_code,
                'from_warehouse_name' => $from_warehouse_name,
                'to_warehouse_id'     => $to_warehouse,
                'to_warehouse_code'   => $to_warehouse_code,
                'to_warehouse_name'   => $to_warehouse_name,
                'note'                => $note,
                'total_tax'           => $product_tax,
                'total'               => $total,
                'grand_total'         => $grand_total,
                'created_by'          => $this->session->userdata('user_id'),
                'status'              => $status,
                'shipping'            => $shipping,
                'from_biller'         => $biller_id,
                'to_biller'           => $to_biller,
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
        if ($this->form_validation->run() == true && $this->transfers_model->addTransfer($data, $products,$accTrans)) {
            $this->session->set_userdata('remove_tols', 1);
            $this->session->set_flashdata('message', lang('transfer_added'));
            admin_redirect('transfers');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['name'] = [
                'name'  => 'name',
                'id'    => 'name',
                'type'  => 'text',
                'value' => $this->form_validation->set_value('name'),
            ];
            $this->data['quantity'] = [
                'name'  => 'quantity',
                'id'    => 'quantity',
                'type'  => 'text',
                'value' => $this->form_validation->set_value('quantity'),
            ];
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['rnumber']    = $this->site->getReference('to');
            $this->data['billers']    = $this->site->getAllCompanies('biller');
            $this->data['to_billers'] = $this->site->getAllCompanies('biller');
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('transfers'), 'page' => lang('transfers')], ['link' => '#', 'page' => lang('transfer_by_csv')]];
            $meta = ['page_title' => lang('add_transfer_by_csv'), 'bc' => $bc];
            $this->page_construct('transfers/transfer_by_csv', $meta, $this->data);
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
            admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'transfers');
        }
        if ($this->form_validation->run() == true && $this->transfers_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'transfers');
        } else {
            $this->data['inv']      = $this->transfers_model->getTransferByID($id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'transfers/update_status', $this->data);
        }
    }

    public function view_detail($transfer_id = null)
    {
        $this->bpas->checkPermissions('index', true);
        if ($this->input->get('id')) {
            $transfer_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $transfer            = $this->transfers_model->getTransferByID($transfer_id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($transfer->created_by, true);
        }
        $this->data['rows']           = $this->transfers_model->getAllTransferItems($transfer_id, $transfer->status);
        $this->data['from_warehouse'] = $this->site->getWarehouseByID($transfer->from_warehouse_id);
        $this->data['to_warehouse']   = $this->site->getWarehouseByID($transfer->to_warehouse_id);
        $this->data['transfer']       = $transfer;
        $this->data['tid']            = $transfer_id;
        $this->data['created_by']     = $this->site->getUser($transfer->created_by);

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchases'), 'page' => lang('purchases')], ['link' => '#', 'page' => lang('view')]];
        $meta = ['page_title' => lang('view_purchase_details'), 'bc' => $bc];
        $this->page_construct('transfers/view_detail', $meta, $this->data);
    }

    public function view($transfer_id = null)
    {
        $this->bpas->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $transfer_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $transfer            = $this->transfers_model->getTransferByID($transfer_id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($transfer->created_by, true);
        }
        $this->data['rows']           = $this->transfers_model->getAllTransferItems($transfer_id, $transfer->status);
        $this->data['from_warehouse'] = $this->site->getWarehouseByID($transfer->from_warehouse_id);
        $this->data['to_warehouse']   = $this->site->getWarehouseByID($transfer->to_warehouse_id);
        $this->data['transfer']       = $transfer;
        $this->data['tid']            = $transfer_id;
        $this->data['created_by']     = $this->site->getUser($transfer->created_by);
        $this->load->view($this->theme . 'transfers/view', $this->data);
    }

    public function transfer_alerts()
    {
        $this->bpas->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('transfers')]];
        $meta = ['page_title' => lang('transfers'), 'bc' => $bc];
        $this->page_construct('transfers/transfer_alerts', $meta, $this->data);
    }
    
    public function getTransferAlerts()
    {
        $this->bpas->checkPermissions('index');

        $detail_link   = anchor('admin/transfers/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('transfer_details'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $email_link    = anchor('admin/transfers/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_transfer'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link     = anchor('admin/transfers/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_transfer'));
        $pdf_link      = anchor('admin/transfers/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $print_barcode = anchor('admin/products/print_barcodes/?transfer=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
        $delete_link   = "<a href='#' class='tip po' title='<b>" . lang('delete_transfer') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' id='a__$1' href='" . admin_url('transfers/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_transfer') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
                . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
                . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $detail_link . '</li>
                <li>' . $edit_link . '</li>
                <li>' . $pdf_link . '</li>
                <li>' . $email_link . '</li>
                <li>' . $print_barcode . '</li>
                <li>' . $delete_link . '</li>
            </ul>
        </div></div>';

        $this->load->library('datatables');

        $this->datatables
            ->select('id, date, transfer_no, from_warehouse_name as fname, from_warehouse_code as fcode, to_warehouse_name as tname,to_warehouse_code as tcode, total, total_tax, grand_total, status, attachment')
            ->from('transfers')
            ->where('transfers.status', 'pending')
            ->edit_column('fname', '$1 ($2)', 'fname, fcode')
            ->edit_column('tname', '$1 ($2)', 'tname, tcode');

        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }

        // $this->datatables->add_column('Actions', $action, 'id')
        $this->datatables->unset_column('fcode');
        $this->datatables->unset_column('tcode');
        echo $this->datatables->generate();
    }

    public function email($transfer_id = null)
    {
        $this->bpas->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $transfer_id = $this->input->get('id');
        }
        $transfer = $this->transfers_model->getTransferByID($transfer_id);
        $this->form_validation->set_rules('to', lang('to') . ' ' . lang('email'), 'trim|required|valid_email');
        $this->form_validation->set_rules('subject', lang('subject'), 'trim|required');
        $this->form_validation->set_rules('cc', lang('cc'), 'trim|valid_emails');
        $this->form_validation->set_rules('bcc', lang('bcc'), 'trim|valid_emails');
        $this->form_validation->set_rules('note', lang('message'), 'trim');

        if ($this->form_validation->run() == true) {
            if (!$this->session->userdata('view_right')) {
                $this->bpas->view_rights($transfer->created_by);
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

            $this->load->library('parser');
            $parse_data = [
                'reference_number' => $transfer->transfer_no,
                'site_link'        => base_url(),
                'site_name'        => $this->Settings->site_name,
                'logo'             => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>',
            ];
            $msg        = $this->input->post('note');
            $message    = $this->parser->parse_string($msg, $parse_data);
            $attachment = $this->pdf($transfer_id, null, 'S');

            try {
                if ($this->bpas->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
                    delete_files($attachment);
                    $this->session->set_flashdata('message', lang('email_sent'));
                    admin_redirect('transfers');
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

            if (file_exists('./themes/' . $this->Settings->theme . '/admin/views/email_templates/transfer.html')) {
                $transfer_temp = file_get_contents('themes/' . $this->Settings->theme . '/admin/views/email_templates/transfer.html');
            } else {
                $transfer_temp = file_get_contents('./themes/default/admin/views/email_templates/transfer.html');
            }
            $this->data['subject'] = ['name' => 'subject',
                'id'                         => 'subject',
                'type'                       => 'text',
                'value'                      => $this->form_validation->set_value('subject', lang('transfer_order') . ' (' . $transfer->transfer_no . ') ' . lang('from') . ' ' . $transfer->from_warehouse_name),
            ];
            $this->data['note'] = ['name' => 'note',
                'id'                      => 'note',
                'type'                    => 'text',
                'value'                   => $this->form_validation->set_value('note', $transfer_temp),
            ];
            $this->data['warehouse'] = $this->site->getWarehouseByID($transfer->to_warehouse_id);

            $this->data['id']       = $transfer_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'transfers/email', $this->data);
        }
    }

    public function combine_pdf($transfers_id)
    {
        $this->bpas->checkPermissions('pdf');

        foreach ($transfers_id as $transfer_id) {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $transfer            = $this->transfers_model->getTransferByID($transfer_id);
            if (!$this->session->userdata('view_right')) {
                $this->bpas->view_rights($transfer->created_by);
            }
            $this->data['rows']           = $this->transfers_model->getAllTransferItems($transfer_id, $transfer->status);
            $this->data['from_warehouse'] = $this->site->getWarehouseByID($transfer->from_warehouse_id);
            $this->data['to_warehouse']   = $this->site->getWarehouseByID($transfer->to_warehouse_id);
            $this->data['transfer']       = $transfer;
            $this->data['tid']            = $transfer_id;
            $this->data['created_by']     = $this->site->getUser($transfer->created_by);

            $html[] = [
                'content' => $this->load->view($this->theme . 'transfers/pdf', $this->data, true),
                'footer'  => '',
            ];
        }

        $name = lang('transfers') . '.pdf';
        $this->bpas->generate_pdf($html, $name);
    }

    public function pdf($transfer_id = null, $view = null, $save_bufffer = null)
    {
        if ($this->input->get('id')) {
            $transfer_id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $transfer            = $this->transfers_model->getTransferByID($transfer_id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($transfer->created_by);
        }
        $this->data['rows']           = $this->transfers_model->getAllTransferItems($transfer_id, $transfer->status);
        $this->data['from_warehouse'] = $this->site->getWarehouseByID($transfer->from_warehouse_id);
        $this->data['to_warehouse']   = $this->site->getWarehouseByID($transfer->to_warehouse_id);
        $this->data['transfer']       = $transfer;
        $this->data['tid']            = $transfer_id;
        $this->data['created_by']     = $this->site->getUser($transfer->created_by);
        $name                         = lang('transfer') . '_' . str_replace('/', '_', $transfer->transfer_no) . '.pdf';
        $html                         = $this->load->view($this->theme . 'transfers/pdf', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'transfers/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->bpas->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->bpas->generate_pdf($html, $name);
        }
    }

    public function suggestions()
    {
        $this->bpas->checkPermissions('index', true);
        $term         = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed  = $this->bpas->analyze_term($term);
        $sr        = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $rows = $this->transfers_model->getProductNames($sr, $warehouse_id);
        if ($rows) {
            $r = 0;
            foreach ($rows as $row) {
                $c                     = uniqid(mt_rand(), true);
                $option                = false;
                $row->product_type     = $row->type;
                $row->item_tax_method  = $row->tax_method;
                $row->qty              = 1;
                $row->quantity         = 0;
                $row->base_quantity    = 1;
                $row->quantity_balance = 0;
                $row->ordered_quantity = 0;
                $row->base_unit        = $row->unit;
                $row->base_unit_cost   = $row->cost;
                $row->unit             = $row->unit;
                $row->unit_name        = $this->site->getUnitByID($row->unit)->name;
                $row->discount         = '0';
                $row->expiry           = '';
                $options               = $this->site->getProductOptions($row->id, $warehouse_id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->transfers_model->getProductOptionByID($option_id) : $options[0];
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt       = json_decode('{}');
                    $opt->cost = 0;
                    $option_id = false;
                }
                $row->option = $option_id;
                $pis = $this->site->getStockMovement_ProductBalanceQuantity($row->id, $warehouse_id, $row->option);
                if ($pis) {
                    $row->quantity = $pis->quantity_balance;
                }
                if ($opt->cost != 0) {
                    $row->cost = $opt->cost;
                }
                $row->real_unit_cost = $row->cost;
                $units       = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate    = $this->site->getTaxRateByID($row->tax_rate);
                $set_price   = $this->site->getUnitByProId($row->id);
                $stock_items = $this->site->getStockMovementByProductID($row->id, $warehouse_id, $row->option);
                if ($stock_items) {
                    foreach ($stock_items as $pi) {
                        $pr[] = [
                            'id'  => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')' . ($pi->expiry != null ? ' (' . $pi->expiry . ')' : ''),
                            'row' => $row, 'tax_rate' => $tax_rate, 'set_price' => $set_price, 'units' => $units, 'options' => $options, 'pitems' => $stock_items, 'expirys' => $stock_items, 'expiry' => $pi->expiry 
                        ];
                        $r++;
                    }
                } else {
                    $pr[] = [
                        'id'  => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                        'row' => $row, 'tax_rate' => $tax_rate, 'set_price' => $set_price, 'units' => $units, 'options' => $options, 'pitems' => $stock_items, 'expirys' => $stock_items, 'expiry' => null 
                    ];
                    $r++;
                }
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }
}
