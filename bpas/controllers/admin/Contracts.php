<?php defined('BASEPATH') or exit('No direct script access allowed');

class Contracts extends MY_Controller
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
            redirect($_SERVER["HTTP_REFERER"]);
        }
    //    $this->lang->load('sales', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('sales_model');
        $this->load->admin_model('contracts_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;
    }

    public function index($warehouse_id = null)
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

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('contracts')));
        $meta = array('page_title' => lang('contracts'), 'bc' => $bc);
        $this->page_construct('contracts/index', $meta, $this->data);
    }

    public function getContracts($warehouse_id = null)
    {
        $this->bpas->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('admin/contracts/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('contract_details'));
        $duplicate_link = anchor('admin/contracts/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $edit_link = anchor('admin/contracts/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_contract'), 'class="sledit"');
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_contract") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . base_url('contracts/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_contract') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("{$this->db->dbprefix('contracts')}.id as id, DATE_FORMAT({$this->db->dbprefix('contracts')}.date, '%Y-%m-%d %T') as date, reference_no, product_name, biller, {$this->db->dbprefix('contracts')}.customer, delivery, total_amount,", false)
                ->from('contracts')
                ->where('warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select("{$this->db->dbprefix('contracts')}.id as id, DATE_FORMAT({$this->db->dbprefix('contracts')}.date, '%Y-%m-%d %T') as date, reference_no, product_name, biller, {$this->db->dbprefix('contracts')}.customer, delivery, total_amount", false)
                ->from('contracts');
        }
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    public function view($id = null)
    {
        $this->bpas->checkPermissions('index');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->contracts_model->getAllContractById($id) ? $this->contracts_model->getAllContractById($id) : NULL;
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->data['barcode'] = "<img src='" . base_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $billers = $this->site->getCompanyByID($inv->biller_id) ? $this->site->getCompanyByID($inv->biller_id) : NULL;
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments'] = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller'] = $billers;
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);

        if ($billers->id == 1011) {
            $view = 'view';
        }else{
            $view = 'view_draft';
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => base_url('contracts'), 'page' => lang('contracts')), array('link' => '#', 'page' => lang('view')));
        $meta = array('page_title' => lang('view_contract_details'), 'bc' => $bc);
        $this->page_construct('contracts/'.$view, $meta, $this->data);
    }

    public function add($quote_id = null)
    {
        $this->bpas->checkPermissions();

        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        
        if ($this->form_validation->run() == true) {

            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so');
            if ($this->Owner || $this->Admin) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $biller_id = $this->input->post('biller');
            $total = $this->input->post('total_amount');
            $payment_term = $this->input->post('payment_term');
            $item_name = $this->input->post('commodity');
            $price = $this->input->post('price');
            $item_quantity = $this->input->post('quantity');
            $warehouse_id = $this->input->post('warehouse');
            $payment_term = $this->input->post('payment_term');
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note = $this->bpas->clear_tags($this->input->post('note'));
            $account_no = $this->bpas->clear_tags($this->input->post('account_no'));

            $data = array('date' => $date,
                'product_name' => $item_name,
                'quantity' => $item_quantity,
                'warehouse_id' => $warehouse_id,
                'unit_price' => $price,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total_amount' => $total,
                'payment_term' => $payment_term,
                'created_by' => $this->session->userdata('user_id'),
                'delivery' => $this->input->post('delivery_term'),
                'account_no' => $account_no,
            );
        }
        if ($this->form_validation->run() == true && $this->contracts_model->addContract($data)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang("contract_added"));
            admin_redirect("contracts");
        } else {
            
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['customers'] = $this->site->getAllCompanies('customer');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['units'] = $this->site->getAllBaseUnits();
            $this->data['slnumber'] = ''; 
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => base_url('contracts'), 'page' => lang('contracts')), array('link' => '#', 'page' => lang('add_contract')));
            $meta = array('page_title' => lang('add_contract'), 'bc' => $bc);
            $this->page_construct('contracts/add', $meta, $this->data);
        }
    }

    /* ------------------------------------------------------------------------ */

    public function edit($id = null)
    {
        $this->bpas->checkPermissions();
        
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        
        if ($this->form_validation->run() == true) {

            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so');
            if ($this->Owner || $this->Admin) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $biller_id = $this->input->post('biller');
            $total = $this->input->post('total_amount');
            $payment_term = $this->input->post('payment_term');
            $item_name = $this->input->post('commodity');
            $price = $this->input->post('price');
            $item_quantity = $this->input->post('quantity');
            $warehouse_id = $this->input->post('warehouse');
            $payment_term = $this->input->post('payment_term');
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note = $this->bpas->clear_tags($this->input->post('note'));
            $account_no = $this->bpas->clear_tags($this->input->post('account_no'));

            $data = array('date' => $date,
                'product_name' => $item_name,
                'quantity' => $item_quantity,
                'warehouse_id' => $warehouse_id,
                'unit_price' => $price,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total_amount' => $total,
                'payment_term' => $payment_term,
                'created_by' => $this->session->userdata('user_id'),
                'delivery' => $this->input->post('delivery_term'),
                'account_no' => $account_no,
            );
        }
        if ($this->form_validation->run() == true && $this->contracts_model->editContract($id, $data)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang("contract_edited"));
            admin_redirect("contracts");
        } else {
            
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['customers'] = $this->site->getAllCompanies('customer');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['slnumber'] = ''; 
            $this->data['result_list'] = $this->contracts_model->getAllContractById($id);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => base_url('contracts'), 'page' => lang('contracts')), array('link' => '#', 'page' => lang('edit_contract')));
            $meta = array('page_title' => lang('edit_contract'), 'bc' => $bc);
            $this->page_construct('contracts/edit', $meta, $this->data);
        }
    }

    public function delete($id = null)
    {
        $this->bpas->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->contracts_model->delete($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(array('error' => 0, 'msg' => lang("contract_deleted")));
            }
            $this->session->set_flashdata('message', lang('contract_deleted'));
            admin_redirect('contracts');
        }
    }

    public function contract_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {

                    $this->bpas->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->contracts_model->delete($id);
                    }
                    $this->session->set_flashdata('message', lang("contract_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'combine') {

                    $html = $this->combine_pdf($_POST['val']);

                } elseif ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('contracts'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('grand_total'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('paid'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('payment_status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sale = $this->sales_model->getInvoiceByID($id);
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
                    $filename = 'contracts_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_contract_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function combine_pdf($sales_id)
    {
        $this->bpas->checkPermissions('pdf');

        foreach ($sales_id as $id) {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv = $this->contracts_model->getAllContractById($id);
            if (!$this->session->userdata('view_right')) {
                $this->bpas->view_rights($inv->created_by);
            }
            $this->data['barcode'] = "<img src='" . base_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
            $this->data['payments'] = $this->sales_model->getPaymentsForSale($id);
            $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
            $this->data['user'] = $this->site->getUser($inv->created_by);
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['inv'] = $inv;
            $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
            $this->data['return_sale'] = NULL;
            $this->data['return_rows'] = NULL;
            $html_data = $this->load->view($this->theme . 'contracts/pdf', $this->data, true);
            if (! $this->Settings->barcode_img) {
                $html_data = preg_replace("'\<\?xml(.*)\?\>'", '', $html_data);
            }

            $html[] = array(
                'content' => $html_data,
                'footer' => $this->data['biller']->invoice_footer,
            );
        }

        $name = lang("contracts_") . ".pdf";
        $this->bpas->generate_pdf($html, $name);

    }


}