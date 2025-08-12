<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Leasing extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->bpas->md('login');
        }	   
        $this->load->admin_model('loan_model');
        $this->load->admin_model('sales_model');
        $this->load->helper('text');
        $this->session->set_userdata('last_activity', now());
        $this->lang->admin_load('pos', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->popup_attributes = array('width' => '900', 'height' => '600', 'window_name' => 'bpas_popup', 'menubar' => 'yes', 'scrollbars' => 'yes', 'status' => 'no', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0');
		$this->data['logo'] = true;
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

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('leasing')]];
        $meta = ['page_title' => lang('leasing'), 'bc' => $bc];
        $this->page_construct('rental/index', $meta, $this->data);
    }
    public function getLeasing($biller_id = null)
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
        $down_payments_link   = anchor('admin/assets/deposits/$1', '<i class="fa fa-money"></i> ' . lang('view_deposit'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_Downpayment_link = anchor('admin/leasing/add_deposit/$1', '<i class="fa fa-money"></i> ' . lang('add_deposit'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $return_detail_link   = anchor('admin/sales/return_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('return_sale') . ' ' . lang('details'));
        $duplicate_link       = anchor('admin/sales/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        //$agreement =
        $email_link           = anchor('admin/sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');

        $edit_link = anchor('admin/leasing/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_leasing'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $pdf_link             = anchor('admin/sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $end_leasing          = anchor('admin/leasing/add_end_leasing/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('end_leasing'),'data-toggle="modal" data-backdrop="static" data-target="#myModal"');

        $delete_link          = "<a href='#' class='po' title='<b>" . lang('delete_sale') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('leasing/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_leasing') . '</a>';

        $action = '<div class="text-center">
            <div class="btn-group text-left">'
                .'<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
                . lang('actions') . ' <span class="caret"></span></button>
                <ul class="dropdown-menu pull-right" role="menu">
                    <li>' . $detail_link . '</li>
                    <li>' . $view_logo . '</li>
                    <li class="edit">' . $edit_link . '</li>
                    <li>' . $pdf_link . '</li>
                    <li>' . $end_leasing . '</li>
                    <li class="delete">' . $delete_link . '</li>
                </ul>
            </div>
        </div>';
        $this->load->library('datatables');

        $si = "( SELECT reservation_id, asset_id, 
                        GROUP_CONCAT(CONCAT({$this->db->dbprefix('suspended_note')}.code, '__', {$this->db->dbprefix('suspended_note')}.name) SEPARATOR '___') as item_nane, 
                            SUM({$this->db->dbprefix('reservation_items')}.quantity) as item_qty 
                    FROM {$this->db->dbprefix('reservation_items')} 
                    LEFT JOIN {$this->db->dbprefix('suspended_note')} ON {$this->db->dbprefix('suspended_note')}.note_id = {$this->db->dbprefix('reservation_items')}.asset_id WHERE 1 ";
        $si .= " GROUP BY {$this->db->dbprefix('reservation_items')}.reservation_id ) FSI";


        $this->datatables
        ->select("{$this->db->dbprefix('reservation')}.id as id, 
            DATE_FORMAT({$this->db->dbprefix('reservation')}.date, '%Y-%m-%d %T') as date,
            {$this->db->dbprefix('reservation')}.reference_no,
            {$this->db->dbprefix('companies')}.name as company,
            cus.name as customer, 
            {$this->db->dbprefix('reservation')}.start_date, 
            CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as saleman_by,
            FSI.item_nane as iname, 
            {$this->db->dbprefix('reservation')}.grand_total,
            {$this->db->dbprefix('reservation')}.note, 
            {$this->db->dbprefix('reservation')}.status")

        ->join($si, 'FSI.reservation_id=reservation.id', 'left')
        ->join('projects', 'reservation.project_id = projects.project_id', 'left')
        ->join('users', 'reservation.saleman_by = users.id', 'left')
        ->join('companies', 'companies.id = reservation.biller_id', 'left')
        ->join('companies cus', 'cus.id = reservation.customer_id', 'left')
        ->order_by('reservation.id', 'desc')
        ->from('reservation');

        if ($biller_id) {
            $this->datatables->where_in('reservation.biller_id', $biller_id);
        }
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            //$this->datatables->where("FIND_IN_SET({$this->db->dbprefix('reservation')}.created_by, '" . $this->session->userdata('user_id') . "')");
            $this->datatables->where("FIND_IN_SET({$this->db->dbprefix('reservation')}.saleman_by, '" . $this->session->userdata('user_id') . "')");
        }
        if ($this->Customer) {
            $this->datatables->where('projects.customer_id','bpas_projects.customer_id');
        }

        if ($this->input->get('attachment') == 'yes') {
            $this->datatables->where('payment_status !=', 'paid')->where('attachment !=', null);
        }

        if ($user_query) {
            $this->datatables->where('reservation.created_by', $user_query);
        }
        
        if ($payment_status) {
            $get_status = explode('_', $payment_status);
            $this->datatables->where_in('reservation.payment_status', $get_status);
        }
        if ($reference_no) {
            $this->datatables->where('reservation.reference_no', $reference_no);
        }

        if ($biller) {
            $this->datatables->where('reservation.biller_id', $biller);
        }
        if ($customer) {
            $this->datatables->where('reservation.customer_id', $customer);
        }
        if ($saleman_by) {
            $this->datatables->where('reservation.saleman_by', $saleman_by);
        }

        if ($a || $a == 'empty') {
            $alert_ids = explode('-', $a);
            $alert_id  = $a;
            if (count($alert_ids) > 1) {
                // $this->datatables->where('sales.payment_term <>', 0);
                // $this->datatables->where('DATE_SUB(bpas_sales.date, INTERVAL 1 DAY) <= CURDATE()');
                $this->datatables->where_in('reservation.id', $alert_ids);
            } else {
                // $this->datatables->where('sales.payment_term <>', 0);
                // $this->datatables->where('DATE_SUB(bpas_sales.date, INTERVAL 1 DAY) <= CURDATE()');
                $this->datatables->where('reservation.id', $alert_id);
            }
        }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('reservation') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
    public function add($asset_id = null)
    {
        $this->bpas->checkPermissions('deposits', true);

        if ($this->input->get('id')) {
            $asset_id = $this->input->get('id');
        }
        $asset = $this->leasing_model->getAssetByID($asset_id);
        if ($asset->status) {
            $this->session->set_flashdata('error', lang('asset_already_rent'));
            $this->bpas->md();
        }

        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang('date'), 'required');
        }
        $this->form_validation->set_rules('customer', lang('customer'), 'required');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $paid_by        = $this->site->getCashAccountByCode($this->input->post('paid_by'));
            $paid_by_account = isset($paid_by->account_code) ? $paid_by->account_code : $this->accounting_setting->default_cash ;
           
            $i = 1;
            $subtotal =0;
            for ($r = 0; $r < $i; $r++) {
                $items = [
                    'asset_id'      => $asset->note_id,
                    'name'          => $asset->name,
                    'commission'    => $this->input->post('commission'),
                    'amount'        => $this->input->post('amount'),
         
                ];
                $products [] = $items;
                $subtotal += $this->input->post('amount');
            }
            $grand_total = $this->bpas->formatDecimal($subtotal);
            $data = [
                'date'          => $date,
                'reference_no'  => $this->site->getReference('ren'),
                'biller_id'     => $this->input->post('biller'),
                'project_id'    => $this->input->post('project'),
                'customer_id'   => $this->input->post('customer'),
                'saleman_by'    => $this->input->post('saleman_by'),
                'start_date'    => $this->bpas->fsd($this->input->post('start_date')),
                'note'          => $this->input->post('note'),
                'grand_total'   => $grand_total,
                'status'        => 1,
                'created_by'    => $this->session->userdata('user_id'),
            ];
            /*
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
                $data['attachment'] = $photo;
            }
            */
            //=====end accountig=====//

        } elseif ($this->input->post('add_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('leasing');
        }

        if ($this->form_validation->run() == true && $this->leasing_model->add_leasing($data,$products)) {
            $this->session->set_flashdata('message', lang('leasing_has_been_added'));
            admin_redirect('leasing');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['currency_dollar'] = $this->site->getCurrencyByCode('USD');
            $this->data['asset']      = $asset;
            $this->data['salemans']     = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['projects']     = $this->site->getAllProjects();
            $this->data['customers']    = $this->site->getCustomers();
            $this->data['billers']      = $this->site->getAllCompanies('biller');
            $this->load->view($this->theme . 'rental/add_lease', $this->data);
        }
    }
    public function add_multi_lease()
    {
        $this->bpas->checkPermissions('deposits', true);

        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang('date'), 'required');
        }
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $paid_by        = $this->site->getCashAccountByCode($this->input->post('paid_by'));
            $paid_by_account = isset($paid_by->account_code) ? $paid_by->account_code : $this->accounting_setting->default_cash ;
            
            $i  = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;

            $subtotal =0;
            for ($r = 0; $r < $i; $r++) {

                $item_id            = $_POST['product_id'][$r];
                $commission         = $_POST['commission'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_price         = $_POST['price'][$r];

                $items = [
                    'asset_id'      => $item_id,
                    'name'          => $item_name,
                    'commission'    => $commission,
                    'amount'        => $item_price,
                ];
                $products [] = $items;
                $subtotal += $item_price;
            }

            $grand_total = $this->bpas->formatDecimal($subtotal);
            $data = [
                'date'          => $date,
                'reference_no'  => $this->site->getReference('ren'),
                'biller_id'     => $this->input->post('biller'),
                'project_id'    => $this->input->post('project'),
                'customer_id'   => $this->input->post('customer'),
                'saleman_by'    => $this->input->post('saleman_by'),
                'start_date'    => $this->bpas->fsd($this->input->post('start_date')),
                'note'          => $this->input->post('note'),
                'grand_total'   => $grand_total,
                'status'        => 1,
                'created_by'    => $this->session->userdata('user_id'),
            ];

        } elseif ($this->input->post('add_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('leasing');
        }

        if ($this->form_validation->run() == true && $this->leasing_model->add_leasing($data,$products)) {
            $this->session->set_flashdata('message', lang('lease_added'));
            admin_redirect('leasing');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']         = $this->site->modal_js();
            $this->data['currency_dollar']  = $this->site->getCurrencyByCode('USD');
            $this->data['salemans']         = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['projects']         = $this->site->getAllProjects();
            $this->data['customers']        = $this->site->getCustomers();
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->load->view($this->theme . 'rental/add_multi_lease', $this->data);
        }
    }
    public function edit($lease_id)
    {
        $this->bpas->checkPermissions('deposits', true);

        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang('date'), 'required');
        }
        $lease          = $this->leasing_model->getReservationByID($lease_id);
        if (!$lease) {
            $this->session->set_flashdata('error', lang('lease_donot_exist'));
            $this->bpas->md();
        }

        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            
            $i  = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            $subtotal =0;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $commission         = $_POST['commission'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_price         = $_POST['price'][$r];

                $items = [
                    'asset_id'      => $item_id,
                    'name'          => $item_name,
                    'commission'    => $commission,
                    'amount'        => $item_price,
                ];
                $products[] = $items;
                $subtotal += $item_price;
            }
            $grand_total = $this->bpas->formatDecimal($subtotal);
            $data = [
                'date'          => $date,
                'biller_id'     => $this->input->post('biller'),
                'project_id'    => $this->input->post('project'),
                'customer_id'   => $this->input->post('customer'),
                'saleman_by'    => $this->input->post('saleman_by'),
                'start_date'    => $this->bpas->fsd($this->input->post('start_date')),
                'note'          => $this->input->post('note'),
                'grand_total'   => $grand_total,
                'status'        => 1,
                'created_by'    => $this->session->userdata('user_id'),
            ];

        } elseif ($this->input->post('update_leasing')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('leasing');
        }

        if ($this->form_validation->run() == true && $this->leasing_model->update_leasing($lease_id,$data,$products)) {
            $this->session->set_flashdata('message', lang('deposit_added'));
            admin_redirect('leasing');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']         = $this->site->modal_js();
            $this->data['currency_dollar']  = $this->site->getCurrencyByCode('USD');
            $this->data['salemans']         = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['projects']         = $this->site->getAllProjects();
            $this->data['customers']        = $this->site->getCustomers();
            $this->data['lease']            = $lease;
            $this->data['lease_items']      = $this->leasing_model->getAllReservationItems($lease_id);
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->load->view($this->theme . 'rental/edit_multi_lease', $this->data);
        }
    }
    public function add_end_leasing($id = null)
    {
        $this->bpas->checkPermissions('end_leasing', true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $company = $this->leasing_model->getReservationByID($id);
        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang('date'), 'required');
        }
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data = [
                'date'          => $date,
                'reserved_id'   => $company->id,
                'note'          => $this->input->post('note'),
                'created_by'    => $this->session->userdata('user_id'),
            ];
        } elseif ($this->input->post('add_blacklist')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->leasing_model->endLeasing($data)) {
            $this->session->set_flashdata('message', lang('leasing_has_been_end'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['currency_dollar'] = $this->site->getCurrencyByCode('USD');
            $this->data['company']      = $company;
            $this->data['projects']     = $this->site->getAllProjects();
            $this->data['billers']      = $this->site->getAllCompanies('biller');
            $this->data['customers']    = $this->site->getCustomers();
            $this->load->view($this->theme . 'rental/add_end_leasing', $this->data);
        }
    }
    public function delete($id = null)
    {
        $this->bpas->checkPermissions('delete_leasing', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->leasing_model->deleteLeasing($id)) {
            $this->session->set_flashdata('message', lang("leasing_deleted"));
            $this->bpas->send_json(['error' => 0, 'msg' => lang('leasing_deleted')]);
        }
    }
    public function modal_view_lease($id = null, $logo = null)
    {
        $this->bpas->checkPermissions('index', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->leasing_model->getReservationByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by, true);
        }
        $this->data['customer']      = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller']        = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by']    = $this->site->getUser($inv->created_by);
        $this->data['inv']           = $inv;
        $this->data['rows']          = $this->leasing_model->getAllReservationItems($id);
        $this->data['islogo']        = $logo;
        $this->data['print']         = $this->site->Assgin_Print('Sale',$inv->id);
        $this->data['sold_by']       = $this->site->getsaleman($inv->saleman_by);
        $this->load->view($this->theme . 'rental/modal_view_lease', $this->data);
    }
	public function pdf($id = null, $view = null, $save_bufffer = null){
        $this->bpas->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->loan_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments'] = $this->loan_model->getPaymentsForSale($id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['user'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['datas'] = $this->loan_model->getInvoicePayments_loan($id);
        $name = lang("sale") . "_" . str_replace('/', '_', $inv->reference_no) . ".pdf";
        $html = $this->load->view($this->theme . 'loans/pdf', $this->data, true);
        if (! $this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }

        if ($view) {
            $this->load->view($this->theme . 'loans/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->bpas->generate_pdf($html, $name, $save_bufffer, '');
        } else {
            $this->bpas->generate_pdf($html, $name, false, '');
        }
    }
	public function sale_actions()
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
                        $this->sales_model->deleteSale($id);
                    }
                    $this->session->set_flashdata('message', lang("sales_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'combine') {

                    $html = $this->combine_pdf($_POST['val']);

                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('sales'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('sale_refer'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('paid'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('loan_payment'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('monthly_payment'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('interest'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sale = $this->loan_model->getloanByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($sale->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sale->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sale->refer);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sale->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sale->paid);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $sale->loan_payment);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $sale->monthly_payment);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $sale->total_interest);
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, $sale->status);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'Loans_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_sale_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function loan_alert($id = null, $status = null){
        $this->bpas->checkPermissions('index');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if($this->input->get('status')=='alert'){
            $status = 'alert';
        }else if($this->input->get('status')=='exp_alert'){
            $status = 'exp_alert';
        }else if($this->input->get('status')=='late_exp'){
            $status = 'late_exp';
        }

        $rows = $this->loan_model->getAllLoanByUserId($status);

        $this->data['test'] = "Loan Alerts";
        //$this->data['inv'] = $this->sales_model->getInvoiceByID(22);
        $this->data['rows'] = $rows;

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('loans'), 'page' => lang('loans')), array('link' => '#', 'page' => lang('loan_alert')));
        $meta = array('page_title' => lang('loan_alert'), 'bc' => $bc);
        $this->page_construct('leasing/loan_alert', $meta, $this->data);
    }

     function assets($warehouse_id = NULL,$start_date = null, $end_date = null)
    {
        $this->bpas->checkPermissions('assets');
         if (!$start_date) {
            $start      = $this->db->escape(date('Y-m') . '-1');
            $start_date = date('Y-m') . '-1';
        } else {
            $start = $this->db->escape(urldecode($start_date));
        }
        if (!$end_date) {
            $end      = $this->db->escape(date('Y-m-d H:i'));
            $end_date = date('Y-m-d H:i');
        } else {
            $end = $this->db->escape(urldecode($end_date));
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : NULL;
        }
        $this->data['start']                  = urldecode($start_date);
        $this->data['end']                    = urldecode($end_date);
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('room'), 'page' => lang('room')), array('link' => '#', 'page' => lang('assets')));
        $meta = array('page_title' => lang('assets'), 'bc' => $bc);
        $this->page_construct('rental/assets', $meta, $this->data);
    }
    function getAssets($index = null, $warehouse_id = NULL)
    {
        $this->bpas->checkPermissions('assets', TRUE);
        $checkIn_link         = anchor('admin/room/checkin/0/0/$1', '<i class="fa fa-money"></i> ' . lang('checkin'));
        $edit_room            = anchor('admin/leasing/edit_asset/$1', '<i class="fa fa-edit"></i> ' . lang('edit_asset'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');

        $add_blacklist ="<a class='tip' title='" . lang('add_deposit') . "' href='" . admin_url('leasing/add_blacklist/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='false'><i class='fa fa-plus'></i> ".lang("add_blacklist")."</a>";

        $qrcode            = anchor('admin/table/qrcode/$1', '<i class="fa fa-qrcode"></i> ' . lang('qrcode'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_leasing ="<a class='tip' title='" . lang('add_leasing') . "' href='" . admin_url('leasing/add/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='false'><i class='fa fa-plus'></i> ".lang("add_leasing")."</a>";

        $view_asset_link = anchor('admin/leasing/view_asset/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_asset'));

        $delete_link          = "<a href='#' class='po' title='<b>" . lang('delete_asset') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('table/delete_room/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_asset') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">' . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu"> 
                <li>' . $view_asset_link . '</li>
                <li class="add_leasing">' . $add_leasing . '</li>
                <li class="blacklist">'.$add_blacklist.'</li>
                <li>' . $edit_room . '</li>
                <li class="delete">' . $delete_link . '</li>
            </ul>
        </div></div>';
        $this->load->library('datatables');
        $this->datatables
            ->select("note_id as id,
                    {$this->db->dbprefix('suspended_note')}.note_id as qr_code,
                    {$this->db->dbprefix('suspended_note')}.code,
                    {$this->db->dbprefix('suspended_note')}.name, 
                    {$this->db->dbprefix('suspended_note')}.price,
                    custom_field.description,
                    CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as saleman_by,
                    status")
            ->join('custom_field', 'custom_field.id = suspended_note.suspend_type', 'left')
            ->join('users', 'users.id=suspended_note.saleman_by', 'left') 
            ->from('suspended_note');
        
        if ($warehouse_id) {
            $this->datatables->where('suspended_note.warehouse_id', $warehouse_id);
        }
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('suspended_note.saleman_by', $this->session->userdata('user_id'));
        }

        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
    function add_asset($page = NULL)
    {
        $this->bpas->checkPermissions('add_asset', true);
        $this->form_validation->set_rules('code', lang("code"), 'trim|required');
        $this->form_validation->set_rules('name', lang("name"), 'trim|required');
        $this->form_validation->set_rules('type', lang("type"), 'trim');
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'trim');
        $this->form_validation->set_rules('description', lang("description"), 'trim');
        $this->form_validation->set_rules('amount', lang("amount"), 'trim|numeric');
        // $this->form_validation->set_rules('price', lang("price"), 'trim|numeric');
        if ($this->form_validation->run() == true) {
            $data = array(
                'code'         => $this->input->post('code'),
                'name'         => $this->input->post('name'),
                'type'         => $this->input->post('type'),
                'suspend_type' => $this->input->post('suspend_type'),
                'warehouse_id' => $this->input->post('warehouse'),
                'amount'       => $this->input->post('amount'),
                'description'  => $this->input->post('description'),
                'saleman_by'   => $this->input->post('saleman_by'),
                'create_date'  => date('Y-m-d H:i:s')
            );
            $data_options = null;
            $options = $this->site->getcustomfield('Room Options');
            if ($this->Settings->module_hotel_apartment && !empty($options)) {
                for ($i=0; $i < sizeof($_POST['custom_field']); $i++) { 
                    $data_options[] = array(
                        'custom_field_id' => $_POST['custom_field'][$i],
                        'price'           => (!empty($_POST['price'][$i]) && $_POST['price'][$i] != '' ? $_POST['price'][$i] : 0),
                    );
                }
                $data['price'] = $data_options[0]['price'];
            } else {
                $data['price'] = $this->input->post('price');
            }
            // $this->bpas->print_arrays($data, $data_options);
        } elseif ($this->input->post('add_room')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("leasing/assets");
        }
        if ($this->form_validation->run() == true && $this->table_model->addRoom($data, $data_options)) { 
            $this->session->set_flashdata('message', lang("data_add"));
            admin_redirect("leasing/assets");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']   = $this->site->modal_js();
            $this->data['page_title'] = lang("add_asset");
            $this->data['floors']     = $this->site->getAllFloors();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['salemans']     = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['options']    = $this->site->getcustomfield('Room Options');
            $this->load->view($this->theme . 'rental/add_asset', $this->data);
        }
    }
    function edit_asset($id = NULL)
    {
        $this->bpas->checkPermissions('edit_asset', true);
        $this->form_validation->set_rules('code', lang("code"), 'trim|required');
        $this->form_validation->set_rules('name', lang("name"), 'trim|required');
        $this->form_validation->set_rules('type', lang("type"), 'trim');
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'trim');
        $this->form_validation->set_rules('description', lang("description"), 'trim');
        $this->form_validation->set_rules('amount', lang("amount"), 'trim|numeric');
        // $this->form_validation->set_rules('price', lang("price"), 'trim|numeric');
        if ($this->form_validation->run() == true) {


            $data = array(
                'code'         => $this->input->post('code'),
                'name'         => $this->input->post('name'),
                'type'         => $this->input->post('type'),
                'suspend_type' => $this->input->post('suspend_type'),
                'warehouse_id' => $this->input->post('warehouse'),
                'floor'        => $this->input->post('floor'),
                'bed'          => $this->input->post('bed'),
                'amount'       => $this->input->post('amount'),
                'description'  => $this->input->post('description'),
                'saleman_by'   => $this->input->post('saleman_by'),
                'create_date'  => date('Y-m-d H:i:s')
            );


            $data_options = null;
            $options = $this->site->getcustomfield('Room Options');
            if ($this->Settings->module_hotel_apartment && !empty($options)) {
                for ($i=0; $i < sizeof($_POST['custom_field']); $i++) { 
                    $data_options[] = array(
                        'suspended_note_id' => $id,
                        'custom_field_id'   => $_POST['custom_field'][$i],
                        'price'             => (!empty($_POST['price'][$i]) && $_POST['price'][$i] != '' ? $_POST['price'][$i] : 0),
                    );
                }
                $data['price'] = $data_options[0]['price'];
            } else {
                $data['price'] = $this->input->post('price');
            }
        } elseif ($this->input->post('edit_room')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("leasing/assets");
        }
        if ($this->form_validation->run() == true && $this->table_model->updateRoom($id, $data, $data_options)) {
            $this->session->set_flashdata('message', lang("data_update"));
            admin_redirect("leasing/assets");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['rooms']        = $this->table_model->getroomByID($id);
            $this->data['room_options'] = $this->table_model->getRoomOptionsByRoomID($id);
            $this->data['id']           = $id;           
            $this->data['modal_js']     = $this->site->modal_js();
            $this->data['floors']       = $this->site->getAllFloors();
            $this->data['page_title']   = lang("edit_asset");
            $this->data['salemans']     = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['options']      = $this->site->getcustomfield('Room Options');
            $this->load->view($this->theme . 'rental/edit_asset', $this->data);
        }
    }
    public function view_asset($id = null)
    {
        $this->bpas->checkPermissions('index');

        $pr_details = $this->leasing_model->getAssetByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $pr_details->code . '/code128/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        $this->data['product']          = $pr_details;
        //$this->data['unit']             = $this->site->getUnitByID($pr_details->unit);
        //$this->data['brand']            = $this->site->getBrandByID($pr_details->brand);

        //$this->data['category']         = $this->site->getCategoryByID($pr_details->category_id);
        //$this->data['subcategory']      = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : null;
        //$this->data['tax_rate']         = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : null;
        //$this->data['variants']         = $this->products_model->getProductOption

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('leasing'), 'page' => lang('leasing')], ['link' => '#', 'page' => $pr_details->name]];
        $meta = ['page_title' => $pr_details->name, 'bc' => $bc];
        $this->page_construct('rental/view_asset', $meta, $this->data);
    }
    public function blacklists(){   
        $this->bpas->checkPermissions('blacklists',true); 
        $this->data['modal_js'] = $this->site->modal_js();
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => 'schools', 'page' => lang('customers')), array('link' => '#', 'page' => lang('blacklist')));
        $meta = array('page_title' => lang('blacklist'), 'bc' => $bc);
        $this->page_construct('rental/black_lists', $meta, $this->data);
    }
    public function getBlackLists()
    {   
        $this->bpas->checkPermissions('blacklists');
        $this->load->library('datatables');

        $delete_link = "<a href='#' class='po' title='" . lang("delete_blacklist") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" .admin_url('leasing/delete_blacklist/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_blacklist') . "</a>";
        
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li class="hide"><a href="'.admin_url('leasing/edit_blacklist/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_blacklist').'</a></li>
                                <li>'.$delete_link.'</li>
                            </ul>
                        </div>';
        $this->datatables
            ->select('
                    blacklist.id as id, 
                    blacklist.date,
                    '.$this->db->dbprefix('suspended_note').'.code as code,
                    '.$this->db->dbprefix("suspended_note").'.name as name,
                    blacklist.note,
                    CONCAT('.$this->db->dbprefix('users').'.first_name," ",
                    '.$this->db->dbprefix('users').'.last_name) as created_by,
            ')
            ->from("blacklist")
            ->join("suspended_note","suspended_note.note_id = blacklist.suspended_id","left")
            ->join('users', 'users.id=blacklist.created_by', 'left')
            ->where('blacklist.suspended_id >', 0)
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
    }
    public function add_blacklist($id = null)
    {
        $this->bpas->checkPermissions('blacklists', true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $company = $this->leasing_model->getAssetByID($id);
        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang('date'), 'required');
        }
        $this->form_validation->set_rules('suspended', lang('suspended'), 'required');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data = [
                'date'          => $date,
                'suspended_id'  => $this->input->post('suspended'),
                'note'          => $this->input->post('note'),
                'created_by'    => $this->session->userdata('user_id'),
            ];
        } elseif ($this->input->post('add_blacklist')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->leasing_model->addBlackList($data)) {
            $this->session->set_flashdata('message', lang('blacklist_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['currency_dollar'] = $this->site->getCurrencyByCode('USD');
            $this->data['company']      = $company;
            $this->data['projects']     = $this->site->getAllProjects();
            $this->data['billers']      = $this->site->getAllCompanies('biller');
            $this->data['customers']    = $this->site->getCustomers();
            $this->load->view($this->theme . 'rental/add_blacklist', $this->data);
        }
    }
    public function edit_blacklist($id = null)
    {
        $this->bpas->checkPermissions('blacklists', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $blacklist  = $this->leasing_model->getBlacklistByID($id);
        $company    = $this->leasing_model->getAssetByID($blacklist->suspended_id);
        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang('date'), 'required');
        }
        $this->form_validation->set_rules('suspended', lang('suspended'), 'required');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $blacklist->date;
            }
            $data = [
                'date'          => $date,
                'suspended_id'  => $this->input->post('suspended'),
                'note'          => $this->input->post('note'),
                'status'        => $this->input->post('status'),
                'created_by'    => $this->session->userdata('user_id'),
            ];
            $cdata = [
                'status'    => $this->input->post('status'),
            ];
        } elseif ($this->input->post('edit_blacklist')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->leasing_model->UpdateBlacklist($id,$data,$cdata)) {
            $this->session->set_flashdata('message', lang('blacklist_updated'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['projects'] = $this->site->getAllProjects();
            $this->data['company']  = $company;
            $this->data['blacklist']  = $blacklist;
            $this->data['customers'] = $this->site->getCustomers();
            $this->load->view($this->theme . 'rental/edit_blacklist', $this->data);
        }
    }
    public function delete_blacklist($id)
    {
        $this->bpas->checkPermissions(null, true);
        if ($this->leasing_model->deleteBlacklist($id)) {
            $this->session->set_flashdata('message', lang('blacklist_deleted'));
            admin_redirect('leasing/blacklists');
        }
    }

     public function list_generate_invoice($biller_id = null)
    {
        $this->bpas->checkPermissions('generate_invoice_index', true, 'leasing');
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

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('leasing')]];
        $meta = ['page_title' => lang('leasing'), 'bc' => $bc];
        $this->page_construct('rental/list_generate_invoice', $meta, $this->data);
    }
    public function getGenerateInvoice($biller_id = null)
    {
        $this->bpas->checkPermissions('generate_invoice_index', true, 'leasing');
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
        $view_logo        = anchor('admin/sales/modal_view/$1/logo', '<i class="fa fa-money"></i> ' . lang('print_with_logo'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $detail_link          = anchor('admin/sales/modal_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $end_leasing          = anchor('admin/leasing/add_end_leasing/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('end_leasing'),'data-toggle="modal" data-backdrop="static" data-target="#myModal"');

        $delete_link          = "<a href='#' class='po' title='<b>" . lang('delete_sale') . "</b>' data-content=\"<p>". lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('leasing/delete_generate/$1') . "'>". lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> ". lang('delete_generate') . '</a>';

        $action = '<div class="text-center">
            <div class="btn-group text-left">'
                .'<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
                . lang('actions') . ' <span class="caret"></span></button>
                <ul class="dropdown-menu pull-right" role="menu">
                    <li>' . $detail_link . '</li>
                    <li class="delete">' . $delete_link . '</li>
                </ul>
            </div>
        </div>';
        $this->load->library('datatables');

        $this->datatables
        ->select("
            {$this->db->dbprefix('reservation_generate')}.id as id, 
            DATE_FORMAT({$this->db->dbprefix('reservation_generate')}.date, '%Y-%m-%d %T') as date,
            {$this->db->dbprefix('companies')}.name as company,
            CONCAT(DAY({$this->db->dbprefix('reservation_generate')}.start_date),'/',{$this->db->dbprefix('reservation_generate')}.month, '/', {$this->db->dbprefix('reservation_generate')}.year) as start_date,
            cus.name as customer, 
            CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as saleman_by,
            {$this->db->dbprefix('reservation')}.reference_no,
            {$this->db->dbprefix('reservation')}.grand_total,
            {$this->db->dbprefix('reservation')}.note
        ")

        ->join('users', 'reservation_generate.saleman_by = users.id', 'left')
        ->join('reservation', 'reservation.id = reservation_generate.reservation_id', 'left')

        ->join('companies', 'companies.id = reservation.biller_id', 'left')
        ->join('companies cus', 'cus.id = reservation_generate.customer_id', 'left')
        ->order_by('reservation_generate.id', 'desc')
        ->from('reservation_generate');

        if ($biller_id) {
            $this->datatables->where_in('reservation_generate.biller_id', $biller_id);
        }
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("FIND_IN_SET({$this->db->dbprefix('reservation')}.created_by, '" . $this->session->userdata('user_id') . "')");
        }
        if ($this->input->get('attachment') == 'yes') {
            $this->datatables->where('payment_status !=', 'paid')->where('attachment !=', null);
        }

        if ($user_query) {
            $this->datatables->where('reservation_generate.created_by', $user_query);
        }
        if ($reference_no) {
            $this->datatables->where('reservation.reference_no', $reference_no);
        }

        if ($biller) {
            $this->datatables->where('reservation.biller_id', $biller);
        }
        if ($customer) {
            $this->datatables->where('reservation_generate.customer_id', $customer);
        }
        if ($saleman_by) {
            $this->datatables->where('reservation_generate.saleman_by', $saleman_by);
        }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('reservation') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
    public function delete_generate($id = null)
    {
        $this->bpas->checkPermissions('delete_generate', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->leasing_model->delete_generate_invoice($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('generate_invoice_deleted')]);
        }
    }
    public function generate_invoice()
    {
        $this->bpas->checkPermissions('generate_invoice_add', true, 'leasing');
        $getexchange_khm = $this->bpas->getExchange_rate('KHR');
        $exchange_khm    = $getexchange_khm->rate;

        $this->form_validation->set_rules('from_date', lang('from_date'), 'required');
        $this->form_validation->set_rules('to_date', lang('to_date'), 'required');
        if ($this->form_validation->run() == true) {
            $date = date('Y-m-d H:i:s');
            $y_month = explode("/",$this->input->post('month'));
            $month = $y_month[0];
            $year = $y_month[1];


            $customer   = $this->input->post('customer');
            $saleman_by = $this->input->post('saleman_by');
            $from_date  = $this->input->post('from_date');//$this->bpas->fsd($this->input->post('from_date'));
            $to_date    = $this->input->post('to_date');//$this->bpas->fsd($this->input->post('to_date'));

            $get_datas = $this->leasing_model->getGenerateReservation($customer,$saleman_by,$month,$year,$from_date,$to_date);

            if (!$get_datas) {
                $this->session->set_flashdata('error', lang('do_not_have_invoice'));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            $data_sales = [];
            foreach($get_datas as $get_data){
                $invoice_date = $get_data->start_date;
                

                // checking generate invoice
                $checking = $this->leasing_model->checkingGenerate($month,$year,$get_data->id);
                if(!$checking){
                    $inv_day = date('d',strtotime($invoice_date));
                    $issue_inv_date =  $year.'-'.$month.'-'.$inv_day.' '.date('H:i');

                    $products = array();
                    $commission_product = 0;
                    $text_items = "";
                    $total            = 0;
                    $product_tax      = 0;
                    $product_discount = 0;

                    $biller_id        = $get_data->biller_id;
                    $customer_id      = $get_data->customer_id;
                    $customer_details = $this->site->getCompanyByID($get_data->customer_id);
                    $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company.'/'.$customer_details->name : $customer_details->name;
                    $biller_details   = $this->site->getCompanyByID($biller_id);
                    $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company.'/'.$biller_details->name : $biller_details->name;
                    $sale_status      = 'completed';
                    $payment_status   = 'pending';
                    $saleman_by         = $get_data->saleman_by;
                    $get_items = $this->leasing_model->getItemByReservationID($get_data->id);
                    foreach ($get_items as $get_item) {
                        $asset = $this->leasing_model->getAssetByID($get_item->asset_id);
                        $item_id            = $asset->note_id;
                        $item_code          = $asset->code;
                        $item_name          = $asset->name;
                        $item_type          = $asset->type;

                        $real_unit_price    = $this->bpas->formatDecimal($asset->price);
                        $unit_price         = $this->bpas->formatDecimal($asset->price);
                        $item_net_price     = $unit_price;

                        $item_unit_quantity = 1;
                        $item_tax_rate      = null;
                        $pr_item_tax = $item_tax = 0;
                        
                        $item_discount      = null;
                        $pr_discount        = 0;//$this->site->calculateDiscount($item_discount, $unit_price);

                        $item_unit          = 'unit';
                        $item_quantity      = 1;
                        $pr_item_discount   = $this->bpas->formatDecimal($pr_discount);
                        $product_discount   = $pr_item_discount;
                        $subtotal     = (($item_net_price * $item_unit_quantity) + $pr_item_tax);

                        $commission_type    = $get_item->commission;
                        $commission         = str_replace('%','',$commission_type);
      
                        $product = [
                            'product_id'        => $item_id,
                            'product_code'      => $item_code,
                            'product_name'      => $item_name,
                            'product_type'      => $item_type,
                            'net_unit_price'    => $item_net_price,
                            'unit_price'        => $this->bpas->formatDecimal($item_net_price),
                            'quantity'          => $item_quantity,
                            'product_unit_id'   => null,
                            'product_unit_code' => null,
                            'unit_quantity'     => $item_unit_quantity,
                            'discount'          => $item_discount,
                            'item_discount'     => $pr_item_discount,
                            'subtotal'          => $this->bpas->formatDecimal($subtotal),
                            'real_unit_price'   => $real_unit_price,
                            'commission_type'   => $commission_type,
                            'commission'        => $this->bpas->formatDecimal(($subtotal*$commission)/100),
                            
                        ];
                        $products[] = $product;
                        $total += $this->bpas->formatDecimal(($item_net_price * $item_unit_quantity));
                    }

                    $grand_total    = $this->bpas->formatDecimal($total);
                    $data       = [
                        'date'                => $issue_inv_date,
                        'project_id'          => $get_data->project_id,
                        'reference_no'        => $this->site->getReference('so'),
                        'po_number'           => $get_data->reference_no,
                        'customer_id'         => $customer_id,
                        'customer'            => $customer,
                        'biller_id'           => $biller_id,
                        'biller'              => $biller,
                        'note'                => $get_data->note,
                        'total'               => $total,
                        'grand_total'         => $grand_total,
                        'sale_status'         => $sale_status,
                        'payment_status'      => $payment_status,
                        'paid'                => 0, 
                        'created_by'          => $this->session->userdata('user_id'),
                        'hash'                => hash('sha256', microtime() . mt_rand()),
                        'saleman_by'          => $get_data->saleman_by,
                        'currency_rate_kh'    => $exchange_khm,
                        'module_type'         => 'rental',
                 
                    ];
                    
                    $generate       = [
                        'date'                => $date,
                        'month'               => $month,
                        'year'                => $year,
                        'reservation_id'      => $get_data->id,
                        'start_date'          => $get_data->start_date,
                        'customer_id'         => $customer_id,
                        'saleman_by'          => $get_data->saleman_by,
                        'created_by'          => $this->session->userdata('user_id'),
                    ];
                    //----summary data----------
                    $data_sales[] = array(
                        'generate'  => $generate,
                        'data'      => $data,
                        'items'     => $products,
                        'accTrans'  => []
                    );
                }
            }

            $datas = $data_sales;
            if(empty($data_sales)){
                $this->session->set_flashdata('error', lang('data_already_generate'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } elseif ($this->input->post('generate')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('leasing/list_generate_invoice');
        }
        if ($this->form_validation->run() == true && $this->leasing_model->GenerateSale($datas)) {
            $this->session->set_flashdata('message', lang('invoice_generate'));
            admin_redirect('leasing/list_generate_invoice');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']         = $this->site->modal_js();
            $this->data['currency_dollar']  = $this->site->getCurrencyByCode('USD');
            $this->data['salemans']         = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['projects']         = $this->site->getAllProjects();
            $this->data['customers']        = $this->site->getCustomers();
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->load->view($this->theme . 'rental/generate_invoice', $this->data);
        }
    }

    public function sales($biller_id = null)
    {
        $this->bpas->checkPermissions('index', true, 'sales');
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
        $this->page_construct('rental/sales', $meta, $this->data);
    }

    public function getSales($biller_id = null)
    {
        $this->bpas->checkPermissions('index', true, 'sales');
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
        $down_payments_link   = anchor('admin/leasing/deposits/$1', '<i class="fa fa-money"></i> ' . lang('view_deposit'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_Downpayment_link = anchor('admin/leasing/add_deposit/$1', '<i class="fa fa-money"></i> ' . lang('add_deposit'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $return_detail_link   = anchor('admin/sales/return_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('return_sale') . ' ' . lang('details'));
        $duplicate_link       = anchor('admin/sales/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link        = anchor('admin/sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_payment_link     = anchor('admin/sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');

        $add_credit_note_link = anchor('admin/sales/add_credit_note/$1', '<i class="fa fa-truck"></i> ' . lang('add_credit_note'));
        $email_link           = anchor('admin/sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link            = anchor('admin/leasing/edit_sale/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'));

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
                <li>' . $view_logo . '</li>
                <li>' . $payments_link . '</li>
                <li class="add_payment">' . $add_payment_link . '</li>
                <li class="edit">' . $edit_link . '</li>
                <li>' . $pdf_link . '</li>
                <li>' . $return_link . '</li>
                <li class="delete">' . $delete_link . '</li>
            </ul>
        </div></div>';

        $this->load->library('datatables');
        $this->datatables
        ->select("{$this->db->dbprefix('sales')}.id as id, 
            DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date,
            {$this->db->dbprefix('sales')}.reference_no,
            {$this->db->dbprefix('sales')}.biller, 
            {$this->db->dbprefix('sales')}.customer, 
    
            CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as saleman_by,
            {$this->db->dbprefix('sales')}.po_number as sr_ref, 
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

        ->join('
            (select sum(amount) as deposit,sale_id 
            from '.$this->db->dbprefix('payments').' 
            where sale_id > 0 AND transaction ="SaleDeposit" 
            GROUP BY sale_id) as payments','payments.sale_id = sales.id','left')

        ->order_by('sales.id', 'desc')
        ->from('sales');

        $this->datatables->where('sales.module_type','rental');
        if ($biller_id) {
            $this->datatables->where_in('sales.biller_id', $biller_id);
        }
        
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            //$this->datatables->where("FIND_IN_SET(bpas_sales.created_by, '" . $this->session->userdata('user_id') . "')");
            $this->datatables->where("FIND_IN_SET(bpas_sales.saleman_by, '" . $this->session->userdata('user_id') . "')");
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
    public function add_sale($sale_order_id = null, $quote_id = null,$room = null)
    {   
        $this->bpas->checkPermissions();
        $getexchange_khm = $this->bpas->getExchange_rate('KHR');
        $exchange_khm    = $getexchange_khm->rate;
        if ($sale_order_id) {
            $sale_o = $this->sales_order_model->getSaleOrder($sale_order_id); 
            
            if(($this->sales_order_model->getSaleOrder($sale_order_id)->order_status) == 'pending'){
                $this->session->set_flashdata('error', lang("sale_order_n_approved"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            if(($this->sales_order_model->getSaleOrder($sale_order_id)->order_status) == 'rejected'){
                $this->session->set_flashdata('error', lang("sale_order_has_been_rejected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            if(($this->sales_order_model->getSaleOrder($sale_order_id)->sale_status) == 'completed'){
                $this->session->set_flashdata('error', lang("sale_order_has_been_created"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }
        $sale_id = $sale_order_id ? $sale_order_id : null;

        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so');
            // $reference = $this->site->CheckedSaleReference($this->input->post('reference_no'), $this->site->getReference('so'));
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
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
            // $due_date            = (isset($payment_term_details[0]->id)? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);
            $due_date               = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping               = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details       = $this->site->getCompanyByID($customer_id);
            $customer               = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company.'/'.$customer_details->name : $customer_details->name;
            $biller_details         = $this->site->getCompanyByID($biller_id);
            $biller                 = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company.'/'.$biller_details->name : $biller_details->name;
            $note                   = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note             = $this->bpas->clear_tags($this->input->post('staff_note'));
            $quote_id               = $this->input->post('quote_id') ? $this->input->post('quote_id') : null;
            // $total_weight        = 0;
            $commission_product     = 0;
            $text_items             = "";
            $total                  = 0;
            $product_tax            = 0;
            $product_discount       = 0;
            $digital                = false;
            $gst_data               = [];
            $total_cgst             = $total_sgst       = $total_igst       = 0;
            $i                      = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
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
                $item_warranty      = isset($_POST['warranty'][$r]) ? $_POST['warranty'][$r] : '';
                $item_weight        = 0;
                $item_detail        = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : '';
                $checkin_date       = isset($_POST['checkin_date'][$r]) ? $this->bpas->fld(trim($_POST['checkin_date'][$r])) : '';
                $item_commission   = isset($_POST['commission'][$r]) ? $_POST['commission'][$r] : null;

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    // $unit_price = $real_unit_price;
                    $cost = 0;
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
                    $total_weight = number_format((float) ($item_weight * $item_unit_quantity), 4, '.', '');
                    $saleman = $this->site->getuser($this->input->post('saleman_by'));

                    $commission = $this->site->getCustomeFieldByID($item_commission);

                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'purchase_unit_cost'=> 0,
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
                        'check_in'          => $checkin_date,
                        'commission_type'   => $item_commission,
                        'commission'        => $this->bpas->formatDecimal(($subtotal*$commission->name)/100),
                    ];
                    $text_items .=  $r+1 . "/ " . $item_name . "(".$item_code.")" ." | ". $item_quantity." | ".$this->bpas->formatDecimal($real_unit_price) ." | ". $pr_item_discount ." | ". $this->bpas->formatDecimal($subtotal)."\n";
                    //========add accounting=========//
                    if($this->Settings->accounting == 1 && $item_type != 'manual' && ($sale_status=='completed')){
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
            $staff = $this->site->getUser($this->input->post('saleman_by'));
            if($staff->save_point){
                if (!empty($this->Settings->each_sale)) {
                    $saleman_award_points = floor(($grand_total / $this->Settings->each_sale) * $this->Settings->sa_point);
                }
            }
            //=======acounting=========//
            if($this->Settings->accounting == 1){
                $saleAcc = $this->site->getAccountSettingByBiller();
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
            $data       = [
                'date'                => $date,
                'project_id'          => $this->input->post('project'),
                'so_id'               => $this->input->post('sale_order_id') ? $this->input->post('sale_order_id') : null,
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
                'payment_status'      => $payment_status,
                'payment_term'        => $payment_term,
                'due_date'            => $due_date,
                'paid'                => 0, 
                'created_by'          => $this->session->userdata('user_id'),
                'hash'                => hash('sha256', microtime() . mt_rand()),
                'saleman_by'          => $this->input->post('saleman_by'),
                'module_type'         => 'rental',
                'currency_rate_kh'    => $exchange_khm,
                'date_in'             => $this->bpas->fld(trim($this->input->post('arrival'))),
                'date_out'            => $this->bpas->fld(trim($this->input->post('departure'))),
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
                if ($this->Settings->accounting == 1) {
                    if ($this->input->post('paid_by') == 'deposit') {
                        $payment['bank_account'] = $saleAcc->default_sale_deposit;
                        $paying_to = $saleAcc->default_sale_deposit;
                    } else {
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
        }
        if ($this->form_validation->run() == true && $this->leasing_model->addSale($data, $products, $payment, $si_return = array(), $accTrans, $accTranPayments, null, $commission_product)) {
            $this->session->set_userdata('remove_slls', 1);
            
            if ($quote_id) {
                $this->db->update('quotes', ['status' => 'completed'], ['id' => $quote_id]);
            }
            if ($sale_id) {
                $status = 'completed';
                $sale_order_id    = $this->input->post('sale_order_id');
                $sale_items       = $this->site->getSaleItemsBySaleOrderID($sale_order_id);
                $sale_order_items = $this->site->getSaleOrderItemsBySaleID($sale_id);

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

                $this->db->update('sales_order', array('sale_status' => $status), array('id' => $sale_id));
            }
    

            $this->session->set_flashdata('message', lang('sale_added'));
            admin_redirect('leasing/sales');
        } else {
            if ($quote_id || $sale_id || $room) {
                if ($quote_id) {
                    $this->data['quote'] = $this->quotes_model->getQuoteByID($quote_id);
                    $items               = $this->quotes_model->getAllQuoteItems($quote_id);
                    $this->data['inv']   = $this->data['quote'];
                    $sale_items = [];
                } elseif ($sale_id) {
                    $this->data['quote'] = $this->sales_order_model->getInvoiceByID($sale_id);
                    $items               = $this->sales_order_model->getAllInvoiceItems($sale_id);
                    $this->data['inv']   = $this->data['quote'];
                    $sale_items          = $this->site->getSaleItemsBySaleOrderID($sale_id);
                } elseif ($room) {
                    $this->data['quote'] = $this->sales_order_model->getInvoiceByID($sale_id);
                    $items               = $this->sales_order_model->getsuspendNoteByID($room);       
                    $this->data['inv']   = $this->data['quote'];
                    $sale_items          = $this->site->getSaleItemsBySaleOrderID($sale_id);
                }

                // $this->bpas->print_arrays($items);
                $warehouse_id   = $items[0]->warehouse_id;
                $customer_id    = $this->pos_settings->default_customer;
                $warehouse      = $this->site->getWarehouseByID($warehouse_id);
                $customer       = $this->site->getCompanyByID($customer_id);
                $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);

                if($items){
                    $r = 0; $pr = array();
                    foreach ($items as $row) {
                        $c = uniqid(mt_rand(), true);
                        $option               = false;
                        $row->quantity        = 0;
                        $row->item_tax_method = 0;
                        $row->qty             = 1;
                        $row->discount        = '0';
                        $row->serial          = '';
                        $options              = null;
                        $product_options      = null;
                        $row->quantity        = 0;
                        $row->code            = '';
                        $opt                  = json_decode('{}');
                        $opt->price           = 0;
                        $option_id            = false;
                        $row->option          = $option_id;
                        $row->price           = $row->price + (($row->price * $customer_group->percent) / 100); 
                        $row->real_unit_price = $row->price;
                        $row->base_quantity   = 1;
                        $row->base_unit       = $row->bed;
                        $row->base_unit_price = $row->price;
                        $row->unit            = $row->bed;
                        $row->comment         = '';
                        $combo_items          = false;
                        $categories           = null;
                        $units                = $row->bed;
                        $tax_rate             = null;
                        $set_price = $this->site->getUnitByProId($row->id);
                        $set_price = '';

                        $ri = $this->Settings->item_addition ? $row->id : sha1($c . $r);
                    
                        $pr[$ri] = ['id' => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name , 'category' => null,
                        'row'     => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate,'set_price'=>$set_price, 'units' => $units, 'options' => $options, 'fiber' => null,'product_options' => $product_options, ];
                        $r++;
                    }
                    $this->data['quote_items'] = json_encode($pr);
                }else{
                    $this->bpas->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
                }       
            }
            $this->data['customer']      = $this->site->getCompanyByID($this->pos_settings->default_customer);
            $this->data['count']         = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['projects']      = $this->site->getAllProject();
            $this->data['error']         = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['quote_id']      = $quote_id ? $quote_id : $sale_id;
            $this->data['room_id']       = $room ? $room : null;
            $this->data['sale_order_id'] = $sale_order_id;
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
            $this->data['suspend_notes'] = $this->table_model->getAll_suspend_note();
            $this->data['group_price']   = json_encode($this->site->getAllGroupPrice());
            $Settings                    = $this->site->getSettings();
            $this->data['salemans']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            //$this->data['currencies']  = $this->sales_model->getAllCurrencies();
            $this->data['slnumber']      = $this->site->getReference('so');
            $this->data['sltaxnumber']   = $this->site->getReference('st');
            $this->data['payment_ref']   = ''; //$this->site->getReference('pay');
            $bc      = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'),
                                                'page' => lang('sales')], ['link' => '#', 'page' => lang('add_sale')]];
            $meta                        = ['page_title' => lang('add_sale'), 'bc' => $bc];
            $this->page_construct('rental/add_sale', $meta, $this->data);
        }
    }
    public function edit_sale($id = null)
    {
        $this->bpas->checkPermissions();
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
            $project_id = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $sale_status      = $this->input->post('sale_status');
            $payment_status   = $this->input->post('payment_status');
            $payment_term     = $this->input->post('payment_term');
            $payment_term_details   = $this->site->getAllPaymentTermByID($payment_term);
            // $due_date      = (isset($payment_term_details[0]->id) ? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);
            $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company.'/'.$customer_details->name : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company.'/'.$biller_details->name : $biller_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note       = $this->bpas->clear_tags($this->input->post('staff_note'));
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
                $item_warranty      = isset($_POST['warranty'][$r]) ? $_POST['warranty'][$r] :'';
                $item_detail        = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : '';
                $item_room_option   = isset($_POST['room_option'][$r]) ? $_POST['room_option'][$r] : null;

                $item_commission   = isset($_POST['commission'][$r]) ? $_POST['commission'][$r] : null;

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    $cost = 0;
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
                    $purchase_unit_cost = 0;

                    $commission = $this->site->getCustomeFieldByID($item_commission);


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
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'max_serial'        => $item_max_serial,
                        'real_unit_price'   => $real_unit_price,
                        'addition_type'     => $item_addition_type,
                        'warranty'          => $item_warranty,
                        'comment'           => $item_detail,
                        'commission_type'   => $item_commission,
                        'commission'        => $this->bpas->formatDecimal(($subtotal*$commission->name)/100),
                        'room_option'       => $item_room_option
                    ];
                    $commission_product += isset($commission_item->price) ? ($commission_item->price * $item_quantity) : 0; 
                    //========add accounting=========//
                    if($this->Settings->accounting == 1 && $item_type != 'manual' && $sale_status=='completed'){
                 
                        $default_sale = ($item_type == 'standard') ? $this->accounting_setting->default_sale : $this->accounting_setting->other_income;
                       
                        $accTrans[] = array(
                            'tran_no'       => $id,
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
                            'activity_type' => 0
                        );
                        $accTrans[] = array(
                            'tran_no'       => $id,
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
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_cost),
                        );
                        $accTrans[] = array(
                            'tran_no'       => $id,
                            'tran_type'     => 'Sale',
                            'tran_date'     => $date,
                            'reference_no'  => $reference,
                            'account_code'  => $default_sale,
                            'amount'        => - $subtotal,
                            'narrative'     =>  $this->site->getAccountName($default_sale),
                            'description'   => $note,
                            'biller_id'     => $biller_id,
                            'project_id'    => $project_id,
                            'customer_id'   => $customer_id,
                            'created_by'    => $this->session->userdata('user_id'),
                            'activity_type' => 0
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
            $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            $saleman_award_points = 0;
            $staff = $this->site->getUser($inv->saleman_by);
            //=======acounting=========//
            if($this->Settings->accounting == 1){
            //  $saleAcc = $this->site->getAccountSettingByBiller($biller_id);
                $saleAcc = $this->site->getAccountSettingByBiller();
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
            $data           = [
                'date'              => $date,
                'project_id'        => $this->input->post('project'),
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
                'updated_by'        => $this->session->userdata('user_id'),
                'saleman_by'        => $this->input->post('saleman_by'),
                'zone_id'           => $this->input->post('zone_id'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ];
            if ($payment_status != 'paid') {
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
                        if ($this->input->post('paid_by') == 'deposit') {
                            $paying_to = $saleAcc->default_sale_deposit;
                        } else {
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
                        'account_code' => $this->accounting_setting->default_receivable,
                        'amount' => $grand_total,
                        'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'payment_id' => $id,
                        'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                    );
                }
            }else{
                $accTranPayments[] = array(
                    'tran_no' => $id,
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
                    'payment_id' => $id,
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
            $cus_sales         = $this->sales_model->getSalesTotals($customer_id);
            if(($customer_details->credit_limit !=0) && (($cus_sales->total_amount - $cus_sales->paid) - $inv->grand_total + $data['grand_total']) > $customer_details->credit_limit){
                $this->session->set_flashdata('error', lang("customer_due_over_credit_amount"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }
        if ($this->form_validation->run() == true && $this->sales_model->updateSale($id, $data, $products,$accTrans,$accTranPayments, $commission_product)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang('sale_updated'));
            admin_redirect('leasing/sales');
        } else {   
            $items = $this->sales_model->getAllInvoiceItemsRoom($id);
            foreach($items as $item) {
                $warehouse_id   = $item->warehouse_id;
                $customer_id    = $this->pos_settings->default_customer;
                $warehouse      = $this->site->getWarehouseByID($warehouse_id);
                $customer       = $this->site->getCompanyByID($customer_id);
                $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
            }
            if ($items) {
                $r = 0; 
                $pr = array();
                foreach ($items as $row) {
                    $c = uniqid(mt_rand(), true);
                    $option               = false;
                    $row->item_tax_method = 0;
                    $row->qty             = $row->quantity;
                    $row->discount        = '0';
                    $row->serial          = '';
                    $options              = null;
                    $product_options      = null;
                    $row->quantity        = 0;
                    $row->code            = $row->product_code;
                    $row->name            = $row->product_name;
                    $opt                  = json_decode('{}');
                    $opt->price           = 0;
                    $option_id            = false;
                    $row->option          = $option_id;
                    $row->price           = $row->net_unit_price + (($row->net_unit_price * $customer_group->percent) / 100); 
                    $row->real_unit_price = $row->net_unit_price;
                    $row->base_quantity   = 1;
                    $row->base_unit       = $row->bed;
                    $row->base_unit_price = $row->net_unit_price;
                    $row->unit            = $row->bed;
                    $row->comment         = '';
                    //$room_options         = $this->leasing_model->getRoomOptionsByRoomID($row->product_id);

                    $room_options         = $this->leasing_model->getAllAssetID($row->product_id);
                    $row->room_option     = (!empty($room_options) ? ($row->room_option ? $row->room_option : $room_options) : null);
                    $combo_items          = false;
                    $categories           = null;
                    $units                = $row->bed;
                    $tax_rate             = null;
                    $set_price            = $this->site->getUnitByProId($row->id);
                    $set_price            = '';
                    $ri = $this->Settings->item_addition ? $row->id : sha1($c . $r);
                    $pr[$ri] =   [
                        'id'              => sha1($c . $r),
                        'item_id'          => $row->id,
                        'label'            => $row->name,
                        'category'         => null,
                        'row'              => $row,
                        'combo_items'      => $combo_items,
                        'tax_rate'         => $tax_rate,
                        'set_price'        => $set_price,
                        'units'            => $units,
                        'options'          => $options,
                        'fiber'            => null,
                        'product_options'  => $product_options,
                        'room_options'     => $room_options
                    ];
                    $r++;
                }
                // $this->data['quote_items'] = json_encode($pr);
                $this->data['inv_items'] = json_encode($pr);
            } else {
                $this->bpas->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
            } 
            $this->data['count'] = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['projects']     = $this->site->getAllProject();
            $this->data['inv']          = $this->sales_model->getInvoiceByID($id);  
            $this->data['id']           = $id;
            $this->data['payment_term'] = $this->site->getAllPaymentTerm();
            $this->data['agencies']     = $this->site->getAllUsers();
            $this->data['billers']      = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            $this->data['units']        = $this->site->getAllBaseUnits();
            $this->data['tax_rates']    = $this->site->getAllTaxRates();
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['zones']        = $this->site->getAllZones();
            $Settings = $this->site->getSettings();
            $this->data['salemans']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('edit_sale')]];
            $meta = ['page_title' => lang('edit_sale'), 'bc' => $bc];
            $this->page_construct('rental/edit_sale', $meta, $this->data);
        }
    }
     public function end_leasing(){   
        $this->bpas->checkPermissions('end_leasing',true);
        $this->data['modal_js'] = $this->site->modal_js();
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => 'schools', 'page' => lang('customers')), array('link' => '#', 'page' => lang('blacklist')));
        $meta = array('page_title' => lang('blacklist'), 'bc' => $bc);
        $this->page_construct('rental/end_leasing', $meta, $this->data);
    }
    public function getEndleasing()
    {   
        $this->bpas->checkPermissions('end_leasing');
        $this->load->library('datatables');
        $delete_link = "<a href='#' class='po' title='" . lang("delete_blacklist") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" .admin_url('customers/delete_blacklist/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_blacklist') . "</a>";
        
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
                            <ul class="dropdown-menu pull-right hide" role="menu">
                                <li><a href="'.admin_url('customers/edit_blacklist/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_blacklist').'</a></li>
                                <li>'.$delete_link.'</li>
                            </ul>
                        </div>';
        $this->datatables
            ->select('
                    reservation_checkOut.id as id, 
                    reservation_checkOut.date,
                    '.$this->db->dbprefix('reservation').'.reference_no as reference_no,
                    reservation_checkOut.note,
                    CONCAT('.$this->db->dbprefix('users').'.first_name," ",
                    '.$this->db->dbprefix('users').'.last_name) as created_by,
            ')
            ->from("reservation_checkOut")
            ->join("reservation","reservation.id = reservation_checkOut.reserved_id","left")
            ->join('users', 'users.id=reservation_checkOut.created_by', 'left')
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
    }
}
