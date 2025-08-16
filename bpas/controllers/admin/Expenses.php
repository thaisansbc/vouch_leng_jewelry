<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Expenses extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->bpas->md('login');
        }
        if ($this->Customer) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->lang->admin_load('purchases', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('purchases_model');
        $this->load->admin_model('accounts_model');
        $this->load->admin_model('approved_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path         = 'assets/uploads/';
        $this->thumbs_path         = 'assets/uploads/thumbs/';
        $this->image_types         = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types  = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size   = '1024';
        $this->data['logo']        = true;
    }
    public function index($biller_id = null){
        $this->bpas->checkPermissions('index',null);
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

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('expenses')]];
        $meta                = ['page_title' => lang('expenses'), 'bc' => $bc];
        $this->page_construct('expenses/index', $meta, $this->data);
    }
    public function getExpenses($biller_id = null)
    {
        $this->bpas->checkPermissions('index',null);
        if ((!$this->Owner && !$this->Admin) && !$biller_id) {
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $biller_id = $user->multi_biller;
            } else {
                $biller_id = $user->biller_id;
            }
        }
        $payments_link ='';
        $add_payment_link='';
        if($this->Settings->payment_expense==1){
            $payments_link = anchor('admin/purchases/payments/0/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), ' class="expense_payment" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
            $add_payment_link = anchor('admin/expenses/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'class="expense_payment" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        }

        $detail_link = anchor('admin/expenses/expense_note/$1', '<i class="fa fa-file-text-o"></i> ' . lang('expense_note'), 'data-toggle="modal" data-target="#myModal2"');
        $edit_link   = anchor('admin/expenses/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_expense'));
        $edit_link1   = anchor('admin/expenses/edit_expense/$1', '<i class="fa fa-edit"></i> ' . lang('edit_expense'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        //$attachment_link = '<a href="'.base_url('assets/uploads/$1').'" target="_blank"><i class="fa fa-chain"></i></a>';
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line('delete_expense') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('expenses/delete_expense/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_expense') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $delete_link . '</li>
            <li>' . $payments_link . '</li>
            <li>' . $add_payment_link . '</li>
        </ul>
        </div></div>';

        $this->load->library('datatables');
        $this->datatables
            ->select($this->db->dbprefix('expenses') . ".id as id, 
                {$this->db->dbprefix('expenses')}.date, 
                {$this->db->dbprefix('expenses')}.reference, 
                {$this->db->dbprefix('companies')}.name as biller,
                {$this->db->dbprefix('expenses')}.grand_total as amount,
                {$this->db->dbprefix('expenses')}.paid as paid, 
                (grand_total- IFNULL(paid,0)) as balance, 
                {$this->db->dbprefix('expenses')}.payment_status, 
                {$this->db->dbprefix('expenses')}.paid_by as expense_by,
                CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user, 
                {$this->db->dbprefix('expenses')}.attachment", false)
            ->from('expenses')
            ->join('companies', 'companies.id=expenses.biller_id', 'left')
            ->join('users', 'users.id=expenses.created_by', 'left')
            ->join('users exby', 'exby.id=expenses.expense_by', 'left');

        if ($biller_id) {
            $this->datatables->where('expenses.biller_id IN ('.$biller_id.')');
        } 
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('expenses.created_by', $this->session->userdata('user_id'));
        }

        //$this->datatables->edit_column("attachment", $attachment_link, "attachment");
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
    public function expense_note($id = null)
    {
        $this->data['exchange_rate'] = $this->bpas->getExchange_rate('KHR');
        $expense                    = $this->purchases_model->getExpenseByID($id);
        $this->data['expenseByReference']    = $this->purchases_model->getExpenseByReference($expense->reference);
        $this->data['user']         = $this->site->getUser($expense->created_by);
        $this->data['category']     = $expense->category_id ? $this->purchases_model->getExpenseCategoryByID($expense->category_id) : null;
        $this->data['biller']      = $expense->biller_id ? $this->site->getCompanyByID($expense->biller_id) : null;
        $this->data['expense']      = $expense;
        $this->data['projects']     = $this->site->getAllProject();
        $this->data['bankAccounts'] = $this->accounts_model->getAllChartAccountIn('50,60,80');
        $this->data['paid_by']      = $this->accounts_model->getAllChartAccountBank();
        $this->data['page_title']   = $this->lang->line('expense_note');
        $this->data['items'] = $this->purchases_model->getExpenseItems($id);
        $this->load->view($this->theme . 'expenses/expense_note', $this->data);
    }
    public function add_expense_($biller_id=false,$project_id=false)
    {
        $this->bpas->checkPermissions('add',null);
        $this->load->helper('security');
        $this->form_validation->set_rules('reference', lang("reference"), 'required|is_unique[expenses.reference]');
        $this->form_validation->set_rules('amount[]', lang('amount'), 'required');
        $this->form_validation->set_rules('paid_by[]', lang('paid_by'), 'required');
        
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $arrays[] = 0;
            $array1 = $this->input->post('amount');
            $array2 = $this->input->post('paid_by');
            $array3 = $this->input->post('bank_account');
            $i                = sizeof($_POST['amount']);
    
            // foreach ($array1 as $key => $value) {
            for ($r = 0; $r < $i; $r++) {
                $amount = $_POST['amount'][$r];
                $bank_account = $_POST['bank_account'][$r];
                $account_paid = $_POST['paid_by'][$r];

                $data = [
                    'date'         => $date,
                    'reference'    => $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('ex'),
                    'amount'       => $amount,
                    'created_by'   => $this->session->userdata('user_id'),
                    'note'         => $this->input->post('note', true),
                    'category_id'  => $this->input->post('category', true),
                    'warehouse_id' => $this->input->post('warehouse', true),
                    'project_id'   => $this->input->post('project'),
                    'biller_id'    => $this->input->post('biller'),
                    'expense_by'  => $this->input->post('expense_by')?$this->input->post('expense_by'):null,
                ];
                if($this->Settings->accounting == 1){
                    $data['bank_account'] = $bank_account;
                    $data['account_paying_from']    = $account_paid;
                }else{
                    $data['paid_by']      = $account_paid;
                }

                //=======add acounting=========//
                if($this->Settings->accounting == 1){
                    $expense_category = $this->purchases_model->getExpenseCategoryByID($this->input->post('category'));
                    // TODO Add required field more
                    $biller_id = $this->input->post('biller') ? $this->input->post('biller') : $this->Settings->default_biller;
                    $project_id = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
                    $supplier_id = $this->Settings->default_supplier;
                    $supplier_details = $this->site->getCompanyByID($supplier_id);
                    $supplier         = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
                    $pay_from_account = $this->input->post('bank_account');
                    $reference = $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('ex');
                    // $paid_by = $this->input->post('paid_by');

                    $chart_account=$this->site->getChartByID($bank_account);

                    $accTrans[] = array(
                        'tran_type' => 'Expense',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' =>  $bank_account,
                        'amount' => $amount,
                        'narrative' => $this->input->post('reference').'#'.$this->site->getAccountName($bank_account),
                        'note' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'supplier_id' => $supplier_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => $chart_account->type // 1= bussiness, 2 = investing, 3= financing activity
                    );

                    $accTrans[] = array(
                        'tran_type' => 'Expense',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $account_paid,
                        'amount' => -($amount),
                        'narrative' => $this->site->getAccountName($account_paid),
                        'note' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'supplier_id' => $supplier_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                //============end accounting=======//
                if ($_FILES['userfile']['size'] > 0) {
                    $this->load->library('upload');
                    $config['upload_path']   = $this->upload_path;
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
                    $photo              = $this->upload->file_name;
                    $data['attachment'] = $photo;
                }
                $datas[] = $data;
            }
            krsort($datas);
        } elseif ($this->input->post('add_expense')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->purchases_model->addExpense($datas,$accTrans)) {
            $this->session->set_flashdata('message', lang('expense_added'));
            redirect($_SERVER['HTTP_REFERER']);
            //admin_redirect('expenses');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['exnumber']   = $this->site->getReference('ex');
            if ($this->Owner || $this->Admin) {
                $this->data['projects'] = $this->site->getAllProject();
            }else{
                $this->data['projects'] = $this->site->getAllProject($this->session->userdata('warehouse_id'));
            }

            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->data['warehouses']       = $this->site->getAllWarehouses();

            $this->data['users']        = $this->site->getStaff();
            $this->data['bankAccounts'] = $this->accounts_model->getAllChartAccountexpense('50,60,80');
            $this->data['paid_by']      = $this->site->getAllBankAccounts();
            $this->data['biller_id']    = $biller_id?$biller_id:'';
            $this->data['project_id']   = $project_id?$project_id:'';
            $this->data['currency']     = $this->site->getCurrency();
            $this->data['currencies']   = $this->bpas->getAllCurrencies();
            $this->data['categories']   = $this->purchases_model->getExpenseCategories();
            $this->data['nest_categories'] = $this->site->getNestedExpenseCategories();
            $this->data['modal_js']     = $this->site->modal_js();
            $this->load->view($this->theme . 'expenses/add_expense', $this->data);
        }
    }
    public function edit_expense($id = null)
    {
        $this->bpas->checkPermissions('edit', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('reference', lang("reference"), 'required');
        $this->form_validation->set_rules('amount[]', lang('amount'), 'required');
        $this->form_validation->set_rules('paid_by[]', lang('paid_by'), 'required');
        
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $i                = sizeof($_POST['amount']);
            //    foreach ($array1 as $key => $value) {
            $this->data['expense']    = $this->purchases_model->getExpenseByID($id);
            $this->data['expenseByReference']    = $this->purchases_model->getExpenseByReference($this->data['expense']->reference);
            $referenceID = $this->data['expense']->reference;
            $this->purchases_model->deleteExpenseByReference($referenceID);
            $this->accounts_model->deleteJournalByRef($referenceID);
            for ($r = 0; $r < $i; $r++) {
                $amount = $_POST['amount'][$r];
                $bank_account = $_POST['bank_account'][$r];
                $account_paid = $_POST['paid_by'][$r];

                $data = [
                    'date'         => $date,
                    'reference'    => $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('ex'),
           
                    'amount'       => $amount,
                    'created_by'   => $this->session->userdata('user_id'),
                    'note'         => $this->input->post('note', true),
                    'category_id'  => $this->input->post('category', true),
                    'warehouse_id' => $this->input->post('warehouse', true),
                    'project_id' => $this->input->post('project'),
                    'biller_id' => $this->input->post('biller'),
                    'expense_by'  => $this->input->post('expense_by')?$this->input->post('expense_by'):null,
                ];
                if($this->Settings->accounting == 1){
                    $data['bank_account'] = $bank_account;
                    $data['account_paying_from']    = $account_paid;
                }else{
                    $data['paid_by']      = $account_paid;
                }

                //=======add acounting=========//
                if ($this->Settings->accounting == 1) {
                    $expense_category = $this->purchases_model->getExpenseCategoryByID($this->input->post('category'));
                    // TODO Add required field more
                    $biller_id =  $this->input->post('biller') ? $this->input->post('biller') : $this->Settings->default_biller;
                    $project_id = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project;
                    $supplier_id = $this->Settings->default_supplier;
                    $supplier_details = $this->site->getCompanyByID($supplier_id);
                    $supplier         = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
                    $pay_from_account = $this->input->post('bank_account');
                    $reference = $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('ex');
                    // $paid_by = $this->input->post('paid_by');

                    $chart_account=$this->site->getChartByID($bank_account);

                    $accTrans[] = array(
                        'tran_no'       => $id,
                        'tran_type'     => 'Expense',
                        'tran_date'     => $date,
                        'reference_no' => $reference,
                        'account_code' => $account_paid,
                        'amount' => - ($amount),
                        'narrative' => $this->site->getAccountName($account_paid),
                        'note' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'supplier_id' => $supplier_id,
                        'created_by'  => $this->session->userdata('user_id'),

                    );
                    
                    $accTrans[] = array(
                        'tran_no'       => $id,
                        'tran_type' => 'Expense',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' =>  $bank_account,
                        'amount' => $amount,
                        'narrative' => $this->input->post('reference') . '#' . $this->site->getAccountName($bank_account),
                        'note' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'supplier_id' => $supplier_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => $chart_account->type // 1= bussiness, 2 = investing, 3= financing activity
                    );
                }
                //============end accounting=======//
                if ($_FILES['userfile']['size'] > 0) {
                    $this->load->library('upload');
                    $config['upload_path']   = $this->upload_path;
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
                    $photo              = $this->upload->file_name;
                    $data['attachment'] = $photo;
                }
                $datas[] = $data;
            }
            krsort($datas);
        
        } elseif ($this->input->post('add_expense'))  {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true &&  $this->purchases_model->updateExpenseByReference($id, $datas, $accTrans)) {
            $this->session->set_flashdata('message', lang('expense_updated'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['expense']    = $this->purchases_model->getExpenseByID($id);
            $this->data['expenseByReference']    = $this->purchases_model->getExpenseByReference($this->data['expense']->reference);
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $this->data['projects'] = $this->site->getAllProject();
            }else{
                $this->data['projects'] = $this->site->getAllProject($this->session->userdata('warehouse_id'));
            }
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->data['warehouses']       = $this->site->getAllWarehouses();

            $this->data['bankAccounts']     = $this->accounts_model->getAllChartAccountexpense('50,60,80');
            $this->data['paid_by']          = $this->site->getAllBankAccounts();
            $this->data['cash_accounts']    = $this->site->getCashAccounts();
            $this->data['users']            = $this->site->getStaff();
            $this->data['currency']         = $this->site->getCurrency();
            $this->data['currencies']       = $this->bpas->getAllCurrencies();
            $this->data['modal_js']         = $this->site->modal_js();
            $this->data['categories']       = $this->purchases_model->getExpenseCategories();
            $this->data['nest_categories']  = $this->site->getNestedExpenseCategories();
            $this->load->view($this->theme . 'expenses/edit_expense', $this->data);
        }
    }
    public function delete_expense($id = null)
    {
        $this->bpas->checkPermissions('delete', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['expense']    = $this->purchases_model->getExpenseByID($id);
        $this->data['expenseByReference']    = $this->purchases_model->getExpenseByReference($this->data['expense']->reference);
        $referenceID = $this->data['expense']->reference;
        $this->accounts_model->deleteJournalByRef($referenceID);
        $expense = $this->purchases_model->getExpenseByID($id);
        if($this->purchases_model->deleteExpenseByReference($referenceID)){
            if ($expense->attachment) {
                unlink($this->upload_path . $expense->attachment);
            }
        }  
        $this->bpas->send_json(['error' => 0, 'msg' => lang('expense_deleted')]);
        redirect($_SERVER['HTTP_REFERER']);
    }
    public function expense_actions()
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
                        $this->data['expense']    = $this->purchases_model->getExpenseByID($id);
                        $this->data['expenseByReference']    = $this->purchases_model->getExpenseByReference($this->data['expense']->reference);
                        foreach ($this->data['expenseByReference'] as $value) {
                        $this->purchases_model->deleteExpenseByReference($value->reference);
                        $this->accounts_model->deleteJournalByRef($value->reference);
                        }
                    }
                    $this->session->set_flashdata('message', $this->lang->line('expenses_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('expenses'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('currency'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('amount'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('created_by'));
                    $row = 2;
                    foreach($_POST['val'] as $id){
                        $this->data['expense']    = $this->purchases_model->getExpenseByID($id);
                        $this->data['expenseByReference']    = $this->purchases_model->getExpenseByReference($this->data['expense']->reference);
                        foreach ($this->data['expenseByReference'] as $value) {
                            $user   = $this->site->getUser($value->created_by);
                            $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($value->date));
                            $this->excel->getActiveSheet()->SetCellValue('B' . $row, $value->reference);
                            $this->excel->getActiveSheet()->SetCellValue('C' . $row, $value->currency);
                            $this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatMoney($value->amount));
                            $this->excel->getActiveSheet()->SetCellValue('E' . $row, $value->note);
                            $this->excel->getActiveSheet()->SetCellValue('F' . $row, $user->first_name . ' ' . $user->last_name);
                            $row++;
                        }   
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(35);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'expenses_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line('no_expense_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    //-------------------expense budget----------------------
    public function expenses_budget($id = null, $biller_id = null)
    {
        $this->bpas->checkPermissions();
        $this->data['error']     = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

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

        $this->data['budget_id'] = $id;
        $bc                      = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('expenses_budget')]];
        $meta                    = ['page_title' => lang('expenses_budget'), 'bc' => $bc];
        $this->page_construct('purchases/expenses_budget', $meta, $this->data);
    }
    public function getExpensesBudget($biller_id = null)
    {
        $this->bpas->checkPermissions('expenses_budget');
        if ((!$this->Owner && !$this->Admin) && !$biller_id) {
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $biller_id = $user->multi_biller;
            } else {
                $biller_id = $user->biller_id;
            }
        }

        $budget_id   = $this->input->get('budget_id') ? $this->input->get('budget_id') : null;  
        $detail_link = anchor('admin/expenses/expense_budget_note/$1', '<i class="fa fa-file-text-o"></i> ' . lang('expense_budget_note'), 'data-toggle="modal" data-target="#myModal2"');
        $edit_link   = anchor('admin/expenses/edit_expense_budget/$1', '<i class="fa fa-edit"></i> ' . lang('edit_expense_budget'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line('delete_expense_budget') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('expenses/delete_expense_budget/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_expense_budget') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $detail_link . '</li>
                <li>' . $edit_link . '</li>
                <li>' . $delete_link . '</li>
            </ul>
            </div></div>';

        $str = " ( SELECT IF({$this->db->dbprefix('companies')}.company != '-', {$this->db->dbprefix('companies')}.company, {$this->db->dbprefix('companies')}.name) FROM {$this->db->dbprefix('companies')} WHERE {$this->db->dbprefix('companies')}.id = {$this->db->dbprefix('budgets')}.biller_id ) ";
        $this->load->library('datatables');
        $this->datatables
            ->select(
                $this->db->dbprefix('expenses_budget') . ".id as id, 
                {$this->db->dbprefix('expenses_budget')}.date, 
                {$this->db->dbprefix('expenses_budget')}.reference, 
                {$this->db->dbprefix('expense_categories')}.name as category, 
                CONCAT({$this->db->dbprefix('budgets')}.title, ' (', {$this->db->dbprefix('budgets')}.amount, ') ', " . $str . ") as budget,
                {$this->db->dbprefix('expenses_budget')}.amount_usd as usd, 
                {$this->db->dbprefix('expenses_budget')}.amount_khm as khm, 
                {$this->db->dbprefix('expenses_budget')}.amount as amount, 
                {$this->db->dbprefix('expenses_budget')}.note, 
                CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user, 
                {$this->db->dbprefix('expenses_budget')}.attachment")
            ->from('expenses_budget')
            ->join('users', 'users.id=expenses_budget.created_by', 'left')
            ->join('expense_categories', 'expense_categories.id=expenses_budget.category_id', 'left')
            ->join('budgets', 'budgets.id=expenses_budget.budget_id', 'left')
            ->group_by('expenses_budget.reference');

        if ($biller_id) {
            $this->datatables->where('expenses_budget.biller_id IN ('.$biller_id.')');
        } 
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('expenses_budget.created_by', $this->session->userdata('user_id'));
        }
        if ($budget_id) {
            $this->datatables->where('expenses_budget.budget_id', $budget_id);
        }

        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
    public function expense_budget_note($id = null)
    {
        $this->data['exchange_rate']      = $this->bpas->getExchange_rate('KHR');
        $expense                          = $this->purchases_model->getExpenseBudgetByID($id);
        $this->data['expenseByReference'] = $this->purchases_model->getExpenseBudgetByReference($expense->reference);
        $this->data['user']               = $this->site->getUser($expense->created_by);
        $this->data['category']           = $expense->category_id ? $this->purchases_model->getExpenseCategoryByID($expense->category_id) : null;
        $this->data['warehouse']          = $expense->warehouse_id ? $this->site->getWarehouseByID($expense->warehouse_id) : null;
        $this->data['expense']            = $expense;
        $this->data['projects']           = $this->site->getAllProject();
        $this->data['bankAccounts']       = $this->accounts_model->getAllChartAccountIn('50,60,80');
        $this->data['paid_by']            = $this->accounts_model->getAllChartAccountBank();
        $this->data['page_title']         = $this->lang->line('expense_note');
        $this->load->view($this->theme . 'expenses/expense_note', $this->data);
    }
    public function delete_expense_budget($id = null)
    {
        $this->bpas->checkPermissions('delete', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['expense_budget']           = $this->purchases_model->getExpenseBudgetByID($id);
        $this->data['expenseBudgetByReference'] = $this->purchases_model->getExpenseBudgetByReference($this->data['expense_budget']->reference);
        $referenceID = $this->data['expense_budget']->reference;
        $this->accounts_model->deleteJournalByRef($referenceID);
        $expense_budget = $this->purchases_model->getExpenseBudgetByID($id);
        if($this->purchases_model->deleteExpenseBudgetByReference($referenceID)){
            if ($expense_budget->attachment) {
                unlink($this->upload_path . $expense_budget->attachment);
            }
        }  
        $this->bpas->send_json(['error' => 0, 'msg' => lang('expense_budget_deleted')]);
        redirect($_SERVER['HTTP_REFERER']);
    }
    public function add_expense_budget()
    {
        $this->bpas->checkPermissions('expenses_budget', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('reference', lang("reference"), 'required|is_unique[expenses_budget.reference]');
        $this->form_validation->set_rules('amount[]', lang('amount'), 'required');
        if($this->Settings->accounting){
            $this->form_validation->set_rules('paid_by[]', lang('paid_by'), 'required');
        }
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $arrays[] = 0;
            $array1 = $this->input->post('amount');
            $array2 = $this->input->post('paid_by');
            $array3 = $this->input->post('bank_account');
            $i      = sizeof($_POST['amount']);

            for ($r = 0; $r < $i; $r++) {
                $amount       = $_POST['amount'][$r];
                $bank_account = isset($_POST['bank_account'][$r]) ? $_POST['bank_account'][$r] : null;
                $account_paid = isset($_POST['paid_by'][$r]) ? $_POST['paid_by'][$r] : null;

                if($amount == 0){
                    $this->session->set_flashdata('error', lang('please_input_amount_greater_than_zero!'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                $data = [
                    'date'         => $date,
                    'reference'    => $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('exb'),
                    'amount_usd'   => (isset($_POST['amount_usd']) && $_POST['amount_usd'] != null) ? $_POST['amount_usd'] : $amount,
                    'amount_khm'   => (isset($_POST['amount_khm']) && $_POST['amount_khm'] != null) ? $_POST['amount_khm'] : 0,
                    'amount'       => $amount,
                    'created_by'   => $this->session->userdata('user_id'),
                    'note'         => $this->input->post('note', true),
                    'category_id'  => $this->input->post('category', true),
                    'budget_id'    => $this->input->post('budget', true),
                    'warehouse_id' => $this->input->post('warehouse', true),
                    'project_id'   => $this->input->post('project'),
                    'bank_account' => $bank_account,
                    'bank_code'    => $account_paid,
                    'biller_id'    => $this->input->post('biller')
                ];

                //=======add acounting=========//
                if($this->Settings->module_account == 1){
                    $expense_category = $this->purchases_model->getExpenseCategoryByID($this->input->post('category'));
                    // TODO Add required field more
                    $biller_id = $this->input->post('biller') ? $this->input->post('biller') : $this->Settings->default_biller;
                    $project_id = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
                    $supplier_id = $this->Settings->default_supplier;
                    $supplier_details = $this->site->getCompanyByID($supplier_id);
                    $supplier         = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
                    $pay_from_account = $this->input->post('bank_account');
                    $reference = $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('exb');
                    // $paid_by = $this->input->post('paid_by');

                    $accTrans[] = array(
                        'tran_type' => 'Expense Budget',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' =>  $bank_account,
                        'amount' => $amount,
                        'narrative' => $this->input->post('reference').'#'.$this->site->getAccountName($bank_account),
                        'description' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'supplier_id' => $supplier_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );

                    $accTrans[] = array(
                        'tran_type' => 'Expense Budget',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $account_paid,
                        'amount' => -($amount),
                        'narrative' => $this->site->getAccountName($account_paid),
                        'description' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'supplier_id' => $supplier_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }else{
                    $accTrans =[];
                }
                //============end accounting=======//
                if ($_FILES['userfile']['size'] > 0) {
                    $this->load->library('upload');
                    $config['upload_path']   = $this->upload_path;
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
                    $photo              = $this->upload->file_name;
                    $data['attachment'] = $photo;
                }
                $datas[] = $data;
            }
            krsort($datas);
        } elseif ($this->input->post('add_expense_budget')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->purchases_model->addExpenseBudget($datas, $accTrans)) {
            $this->session->set_flashdata('message', lang('expense_budget_added'));
            admin_redirect('expenses/expenses_budget');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['ref']        = $this->site->getReference('exb');
            if ($this->Owner || $this->Admin) {
                $this->data['projects'] = $this->site->getAllProject();
            }else{
                $this->data['projects'] = $this->site->getAllProject($this->session->userdata('warehouse_id'));
            }

            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->data['warehouses']       = $this->site->getAllWarehouses();

            $this->data['ex_rate']      = $this->site->getExchangeRate('KHR');
            $this->data['bankAccounts'] = $this->accounts_model->getAllChartAccountexpense('50,60,80');
            $this->data['paid_by']      = $this->accounts_model->getAllChartAccounts();
            $this->data['categories']   = $this->purchases_model->getExpenseCategories();
            $this->data['nest_categories'] = $this->site->getNestedExpenseCategories();
            $this->data['expenses']     = $this->purchases_model->getAllExpensesBudget();
            $this->data['budgets']      = $this->purchases_model->getAllBudgets();
            $this->data['modal_js']     = $this->site->modal_js();
            $this->load->view($this->theme . 'expenses/add_expense_budget', $this->data);
        }
    }
    public function edit_expense_budget($id = null)
    {
        $this->bpas->checkPermissions('edit', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('reference', lang("reference"), 'required');
        $this->form_validation->set_rules('amount[]', lang('amount'), 'required');
        if($this->Settings->accounting){
            $this->form_validation->set_rules('paid_by[]', lang('paid_by'), 'required');
        }
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $accTrans = (array) null;
            $i = sizeof($_POST['amount']);
            for ($r = 0; $r < $i; $r++) {
                $amount       = $_POST['amount'][$r];
                $bank_account = isset($_POST['bank_account'][$r]) ? $_POST['bank_account'][$r] : null;
                $account_paid = isset($_POST['paid_by'][$r]) ? $_POST['paid_by'][$r] : null;

                if($amount == 0){
                    $this->session->set_flashdata('error', lang('please_input_amount_greater_than_zero!'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                $data = [
                    'date'         => $date,
                    'reference'    => $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('exb'),
                    'amount_usd'   => (isset($_POST['amount_usd']) && $_POST['amount_usd'] != null) ? $_POST['amount_usd'] : $amount,
                    'amount_khm'   => (isset($_POST['amount_khm']) && $_POST['amount_khm'] != null) ? $_POST['amount_khm'] : 0,
                    'amount'       => $amount,
                    'created_by'   => $this->session->userdata('user_id'),
                    'note'         => $this->input->post('note', true),
                    'category_id'  => $this->input->post('category', true),
                    'budget_id'    => $this->input->post('budget', true),
                    'warehouse_id' => $this->input->post('warehouse', true),
                    'project_id'   => $this->input->post('project'),
                    'bank_account' => $bank_account,
                    'account_paying_from'    => $account_paid,
                    'biller_id'    => $this->input->post('biller')
                ];

                //=======add acounting=========//
                if ($this->Settings->module_account == 1) {
                    $expense_category = $this->purchases_model->getExpenseCategoryByID($this->input->post('category'));
                    // TODO Add required field more
                    $biller_id        =  $this->input->post('biller') ? $this->input->post('biller') : $this->Settings->default_biller;
                    $project_id       = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project;
                    $supplier_id      = $this->Settings->default_supplier;
                    $supplier_details = $this->site->getCompanyByID($supplier_id);
                    $supplier         = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
                    $pay_from_account = $this->input->post('bank_account');
                    $reference        = $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('exb');
                    // $paid_by = $this->input->post('paid_by');
                    
                    $expenseAcc = $this->site->getAccountSettingByBiller();
                    $accTrans[] = array(
                        'tran_no'       => $id,
                        'tran_type' => 'Expense Budget',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $account_paid,
                        'amount' => - ($amount),
                        'narrative' => $this->site->getAccountName($account_paid),
                        'description' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'supplier_id' => $supplier_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                    
                    $accTrans[] = array(
                        'tran_no'       => $id,
                        'tran_type'     => 'Expense Budget',
                        'tran_date'     => $date,
                        'reference_no' => $reference,
                        'account_code' =>  $bank_account,
                        'amount' => $amount,
                        'narrative' => $this->input->post('reference') . '#' . $this->site->getAccountName($bank_account),
                        'description' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'supplier_id' => $supplier_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                //============end accounting=======//
                if ($_FILES['userfile']['size'] > 0) {
                    $this->load->library('upload');
                    $config['upload_path']   = $this->upload_path;
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
                    $photo              = $this->upload->file_name;
                    $data['attachment'] = $photo;
                }
                $datas[] = $data;
            }
            krsort($datas);
        
        } elseif ($this->input->post('add_expense_budget'))  {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true) {
            $this->data['expense_budget']           = $this->purchases_model->getExpenseBudgetByID($id);
            $this->data['expenseBudgetByReference'] = $this->purchases_model->getExpenseBudgetByReference($this->data['expense_budget']->reference);
            $referenceID = $this->data['expense_budget']->reference;
            $this->purchases_model->deleteExpenseBudgetByReference($referenceID);
            $this->accounts_model->deleteJournalByRef($referenceID);
            $this->purchases_model->updateExpenseBudgetByReference($id, $datas, $accTrans);
            $this->session->set_flashdata('message', lang('expense_budget_updated'));
            admin_redirect('expenses/expenses_budget');
        } else {
            $this->data['error']                    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['expense_budget']           = $this->purchases_model->getExpenseBudgetByID($id);
            $this->data['expenseBudgetByReference'] = $this->purchases_model->getExpenseBudgetByReference($this->data['expense_budget']->reference);
            if ($this->Owner || $this->Admin) {
                $this->data['projects'] = $this->site->getAllProject();
            }else{
                $this->data['projects'] = $this->site->getAllProject($this->session->userdata('warehouse_id'));
            }

            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->data['warehouses']       = $this->site->getAllWarehouses();

            $this->data['ex_rate']      = $this->site->getExchangeRate('KHR');
            $this->data['bankAccounts'] = $this->accounts_model->getAllChartAccountexpense('50,60,80');
            $this->data['paid_by']      = $this->accounts_model->getAllChartAccounts();
            $this->data['modal_js']     = $this->site->modal_js();
            $this->data['categories']   = $this->purchases_model->getExpenseCategories();
            $this->data['nest_categories'] = $this->site->getNestedExpenseCategories();
            $this->data['expenses']     = $this->purchases_model->getAllExpensesBudget();
            $this->data['budgets']      = $this->purchases_model->getAllBudgets();
            $this->load->view($this->theme . 'expenses/edit_expense_budget', $this->data);
        }
    }
    public function expense_budget_actions()
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
                        $this->data['expense_budget']           = $this->purchases_model->getExpenseBudgetByID($id);
                        $this->data['expenseBudgetByReference'] = $this->purchases_model->getExpenseBudgetByReference($this->data['expense_budget']->reference);
                        foreach ($this->data['expenseBudgetByReference'] as $value) {
                            $this->purchases_model->deleteExpenseBudgetByReference($value->reference);
                            $this->accounts_model->deleteJournalByRef($value->reference);
                        }
                    }
                    $this->session->set_flashdata('message', $this->lang->line('expenses_budget_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('expenses_budget'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('budget'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('amount_usd'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('amount_khm'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('total_amount'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('created_by'));
                    $row = 2;
                    $amt_usd = 0;
                    $amt_khm = 0;
                    $total_amt = 0;
                    foreach($_POST['val'] as $id){
                        $this->data['expense_budget']           = $this->purchases_model->getExpenseBudgetByID($id);
                        $this->data['expenseBudgetByReference'] = $this->purchases_model->getExpenseBudgetByReference($this->data['expense_budget']->reference);
                        foreach ($this->data['expenseBudgetByReference'] as $value) {
                            $user   = $this->site->getUser($value->created_by);
                            if($value->budget_id){
                                $budget  = $this->purchases_model->getBudgetByID($value->budget_id);
                                $company = $this->site->getCompanyByID($budget->biller_id);
                            } else {
                                $budget = null;
                            }
                            $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($value->date));
                            $this->excel->getActiveSheet()->SetCellValue('B' . $row, $value->reference);
                            $this->excel->getActiveSheet()->SetCellValue('C' . $row, $budget !== null ? $budget->title . ' (' . $budget->amount . ') ' . ($company->company != '-' ? $company->company : $company->name) : '');
                            $this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatMoney($value->amount_usd));
                            $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatMoney($value->amount_khm));
                            $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatMoney($value->amount));
                            $this->excel->getActiveSheet()->SetCellValue('G' . $row, strip_tags($value->note));
                            $this->excel->getActiveSheet()->SetCellValue('H' . $row, $user->first_name . ' ' . $user->last_name);
                            $amt_usd += $value->amount_usd;
                            $amt_khm += $value->amount_khm;
                            $total_amt += $value->amount;
                            $row++;
                        }   
                    }
                    $this->excel->getActiveSheet()->getStyle('D' . $row . ':E' . $row)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $amt_usd);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $amt_khm);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $total_amt);
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(35);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'expenses_budget_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line('no_expense_budget_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function suggestion_expenses()
    {
        $term = $this->input->get('term', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed = $this->bpas->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $rows = $this->purchases_model->getExpenseNames($sr);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                $option = false;
                $row->quantity = 1;
                $row->unit_cost = 1;
                $row->description = $this->bpas->remove_tag($row->note);
                $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row);
                $r++;
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
    public function add($request_id = false){
        $this->bpas->checkPermissions('add', null);
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
        if($this->Settings->payment_expense == 1){
            $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');
        }
        $this->purchases_model->sysnceExpenseRequest($request_id);
        if ($this->form_validation->run() == true) {
            $biller_id      = $this->input->post('biller');
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller         = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $expenseAcc     = $this->site->getAccountSettingByBiller($biller_id);

            $default_payable    = $this->input->post('payable_account') ? $this->input->post('payable_account'): ($expenseAcc->default_payable ? $expenseAcc->default_payable: $this->accounting_setting->default_payable);
            $purchase_discount  = $expenseAcc->default_purchase_discount? $expenseAcc->default_purchase_discount:$this->accounting_setting->default_purchase_discount;
            $purchase_tax   = $expenseAcc->default_purchase_tax?$expenseAcc->default_purchase_tax:$this->accounting_setting->default_purchase_tax;

            $project_id = $this->input->post('project');
            $supplier_id = $this->input->post('supplier');
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            if ($this->Owner || $this->Admin || $this->bpas->GP['purchases-expenses-date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $accTrans = false;
            $payment = false;
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ex',$biller_id);
            $payment_status = 'pending';
            $payment_amount = 0;
            $total = 0;
            $grand_total = 0;
            $order_tax = 0;
            $order_discount = 0;
            $percentage = '%';
            $status = ($this->Settings->approval_expense==1 ? "pending" : "approved");
            $i = isset($_POST['expense_id']) ? sizeof($_POST['expense_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $subtotal = 0;
                $id = $_POST['expense_id'][$r];
                $code = $_POST['expense_code'][$r];
                $name = $_POST['expense_name'][$r];
                $desc = $_POST['description'][$r];
                $unit_cost = $_POST['unit_cost'][$r];
                $quantity = $_POST['quantity'][$r];
                if (isset($id) && isset($unit_cost) && isset($quantity)) {
                    $subtotal = $unit_cost * $quantity;
                    $total += $subtotal;
                    $items[] = array(
                        'category_id' => $id,
                        'category_code' => $code,
                        'category_name' => $name,
                        'description' => $desc,
                        'unit_cost' => $unit_cost,
                        'quantity' => $quantity,
                        'subtotal' => $subtotal
                    );
                    if($this->Settings->module_account == 1){
                        $expenseCategory = $this->purchases_model->getExpenseCategoryByID($id);
                        $accTrans[] = array(
                            'tran_type'     => 'Expense',
                            'tran_date'     => $date,
                            'reference_no'  => $reference,
                            'account_code'  =>  $expenseCategory->expense_account?$expenseCategory->expense_account:$expenseAcc->default_expense,
                            'amount'        => $subtotal,
                            'narrative'     => 'Expense '.$supplier,
                            'description'   => $desc,
                            'biller_id'     => $biller_id,
                            'project_id'    => $project_id,
                            'created_by'    => $this->session->userdata('user_id'),
                            'supplier_id'   => $supplier_id,
                        );
                    }
                }
            }
            
            if (empty($items)) {
                $this->form_validation->set_rules('expense', lang("order_items"), 'required');
            } else {
                krsort($items);
            }
            
            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->bpas->formatDecimalRaw(((($total) * (Float) ($ods[0])) / 100), 4);

                } else {
                    $order_discount = $this->bpas->formatDecimalRaw($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            
            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = (($total-$order_discount) * $order_tax_details->rate) / 100;
                    }
                }
            } else {
                $order_tax_id = null;
            }
            $grand_total = $total + $order_tax - $order_discount;
            $cash_account = $this->site->getCashAccountByCode($this->input->post('paid_by'));
            $paying_from = $cash_account->account_code;
            if($this->Settings->payment_expense == 0){
                $payment = array(
                            'date'          => $date,
                            'reference_no'  => $reference,
                            'amount'        => $grand_total,
                            'paid_by'       => $this->input->post('paid_by'),
                            'note'          => $this->input->post('note', true),
                            'created_by'    => $this->session->userdata('user_id'),
                            'type'          => 'expense',
                            'currencies'    => json_encode($currencies),
                            'account_code'  => $paying_from,
                        );
                $payment_status = 'paid';
                $payment_amount = $grand_total;             
            }
            $currency_rate = array();
            $exchange = $this->bpas->getAllCurrencies();
            if(!empty($exchange)){
                foreach($exchange as $camount){
                    $currency_rate[] = array(
                                "code" => $camount->code,
                                "rate" => $camount->rate,
                            );
                }
            }
            $data = array(
                'date'          => $date,
                'reference'     => $reference,
                'biller_id'     => $biller_id,
                'project_id'    => $project_id,
                'supplier_id'   => $supplier_id,
                'supplier'      => $supplier,
                'account_paying_from' => $paying_from,
                'amount'        => $total,
                'grand_total'   => $grand_total,
                'paid'          => $payment_amount,
                'payment_status' => $payment_status,
                'status'        => $status,
                'created_by'    => $this->session->userdata('user_id'),
                'note'          => $this->input->post('note', true),
                'warehouse_id'  => $this->input->post('warehouse', true),
                'bank_account'  => $default_payable,
                'currency'      => json_encode($currency_rate),
                'order_tax'     => $order_tax,
                'order_tax_id'  => $order_tax_id,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'paid_by' => $this->input->post('paid_by'),
                //'request_id' => $request_id,
            );
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
            if($this->Settings->module_account == 1){           
                if($this->Settings->payment_expense == 0){
                    $accTrans[] = array(
                        'tran_type'     => 'Expense',
                        'tran_date'     => $date,
                        'reference_no'  => $reference,
                        'account_code'  => $paying_from,
                        'amount'        => -$grand_total,
                        'narrative'     => 'Expense Payment',
                        'description'   => $this->input->post('note', true),
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'supplier_id'   => $supplier_id,
                    );
                }else{
                    $accTrans[] = array(
                        'tran_type'     => 'Expense',
                        'tran_date'     => $date,
                        'reference_no'  => $reference,
                        'account_code'  => $default_payable,
                        'amount'        => -$grand_total,
                        'narrative'     => 'Expense '.$supplier,
                        'description'   => $this->input->post('note', true),
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'supplier_id'   => $supplier_id,
                    );
                }
                
                if($order_discount > 0){
                    $accTrans[] = array(
                        'tran_type'     => 'Expense',
                        'tran_date'     => $date,
                        'reference_no'  => $reference,
                        'account_code'  => $purchase_discount,
                        'amount'        => -$order_discount,
                        'narrative'     => 'Order Discount '.$supplier,
                        'description'   => $this->input->post('note', true),
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'supplier_id'   => $supplier_id,
                    );
                }
                
                if($order_tax > 0){
                    $accTrans[] = array(
                        'tran_type'     => 'Expense',
                        'tran_date'     => $date,
                        'reference_no'  => $reference,
                        'account_code'  => $purchase_tax,
                        'amount'        => $order_tax,
                        'narrative'     => 'Tax Expense '.$supplier,
                        'description'   => $this->input->post('note', true),
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'supplier_id'   => $supplier_id,
                    );
                }
                
            }

        }
        
        if ($this->form_validation->run() == true && $this->purchases_model->add_Expense($data,$items, $accTrans,$payment)) {    
            $this->session->set_userdata('remove_expls', 1);
            $this->session->set_flashdata('message', $this->lang->line("expense_added") ." ". $reference);          
            if($this->input->post('add_expense_next')){
                admin_redirect('expenses/add');
            }else{
                admin_redirect('expenses');
            }
        } else {
            $pr = false;
            $request = $request_id ? $this->purchases_model->getExpenseRequestByID($request_id) : false;
            if($request && $request->status == "approved"){
                $request_items = $this->purchases_model->getExpenseRequestItems($request_id);
                krsort($request_items);
                $this->data['request'] = $request;
                $c = rand(100000, 9999999);
                foreach ($request_items as $request_item) {
                    $row = json_decode('{}');
                    $row->id = $request_item->category_id;
                    $row->code = $request_item->category_code;
                    $row->name = $request_item->category_name;
                    $row->description = $request_item->description;
                    $row->unit_cost = $request_item->unit_cost;
                    $row->quantity = $request_item->quantity;
                    $ri = $c;
                    $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row);
                    $c++;
                }
            }
            $this->data['request'] = $request;
            $this->data['request_items'] = json_encode($pr);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['billers'] = $this->site->getBillers();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            if($this->Settings->project == 1){
                $this->data['projects'] = $this->site->getAllProjects();
            }
            $this->data['payable_account'] = false;
            if($this->Settings->payment_expense == 0){
                $this->data['cash_account'] = true;
            }
            $this->data['bankAccounts']    = $this->accounts_model->getAllChartAccountIn('20,21');
            if($this->config->item('expense_request')){
                $this->data['expense_requests'] = $this->site->getRefExpenseRequests('approved');
            }
            $this->data['exnumber']   = $this->site->getReference('ex');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases/expenses'), 'page' => lang('expenses')), array('link' => '#', 'page' => lang('add_expense')));
            $meta = array('page_title' => lang('add_expense'), 'bc' => $bc);
            $this->page_construct('expenses/add', $meta, $this->data);
        }
    }
    public function add_expense($request_id = false){
        $this->bpas->checkPermissions('add', null);
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
        if($this->Settings->payment_expense == 1){
            $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');
        }
        $this->purchases_model->sysnceExpenseRequest($request_id);
        if ($this->form_validation->run() == true) {
            $biller_id      = $this->input->post('biller');
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller         = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $expenseAcc     = $this->site->getAccountSettingByBiller($biller_id);

            $default_payable    = $this->input->post('payable_account') ? $this->input->post('payable_account'): ($expenseAcc->default_payable ? $expenseAcc->default_payable: $this->accounting_setting->default_payable);
            $purchase_discount  = $expenseAcc->default_purchase_discount? $expenseAcc->default_purchase_discount:$this->accounting_setting->default_purchase_discount;
            $purchase_tax   = $expenseAcc->default_purchase_tax?$expenseAcc->default_purchase_tax:$this->accounting_setting->default_purchase_tax;

            $project_id = $this->input->post('project');
            $supplier_id = $this->input->post('supplier');
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            if ($this->Owner || $this->Admin || $this->bpas->GP['purchases-expenses-date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $accTrans = false;
            $payment = false;
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ex',$biller_id);
            $payment_status = 'pending';
            $payment_amount = 0;
            $total = 0;
            $grand_total = 0;
            $order_tax = 0;
            $order_discount = 0;
            $percentage = '%';
            $status = ($this->Settings->approval_expense==1 ? "pending" : "approved");
            $i = isset($_POST['expense_id']) ? sizeof($_POST['expense_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $subtotal = 0;
                $id = $_POST['expense_id'][$r];
                $code = $_POST['expense_code'][$r];
                $name = $_POST['expense_name'][$r];
                $desc = $_POST['description'][$r];
                $unit_cost = $_POST['unit_cost'][$r];
                $quantity = $_POST['quantity'][$r];
                if (isset($id) && isset($unit_cost) && isset($quantity)) {
                    $subtotal = $unit_cost * $quantity;
                    $total += $subtotal;
                    $items[] = array(
                        'category_id' => $id,
                        'category_code' => $code,
                        'category_name' => $name,
                        'description' => $desc,
                        'unit_cost' => $unit_cost,
                        'quantity' => $quantity,
                        'subtotal' => $subtotal
                    );
                    if($this->Settings->module_account == 1){
                        $expenseCategory = $this->purchases_model->getExpenseCategoryByID($id);
                        $accTrans[] = array(
                            'tran_type'     => 'Expense',
                            'tran_date'     => $date,
                            'reference_no'  => $reference,
                            'account_code'  =>  $expenseCategory->expense_account?$expenseCategory->expense_account:$expenseAcc->default_expense,
                            'amount'        => $subtotal,
                            'narrative'     => 'Expense '.$supplier,
                            'description'   => $desc,
                            'biller_id'     => $biller_id,
                            'project_id'    => $project_id,
                            'created_by'    => $this->session->userdata('user_id'),
                            'supplier_id'   => $supplier_id,
                        );
                    }
                }
            }
            
            if (empty($items)) {
                $this->form_validation->set_rules('expense', lang("order_items"), 'required');
            } else {
                krsort($items);
            }
            
            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->bpas->formatDecimalRaw(((($total) * (Float) ($ods[0])) / 100), 4);

                } else {
                    $order_discount = $this->bpas->formatDecimalRaw($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            
            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = (($total-$order_discount) * $order_tax_details->rate) / 100;
                    }
                }
            } else {
                $order_tax_id = null;
            }
            $grand_total = $total + $order_tax - $order_discount;
            $cash_account = $this->site->getCashAccountByCode($this->input->post('paid_by'));
            $paying_from = $cash_account->account_code;
            if($this->Settings->payment_expense == 0){
                $payment = array(
                            'date'          => $date,
                            'reference_no'  => $reference,
                            'amount'        => $grand_total,
                            'paid_by'       => $this->input->post('paid_by'),
                            'note'          => $this->input->post('note', true),
                            'created_by'    => $this->session->userdata('user_id'),
                            'type'          => 'expense',
                            'currencies'    => json_encode($currencies),
                            'account_code'  => $paying_from,
                        );
                $payment_status = 'paid';
                $payment_amount = $grand_total;             
            }
            $currency_rate = array();
            $exchange = $this->bpas->getAllCurrencies();
            if(!empty($exchange)){
                foreach($exchange as $camount){
                    $currency_rate[] = array(
                                "code" => $camount->code,
                                "rate" => $camount->rate,
                            );
                }
            }
            $data = array(
                'date'          => $date,
                'reference'     => $reference,
                'biller_id'     => $biller_id,
                'project_id'    => $project_id,
                'supplier_id'   => $supplier_id,
                'supplier'      => $supplier,
                'account_paying_from' => $paying_from,
                'amount'        => $total,
                'grand_total'   => $grand_total,
                'paid'          => $payment_amount,
                'payment_status' => $payment_status,
                'status'        => $status,
                'created_by'    => $this->session->userdata('user_id'),
                'note'          => $this->input->post('note', true),
                'warehouse_id'  => $this->input->post('warehouse', true),
                'bank_account'  => $default_payable,
                'currency'      => json_encode($currency_rate),
                'order_tax'     => $order_tax,
                'order_tax_id'  => $order_tax_id,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'paid_by' => $this->input->post('paid_by'),
                //'request_id' => $request_id,
            );
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
            if($this->Settings->module_account == 1){           
                if($this->Settings->payment_expense == 0){
                    $accTrans[] = array(
                        'tran_type'     => 'Expense',
                        'tran_date'     => $date,
                        'reference_no'  => $reference,
                        'account_code'  => $paying_from,
                        'amount'        => -$grand_total,
                        'narrative'     => 'Expense Payment',
                        'description'   => $this->input->post('note', true),
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'supplier_id'   => $supplier_id,
                    );
                }else{
                    $accTrans[] = array(
                        'tran_type'     => 'Expense',
                        'tran_date'     => $date,
                        'reference_no'  => $reference,
                        'account_code'  => $default_payable,
                        'amount'        => -$grand_total,
                        'narrative'     => 'Expense '.$supplier,
                        'description'   => $this->input->post('note', true),
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'supplier_id'   => $supplier_id,
                    );
                }
                
                if($order_discount > 0){
                    $accTrans[] = array(
                        'tran_type'     => 'Expense',
                        'tran_date'     => $date,
                        'reference_no'  => $reference,
                        'account_code'  => $purchase_discount,
                        'amount'        => -$order_discount,
                        'narrative'     => 'Order Discount '.$supplier,
                        'description'   => $this->input->post('note', true),
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'supplier_id'   => $supplier_id,
                    );
                }
                
                if($order_tax > 0){
                    $accTrans[] = array(
                        'tran_type'     => 'Expense',
                        'tran_date'     => $date,
                        'reference_no'  => $reference,
                        'account_code'  => $purchase_tax,
                        'amount'        => $order_tax,
                        'narrative'     => 'Tax Expense '.$supplier,
                        'description'   => $this->input->post('note', true),
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'supplier_id'   => $supplier_id,
                    );
                }
                
            }

        }
        
        if ($this->form_validation->run() == true && $this->purchases_model->add_Expense($data,$items, $accTrans,$payment)) {    
            $this->session->set_userdata('remove_expls', 1);
            $this->session->set_flashdata('message', $this->lang->line("expense_added") ." ". $reference);          
            if($this->input->post('add_expense_next')){
                admin_redirect('expenses/add');
            }else{
                admin_redirect('expenses');
            }
        } else {
            $pr = false;
            $request = $request_id ? $this->purchases_model->getExpenseRequestByID($request_id) : false;
            if($request && $request->status == "approved"){
                $request_items = $this->purchases_model->getExpenseRequestItems($request_id);
                krsort($request_items);
                $this->data['request'] = $request;
                $c = rand(100000, 9999999);
                foreach ($request_items as $request_item) {
                    $row = json_decode('{}');
                    $row->id = $request_item->category_id;
                    $row->code = $request_item->category_code;
                    $row->name = $request_item->category_name;
                    $row->description = $request_item->description;
                    $row->unit_cost = $request_item->unit_cost;
                    $row->quantity = $request_item->quantity;
                    $ri = $c;
                    $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row);
                    $c++;
                }
            }
            $this->data['request'] = $request;
            $this->data['request_items'] = json_encode($pr);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['billers'] = $this->site->getBillers();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            if($this->Settings->project == 1){
                $this->data['projects'] = $this->site->getAllProjects();
            }
            $this->data['payable_account'] = false;
            if($this->Settings->payment_expense == 0){
                $this->data['cash_account'] = true;
            }
            $this->data['bankAccounts']    = $this->accounts_model->getAllChartAccountIn('20,21');
            if($this->config->item('expense_request')){
                $this->data['expense_requests'] = $this->site->getRefExpenseRequests('approved');
            }
            $this->data['exnumber']   = $this->site->getReference('ex');
            $this->data['modal_js']     = $this->site->modal_js();
            //$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases/expenses'), 'page' => lang('expenses')), array('link' => '#', 'page' => lang('add_expense')));
            $meta = array('page_title' => lang('add_expense'), 'bc' => $bc);
            $this->load->view($this->theme . 'expenses/add_expense',$this->data);
        }
    }
    public function edit($id = null){
        $this->bpas->checkPermissions('edit', null);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $purchase = $this->purchases_model->getExpenseByID($id);
        if (($purchase->paid >0 || ($purchase->payment_status != 'pending')) && $this->Settings->payment_expense ==1) {
            $this->session->set_flashdata('error', lang('expense_can_not_edit'));
            $this->bpas->md();
        }

        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
        if($this->Settings->payment_expense == 1){
            $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');
        }
        if ($this->form_validation->run() == true) {
            $biller_id      = $this->input->post('biller');
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller         = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $project_id     = $this->input->post('project');
            $expenseAcc     = $this->site->getAccountSettingByBiller($biller_id);
            $default_payable    = $this->input->post('payable_account') ? $this->input->post('payable_account'): ($expenseAcc->default_payable ? $expenseAcc->default_payable: $this->accounting_setting->default_payable);
            $purchase_discount  = $expenseAcc->default_purchase_discount? $expenseAcc->default_purchase_discount:$this->accounting_setting->default_purchase_discount;
            $purchase_tax   = $expenseAcc->default_purchase_tax?$expenseAcc->default_purchase_tax:$this->accounting_setting->default_purchase_tax;

            $supplier_id = $this->input->post('supplier')?$this->input->post('supplier'):null;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;

            if ($this->Owner || $this->Admin || $this->bpas->GP['purchases-expenses-date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ex',$biller_id);
            $payment_status = 'pending';
            $payment_amount = 0;
            $total = 0;
            $grand_total = 0;
            $order_tax = 0;
            $order_discount = 0;
            $percentage = '%';
            $payment = false;
            $accTrans = false;
            $i = isset($_POST['expense_id']) ? sizeof($_POST['expense_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $subtotal = 0;
                $cat_id = $_POST['expense_id'][$r];
                $code = $_POST['expense_code'][$r];
                $name = $_POST['expense_name'][$r];
                $desc = $_POST['description'][$r];
                $unit_cost = $_POST['unit_cost'][$r];
                $quantity = $_POST['quantity'][$r];
                if (isset($id) && isset($unit_cost) && isset($quantity)) {
                    $subtotal = $unit_cost * $quantity;
                    $total += $subtotal;
                    $items[] = array(
                        'expense_id' => $id,
                        'category_id' => $cat_id,
                        'category_code' => $code,
                        'category_name' => $name,
                        'description' => $desc,
                        'unit_cost' => $unit_cost,
                        'quantity' => $quantity,
                        'subtotal' => $subtotal
                    );
                    if($this->Settings->module_account == 1){
                        $expenseCategory = $this->purchases_model->getExpenseCategoryByID($cat_id);
                        $accTrans[] = array(
                            'tran_no'   => $id,
                            'tran_type' => 'Expense',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' =>  $expenseCategory->expense_account?$expenseCategory->expense_account: $expenseAcc->default_expense,
                            'amount'        => $subtotal,
                            'narrative'     => 'Expense ',
                            'description'   => $desc,
                            'biller_id'     => $biller_id,
                            'project_id'    => $project_id,
                            'created_by'    => $this->session->userdata('user_id'),
                            'supplier_id'   => $supplier_id,
                        );
                    }
                }
            }
            
            if (empty($items)) {
                $this->form_validation->set_rules('expense', lang("order_items"), 'required');
            } else {
                krsort($items);
            }
            
            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->bpas->formatDecimalRaw(((($total) * (Float) ($ods[0])) / 100), 4);

                } else {
                    $order_discount = $this->bpas->formatDecimalRaw($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            
            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = (($total-$order_discount) * $order_tax_details->rate) / 100;
                    }
                }
            } else {
                $order_tax_id = null;
            }
            $grand_total = $total + $order_tax - $order_discount;
            
             $currency_rate = array();
            $exchange = $this->bpas->getAllCurrencies();
            if(!empty($exchange)){
                foreach($exchange as $camount){
                    $currency_rate[] = array(
                                "code" => $camount->code,
                                "rate" => $camount->rate,
                            );
                }
            }
               
            $data = array(
                'date' => $date,
                'reference' => $reference,
                'biller_id' => $biller_id,
                'project_id' => $project_id,
                'supplier_id' => $supplier_id,
                'amount' => $total,
                'grand_total' => $grand_total,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date("Y-m-d H:i"),
                'note' => $this->input->post('note', true),
                'warehouse_id' => $this->input->post('warehouse', true),
                'bank_account' => ($this->Settings->default_payable_account == 0 ? $expenseAcc->ap_acc : $this->input->post('payable_account')),
                'order_tax' => $order_tax,
                'order_tax_id' => $order_tax_id,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'paid_by' => $this->input->post('paid_by'),
                'currency'      => json_encode($currency_rate),
            );
            if($this->Settings->payment_expense == 0){
                $cash_account = $this->site->getCashAccountByCode($this->input->post('paid_by'));
                $paying_from = $cash_account->account_code;
                $payment = array(
                            'expense_id' => $id,
                            'date'      => $date,
                            'reference_no' => $reference,
                            'amount'    => $grand_total,
                            'paid_by'   => $this->input->post('paid_by'),
                            'note'      => $this->input->post('note', true),
                            'updated_by' => $this->session->userdata('user_id'),
                            'updated_at' => date("Y-m-d H:i"),
                            'type'      => 'expense',
                            'currencies' => json_encode($currencies),
                            'account_code' => $paying_from,
                        );
                
                $payment_status = 'paid';
                $data['payment_status'] = $payment_status;
                $data['paid']   = $grand_total;             
                $data['account_paying_from']   = $paying_from;             
            }
            
            if($this->config->item("vehicle")){
                $data['vehicle_id'] = $this->input->post("vehicle");
            }
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
            
            
            
            if($this->Settings->module_account == 1){           
                if($this->Settings->payment_expense == 0){
                    $accTrans[] = array(
                        'tran_no' => $id,
                        'tran_type' => 'Expense',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $paying_from,
                        'amount' => -$grand_total,
                        'narrative' => 'Expense Payment',
                        'description' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'created_by' => $this->session->userdata('user_id'),
                        'supplier_id' => $supplier_id,
                    );
                }else{
                    $accTrans[] = array(
                        'tran_no' => $id,
                        'tran_type' => 'Expense',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $default_payable,
                        'amount' => -$grand_total,
                        'narrative' => 'Expense '.$supplier,
                        'description' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'created_by' => $this->session->userdata('user_id'),
                        'supplier_id' => $supplier_id,
                    );
                }
                
                if($order_discount > 0){
                    $accTrans[] = array(
                        'tran_no' => $id,
                        'tran_type' => 'Expense',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $purchase_discount,
                        'amount' => -$order_discount,
                        'narrative' => 'Order Discount '.$supplier,
                        'description' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'created_by' => $this->session->userdata('user_id'),
                        'supplier_id' => $supplier_id,
                    );
                }
                
                if($order_tax > 0){
                    $accTrans[] = array(
                        'tran_no' => $id,
                        'tran_type' => 'Expense',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $purchase_tax,
                        'amount' => $order_tax,
                        'narrative' => 'Tax Expense '.$supplier,
                        'description' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'created_by' => $this->session->userdata('user_id'),
                        'supplier_id' => $supplier_id,
                    );
                }
                
            }
        }
        
        
        if ($this->form_validation->run() == true && $this->purchases_model->update_Expense($id,$data,$items,$accTrans,$payment)) {  
            $this->session->set_userdata('remove_expls', 1);
            $this->session->set_flashdata('message', $this->lang->line("expense_updated") ." ". $reference);    
            admin_redirect('expenses');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $expenses = $this->purchases_model->getExpenseByID($id);
            $expense_items = $this->purchases_model->getExpenseItems($id);
            krsort($expense_items);
            $c = rand(100000, 9999999);
            foreach ($expense_items as $expense_item) {
                $row = json_decode('{}');
                $row->id = $expense_item->category_id;
                $row->code = $expense_item->category_code;
                $row->name = $expense_item->category_name;
                $row->name = $expense_item->category_name;
                $row->description = $expense_item->description;
                $row->unit_cost = $expense_item->unit_cost;
                $row->quantity = $expense_item->quantity;
                $ri = $c;
                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row);
                $c++;
            }
            $this->session->set_userdata('remove_expls', 1);
            $this->data['expenses'] = $expenses;
            $this->data['expense_items'] = json_encode($pr);
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['billers'] = $this->site->getBillers();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            
            if($this->Settings->project == 1){
                $this->data['projects'] = $this->site->getAllProjects();
            }
            $this->data['payable_account'] = false;
            if($this->Settings->payment_expense == 0){
                $this->data['cash_account'] = true;
            }
            $this->data['bankAccounts']    = $this->accounts_model->getAllChartAccountIn('20,21');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases/expenses'), 'page' => lang('expenses')), array('link' => '#', 'page' => lang('edit_expense')));
            $meta = array('page_title' => lang('edit_expense'), 'bc' => $bc);
            $this->page_construct('expenses/edit', $meta, $this->data);
        }
    }
    public function expense_categories()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('expense_categories')]];
        $meta                = ['page_title' => lang('categories'), 'bc' => $bc];
        $this->page_construct('settings/expense_categories', $meta, $this->data);
    }
    public function add_payment($id = null)
    {
        $this->bpas->checkPermissions('payments', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $purchase = $this->purchases_model->getExpenseByID($id);
        if ($purchase->payment_status == 'paid' && $purchase->grand_total == $purchase->paid) {
            $this->session->set_flashdata('error', lang('expense_already_paid'));
            $this->bpas->md();
        }
        //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang('amount'), 'required');
        $this->form_validation->set_rules('paid_by', lang('paid_by'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $paid_by = $this->site->getCashAccountByCode($this->input->post('paid_by'));
            $paid_by_account = $paid_by->account_code;

            if ($this->input->post('paid_by') == 'deposit') {
                //$customer_id = $sale->customer_id;
                if (!$this->site->check_customer_deposit($purchase->supplier_id, $this->input->post('amount-paid'))) {
                    $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
            
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            // $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay');
            $reference_no = $this->site->CheckedPaymentReference($this->input->post('reference_no'), $this->site->getReference('ppay'));
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

            $payment = [
                'date'         => $date,
                'expense_id'   => $this->input->post('expense_id'),
                'reference_no' => $reference_no,
                'amount'       => $this->input->post('amount-paid'),
                'discount'     => $this->input->post('discount'),
                'paid_by'      => $this->input->post('paid_by'),
                'cheque_no'    => $this->input->post('cheque_no'),
                'cc_no'        => $this->input->post('pcc_no'),
                'cc_holder'    => $this->input->post('pcc_holder'),
                'cc_month'     => $this->input->post('pcc_month'),
                'cc_year'      => $this->input->post('pcc_year'),
                'cc_type'      => $this->input->post('pcc_type'),
                'note'         => $this->bpas->clear_tags($this->input->post('note')),
                'created_by'   => $this->session->userdata('user_id'),
                'type'         => 'sent',
                'bank_account' => $paid_by_account,
                'currencies'   => json_encode($currencies),
            ];

            //=====accountig=====//
            if($this->Settings->module_account == 1){
                $biller_id = $purchase->biller_id ? $purchase->biller_id: $this->Settings->default_biller;
                $account_payable = $purchase->bank_account? $purchase->bank_account:$this->accounting_setting->default_payable;
                $narrative = $this->site->getAccountName($account_payable);

                $payment_from_account =  $paid_by_account;
       
                $accTranPayments[] = array(
                        'tran_type'     => 'Payment',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $account_payable,
                        'amount'        => ($this->input->post('amount-paid')+$this->input->post('discount')),
                        'narrative'     => $narrative,
                        'description'   => $this->input->post('note'),
                        'biller_id'     => $biller_id,
                        'project_id'    => $purchase->project_id,
                        'supplier_id'   => $purchase->supplier_id,
                        'created_by'    => $this->session->userdata('user_id')
                    );
                $accTranPayments[] = array(
                        'tran_type'     => 'Payment',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $payment_from_account,
                        'amount'        => $this->input->post('amount-paid') * (-1),
                        'narrative'     => $this->site->getAccountName($payment_from_account),
                        'description'   => $this->input->post('note'),
                        'biller_id'     => $biller_id,
                        'project_id'    => $purchase->project_id,
                        'supplier_id'   => $purchase->supplier_id,
                        'created_by'    => $this->session->userdata('user_id')
                    );
                if($this->input->post('discount') != 0){
                    $accTranPayments[] = array(
                        'tran_type'     => 'Payment',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $this->accounting_setting->default_purchase_discount,
                        'amount'        => $this->input->post('discount') * (-1),
                        'narrative'     => 'Purchase Payment Discount '.$reference_no,
                        'description'   => $this->input->post('note'),
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'supplier_id'   => $purchase->supplier_id,
                        'created_by'    => $this->session->userdata('user_id')
                    );
                }
            }
            //=====end accountig=====//

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

        if ($this->form_validation->run() == true) {
            if ($this->purchases_model->addExpensePayment($payment,$accTranPayments)) {
                $this->session->set_flashdata('message', lang('payment_added'));
                admin_redirect('expenses');

            }else{
                $this->session->set_flashdata('error', lang('payment_request_fail_submited'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->data['error']       = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv']         = $purchase;
            $this->data['payment_ref'] = $this->site->getReference('ppay');
            $this->data['modal_js']    = $this->site->modal_js();
            $this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'expenses/add_payment', $this->data);
        }
    }
    public function edit_payment($id = null)
    {
        $this->bpas->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $purchase_id = $this->input->post('expense_id');
        $purchase = $this->purchases_model->getExpenseByID($purchase_id);
        
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('amount-paid', lang('amount'), 'required');
        $this->form_validation->set_rules('paid_by', lang('paid_by'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $paid_by = $this->site->getCashAccountByCode($this->input->post('paid_by'));
            $paid_by_account = $paid_by->account_code;

            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
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
            $payment = [
                'date'         => $date,
                'expense_id'   => $this->input->post('expense_id'),
                'reference_no' => $this->input->post('reference_no'),
                'amount'       => $this->input->post('amount-paid'),
                'discount'     => $this->input->post('discount'),
                'paid_by'      => $this->input->post('paid_by'),
                'cheque_no'    => $this->input->post('cheque_no'),
                'cc_no'        => $this->input->post('pcc_no'),
                'cc_holder'    => $this->input->post('pcc_holder'),
                'cc_month'     => $this->input->post('pcc_month'),
                'cc_year'      => $this->input->post('pcc_year'),
                'cc_type'      => $this->input->post('pcc_type'),
                'note'         => $this->bpas->clear_tags($this->input->post('note')),
                'bank_account' => $paid_by_account,
                'currencies'   => json_encode($currencies),

            ];
            //=====accountig=====//
            if($this->Settings->module_account == 1){
                $biller_id = $purchase->biller_id ? $purchase->biller_id: $this->Settings->default_biller;
                $account_payable = $purchase->bank_account? $purchase->bank_account:$this->accounting_setting->default_payable;

                $accTranPayments[] = array(
                    'tran_type'     => 'Payment',
                    'tran_no'       => $id,
                    'tran_date'     => $date,
                    'reference_no'  => $this->input->post('reference_no')?$this->input->post('reference_no'):$this->site->getReference('ppay'),
                    'account_code'  => $account_payable,
                    'amount'        => ($this->input->post('amount-paid') + $this->input->post('discount')),
                    'narrative'     => $this->site->getAccountName($account_payable),
                    'description'   => $this->input->post('note'),
                    'biller_id'     => $biller_id,
                    'project_id'    => $purchase->project_id,
                    'supplier_id'   => $purchase->supplier_id,
                    'created_by'    => $this->session->userdata('user_id')
                );
                $accTranPayments[] = array(
                    'tran_type'     => 'Payment',
                    'tran_no'       => $id,
                    'tran_date'     => $date,
                    'reference_no'  => $this->input->post('reference_no')?$this->input->post('reference_no'):$this->site->getReference('ppay'),
                    'account_code'  => $paid_by_account,
                    'amount'        => $this->input->post('amount-paid') * (-1),
                    'narrative'     => $this->site->getAccountName($paid_by_account),
                    'description'   => $this->input->post('note'),
                    'biller_id'     => $biller_id,
                    'project_id'    => $purchase->project_id,
                    'supplier_id'   => $purchase->supplier_id,
                    'created_by'    => $this->session->userdata('user_id')
                );
                if($this->input->post('discount') != 0){
                    $accTranPayments[] = array(
                        'tran_type'     => 'Payment',
                        'tran_no'       => $id,
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $this->accounting_setting->default_purchase_discount,
                        'amount'        => $this->input->post('discount') * (-1),
                        'narrative'     => 'Purchase Payment Discount '.$reference_no,
                        'description'   => $this->input->post('note'),
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'supplier_id'   => $purchase->supplier_id,
                        'created_by'    => $this->session->userdata('user_id')
                    );
                }
            }
            //=====end accountig=====//
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
        } elseif ($this->input->post('edit_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->updateExpensePayment($id, $payment,$accTranPayments)) {
            $this->session->set_flashdata('message', lang('payment_updated'));
            admin_redirect('expenses');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['payment']  = $this->purchases_model->getPaymentByID($id);
            
            $this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'expenses/edit_payment', $this->data);
        }
    }
    public function delete_payment($id = null)
    {
        $this->bpas->checkPermissions('delete', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->purchases_model->deleteExpensePayment($id)) {
            //account---
            $this->site->deleteAccTran('Payment', $id);
            //---end account
            $this->session->set_flashdata('message', lang('payment_deleted'));
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function payment_note($id = null)
    {
        $this->bpas->checkPermissions('payments', true);
        $payment = $this->purchases_model->getPaymentByID($id);
        //$inv_payments = $this->purchases_model->getPaymentsByRef($payment->reference_no,$payment->date);

        $inv = $this->purchases_model->getExpenseByID($payment->expense_id);
        $inv->grand_total = $inv->amount;
        
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;

        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['payment'] = $payment;
        $this->data['page_title'] = $this->lang->line("payment_note");
        
        if($this->Owner || $this->Admin || $this->bpas->GP['unlimited-print']){
            $this->data['print'] = 0;
        }else{
            if($this->Settings->limit_print=='1' && $this->site->checkPrint('Purchase Payment',$payment->id)){
                $this->data['print'] = 1;
            }else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Purchase Payment',$payment->id)){
                $this->data['print'] = 2;
            }else{
                $this->data['print'] = 0;
            }
        }
        
        $this->load->view($this->theme . 'expenses/payment_note', $this->data);
    }
    public function budgets($biller_id = null)
    {
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

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

        $this->data['warehouse_id'] = null;
        $this->data['warehouses']   = $this->site->getAllWarehouses();
        
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('budgets')]];
        $meta                = ['page_title' => lang('budgets'), 'bc' => $bc];
        $this->page_construct('expenses/budgets', $meta, $this->data);
    }
    public function getBudgets($biller_id = null)
    {
        $this->bpas->checkPermissions('budgets');
        if ((!$this->Owner && !$this->Admin) && !$biller_id) {
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $biller_id = $user->multi_biller;
            } else {
                $biller_id = $user->biller_id;
            }
        }
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : null;
        $project_id   = $this->input->get('project') ? $this->input->get('project') : null;
        $start_date   = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date     = $this->input->get('end_date') ? $this->input->get('end_date') : null;
        $biller       = $this->input->get('project') ? $this->input->get('project') : null;
        $title        = $this->input->get('title') ? $this->input->get('title') : null;

        if ($start_date) {
            $start_date = $this->bpas->fsd($start_date);
            $end_date   = $this->bpas->fsd($end_date);
        }

        $edit_link   = anchor('admin/expenses/edit_budget/$1', '<i class="fa fa-edit"></i> ' . lang('edit_budget'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $delete_link = "<a href='#' class='po' title='" . $this->lang->line('delete_budget') . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('expenses/delete_budget/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_budget') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
                <ul class="dropdown-menu pull-right" role="menu">
                    <li>' . $edit_link . '</li>
                    <li>' . $delete_link . '</li>
                </ul>
            </div></div>';

        $this->load->library('datatables');
        $this->datatables
            ->select(
                $this->db->dbprefix('budgets').".id as id, 
                {$this->db->dbprefix('budgets')}.date,
                concat({$this->db->dbprefix('companies')}.company,'/',{$this->db->dbprefix('companies')}.name) as biller, 
                {$this->db->dbprefix('projects')}.project_name as project, 
                reference, 
                title, 
                sum(amount) as amount, 
                {$this->db->dbprefix('budgets')}.attachment", false)
            ->from('budgets')
            ->join('users', 'users.id = budgets.created_by', 'left')
            ->join('companies', 'companies.id = budgets.biller_id', 'left')
            ->join('projects', 'projects.project_id = budgets.project_id', 'left')
            ->group_by('budgets.reference');

        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }

        if ($reference_no) {
            $this->datatables->where('budgets.reference', $reference_no);
        }
        if ($title) {
            $this->datatables->like('budgets.title', $title, 'both');
        }
        if ($project_id) {
            $this->datatables->where('budgets.project_id', $project_id);
        }
        if ($biller_id) {
            $this->datatables->where('budgets.biller_id IN ('.$biller_id.')');
        }
        if ($start_date) {
            $this->datatables->where('budgets' . '.date BETWEEN "' . $start_date . ' 00:00:00" and "' . $end_date . ' 23:59:00"');
        }

        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
    public function budget_actions()
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
                        $this->data['budget']            = $this->purchases_model->getBudgetByID($id);
                        $this->data['budgetByReference'] = $this->purchases_model->getBudgetByReference($this->data['budget']->reference);
                        foreach ($this->data['budgetByReference'] as $value) {
                            $this->purchases_model->deleteBudgetByReference($value->reference);
                        }
                    }
                    $this->session->set_flashdata('message', $this->lang->line('budgets_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('budgets'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('title'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('amount'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('created_by'));

                    $row = 2;
                    foreach($_POST['val'] as $id){
                        $this->data['budget']            = $this->purchases_model->getBudgetByID($id);
                        $this->data['budgetByReference'] = $this->purchases_model->getBudgetByReference($this->data['budget']->reference);
                        foreach ($this->data['budgetByReference'] as $value) {
                            $user = $this->site->getUser($value->created_by);
                            $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($value->date));
                            $this->excel->getActiveSheet()->SetCellValue('B' . $row, $value->reference);
                            $this->excel->getActiveSheet()->SetCellValue('C' . $row, $value->title);
                            $this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatMoney($value->amount));
                            $this->excel->getActiveSheet()->SetCellValue('E' . $row, strip_tags($value->note));
                            $this->excel->getActiveSheet()->SetCellValue('F' . $row, $user->first_name . ' ' . $user->last_name);
                            $row++;
                        }   
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(35);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'budgets_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line('no_budget_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function add_budget()
    {
        $this->bpas->checkPermissions('budgets', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('amount', lang('amount'), 'required');
        $this->form_validation->set_rules('title', lang('title'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        $this->form_validation->set_rules('reference', lang("reference"), 'required|is_unique[expenses.reference]');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $data = [
                'date'         => $date,
                'reference'    => $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('ex'),
                'title'        => $this->input->post('title'),
                'amount'       => $this->input->post('amount'),
                'created_by'   => $this->session->userdata('user_id'),
                'note'         => $this->input->post('note', true),
                'category_id'  => $this->input->post('category', true),
                'warehouse_id' => $this->input->post('warehouse', true),
                'project_id'   => $this->input->post('project'),
                'biller_id'    => $this->input->post('biller')
            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
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
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        } elseif ($this->input->post('add_budget')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->purchases_model->addBudget($data)) {
            $this->session->set_flashdata('message', lang('budget_added'));
            admin_redirect('expenses/budgets');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['ref']      = $this->site->getReference('bg');
            if ($this->Owner || $this->Admin) {
                $this->data['projects'] = $this->site->getAllProject();
            }else{
                $this->data['projects'] = $this->site->getAllProject($this->session->userdata('warehouse_id'));
            }
            
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->data['warehouses']       = $this->site->getAllWarehouses();

            $this->data['users']        = $this->site->getStaff();
            $this->data['modal_js']   = $this->site->modal_js();
            $this->load->view($this->theme . 'expenses/add_budget', $this->data);
        }
    }
    public function edit_budget($id = null)
    {
        $this->bpas->checkPermissions('edit', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('reference', lang("reference"), 'required');
        $this->form_validation->set_rules('title', lang('title'), 'required');
        $this->form_validation->set_rules('amount', lang('amount'), 'required');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $this->data['budget']    = $this->purchases_model->getBudgetByID($id);
            $this->data['budgetByReference']    = $this->purchases_model->getBudgetByReference($this->data['budget']->reference);

            $data = [
                'date'         => $date,
                'reference'    => $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('ex'),
                'title'        => $this->input->post('title'),
                'amount'       => $this->input->post('amount'),
                'created_by'   => $this->session->userdata('user_id'),
                'note'         => $this->input->post('note', true),
                'category_id'  => $this->input->post('category', true),
                'warehouse_id' => $this->input->post('warehouse', true),
                'project_id'   => $this->input->post('project'),
                'biller_id'    => $this->input->post('biller')
            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
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
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        } elseif ($this->input->post('add_budget'))  {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true &&  $this->purchases_model->updateBudgetByReference($id, $data)) {
            $this->session->set_flashdata('message', lang('budget_updated'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['budget']     = $this->purchases_model->getBudgetByID($id);
            $this->data['budgetByReference']    = $this->purchases_model->getBudgetByReference($this->data['budget']->reference);
            if ($this->Owner || $this->Admin) {
                $this->data['projects'] = $this->site->getAllProject();
            }else{
                $this->data['projects'] = $this->site->getAllProject($this->session->userdata('warehouse_id'));
            }
            
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->data['warehouses']       = $this->site->getAllWarehouses();

            $this->data['users']        = $this->site->getStaff();
            $this->data['modal_js']   = $this->site->modal_js();
            $this->load->view($this->theme . 'expenses/edit_budget', $this->data);
        }
    }
    public function delete_budget($id = null)
    {
        $this->bpas->checkPermissions('delete', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['budget'] = $this->purchases_model->getBudgetByID($id);
        $this->data['budgetByReference'] = $this->purchases_model->getBudgetByReference($this->data['budget']->reference);
        $referenceID = $this->data['budget']->reference;
        $budget      = $this->purchases_model->getBudgetByID($id);
        if($this->purchases_model->deleteBudgetByReference($referenceID)){
            if ($budget->attachment) {
                unlink($this->upload_path . $budget->attachment);
            }
        }  
        $this->bpas->send_json(['error' => 0, 'msg' => lang('budget_deleted')]);
        redirect($_SERVER['HTTP_REFERER']);
    }
}
?>