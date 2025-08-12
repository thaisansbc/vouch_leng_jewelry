<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sales_order_store extends MY_Controller
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
        $this->load->admin_model('sales_order_store_model');
        $this->load->admin_model('accounts_model');
        $this->load->admin_model('table_model'); 
        $this->load->admin_model('approved_model');

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
        $this->bpas->checkPermissions('index', true, 'store_sales_order');
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
       
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('sales_order_store')]];
        $meta = ['page_title' => lang('sales_order_store'), 'bc' => $bc];
        $this->page_construct('sales_order_store/index', $meta, $this->data);
    }

    public function getSales($biller_id = null)
    {
        $this->bpas->checkPermissions('index', true, 'store_sales_order');
        if ((!$this->Owner && !$this->Admin) && !$biller_id) {
            $biller_id = $this->session->userdata('biller_id');
        }
        $detail_link        = anchor('admin/sales_order_store/view/$1', '<i class="fa fa-file-text-o"></i>' . lang('sale_details'));
        $email_link         = anchor('admin/sales_order_store/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link          = anchor('admin/sales_order_store/edit/$1', '<i class="fa fa-edit"></i>' . lang('edit_sale'), 'class="sledit"');
        $pdf_link           = anchor('admin/sales_order_store/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $add_sale           = anchor('admin/sales_store/add/$1', '<i class="fa fa-money"></i>' . lang('add_sale'));
        $authorization      = anchor('admin/sales_order/getAuthorization/$1', '<i class="fa fa-check"></i>' . lang('approved'), '');
        $unapproved         = anchor('admin/sales_order/getunapproved/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('unapproved'), '');
        $rejected           = anchor('admin/sales_order/getrejected/$1', '<i class="fa fa-times"></i> ' . lang('rejected'), '');

        $delete_link       = "<a href='#' class='po' title='<b>" . lang('delete_sale') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales_order_store/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $detail_link . '</li>';
                $action .= 
                (($this->Owner || $this->Admin) ? '<li class="edit">'.$edit_link.'</li>' : ($this->GP['store_sales_order-edit'] ? '<li class="add">'.$edit_link.'</li>' : '')).
                (($this->Owner || $this->Admin) ? '<li class="delete">'.$delete_link.'</li>' : ($this->GP['store_sales_order-delete'] ? '<li class="add">'.$delete_link.'</li>' : '')).
                (($this->Owner || $this->Admin) ? '<li class="add">'.$add_sale.'</li>' : ($this->GP['store_sales-add'] ? '<li class="add">'.$add_sale.'</li>' : '')).
                '
            </ul>
        </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';
        
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('sales_order')}.id as id, DATE_FORMAT({$this->db->dbprefix('sales_order')}.date, '%Y-%m-%d %T') as date,project_name,
                reference_no, biller, {$this->db->dbprefix('sales_order')}.customer, sale_status, grand_total, paid, (grand_total-paid) as balance, order_status, {$this->db->dbprefix('sales_order')}.attachment, return_id")
            ->join('projects', 'sales_order.project_id = projects.project_id', 'left')
            ->where('sales_order.store_sale', 1)
            ->from('sales_order');
        if ($biller_id) {
            $this->datatables->where('sales_order.biller_id', $biller_id);
        } elseif (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("FIND_IN_SET(bpas_sales_order.biller_id, '".$this->session->userdata('biller_id')."')");
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        
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

    public function add($quote_id = null)
    {
        $this->bpas->checkPermissions('add', true, 'store_sales_order');
        $sale_id = $this->input->get('sale_id') ? $this->input->get('sale_id') : null;
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');
        $this->form_validation->set_rules('from_warehouse', lang('from_warehouse'), 'required');
        $this->form_validation->set_rules('to_warehouse', lang('to_warehouse'), 'required');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('sr');
            if ($this->Owner || $this->Admin) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $project_id =$this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $from_warehouse_id= $this->input->post('from_warehouse');
            $to_warehouse_id  = $this->input->post('to_warehouse');
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
                        'comment'           => $item_detail,
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
            $data           = ['date' => $date,
                'project_id'          => $project_id,
                'reference_no'        => $reference,
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
                'order_status'        => 'pending',
                'payment_status'      => $payment_status,
                'payment_term'        => $payment_term,
                'due_date'            => $due_date,
                'paid'                => 0,
                'saleman_by'          => $this->input->post('saleman_by'),
                'created_by'          => $this->session->userdata('user_id'),
                'hash'                => hash('sha256', microtime() . mt_rand()),
                'store_sale'          => 1,
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

        if ($this->form_validation->run() == true && $this->sales_order_store_model->addSale($data, $products, $payment)) {
            $this->session->set_userdata('remove_slls', 1);
            if ($quote_id) {
                $this->db->update('quotes', ['status' => 'completed'], ['id' => $quote_id]);
            }
            $this->session->set_flashdata('message', lang('sale_added'));
            admin_redirect('sales_order_store');
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
                    $row->serial_no       = $row->serial_no;
                    $row->option          = $item->option_id;
                    $row->details         = $item->comment;
                    $options              = $this->sales_order_model->getProductOptions($row->id, $item->warehouse_id);
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
                        $combo_items = $this->sales_order_model->getProductComboItems($row->id, $item->warehouse_id);
                    }
                    $units    = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                    $ri       = $this->Settings->item_addition ? $row->id : $c;
                    $set_price = $this->site->getUnitByProId($row->id);
                    $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                        'row'        => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'set_price'=>$set_price, 'units' => $units, 'options' => $options, ];
                    $c++;
                }
                $this->data['quote_items'] = json_encode($pr);
            }

            $Settings                  = $this->site->getSettings();
            $this->data['count']       = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['projects']    = $this->site->getAllProject();
            $this->data['error']       = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['quote_id']    = $quote_id ? $quote_id : $sale_id;
            $this->data['billers']     = $this->site->getAllCompanies('biller');
            $this->data['warehouses']  = $this->site->getAllWarehouses();
            $this->data['tax_rates']   = $this->site->getAllTaxRates();
            $this->data['units']       = $this->site->getAllBaseUnits();
            $this->data['slnumber']    = $this->site->getReference('spr');
            $this->data['salemans']    = $this->site->getAllSalemans($Settings->group_saleman_id);
            $bc                        = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales_order_store'), 'page' => lang('sales_order_store')], ['link' => '#', 'page' => lang('add_sale_order')]];
            $meta                      = ['page_title' => lang('add_sale_order'), 'bc' => $bc];
            $this->page_construct('sales_order_store/add', $meta, $this->data);
        }
    }

    public function edit($id = null)
    {
        $this->bpas->checkPermissions('edit', true, 'store_sales_order');

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
        $this->form_validation->set_rules('from_warehouse', lang('from_warehouse'), 'required');
        $this->form_validation->set_rules('to_warehouse', lang('to_warehouse'), 'required');
        $project_id =$this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $from_warehouse_id= $this->input->post('from_warehouse');
            $to_warehouse_id  = $this->input->post('to_warehouse');
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
                        'comment'           => $item_detail
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
            $data           = [
                'date'                => $date,
                'project_id'          => $project_id,
                'reference_no'        => $reference,
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
                'saleman_by'          => $this->input->post('saleman_by'),
                'updated_by'          => $this->session->userdata('user_id'),
                'updated_at'          => date('Y-m-d H:i:s'),
                'store_sale'          => 1,
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

        if ($this->form_validation->run() == true && $this->sales_order_store_model->updateSale($id, $data, $products)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang('sale_updated'));
            admin_redirect('sales_order_store');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $this->sales_order_model->getInvoiceByID($id);
            $this->bpas->deadlineDayEditing($this->data['inv']->date);
            $inv_items = $this->sales_order_model->getAllInvoiceItems($id);
            // krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                // $row = $this->site->getProductByID($item->product_id);
                $row = $this->sales_order_model->getWarehouseProduct($item->product_id, $item->warehouse_id);
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
                $row->serial_no       = $item->max_serial;
                $row->details         = $item->comment;
                $row->option          = $item->option_id;
                $options              = $this->sales_order_model->getProductOptions($row->id, $item->warehouse_id);
            
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
                    $combo_items = $this->sales_order_model->getProductComboItems($row->id, $item->warehouse_id);
                    $te          = $combo_items;
                    foreach ($combo_items as $combo_item) {
                        $combo_item->quantity = $combo_item->qty * $item->quantity;
                    }
                }
                $units    = !empty($row->base_unit) ? $this->site->getUnitsByBUID($row->base_unit) : null;
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri       = $this->Settings->item_addition ? $row->id : $c;
                $set_price = $this->site->getUnitByProId($row->id);
                $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row'        => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate,'set_price'=>$set_price, 'units' => $units, 'options' => $options, ];
                $c++;
            }

            $Settings = $this->site->getSettings();
            $this->data['count']      = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['projects']   = $this->site->getAllProject();
            $this->data['inv_items']  = json_encode($pr);
            $this->data['id']         = $id;
            $this->data['billers']    = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            $this->data['units']      = $this->site->getAllBaseUnits();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['salemans']   = $this->site->getAllSalemans($Settings->group_saleman_id);

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales_order_store'), 'page' => lang('sales_order_store')], ['link' => '#', 'page' => lang('edit_sale_order_store')]];
            $meta = ['page_title' => lang('edit_sale_order_store'), 'bc' => $bc];
            $this->page_construct('sales_order_store/edit', $meta, $this->data);
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
        $this->bpas->checkPermissions('delete', true, 'store_sales_order');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $inv = $this->sales_order_store_model->getInvoiceByID($id);
        if ($inv->sale_status == 'returned') {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('sale_x_action')]);
        }

        if ($this->sales_order_store_model->deleteSale($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('sale_deleted')]);
            }
            $this->session->set_flashdata('message', lang('sale_deleted'));
            admin_redirect('welcome');
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
        $this->bpas->checkPermissions('index', true, 'store_sales_order');
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

    public function view($id = null)
    {
        $this->bpas->checkPermissions('index', true, 'store_sales_order');

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
        $this->data['getSignbox']           = $this->approved_model->getSignbox($group_pr->id);
        $this->data['group_id']             = $group_pr->id;

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('view')]];
        $meta = ['page_title' => lang('view_sales_details'), 'bc' => $bc];
        $this->page_construct('sales_order_store/view', $meta, $this->data);
    }

    public function view_a5($id = null)
    {
        $this->bpas->checkPermissions('index', true, 'store_sales_order');

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
        $this->bpas->checkPermissions('index', true, 'store_sales_order');

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

    public function email($id = null)
    {
        $this->bpas->checkPermissions('index', true, 'store_sales_order');
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
        $this->bpas->checkPermissions('index', true, 'store_sales_order');
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