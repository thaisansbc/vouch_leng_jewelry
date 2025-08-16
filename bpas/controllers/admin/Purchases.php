<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Purchases extends MY_Controller
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
        $this->load->admin_model('products_model');
        $this->load->admin_model('purchases_model');
        $this->load->admin_model('purchases_order_model');
        $this->load->admin_model('accounts_model');
        $this->load->admin_model('approved_model');
        $this->load->admin_model('accounts_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path         = 'assets/uploads/';
        $this->thumbs_path         = 'assets/uploads/thumbs/';
        $this->image_types         = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types  = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size   = '1024';
        $this->data['logo']        = true;
    }
    public function getchange_base_unit_cose()
    {
        if ($this->input->get('unit_id')) {
            $id = $this->input->get('unit_id');
        }
        if ($this->input->get('product_id')) {
            $product_id = $this->input->get('product_id');
        }
        $data = $this->site->getUnitProductByID($id, $product_id);

        echo json_encode($data);
    } 
    public function getchange_base_unit_cost()
    {
        if ($this->input->get('unit_id')) {
            $id = $this->input->get('unit_id');
        }
        if ($this->input->get('product_id')) {
            $product_id = $this->input->get('product_id');
        }
        $data = $this->site->getUnitProductByID($id, $product_id);

        echo json_encode($data);
    }
    public function add_payment($id = null)
    {
        $this->bpas->checkPermissions('payments', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $ap_requested_id = $this->input->post('ap_requested_id') ? $this->input->post('ap_requested_id'):'';
        $purchase = $this->purchases_model->getPurchaseByID($id);
        if ($purchase->payment_status == 'paid' && $purchase->grand_total == $purchase->paid) {
            $this->session->set_flashdata('error', lang('purchase_already_paid'));
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
                'purchase_id'  => $this->input->post('purchase_id'),
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
                $narrative = $this->site->getAccountName($this->accounting_setting->default_payable);

                $payment_from_account = isset($paid_by_account) ? $paid_by_account : $this->accounting_setting->default_cash ;


                $accTranPayments[] = array(
                        'tran_type'     => 'Payment',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $this->accounting_setting->default_payable,
                        'amount'        => ($this->input->post('amount-paid')+$this->input->post('discount')),
                        'narrative'     => $narrative,
                        'description'   => $this->input->post('note'),
                        'biller_id'     => $biller_id,
                        'project_id'    => $purchase->project_id,
                        'supplier_id'   => $purchase->supplier_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'activity_type' => $this->site->get_activity($this->accounting_setting->default_payable)
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
                        'created_by'    => $this->session->userdata('user_id'),
                        'activity_type' => $this->site->get_activity($payment_from_account)
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
                        'created_by'    => $this->session->userdata('user_id'),
                        'activity_type' => $this->site->get_activity($this->accounting_setting->default_purchase_discount)
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
            if ($this->purchases_model->addPayment($payment,$accTranPayments)) {
                if($ap_requested_id){
                    $this->db->update('payments_requested', array('type' => 'approved'), array('id' => $ap_requested_id));
                }
                if ((!$this->Owner && !$this->Admin) && 
                    $this->config->item('requested_ap') && $this->GP['purchases-payments_requested']) {
                    $this->session->set_flashdata('message', lang('payment_request_submited'));
                    admin_redirect('account/ap_requested');
                }else{
                    $this->session->set_flashdata('message', lang('payment_added'));
                    admin_redirect('purchases');
                }

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
            $this->load->view($this->theme . 'purchases/add_payment', $this->data);
        }
    }
    public function edit_payment($id = null)
    {
        $this->bpas->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $purchase_id = $this->input->post('purchase_id');
        $purchase = $this->purchases_model->getPurchaseByID($purchase_id);
        
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
                'purchase_id'  => $this->input->post('purchase_id'),
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

                $payment_from_account = isset($paid_by_account) ? $paid_by_account : $this->accounting_setting->default_cash ;
                
                $accTranPayments[] = array(
                    'tran_type'     => 'Payment',
                    'tran_no'       => $id,
                    'tran_date'     => $date,
                    'reference_no'  => $this->input->post('reference_no')?$this->input->post('reference_no'):$this->site->getReference('ppay'),
                    'account_code'  => $this->accounting_setting->default_payable,
                    'amount'        => ($this->input->post('amount-paid') + $this->input->post('discount')),
                    'narrative'     => $this->site->getAccountName($this->accounting_setting->default_payable),
                    'description'   => $this->input->post('note'),
                    'biller_id'     => $biller_id,
                    'project_id'    => $purchase->project_id,
                    'supplier_id'   => $purchase->supplier_id,
                    'created_by'    => $this->session->userdata('user_id'),
                    'activity_type' => $this->site->get_activity($this->accounting_setting->default_payable)
                );
                $accTranPayments[] = array(
                    'tran_type'     => 'Payment',
                    'tran_no'       => $id,
                    'tran_date'     => $date,
                    'reference_no'  => $this->input->post('reference_no')?$this->input->post('reference_no'):$this->site->getReference('ppay'),
                    'account_code'  => $payment_from_account,
                    'amount'        => $this->input->post('amount-paid') * (-1),
                    'narrative'     => $this->site->getAccountName($payment_from_account),
                    'description'   => $this->input->post('note'),
                    'biller_id'     => $biller_id,
                    'project_id'    => $purchase->project_id,
                    'supplier_id'   => $purchase->supplier_id,
                    'created_by'    => $this->session->userdata('user_id'),
                    'activity_type' => $this->site->get_activity($this->input->post('bank_account'))
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
                        'created_by'    => $this->session->userdata('user_id'),
                        'activity_type' => $this->site->get_activity($this->accounting_setting->default_purchase_discount)
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

        if ($this->form_validation->run() == true && 
            $this->purchases_model->updatePayment($id, $payment,$accTranPayments)) {
            $this->session->set_flashdata('message', lang('payment_updated'));
            admin_redirect('purchases');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if (!$this->Owner && !$this->Admin && $this->config->item('requested_ap')) {
                $this->data['payment']  = $this->purchases_model->getRequestAPByID($id);
            }else{
                $this->data['payment']  = $this->purchases_model->getPaymentByID($id);
            }
            $this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'purchases/edit_payment', $this->data);
        }
    }
    public function delete_payment($id = null)
    {
        $this->bpas->checkPermissions('delete', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->purchases_model->deletePayment($id)) {
            //account---
            $this->site->deleteAccTran('Payment', $id);
            //---end account
            $this->session->set_flashdata('message', lang('payment_deleted'));
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function combine_pdf($purchases_id)
    {
        $this->bpas->checkPermissions('pdf');
        foreach ($purchases_id as $purchase_id) {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv                 = $this->purchases_model->getPurchaseByID($purchase_id);
            if (!$this->session->userdata('view_right')) {
                $this->bpas->view_rights($inv->created_by);
            }
            $this->data['rows']            = $this->purchases_model->getAllPurchaseItems($purchase_id);
            $this->data['supplier']        = $this->site->getCompanyByID($inv->supplier_id);
            $this->data['warehouse']       = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['created_by']      = $this->site->getUser($inv->created_by);
            $this->data['inv']             = $inv;
            $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : null;
            $this->data['return_rows']     = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : null;
            $inv_html                      = $this->load->view($this->theme . 'purchases/pdf', $this->data, true);
            if (!$this->Settings->barcode_img) {
                $inv_html = preg_replace("'\<\?xml(.*)\?\>'", '', $inv_html);
            }
            $html[] = [
                'content' => $inv_html,
                'footer'  => '',
            ];
        }
        $name = lang('purchases') . '.pdf';
        $this->bpas->generate_pdf($html, $name);
    }
    
    public function email($purchase_id = null)
    {
        $this->bpas->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
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
            $supplier = $this->site->getCompanyByID($inv->supplier_id);
            $this->load->library('parser');
            $parse_data = [
                'reference_number' => $inv->reference_no,
                'contact_person'   => $supplier->name,
                'company'          => $supplier->company,
                'site_link'        => base_url(),
                'site_name'        => $this->Settings->site_name,
                'logo'             => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>',
            ];
            $msg        = $this->input->post('note');
            $message    = $this->parser->parse_string($msg, $parse_data);
            $attachment = $this->pdf($purchase_id, null, 'S');

            try {
                if ($this->bpas->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
                    delete_files($attachment);
                    $this->db->update('purchases', ['status' => 'ordered'], ['id' => $purchase_id]);
                    $this->session->set_flashdata('message', $this->lang->line('email_sent'));
                    admin_redirect('purchases');
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

            if (file_exists('./themes/' . $this->Settings->theme . '/admin/views/email_templates/purchase.html')) {
                $purchase_temp = file_get_contents('themes/' . $this->Settings->theme . '/admin/views/email_templates/purchase.html');
            } else {
                $purchase_temp = file_get_contents('./themes/default/admin/views/email_templates/purchase.html');
            }
            $this->data['subject'] = ['name' => 'subject',
                'id'                         => 'subject',
                'type'                       => 'text',
                'value'                      => $this->form_validation->set_value('subject', lang('purchase_order') . ' (' . $inv->reference_no . ') ' . lang('from') . ' ' . $this->Settings->site_name),
            ];
            $this->data['note'] = ['name' => 'note',
                'id'                      => 'note',
                'type'                    => 'text',
                'value'                   => $this->form_validation->set_value('note', $purchase_temp),
            ];
            $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);

            $this->data['id']       = $purchase_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'purchases/email', $this->data);
        }
    }
    public function email_payment($id = null)
    {
        $this->bpas->checkPermissions('payments', true);
        $payment               = $this->purchases_model->getPaymentByID($id);
        $inv                   = $this->purchases_model->getPurchaseByID($payment->purchase_id);
        $supplier              = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['inv']     = $inv;
        $this->data['payment'] = $payment;
        if (!$supplier->email) {
            $this->bpas->send_json(['msg' => lang('update_supplier_email')]);
        }
        $this->data['supplier']   = $supplier;
        $this->data['warehouse']  = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']        = $inv;
        $this->data['payment']    = $payment;
        $this->data['page_title'] = lang('payment_note');
        $html                     = $this->load->view($this->theme . 'purchases/payment_note', $this->data, true);

        $html = str_replace(['<i class="fa fa-2x">&times;</i>', 'modal-', '<p>&nbsp;</p>', '<p style="border-bottom: 1px solid #666;">&nbsp;</p>', '<p>' . lang('stamp_sign') . '</p>'], '', $html);
        $html = preg_replace("/<img[^>]+\>/i", '', $html);
        // $html = '<div style="border:1px solid #DDD; padding:10px; margin:10px 0;">'.$html.'</div>';

        $this->load->library('parser');
        $parse_data = [
            'stylesheet' => '<link href="' . $this->data['assets'] . 'styles/helpers/bootstrap.min.css" rel="stylesheet"/>',
            'name'       => $supplier->company && $supplier->company != '-' ? $supplier->company : $supplier->name,
            'email'      => $supplier->email,
            'heading'    => lang('payment_note') . '<hr>',
            'msg'        => $html,
            'site_link'  => base_url(),
            'site_name'  => $this->Settings->site_name,
            'logo'       => '<img src="' . base_url('assets/uploads/logos/' . $this->Settings->logo) . '" alt="' . $this->Settings->site_name . '"/>',
        ];
        $msg     = file_get_contents('./themes/' . $this->Settings->theme . '/admin/views/email_templates/email_con.html');
        $message = $this->parser->parse_string($msg, $parse_data);
        $subject = lang('payment_note') . ' - ' . $this->Settings->site_name;

        if ($this->bpas->send_email($supplier->email, $subject, $message)) {
            $this->bpas->send_json(['msg' => lang('email_sent')]);
        } else {
            $this->bpas->send_json(['msg' => lang('email_failed')]);
        }
    }
    /* -------------------------------------------------------------------------------- */
    public function getBudgetBalanceByID_ajax($id = null)
    {
        $result = [];
        $budget_expenses_amount = 0;
        if($budget = $this->purchases_model->getBudgetByID($id)){
            if($expenses = $this->purchases_model->getAllExpensesByBudgetID($id)){
                foreach ($expenses as $expense) {
                    $budget_expenses_amount += $expense->amount;
                }
            }
            $result['budget_balance'] = $budget->amount - $budget_expenses_amount;
            $this->bpas->send_json($result);       
        } else {
            $this->bpas->send_json(false); 
        }
    }
    public function getSupplierCost($supplier_id, $product)
    {
        switch ($supplier_id) {
            case $product->supplier1:
                $cost = $product->supplier1price > 0 ? $product->supplier1price : $product->cost;
                break;
            case $product->supplier2:
                $cost = $product->supplier2price > 0 ? $product->supplier2price : $product->cost;
                break;
            case $product->supplier3:
                $cost = $product->supplier3price > 0 ? $product->supplier3price : $product->cost;
                break;
            case $product->supplier4:
                $cost = $product->supplier4price > 0 ? $product->supplier4price : $product->cost;
                break;
            case $product->supplier5:
                $cost = $product->supplier5price > 0 ? $product->supplier5price : $product->cost;
                break;
            default:
                $cost = $product->cost;
        }
        return $cost;
    }
    /* ------------------------------------------------------------------------- */
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
        $this->data['alert_id'] = isset($_GET['alert_id']) ? ($_GET['alert_id'] !== '' ? $_GET['alert_id'] : 'empty')  : null;
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('purchases')]];
        $meta = ['page_title' => lang('purchases'), 'bc' => $bc];
        $this->page_construct('purchases/index', $meta, $this->data);
    }
    public function getPurchases($biller_id = null)
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
        $a                  = $this->input->get('a') ? $this->input->get('a') : null;
        $detail_link        = anchor('admin/purchases/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('purchase_details'));
        $add_stock_received = anchor('admin/purchases/add_receive/$1', '<i class="fa fa-file-text-o"></i> ' . lang('add_stock_received'));
        $payments_link      = anchor('admin/purchases/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_payment_link   = anchor('admin/purchases/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $email_link         = anchor('admin/purchases/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_purchase'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_debit_note     = anchor('admin/purchases/add_debit_note/$1', '<i class="fa fa-money"></i> ' . lang('add_debit_note'));
        $edit_link          = anchor('admin/purchases/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_purchase'));
        $pdf_link           = anchor('admin/purchases/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $print_barcode      = anchor('admin/products/print_barcodes/?purchase=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
        $return_link        = anchor('admin/purchases/return_purchase/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_purchase'));

        $debit_note_link =''; $add_debit_note_link ='';
        if ($this->Settings->module_account) {
            $debit_note_link        = anchor('admin/account/purchase_crebit_note/$1', '<i class="fa fa-money"></i> ' . lang('debit_note'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
            $add_debit_note_link = anchor('admin/account/add_debit_note/$1', '<i class="fa fa-money"></i> ' . lang('add_debit_note'));
        }

        $delete_link        = "<a href='#' class='po' title='<b>" . $this->lang->line('delete_purchase') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('purchases/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_purchase') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li> ';
        if ($this->Settings->stock_received) {
            $action .= (($this->Owner || $this->Admin) ? '<li class="add_stock_received">'.$add_stock_received.'</li>' : ($this->GP['stock_received-add'] ? '<li class="add_stock_received">'.$add_stock_received.'</li>' : ''));
        }
        $action .=  '<li>' . $payments_link . '</li> 
            <li>' . $add_payment_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $print_barcode . '</li>
            <li>' . $return_link . '</li>
            <li>' . $debit_note_link . '</li>
            <li>' . $add_debit_note_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
        </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if(!$this->Settings->avc_costing){
        $this->datatables
            ->select("purchases.id, DATE_FORMAT({$this->db->dbprefix('purchases')}.date, '%Y-%m-%d %T') as date, 
                projects.project_name,reference_no, order_ref,request_ref,supplier, purchases.status, 
                grand_total, 
                paid, 
                (grand_total-paid) as balance, 
                payment_status, attachment");
        }else{
            $this->datatables
            ->select("purchases.id, DATE_FORMAT({$this->db->dbprefix('purchases')}.date, '%Y-%m-%d %T') as date, 
                projects.project_name,reference_no, order_ref,request_ref,supplier, purchases.status, 
                (grand_total-shipping), 
                paid, 
                ((grand_total-shipping) -paid) as balance, 
                payment_status, attachment");
        }
        $this->datatables->from('purchases');
        $this->datatables->join('projects', 'purchases.project_id = projects.project_id', 'left');
        $this->datatables->where('purchases.is_asset !=',1);
        if ($biller_id) {
            $this->datatables->where_in('purchases.biller_id', $biller_id);
        } 
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("FIND_IN_SET(bpas_purchases.created_by, '" . $this->session->userdata('user_id') . "')");
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }

        if ($a || $a == 'empty') {
            $alert_ids = explode('-', $a);
            $alert_id  = $a;
            if (count($alert_ids) > 1) {
                $this->datatables->where_in('purchases.id', $alert_ids);
            } else {
                $this->datatables->where('purchases.id', $alert_id);
            }
        }
        $this->datatables->add_column("Actions", $action, "purchases.id");
        echo $this->datatables->generate();
    }
    public function add($purchase_order_id = null, $quote_id = null, $plan_id = null)
    {
        $this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('warehouse', $this->lang->line('warehouse'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line('supplier'), 'required');
        $this->form_validation->set_rules('project', $this->lang->line('project'), '');
        $order_referent = $this->purchases_model->getPurchasesOrderbyID($purchase_order_id); 
        if (!empty($order_referent)) {
            if($order_referent->status == 'pending' || $order_referent->status == 'requested'){
                $this->session->set_flashdata('error', lang("purchase_order_n_approved"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            if($order_referent->status == 'reject'){
                $this->session->set_flashdata('error', lang("purchase_order_has_been_rejected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            if($order_referent->order_status == 'completed'){
                $this->session->set_flashdata('error', lang("purchase_order_has_been_created"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }
        $this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('p');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $project_id       = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $biller_id        = $this->input->post('biller') ?  $this->input->post('biller') : $this->Settings->default_biller;
            $warehouse_id     = $this->input->post('warehouse');
            $supplier_id      = $this->input->post('supplier');
            $status           = $this->input->post('status');
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier         = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $payment_term     = $this->input->post('payment_term') ? $this->input->post('payment_term') : null;
            $payment_term_details = $this->site->getAllPaymentTermByID($payment_term);
            if ($this->Settings->payment_term) {
                $due_date     = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            } else {
                $due_date     = (isset($payment_term_details[0]->id)? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);
            }
            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $qtycount         = 0;
            $i                = sizeof($_POST['product']);
            if ($this->Settings->avc_costing) {
                 for ($r = 0; $r < $i; $r++) {
                    $item_unit_quantity = $_POST['quantity'][$r];
                    $item_quantity      = $_POST['product_base_quantity'][$r];

                    if (isset($item_quantity)) {
                        $qtycount += $item_quantity;
                    }
                }
                $costing = $this->bpas->formatDecimal($shipping / $qtycount);
            } else {
                $costing = 0;
            }
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_code          = $_POST['product'][$r];
                $item_net_cost      = $this->bpas->formatDecimal($_POST['net_cost'][$r]) ;
                $unit_cost          = $this->bpas->formatDecimal($_POST['unit_cost'][$r] + $costing);
                $real_unit_cost     = $this->bpas->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $item_expiry        = isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r]) && $_POST['expiry'][$r] != 'false' && $_POST['expiry'][$r] != 'undefined' && $_POST['expiry'][$r] != 'null' && $_POST['expiry'][$r] != 'NULL' && $_POST['expiry'][$r] != '00/00/0000' && $_POST['expiry'][$r] != '' ? $this->bpas->fsd($_POST['expiry'][$r]) : null; 
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $supplier_part_no   = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_description   = $_POST['description'][$r] ? $_POST['description'][$r] : null;
                $item_weight        = isset($_POST['weight'][$r]) ? $_POST['weight'][$r] : null;

                $item_addition_type = '';
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    // $product_details = $this->purchases_model->getProductByCode($item_code);
                    $product_details = $this->purchases_model->getProductByID($item_id);
                    $item_type       = $product_details->type;
                    if ($item_expiry) {
                        $today = date('Y-m-d');
                        if ($item_expiry <= $today) {
                            $this->session->set_flashdata('error', lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                    }
                    // $unit_cost = $real_unit_cost;
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_cost);
                    $unit_cost        = $this->bpas->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost    = $unit_cost;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = $item_tax = 0;
                    $tax         = '';
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];
                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_unit_quantity, 4);
                    }
                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit     = $this->site->getUnitByID($item_unit);
                    $product  = [
                        'product_id'        => $product_details->id,
                        'product_type'      => $product_details->type,
                        'product_code'      => $item_code,
                        'product_name'      => $product_details->name,
                        'option_id'         => $item_option,
                        'net_unit_cost'     => $item_net_cost,
                        'unit_cost'         => $this->bpas->formatDecimal($item_net_cost + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity'     => $item_unit_quantity,
                        'quantity_balance'  => $status == 'pending' ? $item_quantity : 0,
                        'quantity_received' => $status == 'received' ? $item_quantity : 0,
                        'weight'            => $item_weight,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'expiry'            => $item_expiry,
                        'real_unit_cost'    => $real_unit_cost,
                        'date'              => date('Y-m-d', strtotime($date)),
                        'status'            => $status,
                        'supplier_part_no'  => $supplier_part_no,
                        'addition_type'     => $item_addition_type,
                        'description'       => $item_description,
                    ];
                    if ($unit->id != $product_details->unit) {
                        $product['base_unit_cost'] = $this->site->convertToBase($unit, ($item_net_cost + $item_tax));
                    } else {
                        $product['base_unit_cost'] = ($item_net_cost + $item_tax);
                    }
                    //======= Add Accounting For Product=========//
                        if($this->Settings->module_account == 1){       
                            $accTrans[] = array(
                                'tran_type'     => 'Purchases',
                                'tran_date'     => $date,
                                'reference_no'  => $reference,
                                'account_code'  => $this->accounting_setting->default_stock,
                                'amount'        => ($subtotal),
                                'narrative'     => $this->site->getAccountName($this->accounting_setting->default_stock),
                                'description'   => $note,
                                'biller_id'     => $biller_id,
                                'project_id'    => $project_id,
                                'supplier_id'   => $supplier_id,
                                'created_by'    => $this->session->userdata('user_id'),
                            );
                            
                            if($pr_item_discount > 0){
                                $accTrans[] = array(
                                    'tran_type' => 'Purchases',
                                    'tran_date' => $date,
                                    'reference_no' => $reference,
                                    'account_code' => $this->accounting_setting->default_purchase_discount,
                                    'amount' => $pr_item_discount * (-1),
                                    'narrative' => 'Purchase Product Discount',
                                    'description' => $note,
                                    'biller_id' => $biller_id,
                                    'project_id' => $project_id,
                                    'supplier_id' => $supplier_id,
                                    'created_by'  => $this->session->userdata('user_id'),
                                );
                            }
                            if($product_tax > 0){
                                $accTrans[] = array(
                                    'tran_type' => 'Purchases',
                                    'tran_date' => $date,
                                    'reference_no' => $reference,
                                    'account_code' => $this->accounting_setting->default_purchase_tax,
                                    'amount' => $product_tax,
                                    'narrative' => 'Purchase Product Tax',
                                    'description' => $note,
                                    'biller_id' => $biller_id,
                                    'project_id' => $project_id,
                                    'supplier_id' => $supplier_id,
                                    'created_by'  => $this->session->userdata('user_id'),
                                );
                            }
                        }
                    //==================end accounting===========//
                    $products[] = ($product + $gst_data);
                    $total += $this->bpas->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
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
            if ($this->Settings->avc_costing) {
                $grand_total = $this->bpas->formatDecimal((($total + $total_tax) - $order_discount), 4);
            } else {
                $grand_total = $this->bpas->formatDecimal((($total + $total_tax + $this->bpas->formatDecimal($shipping)) - $order_discount), 4);
            }
            //======= Add Acounting for total purchase=========//   
            if ($this->Settings->module_account == 1) {
                $accTrans[] = array(
                    'tran_type'     => 'Purchases',
                    'tran_date'     => $date,
                    'reference_no'  => $reference,
                    'account_code'  => $this->accounting_setting->default_payable,
                    'amount'        => -$grand_total,
                    'narrative'     => $this->site->getAccountName($this->accounting_setting->default_payable),
                    'description'   => $note,
                    'biller_id'     => $biller_id,
                    'project_id'    => $project_id,
                    'supplier_id'   => $supplier_id,
                    'created_by'    => $this->session->userdata('user_id')
                );
                if($order_discount > 0){
                    $accTrans[] = array(
                        'tran_type'     => 'Purchases',
                        'tran_date'     => $date,
                        'reference_no'  => $reference,
                        'account_code'  => $this->accounting_setting->default_purchase_discount,
                        'amount'        => -$order_discount,
                        'narrative'     => $this->site->getAccountName($this->accounting_setting->default_purchase_discount),
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'supplier_id'   => $supplier_id,
                        'created_by'    => $this->session->userdata('user_id')
                    );
                }
                if($order_tax > 0){
                    $accTrans[] = array(
                        'tran_type'     => 'Purchases',
                        'tran_date'     => $date,
                        'reference_no'  => $reference,
                        'account_code'  => $this->accounting_setting->default_purchase_tax,
                        'amount'        => $order_tax,
                        'narrative'     => $this->site->getAccountName($this->accounting_setting->default_purchase_tax),
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'supplier_id'   => $supplier_id,
                        'created_by'    => $this->session->userdata('user_id')
                    );
                }
                if (!$this->Settings->avc_costing) {
                    if($shipping > 0){
                        $accTrans[] = array(
                            'tran_type'     => 'Purchases',
                            'tran_date'     => $date,
                            'reference_no'  => $reference,
                            'account_code'  => $this->accounting_setting->default_purchase_freight,
                            'amount'        => $shipping,
                            'narrative'     => $this->site->getAccountName($this->accounting_setting->default_purchase_freight),
                            'description'   => $note,
                            'biller_id'     => $biller_id,
                            'project_id'    => $project_id,
                            'supplier_id'   => $supplier_id,
                            'created_by'    => $this->session->userdata('user_id')
                        );
                    }
                }
            }   
            //==================end accounting===========//
            $data = [
                'purchase_order_id' => !empty($purchase_order_id) ? $purchase_order_id : null,
                'project_plan_id'   => !empty($plan_id) ? $plan_id : null,
                'biller_id'         => $biller_id,
                'project_id'        => $this->input->post('project'),
                'reference_no'      => $reference,
                'order_ref'    => !empty($order_referent->reference_no)? $order_referent->reference_no :'',
                'request_ref'  => !empty($order_referent->purchase_ref)? $order_referent->purchase_ref :'' ,
                'date'              => $date,
                'supplier_id'       => $supplier_id,
                'supplier'          => $supplier,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'total'             => $total,
                'product_discount'  => $product_discount,
                'order_discount_id' => $this->input->post('discount'),
                'order_discount'    => $order_discount,
                'total_discount'    => $total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'shipping'          => $this->bpas->formatDecimal($shipping),
                'grand_total'       => $grand_total,
                'status'            => $status,
                'created_by'        => $this->session->userdata('user_id'),
                'payment_term'      => $payment_term,
                'due_date'          => $due_date,
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
        if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products, $accTrans, 0)) {
            if ($purchase_order_id){
                $this->db->update('purchases_order', array('status' => 'completed'), array('id' => $purchase_order_id));
            }
            if ($quote_id) {
                $this->db->update('quotes', array('status' => 'completed'), array('id' => $quote_id));
            } 
            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line('purchase_added'));
            admin_redirect('purchases');
        } else {
            if ($purchase_order_id) {
                $this->data['quote'] = $this->purchases_model->getPurchaseorderByID($purchase_order_id);
                $supplier_id = $this->data['quote']->supplier_id;
                if($this->data['quote']->order_status == "completed"){
                    $this->session->set_flashdata('error', "All purchase order is completed. Can not add more.");
                    redirect('purchases/purchase_order');
                } else if($this->data['quote']->status == "pending"){
                    $this->session->set_flashdata('error', "Purchase order is pending. Can not add to purchase.");
                    redirect('purchases/purchase_order');
                } else {
                    $items = $this->purchases_order_model->getAllPurchase_orderItems($purchase_order_id);
                    $row_item = 0;
                    $maxQtyInRow = 4;
                    $c = rand(100000, 9999999);
                    foreach ($items as $item) {
                        if ($item->quantity - $item->quantity_received <= 0) continue;
                        $convert_unit = false;
                        if ($item->quantity_received > 0) {
                            $item->quantity = $item->quantity - $item->quantity_received;
                            $cost = $item->unit_cost;
                            $unit = $this->site->getProductUnit($item->product_id,$item->product_unit_id);
                            if($unit && $unit->unit_qty > 0){
                                $cost = $item->unit_cost / $unit->unit_qty;
                            }
                            $convert_unit = $this->bpas->convertUnit($item->product_id, $item->quantity, $cost);
                            $item->product_unit_id = $convert_unit['unit_id'];
                            $item->unit_quantity   = $convert_unit['quantity'];
                            $item->unit_cost       = $convert_unit['price'];
                            $item->real_unit_cost  = $convert_unit['price'];
                        }
                        $row = $this->site->getProductByID($item->product_id);
                        $cate_id = $row->subcategory_id ? $row->subcategory_id:$row->category_id;
                        if ($row->type == 'combo') {
                            $combo_items = $this->site->getProductComboItems($row->id, $item->warehouse_id);
                            foreach ($combo_items as $citem) {
                                $crow = $this->site->getProductByID($citem->id);
                                if (!$crow) {
                                    $crow = json_decode('{}');
                                    $crow->qty = $item->quantity;
                                } else {
                                    unset($crow->details, $crow->product_details, $crow->price);
                                    $crow->qty = $citem->qty*$item->quantity;
                                }
                                $crow->base_quantity = $item->quantity;
                                $crow->base_unit = $crow->unit ? $crow->unit : $item->product_unit_id;
                                $crow->base_unit_cost = $crow->cost ? $crow->cost : $item->unit_cost;
                                $crow->unit = $item->product_unit_id;
                                $crow->discount = $item->discount ? $item->discount : '0';
                                $supplier_cost = $supplier_id ? $this->getSupplierCost($supplier_id, $crow) : $crow->cost;
                                $crow->cost = $supplier_cost ? $supplier_cost : 0;
                                $crow->tax_rate = $item->tax_rate_id;
                                $crow->real_unit_cost = $item->real_unit_cost ? $item->real_unit_cost : 0;
                                $crow->expiry = '';
                                $crow->description    = $crow->description ? $crow->description :'';
                                $options = $this->purchases_model->getProductOptions($crow->id);
                                $units = $this->site->getUnitsByBUID($row->base_unit);
                                $tax_rate = $this->site->getTaxRateByID($crow->tax_rate);
                                $ri = $this->Settings->item_addition ? $crow->id : $c;
                                $set_price = $this->site->getUnitByProId($crow->id);
                                $pr[$ri] = array('id' => $c, 'item_id' => $crow->id, 'label' => $crow->name . " (" . $crow->code . ")", 'row' => $crow, 'tax_rate' => $tax_rate, 'set_price'=>$set_price, 'units' => $units, 'options' => $options);
                                $c++;
                            }
                        } elseif ($row->type == 'standard') {
                            if (!$row) {
                                $row = json_decode('{}');
                                $row->quantity = 0;
                            } else {
                                unset($row->details, $row->product_details);
                            }
                            $row->id             = $item->product_id;
                            $row->code           = $item->product_code;
                            $row->name           = $item->product_name;
                            $row->base_quantity  = $item->quantity;
                            $row->base_unit      = $row->unit ? $row->unit : $item->product_unit_id;
                            $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                            $row->unit           = $item->product_unit_id;
                            $row->qty            = $item->unit_quantity;
                            $row->order_qty      = $item->unit_quantity;
                            $row->option         = $item->option_id;
                            $row->discount       = $item->discount ? $item->discount : '0';
                            $supplier_cost       = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
                            $row->cost           = $this->bpas->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                            $row->real_unit_cost = $item->real_unit_cost;
                            $row->tax_rate       = $item->tax_rate_id;
                            $row->expiry         = '';
                            $row->total_purchase_qty = $item->quantity_balance;
                            $row->description    = $item->description ? $item->description :'';
                            $options             = $this->purchases_model->getProductOptions($row->id);
                            $units               = $this->site->getUnitsByBUID($row->base_unit);
                            $tax_rate            = $this->site->getTaxRateByID($row->tax_rate);
                            $categories          = $this->site->getCategoryByID($cate_id);
                            $ri                  = $this->Settings->item_addition ? $row->id : $c;
                            $set_price           = $this->site->getUnitByProId($row->id);
                            $pr[$ri]   = array(
                                'id'  => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                                'row' => $row, 'tax_rate' => $tax_rate,'set_price'=>$set_price, 'units' => $units, 'options' => $options, 'fiber' => $categories
                            );
                            $c++;
                        }
                    }
                    $this->data['quote_items'] = json_encode($pr);
                    $this->data['orderid']  = $purchase_order_id;
                    $this->data['quote_id'] = 0;
                    $this->data['plan_id']  = 0;
                    $this->data['purchase'] = $this->data['quote'];
                }
            }
            if ($quote_id) {
                $this->data['quote'] = $this->purchases_model->getQuoteByID($quote_id);
                $supplier_id         = $this->data['quote']->supplier_id;
                $items               = $this->purchases_model->getAllQuoteItems($quote_id);
                krsort($items);
                $c = rand(100000, 9999999);
                foreach ($items as $item) {
                    $row = $this->site->getProductByID($item->product_id);
                    $cate_id = $row->subcategory_id?$row->subcategory_id:$row->category_id;
                    if ($row->type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($row->id, $item->warehouse_id);
                        foreach ($combo_items as $citem) {
                            $crow = $this->site->getProductByID($citem->id);
                            if (!$crow) {
                                $crow      = json_decode('{}');
                                $crow->qty = $item->quantity;
                            } else {
                                unset($crow->details, $crow->product_details, $crow->price);
                                $crow->qty = $citem->qty * $item->quantity;
                            }
                            $crow->base_quantity  = $item->quantity;
                            $crow->base_unit      = $crow->unit ? $crow->unit : $item->product_unit_id;
                            $crow->base_unit_cost = $crow->cost ? $crow->cost : $item->unit_cost;
                            $crow->unit           = $item->product_unit_id;
                            $crow->discount       = $item->discount ? $item->discount : '0';
                            $supplier_cost        = $supplier_id ? $this->getSupplierCost($supplier_id, $crow) : $crow->cost;
                            $crow->cost           = $supplier_cost ? $supplier_cost : 0;
                            $crow->tax_rate       = $item->tax_rate_id;
                            $crow->real_unit_cost = $crow->cost ? $crow->cost : 0;
                            $crow->expiry         = '';
                            $crow->description    = $crow->description ? $crow->description :'';
                            $options              = $this->purchases_model->getProductOptions($crow->id);
                            $units                = $this->site->getUnitsByBUID($row->base_unit);
                            $tax_rate             = $this->site->getTaxRateByID($crow->tax_rate);
                            $ri                   = $this->Settings->item_addition ? $crow->id : $c;
                            $set_price = $this->site->getUnitByProId($crow->id);
                            $pr[$ri] = ['id' => $c, 'item_id' => $crow->id, 'label' => $crow->name . ' (' . $crow->code . ')', 'row' => $crow, 'tax_rate' => $tax_rate,'set_price' => $set_price, 'units' => $units, 'options' => $options];
                            $c++;
                        }
                    } elseif ($row->type == 'standard') {
                        if (!$row) {
                            $row           = json_decode('{}');
                            $row->quantity = 0;
                        } else {
                            unset($row->details, $row->product_details);
                        }
                        $row->id             = $item->product_id;
                        $row->code           = $item->product_code;
                        $row->name           = $item->product_name;
                        $row->base_quantity  = $item->quantity;
                        $row->base_unit      = $row->unit ? $row->unit : $item->product_unit_id;
                        $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                        $row->unit           = $item->product_unit_id;
                        $row->qty            = $item->unit_quantity;
                        $row->option         = $item->option_id;
                        $row->discount       = $item->discount ? $item->discount : '0';
                        $supplier_cost       = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
                        $row->cost           = $supplier_cost ? $supplier_cost : 0;
                        $row->tax_rate       = $item->tax_rate_id;
                        $row->expiry         = '';
                        $row->real_unit_cost = $row->cost ? $row->cost : 0;
                        $options             = $this->purchases_model->getProductOptions($row->id);
                        $row->description    = (isset($item->description) && $item->description) ? $item->description :'';
                        $units    = $this->site->getUnitsByBUID($row->base_unit);
                        $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                        $ri       = $this->Settings->item_addition ? $row->id : $c;
                        $set_price = $this->site->getUnitByProId($row->id);
                        $categories = $this->site->getCategoryByID($cate_id);
                        $pr[$ri] = [
                            'id'  => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                            'row' => $row, 'tax_rate' => $tax_rate, 'set_price'=>$set_price, 'units' => $units, 'options' => $options, 'fiber' => $categories
                        ];
                        $c++;
                    }
                }
                $this->data['quote_items'] = json_encode($pr);
                $this->data['orderid']     = 0;
                $this->data['quote_id']    = $quote_id;
                $this->data['plan_id']     = 0;
                $this->data['purchase']    = $this->data['quote'];
            }
            if ($plan_id) {
                $this->data['quote'] = $this->purchases_model->getProjectPlanByID($plan_id);
                $supplier_id = $this->data['quote']->supplier_id;
                if($this->data['quote']->order_status == "completed"){
                    $this->session->set_flashdata('error', "All Project Plan is completed. Can not add more.");
                    admin_redirect('projects/plans');
                } else if($this->data['quote']->status == "pending"){
                    $this->session->set_flashdata('error', "Project Plan is pending. Can not add to purchase.");
                    admin_redirect('projects/plans');
                } else {
                    $items = $this->purchases_model->getAllProjectPlanItems($plan_id);
                    $row_item = 0;
                    $maxQtyInRow = 4;
                    $c = rand(100000, 9999999);
                    foreach ($items as $item) {
                        //if ($item->quantity - $item->quantity_received <= 0) continue;
                        $item->quantity      = $item->quantity - $item->quantity_received;
                        $item->unit_quantity = $item->unit_quantity - $item->quantity_received;
                        $row = $this->site->getProductByID($item->product_id);
                        $cate_id = $row->subcategory_id?$row->subcategory_id:$row->category_id;
                        if ($row->type == 'combo') {
                            $combo_items = $this->site->getProductComboItems($row->id, $item->warehouse_id);
                            foreach ($combo_items as $citem) {
                                $crow = $this->site->getProductByID($citem->id);
                                if (!$crow) {
                                    $crow = json_decode('{}');
                                    $crow->qty = $item->quantity;
                                } else {
                                    unset($crow->details, $crow->product_details, $crow->price);
                                    $crow->qty = $citem->qty*$item->quantity;
                                }
                                $crow->base_quantity = $item->quantity;
                                $crow->base_unit = $crow->unit ? $crow->unit : $item->product_unit_id;
                                $crow->base_unit_cost = $crow->cost ? $crow->cost : $item->unit_cost;
                                $crow->unit = $item->product_unit_id;
                                $crow->discount = $item->discount ? $item->discount : '0';
                                $supplier_cost = $supplier_id ? $this->getSupplierCost($supplier_id, $crow) : $crow->cost;
                                $crow->cost = $supplier_cost ? $supplier_cost : 0;
                                $crow->tax_rate = $item->tax_rate_id;
                                $crow->real_unit_cost = $crow->cost ? $crow->cost : 0;
                                $crow->expiry = '';
                                $crow->description    = $crow->description ? $crow->description :'';
                                $options = $this->purchases_model->getProductOptions($crow->id);
                                $units = $this->site->getUnitsByBUID($row->base_unit);
                                $tax_rate = $this->site->getTaxRateByID($crow->tax_rate);
                                $ri = $this->Settings->item_addition ? $crow->id : $c;
                                $set_price = $this->site->getUnitByProId($crow->id);
                                $pr[$ri] = array('id' => $c, 'item_id' => $crow->id, 'label' => $crow->name . " (" . $crow->code . ")", 'row' => $crow, 'tax_rate' => $tax_rate, 'set_price'=>$set_price, 'units' => $units, 'options' => $options);
                                $c++;
                            }
                        } elseif ($row->type == 'standard') {
                            if (!$row) {
                                $row = json_decode('{}');
                                $row->quantity = 0;
                            } else {
                                unset($row->details, $row->product_details);
                            }
                            $row->id             = $item->product_id;
                            $row->code           = $item->product_code;
                            $row->name           = $item->product_name;
                            $row->base_quantity  = $item->quantity;
                            $row->base_unit      = $row->unit ? $row->unit : $item->product_unit_id;
                            $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                            $row->unit           = $item->product_unit_id;
                            $row->qty            = $item->quantity1;
                            $row->order_qty      = $item->quantity1;
                            $row->option         = $item->option_id;
                            $row->discount       = $item->discount ? $item->discount : '0';
                            $supplier_cost       = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
                            $row->cost           = $this->bpas->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity1));
                            $row->real_unit_cost = $item->real_unit_cost;
                            $row->tax_rate       = $item->tax_rate_id;
                            $row->expiry         = '';
                            $row->total_purchase_qty = $item->quantity_balance;
                            $row->description    = $item->description ? $item->description :'';
                            $options             = $this->purchases_model->getProductOptions($row->id);
                            $units               = $this->site->getUnitsByBUID($row->base_unit);
                            $tax_rate            = $this->site->getTaxRateByID($row->tax_rate);
                            $categories          = $this->site->getCategoryByID($cate_id);
                            $ri        = $this->Settings->item_addition ? $row->id : $c;
                            $set_price = $this->site->getUnitByProId($row->id);
                            $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                                'row' => $row, 'tax_rate' => $tax_rate,'set_price'=>$set_price, 'units' => $units, 'options' => $options, 'fiber' => $categories);

                            $c++;
                        }
                    }
                    $this->data['quote_items'] = json_encode($pr);
                    $this->data['orderid']  = 0;
                    $this->data['quote_id'] = 0;
                    $this->data['plan_id'] = $plan_id;
                    $this->data['purchase'] = $this->data['quote'];
                }
            }
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if (!$purchase_order_id && !$quote_id && !$plan_id){
                $this->data['quote_id'] = "";
            }
            $this->data['billers']      = $this->site->getAllCompanies('biller');
            $this->data['payment_term'] = $this->site->getAllPaymentTerm();
            $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['ponumber']   = $this->site->getReference('p');
            $this->data['projects']   = $this->site->getAllProject();
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc                 = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchases'), 'page' => lang('purchases')], ['link' => '#', 'page' => lang('add_purchase')]];
            $meta               = ['page_title' => lang('add_purchase'), 'bc' => $bc];
            $this->page_construct('purchases/add', $meta, $this->data);
        }
    }
    public function edit($id = null)
    {
        $this->bpas->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->purchases_model->getPurchaseByID($id);
        if ($inv->status == 'returned' || $inv->return_id || $inv->return_purchase_ref) {
            $this->session->set_flashdata('error', lang('purchase_x_action'));
            admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
        }
        if ($this->Settings->stock_received) {
            if ($this->purchases_model->checkStockReceived($id)) {
                $this->session->set_flashdata('error', lang('purchase_already_stock_received'));
                admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');   
            }
        }
        if (!$this->session->userdata('edit_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('reference_no', $this->lang->line('ref_no'), 'required');
        $this->form_validation->set_rules('warehouse', $this->lang->line('warehouse'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line('supplier'), 'required');
        $this->form_validation->set_rules('project', $this->lang->line('project'), '');
        $this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $project_id = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project;
            $warehouse_id     = $this->input->post('warehouse');
            $supplier_id      = $this->input->post('supplier');
            $status           = $this->input->post('status');
            $biller_id        = $this->input->post('biller') ? $this->input->post('biller') : $this->Settings->default_biller;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier         = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $payment_term     = $this->input->post('payment_term') ? $this->input->post('payment_term') : null;
            $payment_term_details = $this->site->getAllPaymentTermByID($payment_term);
            if ($this->Settings->payment_term) {
                $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            } else {
                $due_date = (isset($payment_term_details[0]->id)? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);
            }
            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $partial          = false;
            $qtycount         = 0;
            $i                = sizeof($_POST['product']);
            if ($this->Settings->avc_costing) {
                 for ($r = 0; $r < $i; $r++) {
                    $item_unit_quantity = $_POST['quantity'][$r];
                    $item_quantity      = $_POST['received_base_quantity'][$r];

                    if (isset($item_quantity)) {
                        $qtycount += $item_quantity;
                    }
                }
                $costing = $this->bpas->formatDecimal($shipping / $qtycount);
            } else {
                $costing = 0;
            }
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_code          = $_POST['product'][$r];
                $item_net_cost      = $this->bpas->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost          = $this->bpas->formatDecimal($_POST['unit_cost'][$r] + $costing);
                $real_unit_cost     = $this->bpas->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $quantity_received  = $_POST['received_base_quantity'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $item_expiry        = isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r]) && $_POST['expiry'][$r] != 'false' && $_POST['expiry'][$r] != 'undefined' && $_POST['expiry'][$r] != 'null' && $_POST['expiry'][$r] != 'NULL' && $_POST['expiry'][$r] != '00/00/0000' && $_POST['expiry'][$r] != '' ? $this->bpas->fsd($_POST['expiry'][$r]) : null; 
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $supplier_part_no   = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $quantity_balance   = $_POST['quantity_balance'][$r];
                $ordered_quantity   = $_POST['ordered_quantity'][$r];
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_description = $_POST['description'][$r] ? $_POST['description'][$r]:null;
                $item_addition_type = '';
                if ($status == 'received' || $status == 'partial') {
                    if ($quantity_received < $item_quantity) {
                        $partial = 'partial';
                    } elseif ($quantity_received > $item_quantity) {
                        $this->session->set_flashdata('error', lang('received_more_than_ordered'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                    $balance_qty = $quantity_received - ($ordered_quantity - $quantity_balance);
                } else {
                    $balance_qty       = $item_quantity;
                    $quantity_received = $item_quantity;
                }
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity) && isset($quantity_balance)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    // $unit_cost = $real_unit_cost;
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_cost);
                    $unit_cost        = $this->bpas->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost    = $unit_cost;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = 0;
                    $item_tax    = 0;
                    $tax         = '';
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];
                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_unit_quantity, 4);  
                    }
                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit     = $this->site->getUnitByID($item_unit);
                    $item     = [
                        'product_id'        => $product_details->id,
                        'product_type'      => $product_details->type,
                        'product_code'      => $item_code,
                        'product_name'      => $product_details->name,
                        'option_id'         => $item_option,
                        'net_unit_cost'     => $item_net_cost,
                        'unit_cost'         => $this->bpas->formatDecimal($item_net_cost + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity'     => $item_unit_quantity,
                        'quantity_balance'  => ($status == 'partial' || $status == 'received') ? $balance_qty : 0,
                        'quantity_received' => ($status == 'partial' || $status == 'received') ? $quantity_received : 0,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'expiry'            => $item_expiry,
                        'real_unit_cost'    => $real_unit_cost,
                        'supplier_part_no'  => $supplier_part_no,
                        'date'              => date('Y-m-d', strtotime($date)),
                        'addition_type'     => $item_addition_type,
                        'description'       => $item_description,
                    ];
                    if ($unit->id != $product_details->unit) {
                        $item['base_unit_cost'] = $this->site->convertToBase($unit, ($item_net_cost + $item_tax));
                    } else {
                        $item['base_unit_cost'] = ($item_net_cost + $item_tax);
                    }
                    //======= Add Accounting For Product=========//
                    if ($this->Settings->module_account == 1) {
                        $accTrans[] = array(
                            'tran_type'     => 'Purchases',
                            'tran_no'       => $id,
                            'tran_date'     => $date,
                            'reference_no'  => $reference,
                            'account_code'  => $this->accounting_setting->default_stock,
                            'amount'        => ($subtotal),
                            'narrative'     => $this->site->getAccountName($this->accounting_setting->default_stock),
                            'description'   => $note,
                            'biller_id'     => $biller_id,
                            'project_id'    => $project_id,
                            'supplier_id'   => $supplier_id,
                            'created_by'    => $this->session->userdata('user_id'),
                            'activity_type' => 0
                        );
                        if ($pr_item_discount > 0) {
                            $accTrans[] = array(
                                'tran_type'     => 'Purchases',
                                'tran_no'       => $id,
                                'tran_date'     => $date,
                                'reference_no'  => $reference,
                                'account_code'  => $this->accounting_setting->default_purchase_discount,
                                'amount'        => $pr_item_discount * (-1),
                                'narrative'     => 'Purchase Product Discount',
                                'description'   => $note,
                                'biller_id'     => $biller_id,
                                'project_id'    => $project_id,
                                'people_id'     => $this->session->userdata('user_id'),
                                'supplier_id'   => $supplier_id,
                                'created_by'    => $this->session->userdata('user_id'),
                            );
                        }
                    }
                    //==================end accounting===========//
                    $items[] = ($item + $gst_data);
                    $total += $item_net_cost * $item_unit_quantity;
                }
            }
            if (empty($items)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                foreach ($items as $item) {
                    $item['status'] = ($status == 'partial' || $status == 'received') ? 'received' : $status;
                    $products[]     = $item;
                }
                krsort($products);
            }
            $order_discount = $this->site->calculateDiscount($this->input->post('discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            if ($this->Settings->avc_costing) {
                $grand_total = $this->bpas->formatDecimal(($total + $total_tax - $order_discount), 4);
            } else {
                $grand_total = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            }
            if ($this->Settings->module_account == 1) {
                $accTrans[] = array(
                    'tran_type'     => 'Purchases',
                    'tran_no'       => $id,
                    'tran_date'     => $date,
                    'reference_no'  => $reference,
                    'account_code'  => $this->accounting_setting->default_payable,
                    'amount'        => -$grand_total,
                    'narrative'     => $this->site->getAccountName($this->accounting_setting->default_payable),
                    'description'   => $note,
                    'biller_id'     => $biller_id,
                    'project_id'    => $project_id,
                    'supplier_id'   => $supplier_id,
                    'created_by'    => $this->session->userdata('user_id'),
                    'activity_type' => $this->site->get_activity($this->accounting_setting->default_payable)
                );
                if($order_discount > 0){
                    $accTrans[] = array(
                        'tran_type'     => 'Purchases',
                        'tran_no'       => $id,
                        'tran_date'     => $date,
                        'reference_no'  => $reference,
                        'account_code'  => $this->accounting_setting->default_purchase_discount,
                        'amount'        => -$order_discount,
                        'narrative'     => $this->site->getAccountName($this->accounting_setting->default_purchase_discount),
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'supplier_id'   => $supplier_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'activity_type' => 0
                    );
                }
                if($order_tax > 0){
                    $accTrans[] = array(
                        'tran_type'     => 'Purchases',
                        'tran_no'       => $id,
                        'tran_date'     => $date,
                        'reference_no'  => $reference,
                        'account_code'  => $this->accounting_setting->default_purchase_tax,
                        'amount'        => $order_tax,
                        'narrative'     => $this->site->getAccountName($this->accounting_setting->default_purchase_tax),
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'supplier_id'   => $supplier_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'activity_type' => 0
                    );
                }
           
                if ($shipping > 0 && !$this->Settings->avc_costing) {
                    $accTrans[] = array(
                        'tran_type'     => 'Purchases',
                        'tran_no'       => $id,
                        'tran_date'     => $date,
                        'reference_no'  => $reference,
                        'account_code'  => $this->accounting_setting->default_purchase_freight,
                        'amount'        => $shipping,
                        'narrative'     => $this->site->getAccountName($this->accounting_setting->default_purchase_freight),
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'supplier_id'   => $supplier_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'activity_type' => 0
                    );
                }
                
            }   
            $data = [
                'reference_no'                => $reference,
                'project_id'                  => $this->input->post('project'),
                'supplier_id'                 => $supplier_id,
                'supplier'                    => $supplier,
                'warehouse_id'                => $warehouse_id,
                'note'                        => $note,
                'total'                       => $total,
                'product_discount'            => $product_discount,
                'order_discount_id'           => $this->input->post('discount'),
                'order_discount'              => $order_discount,
                'total_discount'              => $total_discount,
                'product_tax'                 => $product_tax,
                'order_tax_id'                => $this->input->post('order_tax'),
                'order_tax'                   => $order_tax,
                'total_tax'                   => $total_tax,
                'shipping'                    => $this->bpas->formatDecimal($shipping),
                'grand_total'                 => $grand_total,
                'status'                      => $status,
                'updated_by'                  => $this->session->userdata('user_id'),
                'updated_at'                  => date('Y-m-d H:i:s'),
                'payment_term'                => $payment_term,
                'due_date'                    => $due_date,
                'biller_id'                   => $biller_id,
                'adjust_paid'                 => $this->input->post('adjust_paid')
            ];
            if ($date) {
                $data['date'] = $date;
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
            // $this->bpas->print_arrays($id, $data, $products, $accTrans);
        }
        if ($this->form_validation->run() == true && $this->purchases_model->updatePurchase($id, $data, $products,$accTrans)) {
            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line('purchase_updated'));
            admin_redirect('purchases');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv']   = $inv;
            $this->bpas->deadlineDayEditing($this->data['inv']->date);
            $inv_items = $this->purchases_model->getAllPurchaseItems($id);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row                     = $this->site->getProductByID($item->product_id);
                $cate_id                 = $row->subcategory_id?$row->subcategory_id:$row->category_id;
                $row->expiry             = (($item->expiry && $item->expiry != '0000-00-00') ? $this->bpas->hrsd($item->expiry) : '');
                $row->base_quantity      = $item->quantity;
                $row->base_unit          = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_cost     = $item->unit_cost;
                $row->weight             = $item->weight;
                $row->unit               = $item->product_unit_id;
                $row->qty                = $item->unit_quantity;
                $row->oqty               = $item->quantity;
                $row->supplier_part_no   = $item->supplier_part_no;
                $row->received           = $item->quantity_received ? $item->quantity_received : $item->quantity;
                $row->quantity_balance   = $item->quantity_balance + ($item->quantity - $row->received);
                $row->discount           = $item->discount ? $item->discount : '0';
                $options                 = $this->purchases_model->getProductOptions($row->id);
                $row->option             = $item->option_id;
                $row->real_unit_cost     = $item->real_unit_cost;
                $row->cost               = $this->bpas->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                $row->tax_rate           = $item->tax_rate_id;
                unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                $row->addition_type      = $item->addition_type;
                $row->total_purchase_qty = $item->quantity_balance + $row->base_quantity;
                $row->description    = $item->description;
                $units                   = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate                = $this->site->getTaxRateByID($row->tax_rate);
                $categories              = $this->site->getCategoryByID($cate_id);
                $ri                      = $this->Settings->item_addition ? $row->id : $c;
                $set_price = $this->site->getUnitByProId($row->id);
                $pr[$ri] = [
                    'id'  => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row' => $row, 'tax_rate' => $tax_rate, 'set_price'=>$set_price, 'units' => $units, 'options' => $options, 'fiber' => $categories, ];
                $c++;
            }
            $this->data['inv_items']        = json_encode($pr);
            $this->data['payment_term']     = $this->site->getAllPaymentTerm();
            $this->data['id']               = $id;
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->data['suppliers']        = $this->site->getAllCompanies('supplier');
            $this->data['purchase']         = $this->purchases_model->getPurchaseByID($id);
            $this->data['categories']       = $this->site->getAllCategories();
            $this->data['tax_rates']        = $this->site->getAllTaxRates();
            $this->data['warehouses']       = $this->site->getAllWarehouses();
            $this->data['projects']         = $this->site->getAllProject();
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->session->set_userdata('remove_pols', 1);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc                 = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchases'), 'page' => lang('purchases')], ['link' => '#', 'page' => lang('edit_purchase')]];
            $meta               = ['page_title' => lang('edit_purchase'), 'bc' => $bc];
            $this->page_construct('purchases/edit', $meta, $this->data);
        }
    }
    public function delete($id = null)
    {
        $this->bpas->checkPermissions(null, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->purchases_model->deletePurchase($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('purchase_deleted')]);
            }
            $this->session->set_flashdata('message', lang('purchase_deleted'));
            admin_redirect('welcome');
        }
    }
    /* ----------------------------------------------------------------------------- */
    public function modal_view($purchase_id = null)
    {
        $this->bpas->checkPermissions('index', true);
        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by, true);
        }
        $this->data['rows']            = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier']        = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse']       = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']             = $inv;
        $sumKHM = 0;
        $sumUSD = 0;
        $sumEuro = 0;
        $sumBAT = 0;
        $sumYuan = 0;
        foreach($this->data['rows'] as $value){
            // var_dump($value->other_cost." ".$value->symbol);
            if($value->currency == "KHR"){
                $sumKHM = $sumKHM + $value->other_cost;
            }
             if($value->currency == "USD"){
                $sumUSD = $sumUSD + $value->unit_cost;
            }
            if($value->currency == "BAHT"){
                $sumBAT = $sumBAT + $value->other_cost;
            }
           
        }
        $this->data['sumKHM']       = $sumKHM;
        $this->data['sumUSD']       = $sumUSD;
        $this->data['sumEuro']      = $sumEuro;
        $this->data['sumBAT']       = $sumBAT;
        $this->data['sumYuan']      = $sumYuan;
        $this->data['currencys']    = $this->site->getAllCurrencies();
        $this->data['payments']        = $this->purchases_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by']      = $this->site->getUser($inv->created_by);
        $this->data['updated_by']      = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : null;
        $this->data['return_rows']     = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : null;
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);

        $this->load->view($this->theme . 'purchases/modal_view', $this->data);
    }
    public function payment_note($id = null)
    {
        $this->bpas->checkPermissions('payments', true);

        $payment                  = $this->purchases_model->getPaymentByID($id);
        $inv                      = $this->purchases_model->getPurchaseByID($payment->purchase_id);

        $this->data['supplier']   = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse']  = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']        = $inv;
        $this->data['payment']    = $payment;
        $this->data['page_title'] = $this->lang->line('payment_note');

        $this->load->view($this->theme . 'purchases/payment_note', $this->data);

    }
    /* -------------------------------------------------------------------------------- */
    public function payments($id = null,$ex_id = null)
    {
        $this->bpas->checkPermissions(false, true);
        if($id){
            $this->data['payments'] = $this->purchases_model->getPurchasePayments($id);
            $this->data['inv'] = $this->purchases_model->getPurchaseByID($id);
            $this->load->view($this->theme . 'purchases/payments', $this->data);
        }else{
            $expense = $this->purchases_model->getExpenseByID($ex_id); 
            $this->data['payments'] = $this->purchases_model->getExpensePayments($ex_id);
            $this->data['inv'] = $expense;
            $this->load->view($this->theme . 'expenses/payments', $this->data);
        }

        
    }

    /* ----------------------------------------------------------------------------- */

    //generate pdf and force to download

    public function pdf($purchase_id = null, $view = null, $save_bufffer = null)
    {
        $this->bpas->checkPermissions();

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->data['rows']            = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier']        = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse']       = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['created_by']      = $this->site->getUser($inv->created_by);
        $this->data['inv']             = $inv;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : null;
        $this->data['return_rows']     = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : null;
        $name                          = $this->lang->line('purchase') . '_' . str_replace('/', '_', $inv->reference_no) . '.pdf';
        $html                          = $this->load->view($this->theme . 'purchases/pdf', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            echo $html;
            die();
        } elseif ($save_bufffer) {
            return $this->bpas->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->bpas->generate_pdf($html, $name);
        }
    }

    /* -------------------------------------------------------------------------------- */

    public function purchase_actions()
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
                        $this->purchases_model->deletePurchase($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line('purchases_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'combine') {
                    $html = $this->combine_pdf($_POST['val']);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->bpas->checkPermissions('export', true, 'purchases');
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('purchases'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('list_purchase'));
                    $this->excel->getActiveSheet()->getStyle('E1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $this->excel->getActiveSheet()->getStyle("E1")->getFont()->setSize(13);
                    $this->excel->getActiveSheet()->SetCellValue('A2', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B2', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C2', lang('supplier'));
                    $this->excel->getActiveSheet()->SetCellValue('D2', lang('product_code'));
                    $this->excel->getActiveSheet()->SetCellValue('E2', lang('product_name'));
                    $this->excel->getActiveSheet()->SetCellValue('F2', lang('unit'));
                    $this->excel->getActiveSheet()->SetCellValue('G2', lang('quantity'));
                    $this->excel->getActiveSheet()->SetCellValue('H2', lang('cost'));
                    $this->excel->getActiveSheet()->SetCellValue('I2', lang('grand_total'));
                    $this->excel->getActiveSheet()->SetCellValue('J2', lang('currency'));
                    $this->excel->getActiveSheet()->SetCellValue('K2', lang('other_cost'));
                    $this->excel->getActiveSheet()->SetCellValue('L2', lang('warehouse'));
                    $this->excel->getActiveSheet()->SetCellValue('M2', lang('status'));
                    $styleArray = array('font'  => array('bold'  => true));
                    $this->excel->getActiveSheet()->getStyle('A1:I1')->applyFromArray($styleArray);
                    $row = 3;
                    foreach ($_POST['val'] as $id) {
                        $purchases = $this->purchases_model->getallPurchase($id);
                        foreach ($purchases as $purchase) {  
                            $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($purchase->date));
                            $this->excel->getActiveSheet()->SetCellValue('B' . $row, $purchase->reference_no);
                            $this->excel->getActiveSheet()->SetCellValue('C' . $row, $purchase->supplier);
                            $this->excel->getActiveSheet()->SetCellValue('D' . $row, $purchase->product_code);
                            $this->excel->getActiveSheet()->SetCellValue('E' . $row, $purchase->product_name);
                            $this->excel->getActiveSheet()->SetCellValue('F' . $row, $purchase->unit_code);
                            $this->excel->getActiveSheet()->SetCellValue('G' . $row, $purchase->quantity);
                            $this->excel->getActiveSheet()->SetCellValue('H' . $row, $purchase->cost);
                            $this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatMoney($purchase->grand_total));
                            $this->excel->getActiveSheet()->SetCellValue('J' . $row, $purchase->currency);
                            $this->excel->getActiveSheet()->SetCellValue('K' . $row, $purchase->other_cost);
                            $this->excel->getActiveSheet()->SetCellValue('L' . $row, $purchase->ware_name);
                            $this->excel->getActiveSheet()->SetCellValue('M' . $row, $purchase->status);
                            $row++;
                        }
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'purchases_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line('no_purchase_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function excel_export($id=null)
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $purchases = $this->purchases_model->getPurchase_detail_ByID($id);
        if ($purchases) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('purchases'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('supplier'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('grand_total'));
                $row = 2;
                foreach ($purchases as $purchase) {
                    
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($purchase->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $purchase->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $purchase->supplier);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $purchase->status);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatMoney($purchase->grand_total));
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $filename = 'Purchases_' . date('Y_m_d_H_i_s');
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
      
         
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    /* ----------------------------------------------------------------------------------------------------------- */
    /**
     * Import excel
     *
     * @return void
     */
    public function purchase_by_excel()
    {
        $this->bpas->checkPermissions('import');
        $this->load->helper('security');
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');
        $this->form_validation->set_rules('userfile', $this->lang->line("upload_file"), 'xss_clean');
        $this->form_validation->set_rules('project', lang('project'), '');
        if ($this->form_validation->run() == true) {
            $quantity  = "quantity";
            $product   = "product";
            $unit_cost = "unit_cost";
            $tax_rate  = "tax_rate";
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('p');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date  = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date  = null;
            }
            $biller_id          = $this->input->post('biller') ? $this->input->post('biller') : $this->Settings->default_biller;
            $project_id         = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $warehouse_id       = $this->input->post('warehouse');
            $supplier_id        = $this->input->post('supplier');
            $status             = $this->input->post('status');
            $project            = $this->input->post('project');
            $shipping           = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details   = $this->site->getCompanyByID($supplier_id);
            $supplier           = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $note               = $this->bpas->clear_tags($this->input->post('note'));

            $this->load->library('excel');
            if (isset($_FILES["userfile"]["name"])) 
            {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = ['csv','xls' , 'xlsx'];
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('purchases/purchase_by_csv');
                }
                $path   = $_FILES["userfile"]["tmp_name"];
                $object = PHPExcel_IOFactory::load($path);
                if(!$object) {
                    $error=$this->excel->display_errors();
                    $this->session->set_flashdata('error',$error);
                    admin_redirect("purchases/purchase_by_csv");
                }
                foreach($object->getWorksheetIterator() as $worksheet)
                {
                    $HighestRow       = $worksheet->getHighestRow();
                    $HighestColumn    = $worksheet->getHighestColumn();
                    $rw               = 2;
                    $total            = 0;
                    $product_tax      = 0;
                    $item_tax         = 0;
                    $product_discount = 0;
                    $qtycount         = 0;
                    if ($this->Settings->avc_costing) {
                        for($row=2; $row <= $HighestRow; $row++) {
                            $item_quantity = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                            if (isset($item_quantity)) {
                                $qtycount += $item_quantity;
                            }
                        }
                        $costing   = $this->bpas->formatDecimal($shipping / $qtycount);
                    } else {
                        $costing   = 0;
                    }
                    $gst_data      = [];
                    $total_cgst    = $total_sgst = $total_igst = 0;
                    for($row=2; $row <= $HighestRow; $row++)
                    {
                        $code               = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
                        // $cost            = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                        $cost_no_ship       = $this->bpas->formatDecimal($worksheet->getCellByColumnAndRow(1, $row)->getValue());
                        $cost               = $this->bpas->formatDecimal($worksheet->getCellByColumnAndRow(1, $row)->getValue() + $costing);
                        $quantity           = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                        $product_variants   = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                        // $tax_method      = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                        $tax_rate           = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                        $discount           = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                        $expiry             = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
                        // $tax_rate        = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
                        $dateValue          = PHPExcel_Shared_Date::ExcelToPHP($expiry);                       
                        $expiry             = isset($expiry) ? date('m-d-Y',$dateValue) : '';
                        if (isset($code) && isset($cost) && isset($quantity)) {
                            if ($product_details = $this->purchases_model->getProductByCode($code)) {
                                if ($product_variants) {
                                    $item_option = $this->purchases_model->getProductVariantByName($product_variants, $product_details->id);
                                    if (!$item_option) {
                                        $this->session->set_flashdata('error', $code . lang("pr_not_found") . " ( " . $product_details->name . " - " . $product_variants . " ). " . lang("line_no") . " " . $rw);
                                        redirect($_SERVER["HTTP_REFERER"]);
                                    }
                                } else {
                                    $item_option = json_decode('{}');
                                    $item_option->id = null;
                                }
                                $item_code               = $code;
                                //  $item_net_cost       = $this->bpas->formatDecimal($csv_pr['cost']);
                                $item_net_cost           = $cost;
                                $item_net_cost_no_ship   = $cost_no_ship;
                                $item_quantity           = $quantity;
                                $quantity_received       = $quantity;
                                $quantity_balance        = $quantity;
                                $item_tax_rate           = $tax_rate;
                                $item_discount           = $discount;
                                $item_expiry             = ($expiry !='') ? $this->bpas->fsd($expiry) : null;
                                $pr_discount             = $this->site->calculateDiscount($item_discount, $item_net_cost);
                                $pr_item_discount        = $this->bpas->formatDecimal(($pr_discount * $item_quantity), 4);
                                $product_discount       += $pr_item_discount;
                                $tax                     = "";
                                $pr_item_tax             = 0;
                                $unit_cost               = $item_net_cost - $pr_discount;
                                $unit_cost_no_ship       = $item_net_cost_no_ship - $pr_discount;
                                $gst_data                = [];
                                $tax_details             = ((isset($item_tax_rate) && !empty($item_tax_rate)) ? $this->purchases_model->getTaxRateByName($item_tax_rate) : $this->site->getTaxRateByID($product_details->tax_rate));
                                if ($tax_details) {
                                    $ctax     = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                                    $item_tax = $ctax['amount'];
                                    $tax      = $ctax['tax'];
                                    if ($product_details->tax_method != 1) {
                                        $item_net_cost = $unit_cost - $item_tax;
                                        $item_net_cost_no_ship = $unit_cost_no_ship - $item_tax;
                                    }
                                    $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_quantity, 4);
                                    if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($this->Settings->state == $supplier_details->state), $tax_details)) {
                                        $total_cgst += $gst_data['cgst'];
                                        $total_sgst += $gst_data['sgst'];
                                        $total_igst += $gst_data['igst'];
                                    }
                                }
                                $product_tax += $pr_item_tax;
                                $subtotal     = $this->bpas->formatDecimal(((($item_net_cost * $item_quantity) + $pr_item_tax) - $pr_item_discount), 4);
                                $unit         = $this->site->getUnitByID($product_details->unit);
                                $product      = array(
                                    'product_id'        => $product_details->id,
                                    'product_code'      => $item_code,
                                    'product_name'      => $product_details->name,
                                    'option_id'         => $item_option->id,
                                    'net_unit_cost'     => $item_net_cost,
                                    'quantity'          => $item_quantity,
                                    'product_unit_id'   => $product_details->unit,
                                    'product_unit_code' => $unit->code,
                                    'unit_quantity'     => $item_quantity,
                                    'quantity_received' => $quantity_received,
                                    'quantity_balance'  => $quantity_balance,
                                    'warehouse_id'      => $warehouse_id,
                                    'item_tax'          => $pr_item_tax,
                                    'tax_rate_id'       => $tax_details ? $tax_details->id : null,
                                    'tax'               => $tax,
                                    'discount'          => $item_discount,
                                    'item_discount'     => $pr_item_discount,
                                    'expiry'            => $item_expiry,
                                    'subtotal'          => $subtotal,
                                    'date'              => date('Y-m-d', strtotime($date)),
                                    'status'            => $status,
                                    'unit_cost'         => $this->bpas->formatDecimal(($item_net_cost + $item_tax), 4),
                                    'real_unit_cost'    => $this->bpas->formatDecimal(($item_net_cost_no_ship + $item_tax + $pr_discount), 4),
                                );
                                if ($unit->id != $product_details->unit) {
                                    $product['base_unit_cost'] = $this->site->convertToBase($unit, ($item_net_cost + $item_tax));
                                } else {
                                    $product['base_unit_cost'] = ($item_net_cost + $item_tax);
                                }
                                //======= Add Accounting For Product=========//
                                $real_unit_cost = $this->bpas->formatDecimal(($item_net_cost_no_ship + $item_tax), 4);
                                if($this->Settings->module_account == 1){                                     
                                    $accTrans[] = array(
                                        'tran_type'    => 'Purchases',
                                        'tran_date'    => $date,
                                        'reference_no' => $reference,
                                        'account_code' => $this->accounting_setting->default_stock,
                                        'amount'       => ($subtotal),
                                        'narrative'    => $this->site->getAccountName($this->accounting_setting->default_stock).': '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$real_unit_cost,
                                        'description'  => $note,
                                        'biller_id'    => $biller_id,
                                        'project_id'   => $project_id,
                                        'people_id'    => $this->session->userdata('user_id'),
                                        'supplier_id'  => $supplier_id,
                                        'created_by'   => $this->session->userdata('user_id'),
                                    );
                                    // if($pr_item_discount > 0){
                                    //     $accTrans[] = array(
                                    //         'tran_type' => 'Purchases',
                                    //         'tran_date' => $date,
                                    //         'reference_no' => $reference,
                                    //         'account_code' => $this->accounting_setting->default_purchase_discount,
                                    //         'amount' => $pr_item_discount * (-1),
                                    //         'narrative' => 'Purchase Product Discount',
                                    //         'description' => $note,
                                    //         'biller_id' => $biller_id,
                                    //         'project_id' => $project_id,
                                    //         'people_id' => $this->session->userdata('user_id'),
                                    //         'supplier_id' => $supplier_id,
                                    //         'created_by'  => $this->session->userdata('user_id'),
                                    //     );
                                    // }
                                }
                                $products[] = ($product + $gst_data);
                                $total += $this->bpas->formatDecimal(($item_net_cost * $item_quantity), 4);
                            } else {
                                $this->session->set_flashdata('error', $this->lang->line("pr_not_found") . " ( " . $code . " ). " . $this->lang->line("line_no") . " " . $rw);
                                redirect($_SERVER["HTTP_REFERER"]);
                            }                            
                        }
                        $rw++;
                    }
                }
            }
            $order_discount  = $this->site->calculateDiscount($this->input->post('discount'), ($total + $product_tax));
            $total_discount  = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax       = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax       = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            if ($this->Settings->avc_costing) {
                $grand_total = $this->bpas->formatDecimal((($total + $total_tax) - $order_discount), 4);
            } else {
                $grand_total = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            }
            //======= Add Acounting for total purchase=========//  
            if($this->Settings->module_account == 1) {
                $accTrans[] = array(
                    'tran_type'        => 'Purchases',
                    'tran_date'        => $date,
                    'reference_no'     => $reference,
                    'account_code'     => $this->accounting_setting->default_payable,
                    'amount'           => -$grand_total,
                    'narrative'        => $this->site->getAccountName($this->accounting_setting->default_payable),
                    'description'      => $note,
                    'biller_id'        => $biller_id,
                    'project_id'       => $project_id,
                    'supplier_id'      => $supplier_id,
                    'created_by'       => $this->session->userdata('user_id'),
                    'activity_type'    => $this->site->get_activity($this->accounting_setting->default_payable)
                );
                if($order_discount > 0){
                    $accTrans[] = array(
                        'tran_type'    => 'Purchases',
                        'tran_date'    => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_purchase_discount,
                        'amount'       => -$order_discount,
                        'narrative'    => 'Order Discount',
                        'description'  => $note,
                        'biller_id'    => $biller_id,
                        'project_id'   => $project_id,
                        'supplier_id'  => $supplier_id,
                        'created_by'   => $this->session->userdata('user_id'),
                    );
                }
                if($order_tax > 0){
                    $accTrans[] = array(
                        'tran_type'    => 'Purchases',
                        'tran_date'    => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_purchase_tax,
                        'amount'       => $order_tax,
                        'narrative'    => 'Order Tax',
                        'description'  => $note,
                        'biller_id'    => $biller_id,
                        'project_id'   => $project_id,
                        'supplier_id'  => $supplier_id,
                        'created_by'   => $this->session->userdata('user_id'),
                    );
                }
                if (!$this->Settings->avc_costing) {
                    if($shipping > 0){
                        $accTrans[] = array(
                            'tran_type'    => 'Purchases',
                            'tran_date'    => $date,
                            'reference_no' => $reference,
                            'account_code' => $this->accounting_setting->default_purchase_freight,
                            'amount'       => $shipping,
                            'narrative'    => 'Shipping',
                            'description'  => $note,
                            'biller_id'    => $biller_id,
                            'project_id'   => $project_id,
                            'people_id'    => $this->session->userdata('user_id'),
                            'supplier_id'  => $supplier_id,
                            'created_by'   => $this->session->userdata('user_id'),
                        );
                    }
                }
            }   
            //==================end accounting===========//
            $data = array(
                'purchase_order_id' => null,
                'project_plan_id'   => null,
                'reference_no'      => $reference,
                'date'              => $date,
                'project_id'        => $this->input->post('project'),
                'supplier_id'       => $supplier_id,
                'supplier'          => $supplier,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'total'             => $total,
                'product_discount'  => $product_discount,
                'order_discount_id' => $this->input->post('discount'),
                'order_discount'    => $order_discount,
                'total_discount'    => $total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'shipping'          => $this->bpas->formatDecimal($shipping),
                'grand_total'       => $grand_total,
                'status'            => $status,
                'created_by'        => $this->session->userdata('user_id'),
                'biller_id'         => $biller_id,
            );
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
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
            // $this->bpas->print_arrays($data, $products);
        }
        if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products, $accTrans)) {
            // $this->purchases_model->addPurchase($data, $products, $accTrans, $purchase->return_id)) {
            $this->session->set_flashdata('message', $this->lang->line("purchase_added"));
            admin_redirect("purchases");
        } else {
            $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['ponumber']   = $this->site->getReference('p');
            $this->data['projects']   = $this->site->getAllProject();
            $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
            $this->data['billers']    = $this->site->getAllCompanies('biller');
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('purchase_by_excel')));
            $meta = array('page_title' => lang('purchase_by_excel'), 'bc' => $bc);
            $this->page_construct('purchases/purchase_by_csv', $meta, $this->data);
        }
    }
    
    public function purchase_by_csv()
    {
        $this->bpas->checkPermissions('import');
        $this->load->helper('security');
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('warehouse', $this->lang->line('warehouse'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line('supplier'), 'required');
        $this->form_validation->set_rules('userfile', $this->lang->line('upload_file'), 'xss_clean');
        //    $this->form_validation->set_rules('project', lang('project'), '');
        
        if ($this->form_validation->run() == true) {
            $quantity  = 'quantity';
            $product   = 'product';
            $unit_cost = 'unit_cost';
            $tax_rate  = 'tax_rate';
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('po');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = null;
            }
            $biller_id = $this->input->post('biller') ? $this->input->post('biller') : $this->Settings->default_biller;
            $project_id = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;

            $warehouse_id     = $this->input->post('warehouse');
            $supplier_id      = $this->input->post('supplier');
            $status           = $this->input->post('status');
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier         = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));

            if ($this->Settings->avc_costing) {
                 for ($r = 0; $r < $i; $r++) {
                    $item_unit_quantity = $_POST['quantity'][$r];
                    $item_quantity      = $_POST['product_base_quantity'][$r];

                    if (isset($item_quantity)) {
                        $qtycount += $item_quantity;
                    }
                }
                $costing = $this->bpas->formatDecimal($shipping / $qtycount);
            }else{
                $costing = 0;
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
                    admin_redirect('purchases/purchase_by_csv');
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

                $keys  = ['code', 'net_unit_cost', 'quantity', 'variant', 'item_tax_rate', 'discount', 'expiry'];
                $final = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv_pr) {
                    if (isset($csv_pr['code']) && isset($csv_pr['net_unit_cost']) && isset($csv_pr['quantity'])) {
                        if ($product_details = $this->purchases_model->getProductByCode($csv_pr['code'])) {
                            if ($csv_pr['variant']) {
                                $item_option = $this->purchases_model->getProductVariantByName($csv_pr['variant'], $product_details->id);
                                if (!$item_option) {
                                    $this->session->set_flashdata('error', lang('pr_not_found') . ' ( ' . $product_details->name . ' - ' . $csv_pr['variant'] . ' ). ' . lang('line_no') . ' ' . $rw);
                                    redirect($_SERVER['HTTP_REFERER']);
                                }
                            } else {
                                $item_option     = json_decode('{}');
                                $item_option->id = null;
                            }
                            $item_code        = $csv_pr['code'];
                            $item_net_cost    = $this->bpas->formatDecimal($csv_pr['net_unit_cost']);
                            $item_quantity    = $csv_pr['quantity'];
                            $quantity_received = $csv_pr['quantity'];
                            $quantity_balance = $csv_pr['quantity'];
                            $item_tax_rate    = $csv_pr['item_tax_rate'];
                            $item_discount    = $csv_pr['discount'];
                            $item_expiry      = isset($csv_pr['expiry']) ? $this->bpas->fsd($csv_pr['expiry']) : null;
                            $pr_discount      = $this->site->calculateDiscount($item_discount, $item_net_cost);
                            $pr_item_discount = $this->bpas->formatDecimal(($pr_discount * $item_quantity), 4);
                            $product_discount += $pr_item_discount;
                            $tax         = '';
                            $pr_item_tax = 0;
                            $unit_cost   = $item_net_cost - $pr_discount;
                            $gst_data    = [];
                            $tax_details = ((isset($item_tax_rate) && !empty($item_tax_rate)) ? $this->purchases_model->getTaxRateByName($item_tax_rate) : $this->site->getTaxRateByID($product_details->tax_rate));
                            if ($tax_details) {
                                $ctax     = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                                $item_tax = $ctax['amount'];
                                $tax      = $ctax['tax'];
                                if ($product_details->tax_method != 1) {
                                    $item_net_cost = $unit_cost - $item_tax;
                                }
                                $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_quantity, 4);
                                if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($this->Settings->state == $supplier_details->state), $tax_details)) {
                                    $total_cgst += $gst_data['cgst'];
                                    $total_sgst += $gst_data['sgst'];
                                    $total_igst += $gst_data['igst'];
                                }
                            }
                            $product_tax += $pr_item_tax;
                            $subtotal = $this->bpas->formatDecimal((($item_net_cost * $item_quantity) + $pr_item_tax), 4);
                            $unit     = $this->site->getUnitByID($product_details->unit);
                            $product  = [
                                'product_id'        => $product_details->id,
                                'product_code'      => $item_code,
                                'product_name'      => $product_details->name,
                                'option_id'         => $item_option->id,
                                'net_unit_cost'     => $item_net_cost,
                                'quantity'          => $item_quantity,
                                'product_unit_id'   => $product_details->unit,
                                'product_unit_code' => $unit->code,
                                'unit_quantity'     => $item_quantity,
                                'quantity_received'  => $quantity_received,
                                'quantity_balance'  => $quantity_balance,
                                'warehouse_id'      => $warehouse_id,
                                'item_tax'          => $pr_item_tax,
                                'tax_rate_id'       => $tax_details ? $tax_details->id : null,
                                'tax'               => $tax,
                                'discount'          => $item_discount,
                                'item_discount'     => $pr_item_discount,
                                'expiry'            => $item_expiry,
                                'subtotal'          => $subtotal,
                                'date'              => date('Y-m-d', strtotime($date)),
                                'status'            => $status,
                                'unit_cost'         => $this->bpas->formatDecimal(($item_net_cost + $item_tax), 4),
                                'real_unit_cost'    => $this->bpas->formatDecimal(($item_net_cost + $item_tax + $pr_discount), 4),
                            ];
                            //======= Add Accounting For Product=========//
                                $real_unit_cost = $this->bpas->formatDecimal(($item_net_cost + $item_tax), 4);
                                if($this->Settings->module_account == 1){

                                    $accTrans[] = array(
                                        'tran_type' => 'Purchases',
                                        'tran_date' => $date,
                                        'reference_no' => $reference,
                                        'account_code' => $this->accounting_setting->default_stock,
                                        'amount' => ($subtotal),
                                        'narrative' => $this->site->getAccountName($this->accounting_setting->default_stock).': '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$unit_cost,
                                        'description' => $note,
                                        'biller_id' => $biller_id,
                                        'project_id' => $project_id,
                                        'people_id' => $this->session->userdata('user_id'),
                                        'supplier_id' => $supplier_id,
                                        'created_by'  => $this->session->userdata('user_id'),
                                    );
                                    // if($pr_item_discount > 0){
                                    //     $accTrans[] = array(
                                    //         'tran_type' => 'Purchases',
                                    //         'tran_date' => $date,
                                    //         'reference_no' => $reference,
                                    //         'account_code' => $this->accounting_setting->default_purchase_discount,
                                    //         'amount' => $pr_item_discount * (-1),
                                    //         'narrative' => 'Purchase Product Discount',
                                    //         'description' => $note,
                                    //         'biller_id' => $biller_id,
                                    //         'project_id' => $project_id,
                                    //         'people_id' => $this->session->userdata('user_id'),
                                    //         'supplier_id' => $supplier_id,
                                    //         'created_by'  => $this->session->userdata('user_id'),
                                    //     );
                                    // }
                                }
                            //==================end accounting===========//
                            $products[] = ($product + $gst_data);
                            $total += $this->bpas->formatDecimal(($item_net_cost * $item_quantity), 4);
                        } else {
                            $this->session->set_flashdata('error', $this->lang->line('pr_not_found') . ' ( ' . $csv_pr['code'] . ' ). ' . $this->lang->line('line_no') . ' ' . $rw);
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                        $rw++;
                    }
                }
            }
            $order_discount = $this->site->calculateDiscount($this->input->post('discount') ? $this->input->post('order_discount') : null, ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            //======= Add Acounting for total purchase=========//  
            if ($this->Settings->module_account == 1) {
                $accTrans[] = array(
                    'tran_type'     => 'Purchases',
                    'tran_date'     => $date,
                    'reference_no'  => $reference,
                    'account_code'  => ($this->accounting_setting->default_payable),
                    'amount'        => -$grand_total,
                    'narrative'     => $this->site->getAccountName($this->accounting_setting->default_payable),
                    'description'   => $note,
                    'biller_id'     => $biller_id,
                    'project_id'    => $project_id,
                    'supplier_id'   => $supplier_id,
                    'created_by'    => $this->session->userdata('user_id'),
                    'activity_type' => $this->site->get_activity($this->accounting_setting->default_payable)
                );
                if ($order_discount > 0) {
                    $accTrans[] = array(
                        'tran_type' => 'Purchases',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_purchase_discount,
                        'amount' => -$order_discount,
                        'narrative' => 'Order Discount',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'supplier_id' => $supplier_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                if($order_tax > 0){
                    //===========Add Accounting for order tax========//
                    $accTrans[] = array(
                        'tran_type' => 'Purchases',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_purchase_tax,
                        'amount' => $order_tax,
                        'narrative' => 'Order Tax',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'supplier_id' => $supplier_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                if($shipping > 0){
                    //===========Add Accounting for shipping========//
                    $accTrans[] = array(
                        'tran_type' => 'Purchases',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' =>$this->accounting_setting->default_purchase_freight,
                        'amount' => $shipping,
                        'narrative' => 'Shipping',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'supplier_id' => $supplier_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
            }   
            //==================end accounting===========//
            $data           = [
                'purchase_order_id' => null,
                'project_plan_id'   => null,
                'reference_no'      => $reference,
                'project_id'        => $this->input->post('project'),
                'date'              => $date,
                'supplier_id'       => $supplier_id,
                'supplier'          => $supplier,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'total'             => $total,
                'product_discount'  => $product_discount,
                'order_discount_id' => $this->input->post('discount'),
                'order_discount'    => $order_discount,
                'total_discount'    => $total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'shipping'          => $this->bpas->formatDecimal($shipping),
                'grand_total'       => $grand_total,
                'status'            => $status,
                'created_by'        => $this->session->userdata('username'),
                'biller_id'         => $biller_id,
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
        if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products, $accTrans)) {
            $this->session->set_flashdata('message', $this->lang->line('purchase_added'));
            admin_redirect('purchases');
        } else {
            $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['ponumber']   = $this->site->getReference('p');
            $this->data['projects']   = $this->site->getAllProject();
            $this->data['billers']    = $this->site->getAllCompanies('biller');
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchases'), 'page' => lang('purchases')], ['link' => '#', 'page' => lang('add_purchase_by_csv')]];
            $meta = ['page_title' => lang('add_purchase_by_csv'), 'bc' => $bc];
            $this->page_construct('purchases/purchase_by_csv', $meta, $this->data);
        }
    }

    public function return_purchase($id = null)
    {
        $this->bpas->checkPermissions('return_purchases');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $purchase = $this->purchases_model->getPurchaseByID($id);
        if ($purchase->return_id) {
            $this->session->set_flashdata('error', lang('purchase_already_returned'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->form_validation->set_rules('return_surcharge', lang('return_surcharge'), 'required');
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('rep');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $biller_id        = $this->input->post('biller') ?  $this->input->post('biller') : $this->Settings->default_biller;
            $return_surcharge = $this->input->post('return_surcharge') ? $this->input->post('return_surcharge') : 0;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $supplier_details = $this->site->getCompanyByID($purchase->supplier_id);
            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            $i                = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_code          = $_POST['product'][$r];
                $purchase_item_id   = $_POST['purchase_item_id'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $item_expiry        = isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r]) && $_POST['expiry'][$r] != 'false' && $_POST['expiry'][$r] != 'undefined' && $_POST['expiry'][$r] != 'null' && $_POST['expiry'][$r] != 'NULL' && $_POST['expiry'][$r] != '00/00/0000' && $_POST['expiry'][$r] != '' ? $this->bpas->fsd($_POST['expiry'][$r]) : null; 
                $real_unit_cost     = $this->bpas->formatDecimal($_POST['real_unit_cost'][$r]);
                $unit_cost          = $this->bpas->formatDecimal($_POST['unit_cost'][$r]);
                $item_unit_quantity = (0 - $_POST['quantity'][$r]);
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = (0 - $_POST['product_base_quantity'][$r]);
                $item_addition_type = '';
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    $item_type        = $product_details->type;
                    $item_name        = $product_details->name;
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_cost);
                    $unit_cost        = $this->bpas->formatDecimal($unit_cost - $pr_discount);
                    $pr_item_discount = $this->bpas->formatDecimal(($pr_discount * $item_unit_quantity), 4);
                    $product_discount += $pr_item_discount;
                    $item_net_cost = $unit_cost;
                    $pr_item_tax   = $item_tax   = 0;
                    $tax           = '';
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];
                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_unit_quantity, 4);
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($this->Settings->state == $supplier_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }
                    $product_tax += $pr_item_tax;
                    $subtotal = $this->bpas->formatDecimal((($item_net_cost * $item_unit_quantity) + $pr_item_tax), 4);
                    $unit     = $this->site->getUnitByID($item_unit);
                    if ($this->Settings->accounting_method == '0') {
                        $costs = $this->site->getFifoCost($item_id, abs($item_quantity), $stockmoves);
                    } else if ($this->Settings->accounting_method == '1') {
                        $costs = $this->site->getLifoCost($item_id, abs($item_quantity), $stockmoves);
                    } else if ($this->Settings->accounting_method == '3') {
                        $costs = $this->site->getProductMethod($item_id, abs($item_quantity), $stockmoves);
                    }
                    if (isset($costs) && !empty($costs)) {
                        $productAcc = $this->site->getProductAccByProductId($item_id);
                        foreach ($costs as $cost_item) {
                            $stockmoves[] = array(
                                'purchase_item_id' => $purchase_item_id,
                                'transaction'      => 'Purchases',
                                'product_type'     => $product_details->type,
                                'product_id'       => $item_id,
                                'product_code'     => $item_code,
                                'product_name'     => $item_name,
                                'option_id'        => $item_option,
                                'quantity'         => -($cost_item['quantity']),
                                'unit_quantity'    => $unit->unit_qty,
                                'unit_code'        => $unit->code,
                                'unit_id'          => $item_unit,
                                'warehouse_id'     => $purchase->warehouse_id,
                                'expiry'           => $item_expiry,
                                'date'             => $date,
                                'serial_no'        => null,
                                'real_unit_cost'   => $cost_item['cost'],
                                'reference_no'     => $reference,
                                'user_id'          =>  $this->session->userdata('user_id'),
                            );
                            //=======accounting=========//
                            if ($this->Settings->module_account == 1) {       
                                if ($cost_item['cost']!=$real_unit_cost) {
                                    $acc_cost_amount = ($cost_item['cost'] - $real_unit_cost) * $cost_item['quantity'] ;
                                    $accTrans[] = array(
                                        'tran_type' => 'Purchases',
                                        'tran_date' => $date,
                                        'reference_no' => $reference,
                                        'account_code' => $this->accounting_setting->default_stock,
                                        'amount' => $acc_cost_amount * (-1),
                                        'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.($cost_item['cost'] - $real_unit_cost),
                                        'description' => $note,
                                        'biller_id' => $purchase->biller_id,
                                        'project_id' => $purchase->project_id,
                                        'supplier_id' => $purchase->supplier_id,
                                        'people_id' => $this->session->userdata('user_id'),
                                        'created_by' => $this->session->userdata('user_id'),
                                    );
                                    $accTrans[] = array(
                                        'tran_type' => 'Purchases',
                                        'tran_date' => $date,
                                        'reference_no' => $reference,
                                        'account_code' => $this->accounting_setting->default_cost,
                                        'amount' => $acc_cost_amount,
                                        'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.($cost_item['cost'] - $real_unit_cost),
                                        'description' => $note,
                                        'biller_id' => $purchase->biller_id,
                                        'project_id' => $purchase->project_id,
                                        'supplier_id' => $purchase->supplier_id,
                                        'people_id' => $this->session->userdata('user_id'),
                                        'created_by' => $this->session->userdata('user_id'),
                                    );
                                }
                                $accTrans[] = array(
                                    'tran_type' => 'Purchases',
                                    'tran_date' => $date,
                                    'reference_no' => $reference,
                                    'account_code' => $this->accounting_setting->default_stock,
                                    'amount' => -($real_unit_cost * $cost_item['quantity']),
                                    'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$real_unit_cost,
                                    'description' => $note,
                                    'biller_id' => $purchase->biller_id,
                                    'project_id' => $purchase->project_id,
                                    'created_by' => $this->session->userdata('user_id'),
                                    'supplier_id' => $purchase->supplier_id,
                                );            
                            }
                            //============end accounting=======//
                        }
                    } else {
                        $stockmoves[] = array(
                            'purchase_item_id' => $purchase_item_id,
                            'transaction'      => 'Purchases',
                            'product_type'     => $product_details->type,
                            'product_id'       => $item_id,
                            'product_code'     => $item_code,
                            'product_name'     => $item_name,
                            'option_id'        => $item_option,
                            'quantity'         => $item_quantity,
                            'unit_quantity'    => $item_unit_quantity,
                            'unit_code'        => $unit->code,
                            'unit_id'          => $item_unit,
                            'warehouse_id'     => $purchase->warehouse_id,
                            'expiry'           => $item_expiry,
                            'serial_no'        => null,
                            'date'             => $date,
                            'real_unit_cost'   => $real_unit_cost,
                            'reference_no'     => $reference,
                            'user_id'          => $this->session->userdata('user_id'),
                        );
                        //=======accounting=========//
                        if ($this->Settings->module_account == 1) { 
                            $productAcc = $this->site->getProductAccByProductId($item_id);
                            $accTrans[] = array(
                                'tran_type' => 'Purchases',
                                'tran_date' => $date,
                                'reference_no' => $reference,
                                'account_code' => $this->accounting_setting->default_stock,
                                'amount' => ($real_unit_cost * $item_quantity),
                                'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$real_unit_cost,
                                'description' => $note,
                                'biller_id' => $purchase->biller_id,
                                'project_id' => $purchase->project_id,
                                'supplier_id' => $purchase->supplier_id,
                                'people_id' => $this->session->userdata('user_id'),
                                'created_by' => $this->session->userdata('user_id'),
                            );
                        }       
                        //============end accounting=======//
                    }
                    $product = [
                        'product_id'        => $item_id,
                        'product_type'      => $product_details->type,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'expiry'            => $item_expiry,
                        'date'              => date('Y-m-d', strtotime($date)),
                        'option_id'         => $item_option,
                        'net_unit_cost'     => $item_net_cost,
                        'unit_cost'         => $this->bpas->formatDecimal($item_net_cost + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity'     => $item_unit_quantity,
                        'quantity_received' => $item_quantity,
                        'quantity_balance'  => $item_quantity,
                        'warehouse_id'      => $purchase->warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'real_unit_cost'    => $real_unit_cost,
                        'purchase_item_id'  => $purchase_item_id,
                        'status'            => 'received',
                        'addition_type'     => $item_addition_type,
                    ];
                    if ($unit->id != $product_details->unit) {
                        $product['base_unit_cost'] = $this->site->convertToBase($unit, ($item_net_cost + $item_tax));
                    } else {
                        $product['base_unit_cost'] = ($item_net_cost + $item_tax);
                    }
                    //==============Add Accounting =========//
                    // $accTrans[] = array(
                    //     'tran_type' => 'Purchases',
                    //     'tran_date' => $date,
                    //     'reference_no' => $reference,
                    //     'account_code' => $this->accounting_setting->default_stock,
                    //     'amount' => ($real_unit_cost * $item_quantity),
                    //     'narrative' => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$real_unit_cost,
                    //     'description' => $note,
                    //     'biller_id' => $biller_id,
                    //     'project_id' => $purchase->project_id,
                    //     'people_id' => $this->session->userdata('user_id'),
                    //     'supplier_id' => $purchase->supplier_id,
                    //     'created_by'  => $this->session->userdata('user_id'),
                    // );
                    if ($pr_item_discount > 0) {
                        $accTrans[] = array(
                            'tran_type' => 'Purchases',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $this->accounting_setting->default_purchase_discount,
                            'amount' => $pr_item_discount * (-1),
                            'narrative' => 'Purchase Product Discount',
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $purchase->project_id,
                            'people_id' => $this->session->userdata('user_id'),
                            'supplier_id' => $purchase->supplier_id,
                            'created_by'  => $this->session->userdata('user_id'),
                        );
                    }
                    //==============End Accounting =========//
                    $products[] = ($product + $gst_data);
                    $total += $this->bpas->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
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
            $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($return_surcharge) - $order_discount), 4);
            //======= Add Acounting for total purchase=========//   
            if($this->Settings->module_account == 1){
                $accTrans[] = array(
                    'tran_type' => 'Purchases',
                    'tran_date' => $date,
                    'reference_no' => $reference,
                    'account_code' => ($this->accounting_setting->default_payable ? $this->accounting_setting->default_payable : $this->input->post('payable_account')),
                    'amount' => abs($grand_total),
                    'narrative' => 'Purchases Return',
                    'description' => $note,
                    'biller_id' => $biller_id,
                    'project_id' => $purchase->project_id,
                    'supplier_id' => $purchase->supplier_id,
                    'created_by'  => $this->session->userdata('user_id'),
                    'activity_type' => $this->site->get_activity($this->accounting_setting->default_payable)
                );
                if(abs($order_discount) > 0){
                    //===========Add Accounting for discount========//
                    $accTrans[] = array(
                        'tran_type' => 'Purchases',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_purchase_discount,
                        'amount' => abs($order_discount),
                        'narrative' => 'Order Discount',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $purchase->project_id,
                        'supplier_id' => $purchase->supplier_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                //TODO add order tax in add purchase form
                if(abs($order_tax) > 0){
                    //===========Add Accounting for order tax========//
                    $accTrans[] = array(
                        'tran_type' => 'Purchases',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_purchase_tax,
                        'amount' => $order_tax,
                        'narrative' => 'Order Tax',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $purchase->project_id,
                        'supplier_id' => $purchase->supplier_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                if($return_surcharge > 0){
                    $accTrans[] = array(
                        'tran_type' => 'Purchases',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $purchase_return_acc,
                        'amount' => $return_surcharge,
                        'narrative' => 'Surcharge Return '.$purchase->reference_no,
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $purchase->project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'supplier_id' => $purchase->supplier_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
            }   
            //==================end accounting===========//
            $data = [
                'purchase_order_id'   => null,
                'project_plan_id'     => null,
                'biller_id'           => $purchase->biller_id,
                'date'                => $date,
                'purchase_id'         => $id,
                'reference_no'        => $purchase->reference_no,
                'supplier_id'         => $purchase->supplier_id,
                'supplier'            => $purchase->supplier,
                'warehouse_id'        => $purchase->warehouse_id,
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
                'surcharge'           => $this->bpas->formatDecimal($return_surcharge),
                'grand_total'         => $grand_total,
                'created_by'          => $this->session->userdata('user_id'),
                'return_purchase_ref' => $reference,
                'status'              => 'returned',
                'payment_status'      => $purchase->payment_status == 'paid' ? 'due' : 'pending',
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
        if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products, $accTrans, $purchase->return_id, $stockmoves)) {
            $this->session->set_flashdata('message', lang('return_purchase_added'));
            admin_redirect('purchases');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $purchase;
            if ($this->data['inv']->status != 'received' && $this->data['inv']->status != 'partial') {
                $this->session->set_flashdata('error', lang('purchase_status_x_received'));
                redirect($_SERVER['HTTP_REFERER']);
            }
            $this->bpas->deadlineDayEditing($this->data['inv']->date);
            $inv_items = $this->purchases_model->getAllPurchaseItems($id);
            // krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row                   = $this->site->getProductByID($item->product_id);
                $row->expiry           = (($item->expiry && $item->expiry != '0000-00-00') ? $this->bpas->hrsd($item->expiry) : '');
                $row->base_quantity    = $item->quantity;
                $row->base_unit        = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_cost   = $row->cost ? $row->cost : $item->unit_cost;
                $row->unit             = $item->product_unit_id;
                $row->qty              = $item->unit_quantity;
                $row->oqty             = $item->unit_quantity;
                $row->purchase_item_id = $item->id;
                $row->supplier_part_no = $item->supplier_part_no;
                $row->received         = $item->quantity_received ? $item->quantity_received : $item->quantity;
                $row->quantity_balance = $item->quantity_balance + ($item->quantity - $row->received);
                $row->discount         = $item->discount ? $item->discount : '0';
                $options               = $this->purchases_model->getProductOptions($row->id);
                $row->option           = !empty($item->option_id) ? $item->option_id : '';
                $row->real_unit_cost   = $item->real_unit_cost;
                $row->cost             = $this->bpas->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                $row->tax_rate         = $item->tax_rate_id;
                unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                $units    = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri       = $this->Settings->item_addition ? $row->id : $c;
                $set_price = $this->site->getUnitByProId($row->id);
                $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'row' => $row, 'set_price' => $set_price, 'units' => $units, 'tax_rate' => $tax_rate, 'options' => $options];
                $c++;
            }
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id']        = $id;
            $this->data['reference'] = '';
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $bc                      = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchases'), 'page' => lang('purchases')], ['link' => '#', 'page' => lang('return_purchase')]];
            $meta                    = ['page_title' => lang('return_purchase'), 'bc' => $bc];
            $this->page_construct('purchases/return_purchase', $meta, $this->data);
        }
    }

    /* --------------------------------------------------------------------------- */

    public function suggestions()
    {
        $term        = $this->input->get('term', true);
        $supplier_id = $this->input->get('supplier_id', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed  = $this->bpas->analyze_term($term);
        $sr        = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $rows      = $this->purchases_model->getProductNames($sr);
        if ($rows) {
            $r = 0;
            foreach ($rows as $row) {
                $cate_id = $row->subcategory_id?$row->subcategory_id:$row->category_id;
                $c                    = uniqid(mt_rand(), true);
                $option               = false;
                $row->item_tax_method = $row->tax_method;
                $options              = $this->purchases_model->getProductOptions($row->id);
                $purchase_item = $this->purchases_model->getPurchaseOrderByProducId($row->id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->purchases_model->getProductOptionByID($option_id) : current($options);
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt       = json_decode('{}');
                    $opt->cost = 0;
                    $option_id = false;
                }
                $row->option           = $option_id;
                $row->supplier_part_no = '';
                if ($row->supplier1 == $supplier_id) {
                    $row->supplier_part_no = $row->supplier1_part_no;
                } elseif ($row->supplier2 == $supplier_id) {
                    $row->supplier_part_no = $row->supplier2_part_no;
                } elseif ($row->supplier3 == $supplier_id) {
                    $row->supplier_part_no = $row->supplier3_part_no;
                } elseif ($row->supplier4 == $supplier_id) {
                    $row->supplier_part_no = $row->supplier4_part_no;
                } elseif ($row->supplier5 == $supplier_id) {
                    $row->supplier_part_no = $row->supplier5_part_no;
                }
                if ($opt->cost != 0) {
                    $row->cost = $opt->cost;
                }
                $cost_price_by_unit      = $this->site->getProductCostPriceByUnit($row->id, $row->purchase_unit);
                $row->cost               = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : ($cost_price_by_unit ? $cost_price_by_unit->cost : $row->cost);
                $row->real_unit_cost     = $cost_price_by_unit ? $cost_price_by_unit->cost : $row->cost;
                $row->base_quantity      = 1;
                $row->base_unit          = $row->unit;
                $row->base_unit_cost     = $cost_price_by_unit ? $cost_price_by_unit->cost : $row->cost;
                $row->unit               = $row->purchase_unit ? $row->purchase_unit : $row->unit;
                $row->new_entry          = 1;
                $row->expiry             = '';
                $row->qty                = 1;
                $row->order_qty          = $purchase_item->qty ? $purchase_item->qty:0;
                $row->quantity_balance   = '';
                $row->discount           = 0;
                $row->qoh                = $row->quantity;
                $row->total_purchase_qty = false;
                $row->weight             = 1;
                unset($row->details, $row->product_details, $row->price, $row->file, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);

                $units      = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate   = $this->site->getTaxRateByID($row->tax_rate);
                $categories = $this->site->getCategoryByID($cate_id);
                $set_price  = $this->site->getUnitByProId($row->id);
                $pr[] = [
                    'id'  => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row' => $row, 'tax_rate' => $tax_rate, 'set_price' => $set_price, 'units' => $units, 'options' => $options, 'fiber' => $categories, 
                ];
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

        if ($this->form_validation->run() == true && $this->purchases_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'sales');
        } else {
            $this->data['inv']      = $this->purchases_model->getPurchaseByID($id);
            $this->data['returned'] = false;
            if ($this->data['inv']->status == 'returned' || $this->data['inv']->return_id) {
                $this->data['returned'] = true;
            }
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'purchases/update_status', $this->data);
        }
    }
   function edit_expiry($id = null, $TP_id= null){
        $this->form_validation->set_rules('quantity_balance', lang('expiry'), 'required');
        $purchase = $this->purchases_model->getPurchaseItemByID($id);
        if ($this->form_validation->run() == true) {
            if($purchase->expiry == $this->input->post('expiry')){
                $expiry = $this->bpas->hrsd($this->input->post('expiry'));
            }else{
                $expiry =  $this->input->post('expiry');
            }
            $data = [
                'id'           => $id,
                'transfer_id'  => $purchase->transfer_id,
                'purchase_id'  => $purchase->purchase_id,
                'quantity_balance'     => $this->input->post('quantity_balance'),
                'expiry'       => $expiry ? $this->bpas->fsd($expiry) : null,
                'product_id'  => $purchase->product_id,
                'product_code'  => $purchase->product_code,
                'product_name'  => $purchase->product_name,
                'option_id'  => $purchase->option_id,
                'net_unit_cost'  => $purchase->net_unit_cost,
                'quantity'  => $this->input->post('quantity_balance'),
                'warehouse_id'  => $purchase->warehouse_id,
                'item_tax'  => ($purchase->item_tax / $purchase->quantity) * $this->input->post('quantity_balance'),
                'tax_rate_id'  => $purchase->tax_rate_id,
                'tax'  => $purchase->tax,
                'discount'  => $purchase->discount,
                'item_discount'  => ($purchase->item_discount / $purchase->quantity) * $this->input->post('quantity_balance'),
                'subtotal'  => ($this->input->post('quantity_balance') * ($purchase->unit_cost)),
                'date'  => date('Y-m-d'),
                'status'  => $purchase->status,
                'status_change'  => "change_expiry",
                'change_item_id'  => $id,
                'unit_cost'  => $purchase->unit_cost,
                'real_unit_cost'  => $purchase->real_unit_cost,
                'quantity_received'  => $this->input->post('quantity_balance'),
                'supplier_part_no'  => $purchase->supplier_part_no,
                'gst'  => $purchase->gst,
                'cgst'  => $purchase->cgst,
                'sgst'  => $purchase->sgst,
                'purchase_item_id'  => $purchase->purchase_item_id,
                'product_unit_id'  => $purchase->product_unit_id,
                'product_unit_code'  => $purchase->product_unit_code,
                'unit_quantity'  => $this->input->post('quantity_balance'),
                'igst'  => $purchase->igst,
                'addition_type'  => $purchase->addition_type,
                'convert_id'  => $purchase->convert_id,
                'transaction_id'  => $purchase->transaction_id,
                'transaction_type'  => $purchase->transaction_type,

            ];
         
        } elseif ($this->input->post('edit_expiry')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'sales');
        }
        if ($this->form_validation->run() == true && $this->purchases_model->editExpiry($data,$purchase->quantity_balance)) {
               $this->session->set_flashdata('message', lang('purchase_updated'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']       = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv']         = $purchase;
            $this->data['modal_js']    = $this->site->modal_js();

            $this->load->view($this->theme . 'purchases/edit_expiry', $this->data);
        }
    }
   
    public function view($purchase_id = null)
    {
        $this->bpas->checkPermissions('index');

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->purchases_model->getPurchaseByID($purchase_id);
   
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->data['rows']            = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier']        = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse']       = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']             = $inv;
        $this->data['payments']        = $this->purchases_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by']      = $this->site->getUser($inv->created_by);
        $this->data['updated_by']      = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : null;
        $this->data['return_rows']     = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : null;

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchases'), 'page' => lang('purchases')], ['link' => '#', 'page' => lang('view')]];
        $meta = ['page_title' => lang('view_purchase_details'), 'bc' => $bc];
        $this->page_construct('purchases/view', $meta, $this->data);
    }

    public function view_return($id = null)
    {
        $this->bpas->checkPermissions('return_purchases');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->purchases_model->getReturnByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->data['barcode']   = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['supplier']  = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['payments']  = $this->purchases_model->getPaymentsForPurchase($id);
        $this->data['user']      = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']       = $inv;
        $this->data['rows']      = $this->purchases_model->getAllReturnItems($id);
        $this->data['purchase']  = $this->purchases_model->getPurchaseByID($inv->purchase_id);
        $this->load->view($this->theme . 'purchases/view_return', $this->data);
    }
    //-------------account-----------
    public function getpending_Purchases($warehouse_id = null, $dt = null)
    {
        $this->bpas->checkPermissions('index', true,'accounts');
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }
        if ($this->input->get('user')) {
            $user_query = $this->input->get('user');
        } else {
            $user_query = NULL;
        }
        if ($this->input->get('supplier')) {
            $supplier = $this->input->get('supplier');
        } else {
            $supplier = NULL;
        }
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        } else {
            $warehouse = NULL;
        }

        if ($this->input->get('search_id')) {
            $search_id = $this->input->get('search_id');
        } else {
            $search_id = NULL;
        }

        if ($this->input->get('reference_no')) {
            $reference_no = $this->input->get('reference_no');
        } else {
            $reference_no = NULL;
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
            $start_date = $this->bpas->fld($start_date);
            $end_date = $this->bpas->fld($end_date);
        }
        if ($this->input->get('note')) {
            $note = $this->input->get('note');
        } else {
            $note = NULL;
        }
        
        if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        $detail_link = anchor('admin/purchases/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('purchase_details'));
        $payments_link = anchor('admin/purchases/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_payment_link = anchor('admin/purchases/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $email_link = anchor('admin/purchases/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_purchase'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link = anchor('admin/purchases/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_purchase'));
        $pdf_link = anchor('admin/purchases/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $print_barcode = anchor('admin/products/print_barcodes/?purchase=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_purchase") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('purchases/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_purchase') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>'

            .(($this->Owner || $this->Admin) ? '<li>'.$payments_link.'</li>' : ($this->GP['purchases-payments'] ? '<li>'.$payments_link.'</li>' : '')).
             (($this->Owner || $this->Admin) ? '<li>'.$add_payment_link.'</li>' : ($this->GP['purchases-payments'] ? '<li>'.$add_payment_link.'</li>' : '')).
             (($this->Owner || $this->Admin) ? '<li>'.$pdf_link.'</li>' : ($this->GP['purchases-export'] ? '<li>'.$pdf_link.'</li>' : '')).
             (($this->Owner || $this->Admin) ? '<li>'.$email_link.'</li>' : ($this->GP['purchases-email'] ? '<li>'.$email_link.'</li>' : '')).
             (($this->Owner || $this->Admin) ? '<li>'.$print_barcode.'</li>' : ($this->GP['products-print_barcodes'] ? '<li>'.$print_barcode.'</li>' : '')).

        '</ul>
        </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            
            $this->datatables
                ->select("id, date, reference_no,order_ref,request_ref, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status")
                ->from('purchases')
                ->where('payment_status !=','paid')
                // ->where('status !=','returned')
                ->where('warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select("id, date, reference_no,order_ref,request_ref, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status")
                ->from('purchases')
                // ->where('status !=','returned')
                ->where('payment_status !=','paid');
            if(isset($_REQUEST['d'])){
                $date_c = date('Y-m-d', strtotime('+3 months'));
                $date = $_GET['d'];
                $date1 = str_replace("/", "-", $date);
                $date =  date('Y-m-d', strtotime($date1));
                
                $this->datatables
                ->where("date >=", $date)
                ->where('payment_status !=','paid')
                ->where('DATE_SUB(date, INTERVAL 1 DAY) <= CURDATE()')
                ->where('purchases.payment_term <>', 0);
                
            }
            
        }
        
        // search options
        
        if($search_id) {
            $this->datatables->where('purchases.id', $search_id);
        }
        if ($user_query) {
            $this->datatables->where('purchases.created_by', $user_query);
        }
        if ($product) {
            $this->datatables->like('purchase_items.product_id', $product);
        }
        if ($supplier) {
            $this->datatables->where('purchases.supplier_id', $supplier);
        }
        if ($warehouse) {
            $this->datatables->where('purchases.warehouse_id', $warehouse);
        }
        if ($reference_no) {
            $this->datatables->like('purchases.reference_no', $reference_no, 'both');
        }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . ' 00:00:00" and "' . $end_date . ' 23:59:00"');
        }
        if ($note) {
            $this->datatables->like('purchases.note', $note, 'both');
        }
        
        if($dt == 30){
            $this->datatables->where('date('. $this->db->dbprefix('purchases')  .'.date) > CURDATE() AND date('. $this->db->dbprefix('purchases')  .'.date) <= DATE_ADD(now(), INTERVAL + 30 DAY)');
        }elseif($dt == 60){
            $this->datatables->where('date('. $this->db->dbprefix('purchases')  .'.date) > DATE_ADD(now(), INTERVAL + 30 DAY) AND date('. $this->db->dbprefix('purchases')  .'.date) <= DATE_ADD(now(), INTERVAL + 60 DAY)');
        }elseif($dt == 90){
            $this->datatables->where('date('. $this->db->dbprefix('purchases')  .'.date) > DATE_ADD(now(), INTERVAL + 60 DAY) AND date('. $this->db->dbprefix('purchases')  .'.date) <= DATE_ADD(now(), INTERVAL + 90 DAY)');
        }elseif($dt == 91){
            $this->datatables->where('date(purchases.date) >= DATE_ADD(now(), INTERVAL + 90 DAY)');
        }
        
        /*if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }*/
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
    public function expense_by_csv()
    {

        $this->bpas->checkPermissions('import_expanse', NULL, 'purchases');

        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            
            if (DEMO) {
                $this->session->set_flashdata('message', lang("disabled_in_demo"));
                admin_redirect('welcome');
            }

            if (isset($_FILES["userfile"])) {

                $this->load->library('upload');

                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = TRUE;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {

                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect("purchases/expense_by_csv");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = array('date', 'reference', 'amount', 'note', 'created_by', 'attachment', 'account_code', 'bank_code', 'biller_id', 'updated_by');
                
                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                    
                }
                //$this->bpas->print_arrays($final);
                
                $rw = 2;
                foreach ($final as $csv_pr) {
                    //$this->bpas->print_arrays($final);
                    $data[] = array(
                        'date' => $this->bpas->fsd($csv_pr['date']),
                        'reference' => $csv_pr['reference'],
                        'amount' => $csv_pr['amount'],
                        'note' => $csv_pr['note'],
                        'created_by' => $csv_pr['created_by'],
                        'attachment' => $csv_pr['attachment'],
                        'account_code' => $csv_pr['account_code'],
                        'bank_code' => $csv_pr['bank_code'],
                        'note' => $csv_pr['note'],
                        'biller_id' => $csv_pr['biller_id'],
                        'updated_by' => $csv_pr['updated_by']
                    );
                    
                    if($this->purchases_model->getExpenseByReference($csv_pr['reference'])){
                        $this->session->set_flashdata('error', 'Reference ( '.$csv_pr['reference'].' ) is already exist! Line: ' . $rw);
                        admin_redirect("purchases/expense_by_csv");
                    }
                    
                    $rw++;
                }
                //$this->bpas->print_arrays($data);
            }
        }

        if ($this->form_validation->run() == true && !empty($final)) {
            $this->purchases_model->addExpenses($data);
            $this->session->set_flashdata('message', lang("expense_added"));
            admin_redirect('purchases/expenses');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('import_expense_csv')));
            $meta = array('page_title' => lang('import_expense_csv'), 'bc' => $bc);
            $this->page_construct('purchases/import_expense', $meta, $this->data);
        }
    }
    function supplier_balance()
    {
        $this->bpas->checkPermissions('supplier_balance',NULL,'purchases');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('supplier_balance')));
        $meta = array('page_title' => lang('supplier_balance'), 'bc' => $bc);
        $this->page_construct('purchases/supplier_balance', $meta, $this->data);
    }
    
    function getSupplierBalance($pdf = NULL, $xls = NULL)
    {
        if ($this->input->get('supplier')) {
            $supplier = $this->input->get('supplier');
        } else {
            $supplier = NULL;
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
            $start_date = $this->bpas->fld($start_date);
            $end_date = $this->bpas->fld($end_date);
        }
        if ($pdf || $xls) {

            $this->db
                ->select($this->db->dbprefix('companies') . ".id as id, company, name, phone, email, count(bpas_purchases.id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance", FALSE)
                ->from("companies")
                ->join('purchases', 'purchases.supplier_id=companies.id')
                ->where('companies.group_name', 'supplier')
                ->order_by('companies.company asc')
                ->group_by('companies.id');

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('suppliers_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('company'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('email'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('total_purchases'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('total_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->company);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->phone);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->email);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatNumber($data_row->total));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatMoney($data_row->total_amount));
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatMoney($data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatMoney($data_row->balance));
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $filename = 'suppliers_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                            PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('companies') . ".id as idd, company, name, phone, email, count(" . $this->db->dbprefix('purchases') . ".id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance", FALSE)
                ->from("companies")
                ->join('purchases', 'purchases.supplier_id=companies.id')
                ->where('companies.group_name', 'supplier')
                ->where(array('purchases.status' => 'received', 'purchases.payment_status <>' => 'paid'))
                ->group_by('companies.id')
                ->add_column("Actions", "<div class='text-center'><a class=\"tip\" title='" . lang("view_balance") . "' href='" . admin_url('purchases/view_supplier_balance/$1') . "'><span class='label label-primary'>" . lang("view_balance") . "</span></a></div>", "idd")
                ->unset_column('id');
            if($supplier){
                $this->datatables->where('purchases.supplier_id', $supplier);
            }
            if($this->session->userdata('biller_id') ) {
                $this->datatables->where('purchases.biller_id', $this->session->userdata('biller_id') );
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            echo $this->datatables->generate();

        }

    }
    
    function view_supplier_balance($user_id = NULL, $biller_id = NULL)
    {
        
         $this->bpas->checkPermissions('supplier_balance',NULL,'purchases');
        if (!$user_id && $_GET['d'] == null) {
            $this->session->set_flashdata('error', lang("no_supplier_selected"));
            redirect('reports/suppliers');
        }
        
        if($biller_id != NULL){
            $this->data['biller_id'] = $biller_id;
        }else{
            $this->data['biller_id'] = "";
        }
        if(!$this->Owner && !$this->Admin) {
            if($user->biller_id){
                $this->data['billers'] = $this->site->getCompanyByArray($user->biller_id);
            }else{
                $this->data['billers'] = $this->site->getAllCompanies('biller');
            }
        }else{
            $this->data['billers'] = $this->site->getAllCompanies('biller');
        }
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['purchases'] = $this->purchases_model->getPurchasesTotals($user_id);
        $this->data['total_purchases'] = $this->purchases_model->getSupplierPurchases($user_id);
        $this->data['users'] = $this->purchases_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $this->data['user_id'] = $user_id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('view_supplier_balance')));
        $meta = array('page_title' => lang('view_supplier_balance'), 'bc' => $bc);
        $this->page_construct('purchases/view_supplier_balance', $meta, $this->data);
    }
    
    function getSupplierBalance_action($user_id){
        
        if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
            if ($_POST['val']) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $supplier = $this->site->getCompanyNameByCustomerID($user_id);
                $this->excel->getActiveSheet()->setTitle(lang('supplier'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('supplier_balance'));
                $this->excel->getActiveSheet()->setCellValue('A2','Supplier Name : ');
                $this->excel->getActiveSheet()->setCellValue('B2', $supplier->company);
                
                $this->excel->getActiveSheet()->SetCellValue('A3', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B3', lang('due_date'));
                $this->excel->getActiveSheet()->SetCellValue('C3', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('D3', lang('warehouse'));
                $this->excel->getActiveSheet()->SetCellValue('E3', lang('supplier'));
                $this->excel->getActiveSheet()->SetCellValue('F3', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('G3', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('H3', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('I3', lang('payment_status'));
                $this->excel->getActiveSheet()->getStyle('A1:I1')->getFont()->setBold(true);
                $this->excel->getActiveSheet()->getStyle('A2:I2')->getFont()->setBold(true);
                $this->excel->getActiveSheet()->getStyle('A3:I3')->getFont()->setBold(true);
                $this->excel->getActiveSheet()->getStyle('A1:I1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $this->excel->getActiveSheet()->getStyle('A2:I2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $this->excel->getActiveSheet()->getStyle('A3:I3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $row = 4;
                $sum_grandtotal = 0;
                $sum_paid = 0;
                $sum_balance = 0;
                foreach ($_POST['val'] as $id) {
                        $supplier = $this->db
                        ->select($this->db->dbprefix('purchases') . ".id, ".$this->db->dbprefix('purchases') . ".date, reference_no, due_date, " . 
                                     $this->db->dbprefix('warehouses') . ".name as wname, supplier ,
                                     grand_total, paid, (grand_total-paid) as balance, " . $this->db->dbprefix('purchases') . ".payment_status", FALSE)
                        ->join('purchase_items', 'purchase_items.purchase_id=purchases.id', 'left')
                        ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
                        ->join('companies', 'companies.id = purchase_items.supplier_id', 'left')
                        ->where(array('purchases.status' => 'received', 'purchases.payment_status <>' => 'paid','purchases.id' => $id))
                        ->group_by('purchases.id')
                        ->get("purchases")->result(); 
                        foreach($supplier as $sup){ 
                            $sum_grandtotal += $sup->grand_total;
                            $sum_paid += $sup->paid;
                            $sum_balance += $sup->balance;
                            $this->excel->getActiveSheet()->SetCellValue('A' . $row,$sup->date);
                            $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sup->due_date);
                            $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sup->reference_no);
                            $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sup->wname);
                            $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sup->supplier);
                            $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatMoney($sup->grand_total));
                            $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatMoney($sup->paid));
                            $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatMoney($sup->balance));
                            $this->excel->getActiveSheet()->SetCellValue('I' . $row, $sup->payment_status);
                           
                            $this->excel->getActiveSheet()->getStyle('F'. $row.':H'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                            $i = $row+1;
                            $this->excel->getActiveSheet()->SetCellValue('F' . $i, $this->bpas->formatMoney($sum_grandtotal));
                            $this->excel->getActiveSheet()->SetCellValue('G' . $i, $this->bpas->formatMoney($sum_paid));
                            $this->excel->getActiveSheet()->SetCellValue('H' . $i, $this->bpas->formatMoney($sum_balance));
                            $row++;
                        }   
                }
                                
                
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                
                $filename = lang('supplier_banlance'). date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($this->input->post('form_action') == 'export_pdf') {
                    $styleArray = array(
                        'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                    );
                    $this->excel->getActiveSheet()->getStyle('A3:I3')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $rw = 4;
                    foreach ($_POST['val'] as $id) {                        
                        $this->excel->getActiveSheet()->getStyle('F' . $rw . ':H' . $rw)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                        $rw++;
                    }
                                    
                    $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF';
                    $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                    if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                        die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                        PHP_EOL . ' as appropriate for your directory structure');
                    }

                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                    $objWriter->save('php://output');
                    exit();
                }
                if ($this->input->post('form_action') == 'export_excel') {              
                    ob_clean();
                    $this->excel->getActiveSheet()->getStyle('F'.$i. ':H'.$i)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $this->excel->getActiveSheet()->getStyle('F'. $i.':H'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }
             }else {
                $this->session->set_flashdata('error', $this->lang->line("no_supplier_selected. Please select at least one."));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }
    }
    
    function supplier_balance_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCurrency($id);
                    }
                    $this->session->set_flashdata('message', lang("currencies_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('suppliers_report'));
                    $this->excel->getActiveSheet()->mergeCells('A1:H1');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('Suppliers_Balance'));
                    $this->excel->getActiveSheet()->SetCellValue('A2', lang('company'));
                    $this->excel->getActiveSheet()->SetCellValue('B2', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C2', lang('phone'));
                    $this->excel->getActiveSheet()->SetCellValue('D2', lang('email_address'));
                    $this->excel->getActiveSheet()->SetCellValue('E2', lang('total_purchases'));
                    $this->excel->getActiveSheet()->SetCellValue('F2', lang('total_amount'));
                    $this->excel->getActiveSheet()->SetCellValue('G2', lang('paid'));
                    $this->excel->getActiveSheet()->SetCellValue('H2', lang('balance'));
                    
                     
                     $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                   $this->excel->getActiveSheet()->getStyle('A1:H1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $this->excel->getActiveSheet()->getStyle('A1:H1')->getFont()
                                              ->setName('Times New Roman')
                                              ->setSize(20);
                    $this->excel->getActiveSheet()->getRowDimension(1)->setRowHeight(30);
                    $this->excel->getActiveSheet()->getStyle('A2:H2')->getFont()
                                              ->setName('Times New Roman')
                                              ->setSize(13);
                    $this->excel->getActiveSheet()->getRowDimension(2)->setRowHeight(20);
                    $row = 3;
                    $sum_purcese = $sum_amount =$sum_payment= $sum_balance = 0;
                    foreach ($_POST['val'] as $id) {
                        
                        $sc = $this->reports_model->getSupplierByID($id);   
                        $sum_purcese += $sc->total;
                        $sum_amount += $sc->total_amount;
                        $sum_balance += $sc->balance;
                        $sum_payment += $sc->paid;                  
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->company);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->phone." ");
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->email);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatMoney($sc->total));
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatMoney($sc->total_amount));
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatMoney($sc->paid));
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatMoney($sc->balance));
                        
                        
                        $new_row = $row+1;
                        $this->excel->getActiveSheet()->SetCellValue('E' . $new_row, $this->bpas->formatDecimal($sum_purcese));
                        $this->excel->getActiveSheet()->SetCellValue('F' . $new_row, $this->bpas->formatDecimal($sum_amount));
                        $this->excel->getActiveSheet()->SetCellValue('G' . $new_row, $this->bpas->formatDecimal($sum_payment));
                        $this->excel->getActiveSheet()->SetCellValue('H' . $new_row, $this->bpas->formatDecimal($sum_balance));
                        $row++;
                    }
                    //$this->bpas->print_arrays($_POST['val']);

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(17);
                    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(17);
                    
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'suppliers_balance_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');
                        
                        $styleArray = array(
                        'font'  => array(
                            'bold'  => true
                        )
                        );
                        
                        $this->excel->getActiveSheet()->getStyle('A2:H2')->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getStyle('A2:H2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');
                        $styleArray = array(
                        'font'  => array(
                            'bold'  => true
                        )
                        );
                        $this->excel->getActiveSheet()->getStyle('E' . $new_row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
                        $this->excel->getActiveSheet()->getStyle('E' . $new_row.'')->getFont()->setBold(true);
                        $this->excel->getActiveSheet()->getStyle('F' . $new_row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
                        $this->excel->getActiveSheet()->getStyle('F' . $new_row.'')->getFont()->setBold(true);
                        $this->excel->getActiveSheet()->getStyle('G' . $new_row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
                        $this->excel->getActiveSheet()->getStyle('G' . $new_row.'')->getFont()->setBold(true);
                        $this->excel->getActiveSheet()->getStyle('H' . $new_row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_tax_rate_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    
    function getViewSupplierBalance()
    {
        
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }
        
        if ($this->input->get('biller_id')) {
            $biller_id = $this->input->get('biller_id');
        } else {
            $biller_id = NULL;
        }
        
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = NULL;
        }
        if ($this->input->get('supplier')) {
            $supplier = $this->input->get('supplier');
        } else {
            $supplier = NULL;
        }
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        } else {
            $warehouse = NULL;
        }
        if ($this->input->get('reference_no')) {
            $reference_no = $this->input->get('reference_no');
        } else {
            $reference_no = NULL;
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
            $start_date = $this->bpas->fld($start_date);
            $end_date = $this->bpas->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }
        $detail_link = anchor('purchases/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('purchase_details'));
        $payments_link = anchor('purchases/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_payment_link = anchor('purchases/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_delivery_link = anchor('sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $email_link = anchor('purchases/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link = anchor('sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $pdf_link = anchor('purchases/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_sale") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_sale') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $payments_link . '</li>
            <li>' . $add_payment_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>            
        </ul>
        </div></div>';
        $this->load->library('datatables');
        $this->datatables
        ->select($this->db->dbprefix('purchases') . ".id, ".$this->db->dbprefix('purchases') . ".date, due_date, reference_no, " . 
                     $this->db->dbprefix('warehouses') . ".name as wname, supplier ,
                     grand_total,
                     COALESCE (
                        (
                            SELECT
                                SUM(
                                    bpas_return_purchases.grand_total
                                )
                            FROM
                                bpas_return_purchases
                            WHERE
                                bpas_return_purchases.purchase_id = bpas_purchases.id
                        ),
                        0
                    ) AS return_purchases,
                     paid,
                     (
                        SELECT
                            SUM(

                                IF (
                                    bpas_payments.paid_by = 'deposit',
                                    bpas_payments.amount,
                                    0
                                )
                            )
                        FROM
                            bpas_payments
                        WHERE
                            bpas_payments.purchase_id = bpas_purchases.id
                    ) AS deposit,
                    COALESCE (
                        (
                            SELECT
                                SUM(bpas_payments.discount)
                            FROM
                                bpas_payments
                            WHERE
                                bpas_payments.purchase_id = bpas_purchases.id
                        ),
                        0
                    ) AS discount,
                     
                     (grand_total-paid) as balance, " . $this->db->dbprefix('purchases') . ".payment_status", FALSE)
            ->from('purchases')
            ->join('purchase_items', 'purchase_items.purchase_id=purchases.id', 'left')
            ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
            ->join('companies', 'companies.id = purchase_items.supplier_id', 'left')
            ->where(array('purchases.status' => 'received', 'purchases.payment_status <>' => 'paid'))
            ->group_by('purchases.id');
        
        if ($supplier) {
            $this->datatables->where('purchases.supplier_id', $supplier);
        }
        if ($user) {
            $this->datatables->where('purchases.created_by', $user);
        }
        if(!$this->Owner && !$this->Admin && $this->session->userdata('biller_id') ) {
             $this->datatables->where('purchases.biller_id', $this->session->userdata('biller_id') );
        }
        if ($biller_id) {
            $this->datatables->where('purchases.biller_id', $biller_id);
        }
        if ($product) {
            $this->datatables->like('purchase_items.product_id', $product);
        }
        if ($warehouse) {
            $this->datatables->where('purchases.warehouse_id', $warehouse);
        }
        if ($reference_no) {
            $this->datatables->where('purchases.reference_no', $reference_no);
        }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '00:00" and "' . $end_date . '23:59"');
        }
        $this->datatables->add_column("Actions", $action, "bpas_purchases.id");
        echo $this->datatables->generate();
    }
    public function supplier_opening_balance()
    {
        $this->bpas->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('userfile', $this->lang->line("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $quantity = "quantity";
            $product = "product";
            $unit_cost = "unit_cost";
            $tax_rate = "tax_rate";

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';

            if (isset($_FILES["userfile"])) {

                $this->load->library('upload');

                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = true;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect("purchases/supplier_opening_balance");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = array('supplier_no', 'reference', 'opening_date', 'shop_id','payment_term', 'balance', 'deposit');
                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                //$this->bpas->print_arrays($final);
                $rw = 2;
                $dp = '';
                $syncda = array();
                foreach ($final as $csv_pr) {
                    $dp = $this->site->getDepositsByID($csv_pr['supplier_no']);
                    $supplier = $this->site->getCompanyByID($csv_pr['supplier_no']);
                    $biller = $this->site->getCompanyByID($csv_pr['shop_id']);
                    
                    if(trim($supplier->group_name) != 'supplier'){
                        $this->session->set_flashdata('error', $this->lang->line("supplier_no_does_not_exist.") . ' (Line : ' . $rw . ')');
                        admin_redirect($_SERVER['HTTP_REFERER']);
                    }
                    
                    if(trim($biller->group_name) != 'biller'){
                        $this->session->set_flashdata('error', $this->lang->line("biller_no_does_not_exist.") . ' (Line : ' . $rw . ')');
                        admin_redirect($_SERVER['HTTP_REFERER']);
                    }
                    
                    //$date = $this->bpas->fld($csv_pr['opening_date']);
                    $date = strtr($csv_pr['opening_date'], '/', '-');
                    $date = date('Y-m-d H:i:s', strtotime($date));
                    $amount = $dp? $dp->deposit:0;
                    $deposit = $csv_pr['deposit'];
                    $deposits[] = array(
                        'company_id' =>  $csv_pr['supplier_no'],
                        'updated_by' =>  $this->session->userdata('user_id'),
                        'updated_at' =>  date('Y-m-d h:i:s'),
                        'date'       =>  date('Y-m-d h:i:s'),
                        'created_by' => $this->session->userdata('user_id'),
                        'amount'     =>  $deposit,
                        'biller_id'  => $csv_pr['shop_id'],
                        'reference'  => $csv_pr['reference'],
                        'paid_by'    => 'deposit',
                        'note'       => 'supplier opening balance',
                        'opening'    => 1
                    );
                    $purchase[] = array(
                        'reference_no'  => $csv_pr['reference'],
                        'date'          => $date,
                        'biller_id'     => $csv_pr['shop_id'],
                        'supplier_id'   => $supplier->id,
                        'supplier'      => $supplier->name,
                        'warehouse_id'  => 1,
                        'opening_ap'    => 1,
                        'total'         => $csv_pr['balance'],
                        'grand_total'   => $csv_pr['balance'],
                        'status'        => 'received',
                        'payment_status'=> 'due',
                        'payment_term'  => $csv_pr['payment_term'],
                        'created_by'    => $this->session->userdata('user_id')
                    );
                    
                    $syncda[] = $csv_pr['supplier_no'];
                    
                }
            }
            //$this->bpas->print_arrays($purchase);
        }
        
        if ($this->form_validation->run() == true ) {
            $this->purchases_model->addOpeningAP($purchase, $deposits, $syncda);
            $this->session->set_flashdata('message', $this->lang->line("supplier_opening_balance"));
            admin_redirect("purchases/supplier_opening_balance");
        } else {

            $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['ponumber'] = $this->site->getReference('po');

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('supplier_opening_balance')));
            $meta = array('page_title' => lang('supplier_opening_balance'), 'bc' => $bc);
            $this->page_construct('purchases/supplier_opening_balance', $meta, $this->data);
        }
    }
    function combine_payment_purchase()
    {
        $this->bpas->checkPermissions('payments', true);
        $this->load->helper('security');
        $arr = array();
        if ($this->input->get('data'))
        {
            $arr = explode(',', $this->input->get('data'));
        }
        
        //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $paid_by = $this->site->getCashAccountByCode($this->input->post('paid_by'));
            $paid_by_account = $paid_by->account_code;
            
            $photo ='';
            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                //$payment['attachment'] = $photo;
            }
            
            $purchase_id_arr    = $this->input->post('purchase_id');
            $supplier_balance   = $this->input->post("supplier_balance");
            $payable            = $this->input->post("payable");
            $biller_id          = $this->input->post('biller');
            $amount_paid_arr    = $this->input->post('amount_paid_line');
            $item_discount      = $this->input->post('discount_paid');
            $discount           = $this->input->post('discount');
            $percentage         = '%';
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so', $biller_id);
            $i = 0;
            foreach($purchase_id_arr as $purchase_id){
                $get_purchase = $this->purchases_model->getPurchaseById($purchase_id);
                $payment[] = array(
                    'date'          => $date,
                    'purchase_id'   => $purchase_id,
                    'reference_no'  => $reference_no,
                    'amount'        => $amount_paid_arr[$i],
                    'paid_by'       => $this->input->post('paid_by'),
                    'cheque_no'     => $this->input->post('cheque_no'),
                    'cc_no'         => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                    'cc_holder'     => $this->input->post('pcc_holder'),
                    'cc_month'      => $this->input->post('pcc_month'),
                    'cc_year'       => $this->input->post('pcc_year'),
                    'cc_type'       => $this->input->post('pcc_type'),
                    'note'          => $this->input->post('note'),
                    'created_by'    => $this->session->userdata('user_id'),
                    'type'          => 'sent',
                    'biller_id'     => $biller_id,
                    'attachment'    => $photo,
                    'bank_account'  => $this->input->post('bank_account'),
                );
                //=====accountig=====//
                if($this->Settings->module_account == 1){
                    $paying_to = isset($paid_by_account) ? $paid_by_account : $this->accounting_setting->default_cash ;

                    $paid_amount = $amount_paid_arr[$i];
                    $accTranPayments[$purchase_id][] = array(
                        'tran_type'     => 'Payment',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $this->accounting_setting->default_payable,
                        'amount'        => $paid_amount,
                        'narrative'     => $this->site->getAccountName($this->accounting_setting->default_payable),
                        'description'   => $this->input->post('note'),
                        'biller_id'     => $biller_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'supplier_id'   => $get_purchase->supplier_id,
                    );
                    $accTranPayments[$purchase_id][] = array(
                        'tran_type'     => 'Payment',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $paying_to,
                        'amount'        => $paid_amount * (-1),
                        'narrative'     => $this->site->getAccountName($paying_to),
                        'description'   => $this->input->post('note'),
                        'biller_id'     => $biller_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'supplier_id'   => $get_purchase->supplier_id,
                    );
                }
                //=====end accountig=====//
                $i++;
            }  
        }elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->purchases_model->addPurchasePaymentMulti($payment, $accTranPayments)) {
             if ((!$this->Owner && !$this->Admin) && 
                    $this->config->item('requested_ap') && $this->GP['purchases-payments_requested']) {
                    $this->session->set_flashdata('message', lang('payment_request_submited'));
                    admin_redirect('account/ap_requested');
            }else{
                $this->session->set_flashdata('message', lang("payment_added")); 
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $setting = $this->site->get_setting();
            if($this->session->userdata('biller_id')) {
                $biller_id = $this->session->userdata('biller_id');
            }else {
                $biller_id = $setting->default_biller;
            }
            
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['bankAccounts'] =  $this->site->getAllBankAccounts();
            $combine_payment = $this->purchases_model->getCombinePaymentPurById($arr);
            $this->data['combine_sales'] = $combine_payment;
            
            $this->data['payment_ref'] = ''; //$this->site->getReference('so');
            if ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')){
                $biller_id = $this->site->get_setting()->default_biller;
                $this->data['reference'] = $this->site->getReference('pp',$biller_id);
            }else{
                $biller_id = $this->session->userdata('biller_id');
                $this->data['reference'] = $this->site->getReference('pp',$biller_id);
            }
            $this->data['setting'] = $setting;
            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'purchases/combine_payment', $this->data);
        }
    }
    
    function combine_payment_supplier()
    {
        $this->bpas->checkPermissions('payments', true);
        $this->load->helper('security');
        $arr = array();
        if ($this->input->get('data'))
        {
            $arr = explode(',', $this->input->get('data'));
        }
        //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            
            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                //$payment['attachment'] = $photo;
            }
            $sale_id_arr = $this->input->post('sale_id');
            $biller_id = $this->input->post('biller');
            $amount_paid_arr = $this->input->post('amount_paid_line');
            $i = 0;
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so', $biller_id);
            foreach($sale_id_arr as $sale_id){
                $get_sale = $this->purchases_model->getPurchaseById($sale_id);
                $payment = array(
                    'date' => $date,
                    'purchase_id' => $sale_id,
                    'reference_no' => $reference_no,
                    'amount' => $amount_paid_arr[$i],
                    'paid_by' => $this->input->post('paid_by'),
                    'cheque_no' => $this->input->post('cheque_no'),
                    'cc_no' => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                    'cc_holder' => $this->input->post('pcc_holder'),
                    'cc_month' => $this->input->post('pcc_month'),
                    'cc_year' => $this->input->post('pcc_year'),
                    'cc_type' => $this->input->post('pcc_type'),
                    'note' => $this->input->post('note'),
                    'created_by' => $this->session->userdata('user_id'),
                    'type' => 'received',
                    'biller_id' => $biller_id,
                    'attachment' =>$photo,
                    'bank_account' => $this->input->post('bank_account'),
                    'add_payment' => '1'
                );
                if ($payment['amount'] > 0 ) {
                    $this->purchases_model->addPurchasePaymentMulti($payment);
                }
                $i++;
            }
            $this->session->set_flashdata('message', lang("payment_added"));
            redirect('purchases');
        } else{
            $setting = $this->site->get_setting();
            if ($this->session->userdata('biller_id')) {
                $biller_id = $this->session->userdata('biller_id');
            } else {
                $biller_id = $setting->default_biller;
            }
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['bankAccounts'] =  $this->site->getAllBankAccounts();
            $this->data['userBankAccounts'] =  $this->site->getAllBankAccountsByUserID();
            $combine_payment = $this->purchases_model->getCombinePaymentPurById($arr);
            $this->data['combine_sales'] = $combine_payment;
            $this->data['payment_ref'] = ''; //$this->site->getReference('so');
            if ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')){
                $biller_id = $this->site->get_setting()->default_biller;
                $this->data['reference'] = $this->site->getReference('pp',$biller_id);
            } else {
                $biller_id = $this->session->userdata('biller_id');
                $this->data['reference'] = $this->site->getReference('pp',$biller_id);
            }
            $this->data['setting'] = $setting;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['supplier_balance'] = "supplier_balance";

            $this->load->view($this->theme . 'purchases/combine_payment', $this->data);
        }
    }
    function combine_payment_payable()
    {
        $this->bpas->checkPermissions('payments', true);
        $this->load->helper('security');
        $arr = array();
        if ($this->input->get('data'))
        {
            $arr = explode(',', $this->input->get('data'));
        }
        
        //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            
            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                //$payment['attachment'] = $photo;
            }
            
            $sale_id_arr = $this->input->post('sale_id');
            
            $biller_id = $this->input->post('biller');
            $amount_paid_arr = $this->input->post('amount_paid_line');
            $i = 0;
            // $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay');
            $reference_no = $this->site->CheckedPaymentReference($this->input->post('reference_no'), $this->site->getReference('ppay'));
            foreach($sale_id_arr as $sale_id){
                $get_sale = $this->purchases_model->getPurchaseById($sale_id);
                
                $payment = array(
                    'date' => $date,
                    'purchase_id' => $sale_id,
                    'reference_no' => $reference_no,
                    'amount' => $amount_paid_arr[$i],
                    'paid_by' => $this->input->post('paid_by'),
                    'cheque_no' => $this->input->post('cheque_no'),
                    'cc_no' => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                    'cc_holder' => $this->input->post('pcc_holder'),
                    'cc_month' => $this->input->post('pcc_month'),
                    'cc_year' => $this->input->post('pcc_year'),
                    'cc_type' => $this->input->post('pcc_type'),
                    'note' => $this->input->post('note'),
                    'created_by' => $this->session->userdata('user_id'),
                    'type' => 'received',
                    'biller_id' => $biller_id,
                    'attachment' =>$photo,
                    'bank_account' => $this->input->post('bank_account'),
                    'add_payment' => '1'
                );
                
                if($payment['amount'] > 0 ){
                    $this->purchases_model->addPurchasePaymentMulti($payment);
                }
                
                $i++;
            }
            
            $this->session->set_flashdata('message', lang("payment_added"));
            admin_redirect('purchases');

        } else{
            
            $setting = $this->site->get_setting();
            if($this->session->userdata('biller_id')) {
                $biller_id = $this->session->userdata('biller_id');
            }else {
                $biller_id = $setting->default_biller;
            }
            
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['bankAccounts'] =  $this->site->getAllBankAccounts();
            $combine_payment = $this->purchases_model->getCombinePaymentPurById($arr);
            $this->data['combine_sales'] = $combine_payment;
            
            $this->data['payment_ref'] = ''; //$this->site->getReference('so');
            if ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')){
                $biller_id = $this->site->get_setting()->default_biller;
                $this->data['reference'] = $this->site->getReference('pp');
            }else{
                $biller_id = $this->session->userdata('biller_id');
                $this->data['reference'] = $this->site->getReference('pp');
            }
            $this->data['currency']         = $this->site->getCurrency();
            $this->data['setting'] = $setting;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['payable'] = "payable";
                
            $this->load->view($this->theme . 'purchases/combine_payment', $this->data);
        }
    }

    //-------------assets--------
    public function AssetSuggestions()
    {
        $term        = $this->input->get('term', true);
        $supplier_id = $this->input->get('supplier_id', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed  = $this->bpas->analyze_term($term);
        $sr        = $analyzed['term'];
        $option_id = $analyzed['option_id'];

        $rows = $this->purchases_model->getAssetNames($sr);
        if ($rows) {
            $r = 0;
            foreach ($rows as $row) {
                $cate_id = $row->subcategory_id?$row->subcategory_id:$row->category_id;
                $c                    = uniqid(mt_rand(), true);
                $option               = false;
                $row->item_tax_method = $row->tax_method;
                $options              = $this->purchases_model->getProductOptions($row->id);
                $purchase_item = $this->purchases_model->getPurchaseOrderByProducId($row->id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->purchases_model->getProductOptionByID($option_id) : current($options);
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt       = json_decode('{}');
                    $opt->cost = 0;
                    $option_id = false;
                }
                $row->option           = $option_id;
                $row->supplier_part_no = '';
                if ($row->supplier1 == $supplier_id) {
                    $row->supplier_part_no = $row->supplier1_part_no;
                } elseif ($row->supplier2 == $supplier_id) {
                    $row->supplier_part_no = $row->supplier2_part_no;
                } elseif ($row->supplier3 == $supplier_id) {
                    $row->supplier_part_no = $row->supplier3_part_no;
                } elseif ($row->supplier4 == $supplier_id) {
                    $row->supplier_part_no = $row->supplier4_part_no;
                } elseif ($row->supplier5 == $supplier_id) {
                    $row->supplier_part_no = $row->supplier5_part_no;
                }
                if ($opt->cost != 0) {
                    $row->cost = $opt->cost;
                }
                $row->cost             = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
                $row->real_unit_cost   = $row->cost;
                $row->base_quantity    = 1;
                $row->base_unit        = $row->unit;
                $row->base_unit_cost   = $row->cost;
                $row->unit             = $row->purchase_unit ? $row->purchase_unit : $row->unit;
                $row->new_entry        = 1;
                $row->expiry           = '';
                $row->qty              = 1;
                $row->order_qty        = $purchase_item->qty?$purchase_item->qty:0;
                $row->quantity_balance = '';
                $row->discount         = '0';
                $row->total_purchase_qty = false;
                unset($row->details, $row->product_details, $row->price, $row->file, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);

                $units    = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $categories = $this->site->getCategoryByID($cate_id);
                $set_price = $this->site->getUnitByProId($row->id);
                $pr[] = ['id' => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row'     => $row, 'tax_rate' => $tax_rate, 'set_price'=>$set_price, 'units' => $units, 'options' => $options, 'fiber' => $categories, ];
                $r++;
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }
    
    public function add_asset_expense($purchase_order_id = null, $quote_id = null, $plan_id = null)
    {
        $this->bpas->checkPermissions('expenses',true);

        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('warehouse', $this->lang->line('warehouse'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line('supplier'), 'required');
        $this->form_validation->set_rules('project', $this->lang->line('project'), '');
        
        $order_referent=$this->purchases_model->getPurchasesOrderbyID($purchase_order_id); 

        $this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('p');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $account_paid = $this->input->post('bank_account');
            $bank_account = $this->input->post('account_paid');

            $project_id = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $warehouse_id     = $this->input->post('warehouse');
            $supplier_id      = $this->input->post('supplier');
            $status           = $this->input->post('status');
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier         = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $payment_term     = $this->input->post('payment_term');
            $payment_term_details    = $this->site->getAllPaymentTermByID($payment_term);
            if ($this->Settings->payment_term) {
                $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            } else {
                $due_date         = (isset($payment_term_details[0]->id)? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);
            }
            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $qtycount         = 0;
            $i                = sizeof($_POST['product']);
            if ($this->Settings->avc_costing) {
                 for ($r = 0; $r < $i; $r++) {
                    $item_unit_quantity = $_POST['quantity'][$r];
                    $item_quantity      = $_POST['product_base_quantity'][$r];
                    if (isset($item_quantity)) {
                        $qtycount += $item_quantity;
                    }
                }
                $costing = $this->bpas->formatDecimal($shipping / $qtycount);
            } else {
                $costing = 0;
            }
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            for ($r = 0; $r < $i; $r++) {
                $item_code          = $_POST['product'][$r];
                $item_net_cost      = $this->bpas->formatDecimal($_POST['net_cost'][$r]) ;
                $unit_cost          = $this->bpas->formatDecimal($_POST['unit_cost'][$r] + $costing);
                $real_unit_cost     = $this->bpas->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $item_expiry        = isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r]) && $_POST['expiry'][$r] != 'false' && $_POST['expiry'][$r] != 'undefined' && $_POST['expiry'][$r] != 'null' && $_POST['expiry'][$r] != 'NULL' && $_POST['expiry'][$r] != '00/00/0000' && $_POST['expiry'][$r] != '' ? $this->bpas->fsd($_POST['expiry'][$r]) : null; 
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $supplier_part_no   = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_addition_type = '';
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    $item_type        = $product_details->type;
                    if ($item_expiry) {
                        $today = date('Y-m-d');
                        if ($item_expiry <= $today) {
                            $this->session->set_flashdata('error', lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                    }
                    // $unit_cost = $real_unit_cost;
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_cost);
                    $unit_cost        = $this->bpas->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost    = $unit_cost;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = $item_tax = 0;
                    $tax         = '';
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];
                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_unit_quantity, 4);
                    }
                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit     = $this->site->getUnitByID($item_unit);
                    $product = [
                        'product_id'        => $product_details->id,
                        'product_code'      => $item_code,
                        'product_name'      => $product_details->name,
                        'option_id'         => $item_option,
                        'net_unit_cost'     => $item_net_cost,
                        'unit_cost'         => $this->bpas->formatDecimal($item_net_cost + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity'     => $item_unit_quantity,
                        'quantity_balance'  => $status == 'received' ? $item_quantity : 0,
                        'quantity_received' => $status == 'received' ? $item_quantity : 0,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'expiry'            => $item_expiry,
                        'real_unit_cost'    => $real_unit_cost,
                        'date'              => date('Y-m-d', strtotime($date)),
                        'status'            => $status,
                        'supplier_part_no'  => $supplier_part_no,
                        'addition_type'     => $item_addition_type,
                    ];

                    if ($unit->id != $product_details->unit) {
                        $product['base_unit_cost'] = $this->site->convertToBase($unit, ($item_net_cost + $item_tax));
                    } else {
                        $product['base_unit_cost'] = ($item_net_cost + $item_tax);
                    }

                    $products[] = ($product + $gst_data);
                    $total += $this->bpas->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
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
            if ($this->Settings->avc_costing) {
                $grand_total    = $this->bpas->formatDecimal((($total + $total_tax) - $order_discount), 4);
            } else {
                $grand_total    = $this->bpas->formatDecimal((($total + $total_tax + $this->bpas->formatDecimal($shipping)) - $order_discount), 4);
            }
            //======= Add Accounting For Product=========//
            if($this->Settings->module_account == 1){
                $biller_id = $this->input->post('biller');
                $bank_account = $this->input->post('bank_account');//$_POST['bank_account'][$r];
                $account_paid = $this->input->post('paid_by');
                $accTrans[] = array(
                    'tran_type' => 'ExpenseAsset',
                    'tran_date' => $date,
                    'reference_no' => $reference,
                    'account_code' => $bank_account,
                    'amount' => $grand_total,
                    'narrative' => $this->site->getAccountName($bank_account),
                    'description' => $note,
                    'biller_id' => $biller_id,
                    'supplier_id' => $supplier_id,
                    'created_by'  => $this->session->userdata('user_id'),
                    'activity_type' => 0
                );
                if ($this->input->post('expenses_type') == 'due'){
                    $accTrans[] = array(
                        'tran_type' => 'ExpenseAsset',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_payable,
                        'amount' => -($grand_total),
                        'narrative' => $this->site->getAccountName($this->accounting_setting->default_payable),
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'supplier_id' => $supplier_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => $this->site->get_activity($this->accounting_setting->default_payable)
                    );
       
                }else{
                    $accTrans[] = array(
                        'tran_type' => 'ExpenseAsset',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $account_paid,
                        'amount' => -($grand_total),
                        'narrative' => $this->site->getAccountName($account_paid),
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'supplier_id' => $supplier_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => 0
                    );
                }
            }
            //==================end accounting===========//
            $data           = [
                'purchase_order_id' => !empty($purchase_order_id) ? $purchase_order_id : null,
                'project_plan_id'   => !empty($plan_id) ? $plan_id : null,
                'project_id'        => $this->input->post('project'),
                'reference_no'      => $reference,
                'order_ref'         =>$order_referent->reference_no ? $order_referent->reference_no :'',
                'request_ref'       => $order_referent->purchase_ref ? $order_referent->purchase_ref :'' ,
                'date'              => $date,
                'supplier_id'       => $supplier_id,
                'supplier'          => $supplier,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'total'             => $total,
                'product_discount'  => $product_discount,
                'order_discount_id' => $this->input->post('discount'),
                'order_discount'    => $order_discount,
                'total_discount'    => $total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'shipping'          => $this->bpas->formatDecimal($shipping),
                'grand_total'       => $grand_total,
                'status'            => $status,
                'created_by'        => $this->session->userdata('user_id'),
                'payment_term'      => $payment_term,
                'due_date'          => $due_date,
                'is_asset'          => 1,
                'biller_id'         => $this->input->post('biller'),
                'bank_account'      => $this->input->post('bank_account'),
                'bank_code'         => $this->input->post('paid_by'),
                'useful_life'       => $this->input->post('useful'),
                'residual_value'    => $this->input->post('residual_value'),
                'expenses_type'     => $this->input->post('expenses_type'),
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
        if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products, $accTrans)) {
            //TODO Review update purchase or quot to completed after process
            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line('purchase_added'));
            admin_redirect('assets/expenses');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if (!$purchase_order_id && !$quote_id) { 
                $this->data['quote_id'] = "";
            }
            $this->data['payment_term'] = $this->site->getAllPaymentTerm();
            $this->data['count']        = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['suppliers']    = $this->site->getAllCompanies('supplier');
            $this->data['categories']   = $this->site->getAllCategories();
            $this->data['tax_rates']    = $this->site->getAllTaxRates();
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['ponumber']     = $this->site->getReference('p');
            $this->data['projects']     = $this->site->getAllProject();
            $this->data['billers']      = $this->site->getAllCompanies('biller');
            $this->data['bankAccounts'] = $this->accounts_model->getAllChartAccounts();
            $this->data['paid_by']      = $this->accounts_model->getAllChartAccounts();
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc                 = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchases'), 'page' => lang('purchases')], ['link' => '#', 'page' => lang('add_purchase')]];
            $meta               = ['page_title' => lang('add_purchase'), 'bc' => $bc];
            $this->page_construct('assets/add_asset_expense', $meta, $this->data);
        }
    }

    public function edit_asset_expense($id = null)
    {
        $this->bpas->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->purchases_model->getPurchaseByID($id);
        if ($inv->status == 'returned' || $inv->return_id || $inv->return_purchase_ref) {
            $this->session->set_flashdata('error', lang('purchase_x_action'));
            admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
        }
        if (!$this->session->userdata('edit_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('reference_no', $this->lang->line('ref_no'), 'required');
        $this->form_validation->set_rules('warehouse', $this->lang->line('warehouse'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line('supplier'), 'required');
        $this->form_validation->set_rules('project', $this->lang->line('project'), '');


        $this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $project_id = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project;
            $warehouse_id     = $this->input->post('warehouse');
            $supplier_id      = $this->input->post('supplier');
            $status           = $this->input->post('status');
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier         = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $payment_term     = $this->input->post('payment_term');
            $payment_term_details    = $this->site->getAllPaymentTermByID($payment_term);
            if ($this->Settings->payment_term) {
                $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            }else{
                $due_date         = (isset($payment_term_details[0]->id)? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);
            }

            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $partial          = false;
            $qtycount         = 0;
            $i                = sizeof($_POST['product']);
            if ($this->Settings->avc_costing) {
                 for ($r = 0; $r < $i; $r++) {
                    $item_unit_quantity = $_POST['quantity'][$r];
                    $item_quantity      = $_POST['received_base_quantity'][$r];

                    if (isset($item_quantity)) {
                        $qtycount += $item_quantity;
                    }
                }
                $costing = $this->bpas->formatDecimal($shipping / $qtycount);
            } else {
                $costing = 0;
            }
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            for ($r = 0; $r < $i; $r++) {
                $item_code          = $_POST['product'][$r];
                $item_net_cost      = $this->bpas->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost          = $this->bpas->formatDecimal($_POST['unit_cost'][$r] + $costing);
                $real_unit_cost     = $this->bpas->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $quantity_received  = $_POST['received_base_quantity'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $item_expiry        = isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r]) && $_POST['expiry'][$r] != 'false' && $_POST['expiry'][$r] != 'undefined' && $_POST['expiry'][$r] != 'null' && $_POST['expiry'][$r] != 'NULL' && $_POST['expiry'][$r] != '00/00/0000' && $_POST['expiry'][$r] != '' ? $this->bpas->fsd($_POST['expiry'][$r]) : null; 
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $supplier_part_no   = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $quantity_balance   = $_POST['quantity_balance'][$r];
                $ordered_quantity   = $_POST['ordered_quantity'][$r];
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_addition_type = '';
                if ($status == 'received' || $status == 'partial') {
                    if ($quantity_received < $item_quantity) {
                        $partial = 'partial';
                    } elseif ($quantity_received > $item_quantity) {
                        $this->session->set_flashdata('error', lang('received_more_than_ordered'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                    $balance_qty = $quantity_received - ($ordered_quantity - $quantity_balance);
                } else {
                    $balance_qty       = $item_quantity;
                    $quantity_received = $item_quantity;
                }
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity) && isset($quantity_balance)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    // $unit_cost = $real_unit_cost;
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_cost);
                    $unit_cost        = $this->bpas->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost    = $unit_cost;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = 0;
                    $item_tax    = 0;
                    $tax         = '';
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];
                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_unit_quantity, 4);  
                    }
                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit     = $this->site->getUnitByID($item_unit);
                    $item = [
                        'product_id'        => $product_details->id,
                        'product_code'      => $item_code,
                        'product_name'      => $product_details->name,
                        'option_id'         => $item_option,
                        'net_unit_cost'     => $item_net_cost,
                        'unit_cost'         => $this->bpas->formatDecimal($item_net_cost + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity'     => $item_unit_quantity,
                        'quantity_balance'  => $balance_qty,
                        'quantity_received' => $quantity_received,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'expiry'            => $item_expiry,
                        'real_unit_cost'    => $real_unit_cost,
                        'supplier_part_no'  => $supplier_part_no,
                        'date'              => date('Y-m-d', strtotime($date)),
                        'addition_type'     => $item_addition_type,
                    ];

                    if ($unit->id != $product_details->unit) {
                        $item['base_unit_cost'] = $this->site->convertToBase($unit, ($item_net_cost + $item_tax));
                    } else {
                        $item['base_unit_cost'] = ($item_net_cost + $item_tax);
                    }
                    $items[] = ($item + $gst_data);
                    $total += $item_net_cost * $item_unit_quantity;
                }
            }

            if (empty($items)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                foreach ($items as $item) {
                    $item['status'] = ($status == 'partial') ? 'received' : $status;
                    $products[]     = $item;
                }
                krsort($products);
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);

            if ($this->Settings->avc_costing) {
                $grand_total    = $this->bpas->formatDecimal(($total + $total_tax - $order_discount), 4);
            }else{
                $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            }

            //======= Add Accounting For Product=========//
            if($this->Settings->module_account == 1){
                $biller_id= $this->input->post('biller');
                $bank_account = $this->input->post('bank_account');//$_POST['bank_account'][$r];
                $account_paid = $this->input->post('paid_by');
                $accTrans[] = array(
                    'tran_no'     => $id,
                    'tran_type' => 'ExpenseAsset',
                    'tran_date' => $date,
                    'reference_no' => $reference,
                    'account_code' =>  $bank_account,
                    'amount' => $grand_total,
                    'narrative' => $this->site->getAccountName($bank_account),
                    'note' => $this->input->post('note', true),
                    'biller_id' => $biller_id,
                    'supplier_id' => $supplier_id,
                    'created_by'  => $this->session->userdata('user_id'),
                    'activity_type' => 0
                );
                if ($this->input->post('expenses_type') == 'due'){
                    $accTrans[] = array(
                        'tran_no'     => $id,
                        'tran_type' => 'ExpenseAsset',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_payable,
                        'amount' => -($grand_total),
                        'narrative' => $this->site->getAccountName($this->accounting_setting->default_payable),
                        'note' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'supplier_id' => $supplier_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => $this->site->get_activity($this->accounting_setting->default_payable)
                    );
                }else{
                     $accTrans[] = array(
                        'tran_no'     => $id,
                        'tran_type' => 'ExpenseAsset',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $account_paid,
                        'amount' => -($grand_total),
                        'narrative' => $this->site->getAccountName($account_paid),
                        'note' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'supplier_id' => $supplier_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => 0
                    );
                }
            }
            //==================end accounting===========//
            $data = [
                'reference_no'      => $reference,
                'project_id'        => $this->input->post('project'),
                'supplier_id'       => $supplier_id,
                'supplier'          => $supplier,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'total'             => $total,
                'product_discount'  => $product_discount,
                'order_discount_id' => $this->input->post('discount'),
                'order_discount'    => $order_discount,
                'total_discount'    => $total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'shipping'          => $this->bpas->formatDecimal($shipping),
                'grand_total'       => $grand_total,
                'status'            => $status,
                'updated_by'        => $this->session->userdata('user_id'),
                'updated_at'        => date('Y-m-d H:i:s'),
                'payment_term'      => $payment_term,
                'due_date'          => $due_date,
                'adjust_paid'       => $this->input->post('adjust_paid'),
                'biller_id'         => $this->input->post('biller'),
                'bank_account'      => $this->input->post('bank_account'),
                'bank_code'         => $this->input->post('paid_by'),
                'useful_life'       => $this->input->post('useful'),
                'residual_value'    => $this->input->post('residual_value'),
                'expenses_type'     => $this->input->post('expenses_type'),
            ];
            if ($date) {
                $data['date'] = $date;
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
        if ($this->form_validation->run() == true && $this->purchases_model->updateAssetPurchase($id, $data, $products,$accTrans)) {
            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line('purchase_added'));
            admin_redirect('assets/expenses');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv']   = $inv;
            $this->bpas->deadlineDayEditing($this->data['inv']->date);
            $inv_items = $this->purchases_model->getAllPurchaseItems($id);
            // krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row                   = $this->site->getProductByID($item->product_id);
                $cate_id = $row->subcategory_id?$row->subcategory_id:$row->category_id;
                $row->expiry           = (($item->expiry && $item->expiry != '0000-00-00') ? $this->bpas->hrsd($item->expiry) : '');
                $row->base_quantity    = $item->quantity;
                $row->base_unit        = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_cost   = $row->cost ? $row->cost : $item->unit_cost;
                $row->unit             = $item->product_unit_id;
                $row->qty              = $item->unit_quantity;
                $row->oqty             = $item->quantity;
                $row->supplier_part_no = $item->supplier_part_no;
                $row->received         = $item->quantity_received ? $item->quantity_received : $item->quantity;
                $row->quantity_balance = $item->quantity_balance + ($item->quantity - $row->received);
                $row->discount         = $item->discount ? $item->discount : '0';
                $options               = $this->purchases_model->getProductOptions($row->id);
                $row->option           = $item->option_id;
                $row->real_unit_cost   = $item->real_unit_cost;
                $row->cost             = $this->bpas->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                $row->tax_rate         = $item->tax_rate_id;
                unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                $row->addition_type    = $item->addition_type;
                $row->total_purchase_qty = $item->quantity_balance + $row->base_quantity;
                $units    = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $categories = $this->site->getCategoryByID($cate_id);
                $ri       = $this->Settings->item_addition ? $row->id : $c;
                $set_price = $this->site->getUnitByProId($row->id);
                $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row'        => $row, 'tax_rate' => $tax_rate, 'set_price'=>$set_price, 'units' => $units, 'options' => $options, 'fiber' => $categories, ];
                $c++;
            }
            $this->data['payment_term']     = $this->site->getAllPaymentTerm();
            $this->data['inv_items']  = json_encode($pr);
            $this->data['id']         = $id;
            $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
            $this->data['purchase']   = $this->purchases_model->getPurchaseByID($id);
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['projects']         = $this->site->getAllProject();

            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['bankAccounts'] = $this->accounts_model->getAllChartAccounts();
            $this->data['paid_by']      = $this->accounts_model->getAllChartAccounts();
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->session->set_userdata('remove_pols', 1);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc                 = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchases'), 'page' => lang('purchases')], ['link' => '#', 'page' => lang('edit_purchase')]];
            $meta               = ['page_title' => lang('edit_purchase'), 'bc' => $bc];
            $this->page_construct('assets/edit_asset_expense', $meta, $this->data);
        }
    }

    public function delete_ApRequested($id = null)
    {
        $this->bpas->checkPermissions('delete', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->purchases_model->deleteAPRequestedPayment($id)) {
             $this->bpas->send_json(['error' => 0, 'msg' => lang('payment_deleted')]);
            
        }
        $this->session->set_flashdata('message', lang('payment_deleted'));
        admin_redirect('account/ap_requested');
    }

    public function add_approved_payment($id = null)
    {
        $this->bpas->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $purchase_id = $this->input->post('purchase_id');
        $purchase    = $this->purchases_model->getPurchaseByID($purchase_id);    
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        if ($this->form_validation->run() == true && 
            $this->purchases_model->addPayment($payment,$accTranPayments)) {
            $this->session->set_flashdata('message', lang('payment_updated'));
            admin_redirect('purchases');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['payment']  = $this->purchases_model->getRequestAPByID($id);
            $this->data['payment_refer'] = $this->site->getReference('ppay');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'purchases/add_approved_payment', $this->data);
        }
    }

    ////////////////////////OLD 09_10_2023/////////////////////

    public function add_stock_received($id = null)
    {
        $this->bpas->checkPermissions('add', null, 'stock_received');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->purchases_model->getPurchaseByID($id);
        if ($inv->status == 'returned' || $inv->return_id || $inv->return_purchase_ref) {
            $this->session->set_flashdata('error', lang('purchase_x_action'));
            admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
        }
        if (!$this->session->userdata('edit_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('reference_no', $this->lang->line('ref_no'), 'required');
        $this->form_validation->set_rules('warehouse', $this->lang->line('warehouse'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line('supplier'), 'required');
        $this->form_validation->set_rules('project', $this->lang->line('project'), '');
        $this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $project_id       = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project;
            $warehouse_id     = $this->input->post('warehouse');
            $supplier_id      = $this->input->post('supplier');
            $status           = $this->input->post('status') ? $this->input->post('status') : 'received';
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier         = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $payment_term     = $this->input->post('payment_term');
            $payment_term_details = $this->site->getAllPaymentTermByID($payment_term);
            if ($this->Settings->payment_term) {
                $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            }else{
                $due_date         = (isset($payment_term_details[0]->id)? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);
            }
            $total_qty        = $this->input->post('total_qty');

            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $partial          = false;
            $qtycount         = 0;
            $i                = sizeof($_POST['product']);
            if ($this->Settings->avc_costing) {
                 for ($r = 0; $r < $i; $r++) {
                    $item_unit_quantity = $_POST['quantity'][$r];
                    $item_quantity      = $_POST['received_base_quantity'][$r];
                    if (isset($item_quantity)) {
                        $qtycount += $item_quantity;
                    }
                }
                $costing = $this->bpas->formatDecimal($shipping / $qtycount);
            } else {
                $costing = 0;
            }

            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            for ($r = 0; $r < $i; $r++) {
                $item_code          = $_POST['product'][$r];
                $item_net_cost      = $this->bpas->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost          = $this->bpas->formatDecimal($_POST['unit_cost'][$r] + $costing);
                $real_unit_cost     = $this->bpas->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $quantity_received  = $_POST['received_base_quantity'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $item_expiry        = isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r]) && $_POST['expiry'][$r] != 'false' && $_POST['expiry'][$r] != 'undefined' && $_POST['expiry'][$r] != 'null' && $_POST['expiry'][$r] != 'NULL' && $_POST['expiry'][$r] != '00/00/0000' && $_POST['expiry'][$r] != '' ? $this->bpas->fsd($_POST['expiry'][$r]) : null; 
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $supplier_part_no   = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $quantity_balance   = $_POST['quantity_balance'][$r];
                $ordered_quantity   = $_POST['ordered_quantity'][$r];
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $purchase_item_id   = $_POST['purchase_item_id'][$r];
                $item_addition_type = '';
                if ($status == 'received' || $status == 'partial') {
                    if ($quantity_received < $item_quantity) {
                        $partial = 'partial';
                    } elseif ($quantity_received > $item_quantity) {
                        $this->session->set_flashdata('error', lang('received_more_than_ordered'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                    $balance_qty = $quantity_received - ($ordered_quantity - $quantity_balance);
                } else {
                    $balance_qty       = $item_quantity;
                    $quantity_received = $item_quantity;
                }
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity) && isset($quantity_balance)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    // $unit_cost = $real_unit_cost;
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_cost);
                    $unit_cost        = $this->bpas->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost    = $unit_cost;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = 0;
                    $item_tax    = 0;
                    $tax         = '';
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];
                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_unit_quantity, 4);
                    }
                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit     = $this->site->getUnitByID($item_unit);
                    $item = [
                        'product_id'        => $product_details->id,
                        'product_code'      => $item_code,
                        'product_name'      => $product_details->name,
                        'option_id'         => $item_option,
                        'net_unit_cost'     => $item_net_cost,
                        'unit_cost'         => $this->bpas->formatDecimal($item_net_cost + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity'     => $item_unit_quantity,
                        'quantity_balance'  => $balance_qty,
                        'quantity_received' => $quantity_received,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'expiry'            => $item_expiry,
                        'real_unit_cost'    => $real_unit_cost,
                        'supplier_part_no'  => $supplier_part_no,
                        'date'              => date('Y-m-d', strtotime($date)),
                        'addition_type'     => $item_addition_type,
                    ];
                    $sr_item = [
                        'purchase_item_id'  => $purchase_item_id,
                        'product_id'        => $product_details->id,
                        'quantity'          => $quantity_received,
                    ];
                    if ($unit->id != $product_details->unit) {
                        $item['base_unit_cost']    = $this->site->convertToBase($unit, ($item_net_cost + $item_tax));
                        $sr_item['base_unit_cost'] = $this->site->convertToBase($unit, ($item_net_cost + $item_tax));
                    } else {
                        $item['base_unit_cost']    = ($item_net_cost + $item_tax);
                        $sr_item['base_unit_cost'] = ($item_net_cost + $item_tax);
                    }
                    $items[] = ($item + $gst_data);
                    $total += $item_net_cost * $item_unit_quantity;
                    $sr_items[] = $sr_item;
                }
            }
            if (empty($items)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                foreach ($items as $item) {
                    $item['status'] = ($status == 'partial') ? 'received' : $status;
                    $products[]     = $item;
                }
                krsort($products);
            }
            $order_discount = $this->site->calculateDiscount($this->input->post('discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            if ($this->Settings->avc_costing) {
                $grand_total = $this->bpas->formatDecimal(($total + $total_tax - $order_discount), 4);
            } else {
                $grand_total = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            }
            $data = [
                'reference_no'                => $reference,
                'project_id'                  => $this->input->post('project'),
                'supplier_id'                 => $supplier_id,
                'supplier'                    => $supplier,
                'warehouse_id'                => $warehouse_id,
                // 'note'                        => $note,
                'total'                       => $total,
                'product_discount'            => $product_discount,
                'order_discount_id'           => $this->input->post('discount'),
                'order_discount'              => $order_discount,
                'total_discount'              => $total_discount,
                'product_tax'                 => $product_tax,
                'order_tax_id'                => $this->input->post('order_tax'),
                'order_tax'                   => $order_tax,
                'total_tax'                   => $total_tax,
                'shipping'                    => $this->bpas->formatDecimal($shipping),
                'grand_total'                 => $grand_total,
                'status'                      => $status,
                // 'updated_by'                  => $this->session->userdata('user_id'),
                // 'updated_at'                  => date('Y-m-d H:i:s'),
                // 'payment_term'                => $payment_term,
                // 'due_date'                    => $due_date,
                // 'adjust_paid'                 => $this->input->post('adjust_paid')
            ];
            // if ($_FILES['document']['size'] > 0) {
            //     $this->load->library('upload');
            //     $config['upload_path']   = $this->digital_upload_path;
            //     $config['allowed_types'] = $this->digital_file_types;
            //     $config['max_size']      = $this->allowed_file_size;
            //     $config['overwrite']     = false;
            //     $config['encrypt_name']  = true;
            //     $this->upload->initialize($config);
            //     if (!$this->upload->do_upload('document')) {
            //         $error = $this->upload->display_errors();
            //         $this->session->set_flashdata('error', $error);
            //         redirect($_SERVER['HTTP_REFERER']);
            //     }
            //     $photo              = $this->upload->file_name;
            //     $data['attachment'] = $photo;
            // }
            $stock_received_data = [
                'date'           => $date,
                'purchase_id'    => $id,
                'reference_no'   => ($this->input->post('received_reference_no') ? $this->input->post('received_reference_no') : $this->site->getReference('str')),
                'created_by'     => $this->session->userdata('user_id'),
                'total_quantity' => $total_qty,
                'warehouse_id'   => $warehouse_id,
                'note'           => $note,
            ];
            // $this->bpas->print_arrays($id, $data, $products, $accTrans);
        }
        if ($this->form_validation->run() == true && $this->purchases_model->add_stock_received($id, $stock_received_data, $sr_items)) {
            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line('stock_received_added'));
            admin_redirect('products/stock_received');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv']   = $inv;
            $this->bpas->deadlineDayEditing($this->data['inv']->date);
            $inv_items = $this->purchases_model->getAllPurchaseItems($id);
            // krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                if ($item->quantity <= $item->quantity_received) continue;
                $row                   = $this->site->getProductByID($item->product_id);
                $cate_id               = $row->subcategory_id ? $row->subcategory_id : $row->category_id;
                $row->expiry           = (($item->expiry && $item->expiry != '0000-00-00') ? $this->bpas->hrsd($item->expiry) : '');
                $row->base_quantity    = $item->quantity;
                $row->base_unit        = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_cost   = $row->cost ? $row->cost : $item->unit_cost;
                $row->unit             = $item->product_unit_id;
                $row->qty              = $item->unit_quantity;
                $row->oqty             = $item->quantity;
                $row->supplier_part_no = $item->supplier_part_no;
                $row->received         = $item->quantity - $item->quantity_received;
                $row->quantity_balance = $item->quantity_balance + ($item->quantity - $row->received);
                $row->discount         = $item->discount ? $item->discount : '0';
                $options               = $this->purchases_model->getProductOptions($row->id);
                $row->option           = $item->option_id;
                $row->real_unit_cost   = $item->real_unit_cost;
                $row->cost             = $this->bpas->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                $row->tax_rate         = $item->tax_rate_id;
                unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                $row->addition_type    = $item->addition_type;
                $row->purchase_item_id = $item->id;
                $row->x_received       = $item->quantity_received;
                $row->total_purchase_qty = $item->quantity_balance + $row->base_quantity;
                $units      = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate   = $this->site->getTaxRateByID($row->tax_rate);
                $categories = $this->site->getCategoryByID($cate_id);
                $ri         = $this->Settings->item_addition ? $row->id : $c;
                $set_price = $this->site->getUnitByProId($row->id);
                $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row'        => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'set_price' => $set_price, 'options' => $options, 'fiber' => $categories, ];
                $c++;
            }
            $this->data['reference_no'] = $this->site->getReference('str');
            $this->data['payment_term'] = $this->site->getAllPaymentTerm();
            $this->data['inv_items']  = json_encode($pr);
            $this->data['id']         = $id;
            $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
            $this->data['purchase']   = $this->purchases_model->getPurchaseByID($id);
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['projects']         = $this->site->getAllProject();
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->session->set_userdata('remove_pols', 1);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchases'), 'page' => lang('purchases')], ['link' => '#', 'page' => lang('add_stock_received')]];
            $meta = ['page_title' => lang('add_stock_received'), 'bc' => $bc];
            $this->page_construct('products/add_stock_received', $meta, $this->data);
        }
    }

    public function edit_stock_received($stock_received_id = null, $id = null)
    {
        $this->bpas->checkPermissions('edit', null, 'stock_received');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $stock_received = null;
        if ($stock_received = $this->purchases_model->getStockInByID($stock_received_id)) {
            $id = $stock_received->purchase_id;
        }
        $inv = $this->purchases_model->getPurchaseByID($id);
        if ($inv->status == 'returned' || $inv->return_id || $inv->return_purchase_ref) {
            $this->session->set_flashdata('error', lang('purchase_x_action'));
            admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
        }
        if (!$this->session->userdata('edit_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('reference_no', $this->lang->line('ref_no'), 'required');
        $this->form_validation->set_rules('warehouse', $this->lang->line('warehouse'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line('supplier'), 'required');
        $this->form_validation->set_rules('project', $this->lang->line('project'), '');
        $this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $project_id       = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project;
            $warehouse_id     = $this->input->post('warehouse');
            $supplier_id      = $this->input->post('supplier');
            $status           = $this->input->post('status') ? $this->input->post('status') : 'received';
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier         = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $payment_term     = $this->input->post('payment_term');
            $payment_term_details = $this->site->getAllPaymentTermByID($payment_term);
            if ($this->Settings->payment_term) {
                $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            } else {
                $due_date = (isset($payment_term_details[0]->id)? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);
            }
            $total_qty        = $this->input->post('total_qty');
            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $partial          = false;
            $qtycount         = 0;
            $i                = sizeof($_POST['product']);
            if ($this->Settings->avc_costing) {
                 for ($r = 0; $r < $i; $r++) {
                    $item_unit_quantity = $_POST['quantity'][$r];
                    $item_quantity      = $_POST['received_base_quantity'][$r];
                    if (isset($item_quantity)) {
                        $qtycount += $item_quantity;
                    }
                }
                $costing = $this->bpas->formatDecimal($shipping / $qtycount);
            } else {
                $costing = 0;
            }
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            for ($r = 0; $r < $i; $r++) {
                $item_code          = $_POST['product'][$r];
                $item_net_cost      = $this->bpas->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost          = $this->bpas->formatDecimal($_POST['unit_cost'][$r] + $costing);
                $real_unit_cost     = $this->bpas->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $quantity_received  = $_POST['received_base_quantity'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $item_expiry        = isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r]) && $_POST['expiry'][$r] != 'false' && $_POST['expiry'][$r] != 'undefined' && $_POST['expiry'][$r] != 'null' && $_POST['expiry'][$r] != 'NULL' && $_POST['expiry'][$r] != '00/00/0000' && $_POST['expiry'][$r] != '' ? $this->bpas->fsd($_POST['expiry'][$r]) : null; 
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $supplier_part_no   = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $quantity_balance   = $_POST['quantity_balance'][$r];
                $ordered_quantity   = $_POST['ordered_quantity'][$r];
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $purchase_item_id   = $_POST['purchase_item_id'][$r];
                $item_addition_type = '';
                if ($status == 'received' || $status == 'partial') {
                    if ($quantity_received < $item_quantity) {
                        $partial = 'partial';
                    } elseif ($quantity_received > $item_quantity) {
                        $this->session->set_flashdata('error', lang('received_more_than_ordered'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                    $balance_qty = $quantity_received - ($ordered_quantity - $quantity_balance);
                } else {
                    $balance_qty       = $item_quantity;
                    $quantity_received = $item_quantity;
                }
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity) && isset($quantity_balance)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    // $unit_cost = $real_unit_cost;
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_cost);
                    $unit_cost        = $this->bpas->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost    = $unit_cost;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = 0;
                    $item_tax    = 0;
                    $tax         = '';
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];
                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_unit_quantity, 4);
                    }
                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit     = $this->site->getUnitByID($item_unit);
                    $item = [
                        'product_id'        => $product_details->id,
                        'product_code'      => $item_code,
                        'product_name'      => $product_details->name,
                        'option_id'         => $item_option,
                        'net_unit_cost'     => $item_net_cost,
                        'unit_cost'         => $this->bpas->formatDecimal($item_net_cost + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity'     => $item_unit_quantity,
                        'quantity_balance'  => $balance_qty,
                        'quantity_received' => $quantity_received,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'expiry'            => $item_expiry,
                        'real_unit_cost'    => $real_unit_cost,
                        'supplier_part_no'  => $supplier_part_no,
                        'date'              => date('Y-m-d', strtotime($date)),
                        'addition_type'     => $item_addition_type,
                    ];
                    $sr_item = [
                        'purchase_item_id'  => $purchase_item_id,
                        'product_id'        => $product_details->id,
                        'quantity'          => $quantity_received,
                    ];
                    if ($unit->id != $product_details->unit) {
                        $item['base_unit_cost']    = $this->site->convertToBase($unit, ($item_net_cost + $item_tax));
                        $sr_item['base_unit_cost'] = $this->site->convertToBase($unit, ($item_net_cost + $item_tax));
                    } else {
                        $item['base_unit_cost']    = ($item_net_cost + $item_tax);
                        $sr_item['base_unit_cost'] = ($item_net_cost + $item_tax);
                    }
                    //======= Add Accounting For Product=========//
                        if($this->Settings->module_account == 1){
                            $biller_id = $this->Settings->default_biller;
                            $accTrans[] = array(
                                'tran_type' => 'Purchases',
                                'tran_no' => $id,
                                'tran_date' => $date,
                                'reference_no' => $reference,
                                'account_code' => $this->accounting_setting->default_stock,
                                'amount' => ($subtotal),
                                'narrative' => $this->site->getAccountName($this->accounting_setting->default_stock),
                                'description' => $note,
                                'biller_id' => $biller_id,
                                'project_id' => $project_id,
                                'supplier_id' => $supplier_id,
                                'created_by'  => $this->session->userdata('user_id'),
                                'activity_type' => 0
                            );
                        }
                    //==================end accounting===========//
                    $items[] = ($item + $gst_data);
                    $total += $item_net_cost * $item_unit_quantity;
                    $sr_items[] = $sr_item;
                }
            }
            if (empty($items)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                foreach ($items as $item) {
                    $item['status'] = ($status == 'partial') ? 'received' : $status;
                    $products[]     = $item;
                }
                krsort($products);
            }
            $order_discount = $this->site->calculateDiscount($this->input->post('discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            if ($this->Settings->avc_costing) {
                $grand_total    = $this->bpas->formatDecimal(($total + $total_tax - $order_discount), 4);
            } else {
                $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            }
            $accTrans = null;
            if ($this->Settings->module_account == 1) {
                $accTrans[] = array(
                    'tran_type' => 'Purchases',
                    'tran_no' => $id,
                    'tran_date' => $date,
                    'reference_no' => $reference,
                    'account_code' => $this->accounting_setting->default_payable,
                    'amount' => -$grand_total,
                    'narrative' => $this->site->getAccountName($this->accounting_setting->default_payable),
                    'description' => $note,
                    'biller_id' => $biller_id,
                    'project_id' => $project_id,
                    'supplier_id' => $supplier_id,
                    'created_by'  => $this->session->userdata('user_id'),
                    'activity_type' => $this->site->get_activity($this->accounting_setting->default_payable)
                );
                if($order_discount > 0){
                    $accTrans[] = array(
                        'tran_type' => 'Purchases',
                        'tran_no' => $id,
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_purchase_discount,
                        'amount' => -$order_discount,
                        'narrative' => $this->site->getAccountName($this->accounting_setting->default_purchase_discount),
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'supplier_id' => $supplier_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => 0
                    );
                }
                if($order_tax > 0){
                    $accTrans[] = array(
                        'tran_type' => 'Purchases',
                        'tran_no' => $id,
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_purchase_tax,
                        'amount' => $order_tax,
                        'narrative' => $this->site->getAccountName($this->accounting_setting->default_purchase_tax),
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'supplier_id' => $supplier_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => 0
                    );
                }
                if (!$this->Settings->avc_costing) {
                    if($shipping > 0){
                        $accTrans[] = array(
                            'tran_type' => 'Purchases',
                            'tran_no' => $id,
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $this->accounting_setting->default_purchase_freight,
                            'amount' => $shipping,
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_purchase_freight),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'supplier_id' => $supplier_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => 0
                        );
                    }
                }
            }   
            $data = [
                'reference_no'                => $reference,
                'project_id'                  => $this->input->post('project'),
                'supplier_id'                 => $supplier_id,
                'supplier'                    => $supplier,
                'warehouse_id'                => $warehouse_id,
                // 'note'                        => $note,
                'total'                       => $total,
                'product_discount'            => $product_discount,
                'order_discount_id'           => $this->input->post('discount'),
                'order_discount'              => $order_discount,
                'total_discount'              => $total_discount,
                'product_tax'                 => $product_tax,
                'order_tax_id'                => $this->input->post('order_tax'),
                'order_tax'                   => $order_tax,
                'total_tax'                   => $total_tax,
                'shipping'                    => $this->bpas->formatDecimal($shipping),
                'grand_total'                 => $grand_total,
                'status'                      => $status,
                // 'updated_by'                  => $this->session->userdata('user_id'),
                // 'updated_at'                  => date('Y-m-d H:i:s'),
                // 'payment_term'                => $payment_term,
                // 'due_date'                    => $due_date,
                // 'adjust_paid'                 => $this->input->post('adjust_paid')
            ];
            // if ($_FILES['document']['size'] > 0) {
            //     $this->load->library('upload');
            //     $config['upload_path']   = $this->digital_upload_path;
            //     $config['allowed_types'] = $this->digital_file_types;
            //     $config['max_size']      = $this->allowed_file_size;
            //     $config['overwrite']     = false;
            //     $config['encrypt_name']  = true;
            //     $this->upload->initialize($config);
            //     if (!$this->upload->do_upload('document')) {
            //         $error = $this->upload->display_errors();
            //         $this->session->set_flashdata('error', $error);
            //         redirect($_SERVER['HTTP_REFERER']);
            //     }
            //     $photo              = $this->upload->file_name;
            //     $data['attachment'] = $photo;
            // }
            $stock_received_data = [
                'date'           => $date,
                'purchase_id'    => $id,
                'reference_no'   => ($this->input->post('received_reference_no') ? $this->input->post('received_reference_no') : $this->site->getReference('str')),
                'created_by'     => $this->session->userdata('user_id'),
                'total_quantity' => $total_qty,
                'warehouse_id'   => $warehouse_id,
                'note'           => $note,
            ];
        }
        if ($this->form_validation->run() == true && $this->purchases_model->update_stock_received($id, $stock_received_id, $stock_received_data, $sr_items)) {
            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line('stock_received_updated'));
            admin_redirect('products/stock_received');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv']   = $inv;
            $this->bpas->deadlineDayEditing($this->data['inv']->date);
            $inv_items            = $this->purchases_model->getAllPurchaseItems($id);
            $stock_received_items = $this->purchases_model->getStockInItems($stock_received->id);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $key = array_search($item->id, array_column($stock_received_items, 'id'));
                if ($key === false) continue;
                $row                   = $this->site->getProductByID($item->product_id);
                $cate_id               = $row->subcategory_id ? $row->subcategory_id : $row->category_id;
                $row->expiry           = (($item->expiry && $item->expiry != '0000-00-00') ? $this->bpas->hrsd($item->expiry) : '');
                $row->base_quantity    = $item->quantity;
                $row->base_unit        = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_cost   = $row->cost ? $row->cost : $item->unit_cost;
                $row->unit             = $item->product_unit_id;
                $row->qty              = $item->unit_quantity;
                $row->oqty             = $item->quantity;
                $row->supplier_part_no = $item->supplier_part_no;
                $row->received         = $item->quantity - $item->quantity_received + $stock_received_items[$key]->stock_received_qty;
                $row->quantity_balance = $item->quantity_balance + ($item->quantity - $row->received);
                $row->discount         = $item->discount ? $item->discount : '0';
                $options               = $this->purchases_model->getProductOptions($row->id);
                $row->option           = $item->option_id;
                $row->real_unit_cost   = $item->real_unit_cost;
                $row->cost             = $this->bpas->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                $row->tax_rate         = $item->tax_rate_id;
                unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                $row->addition_type    = $item->addition_type;
                $row->purchase_item_id = $item->id;
                $row->x_received       = $item->quantity_received;
                $row->added_stock_qty  = $stock_received_items[$key]->stock_received_qty;
                $row->on_first_open    = 1;
                $row->total_purchase_qty = $item->quantity_balance + $row->base_quantity;
                $units      = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate   = $this->site->getTaxRateByID($row->tax_rate);
                $categories = $this->site->getCategoryByID($cate_id);
                $ri         = $this->Settings->item_addition ? $row->id : $c;
                $set_price = $this->site->getUnitByProId($row->id);
                $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row'        => $row, 'tax_rate' => $tax_rate, 'set_price' => $set_price, 'units' => $units, 'options' => $options, 'fiber' => $categories, ];
                $c++;
            }
            $this->data['reference_no'] = $stock_received->reference_no;
            $this->data['payment_term'] = $this->site->getAllPaymentTerm();
            $this->data['inv_items']  = json_encode($pr);
            $this->data['id']         = $id;
            $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
            $this->data['purchase']   = $this->purchases_model->getPurchaseByID($id);
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['projects']   = $this->site->getAllProject();
            $this->data['stock_received'] = $stock_received;
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->session->set_userdata('remove_pols', 1);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc                 = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchases'), 'page' => lang('purchases')], ['link' => '#', 'page' => lang('edit_stock_received')]];
            $meta               = ['page_title' => lang('edit_stock_received'), 'bc' => $bc];
            $this->page_construct('products/edit_stock_received', $meta, $this->data);
        }
    }

    public function delete_stock_received($id = null)
    {
        $this->bpas->checkPermissions('delete', null, 'stock_received');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->purchases_model->delete_stock_received($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('stock_received_deleted')]);
            }
            $this->session->set_flashdata('message', lang('stock_received_deleted'));
            admin_redirect('products/stock_received');
        }
    }

    public function view_stock_details($id = null)
    {
        $this->bpas->checkPermissions('index', null, 'stock_received');
        $this->data['stock_ins'] = $this->purchases_model->getAllStockInByPurchaseID($id);
        $this->data['inv']       = $this->purchases_model->getPurchaseByID($id);
        $this->load->view($this->theme . 'purchases/view_stock_details', $this->data);
    }

    public function view_stock_received($stock_in_id = null)
    {
        $this->bpas->checkPermissions('index', null, 'stock_received');
        if ($this->input->get('id')) {
            $stock_in_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $stock_in  = $this->purchases_model->getStockInByID($stock_in_id);
        $inv       = $this->purchases_model->getPurchaseByID($stock_in->purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->data['rows']            = $this->purchases_model->getAllPurchaseItems($stock_in->purchase_id);
        $this->data['supplier']        = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse']       = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']             = $inv;
        $this->data['created_by']      = $this->site->getUser($inv->created_by);
        $this->data['updated_by']      = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['stock_in_by']     = $stock_in->created_by ? $this->site->getUser($stock_in->created_by) : null;
        $this->data['stock_in']        = $stock_in;
        $this->data['stock_in_items']  = $this->purchases_model->getStockInItems($stock_in_id);
        $this->data['page_title']      = $this->lang->line('view_stock_received');
        $this->load->view($this->theme . 'purchases/view_stock_received', $this->data);
    }

    public function modal_view_stock_received($purchase_id = null)
    {
        $this->bpas->checkPermissions('index', null, 'stock_received');
        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by, true);
        }
        $this->data['rows']            = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier']        = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse']       = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']             = $inv;
        $sumKHM = 0;
        $sumUSD = 0;
        $sumEuro = 0;
        $sumBAT = 0;
        $sumYuan = 0;
        foreach($this->data['rows'] as $value){
            if($value->currency == "KHM"){
                $sumKHM = $sumKHM + $value->other_cost;
            }
            if($value->currency == "USD"){
                $sumUSD = $sumUSD + $value->unit_cost;
            }
            if($value->currency == "BAT"){
                $sumBAT = $sumBAT + $value->other_cost;
            }
            if($value->currency == "Yuan"){
                $sumYuan = $sumYuan + $value->other_cost;
            }
        }
        $this->data['sumKHM']       = $sumKHM;
        $this->data['sumUSD']       = $sumUSD;
        $this->data['sumEuro']      = $sumEuro;
        $this->data['sumBAT']       = $sumBAT;
        $this->data['sumYuan']      = $sumYuan;
        $this->data['currencys']    = $this->site->getAllCurrencies();
        $this->data['payments']        = $this->purchases_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by']      = $this->site->getUser($inv->created_by);
        $this->data['updated_by']      = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : null;
        $this->data['return_rows']     = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : null;
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);

        $this->load->view($this->theme . 'purchases/modal_view_stock_received', $this->data);
    }

    ////////////////////////OLD 09_10_2023/////////////////////

    public function receives($biller_id = NULL)
    {
        $this->bpas->checkPermissions();
        $this->data['error']   = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['billers'] = $this->site->getBillers();
        $this->data['biller']  = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc   = array(array('link' => base_url(), 'page' => lang('home')),array('link' => admin_url('purchases'), 'page' => lang('purchase')), array('link' => '#', 'page' => lang('receives')));
        $meta = array('page_title' => lang('receives'), 'bc' => $bc);
        $this->page_construct('purchases/receives', $meta, $this->data);
    }

    public function getReceives($biller_id = NULL)
    {
        $this->bpas->checkPermissions('index', null, 'stock_received');
        $detail_link  = anchor('admin/purchases/receive_note/$1', '<i class="fa fa-file-text-o"></i> ' . lang('receive_note'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal2"');
        $freight_link = anchor('admin/purchases/add_rec_freight/$1', '<i class="fa fa-money"></i> ' . lang('add_freight'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $edit_link    = anchor('admin/purchases/edit_receive/$1', '<i class="fa fa-edit"></i> ' . lang('edit_receive'), ' ');
        $delete_link  = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_receive") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('purchases/delete_receive/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_receive') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $detail_link . '</li>
                <li>' . $edit_link . '</li>
                <li>' . $delete_link . '</li>
            </ul>
        </div></div>';
        $this->load->library('datatables');
        $this->datatables->select(
                $this->db->dbprefix('stock_received') . ".id as id, 
                date, 
                re_reference_no,
                pu_reference_no,
                supplier,
                note,
                CONCAT(last_name, ' ', first_name) as received_by,  
                status, 
                attachment", false)
            ->from('stock_received')
            ->join('users', 'users.id = stock_received.received_by', 'left')
            ->group_by('stock_received.id');
        if ($biller_id) {
            $this->datatables->where('stock_received.biller_id', $biller_id);
        }   
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
            $this->datatables->where('stock_received.biller_id =', $this->session->userdata('biller_id'));
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $this->datatables->where_in('stock_received.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
    
    public function add_receive($id = false, $po_id = false)
    {
        $this->bpas->checkPermissions('add', null, 'stock_received');
        $inv = $this->purchases_model->getPurchaseByID($id);
        if (!empty($inv)) {
            if ($inv->status == 'returned' || $inv->return_id || $inv->return_purchase_ref) {
                $this->session->set_flashdata('error', lang('purchase_x_action'));
                admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
            }
            if (!$this->session->userdata('edit_right')) {
                $this->bpas->view_rights($inv->created_by);
            }
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('warehouse', $this->lang->line('warehouse'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');
        $this->form_validation->set_rules('receive_by', $this->lang->line("receive_by"), 'required');
        if ($this->form_validation->run() == true) {
            $purchase_order_id = $this->input->post("purchase_order_id");
            if ($purchase_order_id > 0) {
                $purchase_details = $this->purchase_order_model->getPurchaseorderByID($purchase_order_id);
            } else {
                $purchase_details = $this->purchases_model->getPurchaseByID($id);
            }
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $reference        = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('str');
            $si_reference_no  = $this->input->post('si_reference_no') ? $this->input->post('si_reference_no') : null;
            $receive_by       = $this->input->post('receive_by');
            $biller_id        = ($purchase_details->biller_id ? $purchase_details->biller_id : 0);
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $project_id       = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project;
            $warehouse_id     = $this->input->post('warehouse');
            $status           = $this->input->post('status');
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_id      = $this->input->post('supplier');
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier         = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $total_qty        = $this->input->post('total_qty');
            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $partial          = false;
            $qtycount         = 0;
            $i = sizeof($_POST['product_code']);
            if ($this->Settings->avc_costing) {
                 for ($r = 0; $r < $i; $r++) {
                    $item_unit_quantity = $_POST['quantity'][$r];
                    $item_quantity      = $_POST['product_base_quantity'][$r];
                    if (isset($item_quantity)) {
                        $qtycount += $item_quantity;
                    }
                }
                $costing = $this->bpas->formatDecimal($shipping / $qtycount);
            } else {
                $costing = 0;
            }
            for ($r = 0; $r < $i; $r++) {
                $purchase_item_id   = $_POST['purchase_item_id'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_net_cost      = $this->bpas->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost          = $this->bpas->formatDecimal($_POST['unit_cost'][$r] + $costing);
                $real_unit_cost     = $this->bpas->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $item_expiry        = isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r]) && $_POST['expiry'][$r] != 'false' && $_POST['expiry'][$r] != 'undefined' && $_POST['expiry'][$r] != 'null' && $_POST['expiry'][$r] != 'NULL' && $_POST['expiry'][$r] != '00/00/0000' && $_POST['expiry'][$r] != '' ? $this->bpas->fsd($_POST['expiry'][$r]) : null; 
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $supplier_part_no   = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_addition_type = '';
                $item_comment       = $_POST['product_comment'][$r];
                $item_note          = isset($_POST['pnote'][$r]) ? $_POST['pnote'][$r] : null;
                $serial_no          = isset($_POST['serial_no'][$r]) && !empty($_POST['serial_no'][$r]) && $_POST['serial_no'][$r] != 'undefined' && $_POST['serial_no'][$r] != 'false' && $_POST['serial_no'][$r] != 'null' && $_POST['serial_no'][$r] != 'NULL' ? $_POST['serial_no'][$r] : null;
                $parent_id          = $_POST['parent_id'][$r];
                $total_cbm          =  $_POST['cbm'][$r] * $item_unit_quantity;
                $sup_qty            = $_POST['sup_qty'][$r];
                $item_weight        = isset($_POST['weight'][$r]) ? $_POST['weight'][$r] : null;

                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details  = $this->purchases_model->getProductByCode($item_code);
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_cost);
                    $unit_cost        = $this->bpas->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost    = $unit_cost;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax      = 0;
                    $item_tax         = 0;
                    $tax              = '';
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];
                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_unit_quantity, 4);
                    }
                    $product_tax += $pr_item_tax;
                    $subtotal   = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit       = $this->site->getUnitByID($item_unit);
                    $products[] = array(
                        'product_type'      => $product_details->type,
                        'product_id'        => $product_details->id,
                        'product_code'      => $item_code,
                        'product_name'      => $product_details->name,
                        'option_id'         => $item_option,
                        'net_unit_cost'     => $item_net_cost,
                        'real_unit_cost'    => $real_unit_cost,
                        'unit_cost'         => $this->bpas->formatDecimal($item_net_cost + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_qty'          => $unit->operation_value,
                        'unit_quantity'     => $item_unit_quantity,
                        'weight'            => $item_weight,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'parent_id'         => $parent_id,
                        'serial_no'         => $serial_no,
                        'comment'           => $item_comment,
                        'reference_no'      => $reference,
                        'expiry'            => $item_expiry,
                        'total_cbm'         => $total_cbm,
                        'sup_qty'           => $sup_qty,
                        'reference_no'      => $reference,
                        'user_id'           => $this->session->userdata('user_id'),
                        'purchase_item_id'  => $purchase_item_id
                    );
                    $total  += ($item_net_cost * $item_unit_quantity);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }
            $data = array(
                'date'            => $date,
                'biller_id'       => $biller_id,
                'project_id'      => $project_id,
                'biller'          => $biller,
                'supplier_id'     => $supplier_id,
                'supplier'        => $supplier,
                'warehouse_id'    => $warehouse_id,
                're_reference_no' => $reference,
                'si_reference_no' => $si_reference_no,
                'pu_reference_no' => $purchase_details->reference_no,
                'address'         => $this->input->post('address'),
                'received_by'     => $receive_by,
                'note'            => $this->bpas->clear_tags($this->input->post('note')),
                'created_by'      => $this->session->userdata('user_id'),
                'status'          => ($purchase_order_id > 0 ? 'pending' : 'completed'),
                'dn_reference'    => $this->input->post('dn_reference'),
                'truck'           => $this->input->post('truck'),
            );
            if ($purchase_order_id > 0) {
                $data['purchase_order_id'] = $purchase_details->id;
            } else {
                $data['purchase_id'] = $purchase_details->id;
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
            // $this->bpas->print_arrays($data, $products);
        }
        if ($this->form_validation->run() == true && $this->purchases_model->addReceive($data, $products)) {
            $this->session->set_flashdata('message', $this->lang->line("stock_received_added"));
            $this->session->set_userdata('remove_rels', 1);
            admin_redirect('purchases/receives');
        } else {
            if ($id) {
                if ($po_id == 1) {
                    $purchase = $this->purchases_order_model->getPurchaseorderByID($id);
                    if ($purchase->status=='completed') {
                        $this->session->set_flashdata('error', lang('purchase_order_is_already_received'));
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
                    $inv_items = $this->purchases_model->getReceiveItemsByPOID($id);
                    $this->data['purchase_order_id'] = $id;
                } else {
                    $purchase = $this->purchases_model->getPurchaseByID($id);
                    if ($purchase->status == 'received') {
                        $this->session->set_flashdata('error', lang('purchase_is_already_received'));
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
                    $inv_items = $this->purchases_model->getReceiveItemsByPurchaseID($id);
                    $this->data['purchase_id'] = $id;
                }
                krsort($inv_items);
                $c = rand(100000, 9999999);
                foreach ($inv_items as $item) {             
                    $row = $this->site->getProductByID($item->product_id);
                    if (!$row) {
                        $row = json_decode('{}');
                        $row->quantity = 0;
                    } else {
                        unset($row->details, $row->product_details);
                    }
                    $row->received = ($item->unit_quantity - $item->received);
                    if ($row->received != 0) {
                        $row->purchase_item_id = $item->id;
                        $row->parent_id        = (isset($item->parent_id) ? $item->parent_id : '');
                        $row->id               = $item->product_id;
                        $row->code             = $row->code;
                        $row->name             = $item->product_name;
                        $row->qty              = $row->received;
                        $row->unit_quantity    = $item->unit_quantity;
                        $row->base_quantity    = $row->received;
                        $row->sup_qty          = $row->qty;
                        $row->base_unit        = $row->unit ? $row->unit : $item->product_unit_id;
                        $row->unit             = $item->product_unit_id;
                        $row->cost             = $this->bpas->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                        $row->base_unit_cost   = $row->cost ? $row->cost : $item->unit_cost;
                        $row->real_unit_cost   = $item->real_unit_cost;
                        $row->unit_cost        = $row->tax_method ? $item->unit_cost + ($item->item_discount / $item->unit_quantity) + ($item->item_tax / $item->unit_quantity) : $item->unit_cost + ($item->item_discount / $item->unit_quantity);
                        $row->option           = $item->option_id;
                        $row->discount         = $item->discount ? $item->discount : '0';
                        $row->tax_rate         = $item->tax_rate_id;
                        $row->expiry           = ((isset($item->expiry) && $item->expiry && $item->expiry != '0000-00-00') ? $this->bpas->hrsd($item->expiry) : '');                        
                        $row->total_cbm        = $item->total_cbm;
                        $row->serial_no        = $item->serial_no?$item->serial_no:'';
                        $row->weight           = $item->weight;
                        $options               = $this->purchases_model->getProductOptions($row->id);
                        $units                 = $this->site->getUnitbyProduct($row->id, $row->base_unit);
                        $tax_rate              = $this->site->getTaxRateByID($row->tax_rate);
                        $ri                    = $this->Settings->item_addition ? $row->id : $c;
                        $pr[$ri] = array(
                            'id'  => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 
                            'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                        $c++;
                    }
                }
                $this->data['inv'] = $purchase;
                $this->data['inv_items'] = json_encode($pr);
                $this->data['supplier'] = $this->site->getCompanyByID($purchase->supplier_id);
            }
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['purchases'] = $this->site->getRefPurchases('received');
            $this->data['purchase_orders'] = $this->purchases_model->getRefPurchaseRC();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['users'] = $this->site->getAllUsers();
            $this->data['re_reference_no'] = '';
            $bc   = array(array('link' => base_url(), 'page' => lang('home')),array('link' => admin_url('purchases'), 'page' => lang('purchase')), array('link' => admin_url('purchases/receives'), 'page' => lang('receive_items')), array('link' => '#', 'page' => lang('add_stock_received')));
            $meta = array('page_title' => lang('add_stock_received'), 'bc' => $bc);
            $this->page_construct('purchases/add_receive', $meta, $this->data);
        }
    }

    public function edit_receive($id = false)
    {
        $this->bpas->checkPermissions('edit', null, 'stock_received');
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('warehouse', $this->lang->line('warehouse'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');
        $this->form_validation->set_rules('receive_by', $this->lang->line("receive_by"), 'required');
        if ($this->form_validation->run() == true) {
            $purchase_order_id = $this->input->post("purchase_order_id");
            if ($purchase_order_id > 0) {
                $purchase_details = $this->purchases_order_model->getPurchaseorderByID($purchase_order_id);
            } else {
                $purchase_id = $this->input->post("purchase_id");
                $purchase_details = $this->purchases_model->getPurchaseByID($purchase_id);
            }
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $reference        = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('str');
            $si_reference_no  = $this->input->post('si_reference_no') ? $this->input->post('si_reference_no') : null;           
            $receive_by       = $this->input->post('receive_by');
            $biller_id        = ($purchase_details->biller_id ? $purchase_details->biller_id : 0);
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $project_id       = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project;
            $warehouse_id     = $this->input->post('warehouse');
            $status           = $this->input->post('status');
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_id      = $this->input->post('supplier');
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier         = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));     
            $total_qty        = $this->input->post('total_qty');
            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $partial          = false;
            $qtycount         = 0;
            $i = sizeof($_POST['product_code']);
            if ($this->Settings->avc_costing) {
                 for ($r = 0; $r < $i; $r++) {
                    $item_unit_quantity = $_POST['quantity'][$r];
                    $item_quantity      = $_POST['product_base_quantity'][$r];
                    if (isset($item_quantity)) {
                        $qtycount += $item_quantity;
                    }
                }
                $costing = $this->bpas->formatDecimal($shipping / $qtycount);
            } else {
                $costing = 0;
            }
            for ($r = 0; $r < $i; $r++) {
                $purchase_item_id   = $_POST['purchase_item_id'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_net_cost      = $this->bpas->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost          = $this->bpas->formatDecimal($_POST['unit_cost'][$r] + $costing);
                $real_unit_cost     = $this->bpas->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $item_expiry        = isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r]) && $_POST['expiry'][$r] != 'false' && $_POST['expiry'][$r] != 'undefined' && $_POST['expiry'][$r] != 'null' && $_POST['expiry'][$r] != 'NULL' && $_POST['expiry'][$r] != '00/00/0000' && $_POST['expiry'][$r] != '' ? $this->bpas->fsd($_POST['expiry'][$r]) : null; 
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $supplier_part_no   = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_addition_type = '';
                $item_comment       = $_POST['product_comment'][$r];
                $item_note          = isset($_POST['pnote'][$r]) ? $_POST['pnote'][$r] : null;
                $serial_no          = isset($_POST['serial_no'][$r]) && !empty($_POST['serial_no'][$r]) && $_POST['serial_no'][$r] != 'undefined' && $_POST['serial_no'][$r] != 'false' && $_POST['serial_no'][$r] != 'null' && $_POST['serial_no'][$r] != 'NULL' ? $_POST['serial_no'][$r] : null;
                $parent_id          = $_POST['parent_id'][$r];
                $total_cbm          =  $_POST['cbm'][$r] * $item_unit_quantity;
                $sup_qty            = $_POST['sup_qty'][$r];
                $weight             = isset($_POST['weight'][$r]) ? $_POST['weight'][$r] : null;

                if (isset($item_code)) {
                    $product_details  = $this->purchases_model->getProductByCode($item_code);
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_cost);
                    $unit_cost        = $this->bpas->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost    = $unit_cost;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax      = 0;
                    $item_tax         = 0;
                    $tax              = '';
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];
                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_unit_quantity, 4);
                    }
                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit     = $this->site->getUnitByID($item_unit);
                    $products[] = array(
                        'product_type'      => $product_details->type,
                        'product_id'        => $product_details->id,
                        'product_code'      => $item_code,
                        'product_name'      => $product_details->name,
                        'option_id'         => $item_option,
                        'net_unit_cost'     => $item_net_cost,
                        'real_unit_cost'    => $real_unit_cost,
                        'unit_cost'         => $this->bpas->formatDecimal($item_net_cost + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_qty'          => $unit->operation_value,
                        'unit_quantity'     => $item_unit_quantity,
                        'weight'            => $weight,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'parent_id'         => $parent_id,
                        'serial_no'         => $serial_no,
                        'comment'           => $item_comment,
                        'reference_no'      => $reference,
                        'expiry'            => $item_expiry,
                        'total_cbm'         => $total_cbm,
                        'sup_qty'           => $sup_qty,
                        'reference_no'      => $reference,
                        'user_id'           => $this->session->userdata('user_id'),
                        'purchase_item_id'  => $purchase_item_id
                    );
                    $total += ($item_net_cost * $item_unit_quantity);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }
            $data = array(
                'date'            => $date,
                'biller_id'       => $biller_id,
                'biller'          => $biller,
                'supplier_id'     => $supplier_id,
                'supplier'        => $supplier,
                'warehouse_id'    => $warehouse_id,
                're_reference_no' => $reference,
                'si_reference_no' => $si_reference_no,
                'pu_reference_no' => $purchase_details->reference_no,
                'address'         => $this->input->post('address'),
                'received_by'     => $receive_by,
                'note'            => $this->bpas->clear_tags($this->input->post('note')),
                'created_by'      => $this->session->userdata('user_id'),
                'updated_at'      => date("Y-m-d H:i"),
                'dn_reference'    => $this->input->post('dn_reference'),
                'truck'           => $this->input->post('truck'),
            );
            if ($purchase_order_id > 0) {
                $data['purchase_order_id'] = $purchase_details->id;
            } else {
                $data['purchase_id'] = $purchase_details->id;
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
        }
        if ($this->form_validation->run() == true && $this->purchases_model->updateReceive($id, $data, $products)) {
            $this->session->set_flashdata('message', $this->lang->line("stock_received_updated"));
            admin_redirect('purchases/receives');
        } else {
            $this->data['inv'] = $this->purchases_model->getReceiveByID($id);
            if($this->data['inv']->purchase_id > 0 && $this->data['inv']->purchase_order_id > 0){
                $this->session->set_flashdata('error', lang("receive_cannot_edit"));
                $this->bpas->md();
            }
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if ($this->data['inv']->purchase_id > 0) {
                $inv_items = $this->purchases_model->getAllReceiveItems($id);
            } else {
                $inv_items = $this->purchases_model->getAllReceivePOItems($id);
            }
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {             
                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $row = json_decode('{}');
                    $row->quantity = 0;
                } else {
                    unset($row->details, $row->product_details);
                }
                $row->purchase_item_id = $item->purchase_item_id;
                $row->parent_id        = $item->parent_id;
                $row->id               = $item->product_id;
                $row->code             = $item->product_code;
                $row->name             = $item->product_name;
                $row->received         = $item->purchase_qty + ($item->tqty ? $item->tqty : $item->quantity);
                $row->qty              = $item->unit_quantity;
                $row->base_quantity    = $item->quantity;
                $row->sup_qty          = $item->sup_qty;
                $row->weight           = $item->weight;
                $row->base_unit        = $row->unit ? $row->unit : $item->product_unit_id;
                $row->unit             = $item->product_unit_id;
                $row->cost             = $this->bpas->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                $row->base_unit_cost   = $row->cost ? $row->cost : $item->unit_cost;
                $row->real_unit_cost   = $item->real_unit_cost;
                $row->unit_cost        = $row->tax_method ? $item->unit_cost + ($item->item_discount / $item->unit_quantity) + ($item->item_tax / $item->unit_quantity) : $item->unit_cost + ($item->item_discount / $item->unit_quantity);
                $row->option           = $item->option_id;
                $row->discount         = $item->discount ? $item->discount : '0';
                $row->tax_rate         = $item->tax_rate_id;
                $row->expiry           = (($item->expiry && $item->expiry != '0000-00-00') ? $this->bpas->hrsd($item->expiry) : '');
                $row->comment          = $item->comment;
                $row->serial_no        = $item->serial_no;

                $options               = $this->purchases_model->getProductOptions($row->id);
                $units                 = $this->site->getUnitbyProduct($row->id,$row->base_unit);
                $tax_rate              = $this->site->getTaxRateByID($row->tax_rate);
                $ri                    = $this->Settings->item_addition ? $row->id : $c;
                $pr[$ri] = array(
                    'id'  => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 
                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                $c++;
            }
            $this->data['inv_items']       = json_encode($pr);
            $this->data['id']              = $id;
            $this->data['billers']         = $this->site->getBillers();
            $this->data['tax_rates']       = $this->site->getAllTaxRates();
            $this->data['supplier']        = $this->site->getCompanyByID($this->data['inv']->supplier_id);
            $this->data['users']           = $this->site->getAllUsers();
            $this->data['warehouses']      = $this->site->getWarehouses();
            $this->data['re_reference_no'] = '';
            $bc   = array(array('link' => base_url(), 'page' => lang('home')),array('link' => admin_url('purchases'), 'page' => lang('purchase')), array('link' => admin_url('purchases/receives'), 'page' => lang('receive_items')), array('link' => '#', 'page' => lang('edit_stock_received')));
            $meta = array('page_title' => lang('edit_stock_received'), 'bc' => $bc);
            $this->page_construct('purchases/edit_receive', $meta, $this->data);
        }
    }
    
    public function delete_receive($id = null)
    {
        $this->bpas->checkPermissions('delete', null, 'stock_received');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $receive = $this->purchases_model->getReceiveByID($id);
        if ($this->purchases_model->deleteReceive($id)) {
            if ($receive->attachment) {
                unlink($this->upload_path . $expense->attachment);
            }
            echo lang("stock_received_deleted");
            $this->session->set_flashdata('message', lang('stock_received_deleted'));
            admin_redirect('purchases/receives');
        }
    }
    
    public function receive_note($id = null)
    {
        $this->bpas->checkPermissions('index', null, 'stock_received');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $receive = $this->purchases_model->getReceiveByID($id);
        $this->data['receive']  = $receive;
        $this->data['supplier'] = $this->site->getCompanyByID($receive->supplier_id);
        $this->data['biller']   = $this->site->getCompanyByID($receive->biller_id);
        if ($receive->purchase_order_id > 0) {
            $this->data['rows'] = $this->purchases_model->getAllReceivePOItems($id);
        } else {
            $this->data['rows'] = $this->purchases_model->getAllReceiveItems($id);
        }
        $this->data['user'] = $this->site->getUser($receive->created_by);
        $this->data['page_title'] = lang("receive_note");
        if ($this->Owner || $this->Admin || $this->bpas->GP['unlimited-print']) {
            $this->data['print'] = 0;
        } else {
            if ($this->Settings->limit_print=='1' && $this->site->checkPrint('Receive',$inv->id)) {
                $this->data['print'] = 1;
            } else if ($this->Settings->limit_print=='2' && $this->site->checkPrint('Receive',$inv->id)) { 
                $this->data['print'] = 2;
            } else {
                $this->data['print'] = 0;
            }
        }
        $this->load->view($this->theme . 'purchases/receive_note', $this->data);
    }
    
    public function receive_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'add_purchase') {
                    $ids = false; 
                    $supplier_id = "";
                    $biller_id = "";
                    $warehouse_id = "";
                    foreach ($_POST['val'] as $id) {
                        $row = $this->purchases_model->getReceiveByID($id);
                        if(($warehouse_id == "" || $warehouse_id == $row->warehouse_id) && ($biller_id == "" || $biller_id == $row->biller_id) && ($supplier_id == "" || $supplier_id == $row->supplier_id) && $row->status != "completed"){
                            $supplier_id = $row->supplier_id;
                            $biller_id = $row->biller_id;
                            $warehouse_id = $row->warehouse_id;
                            $ids[] = $id;
                        }
                        if(!$ids){
                            $this->session->set_flashdata('error', lang("cannot_add_purchase"));
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    }
                    redirect('purchases/add?receive_ids='.json_encode($ids));
                }else if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete', null, 'stock_received');
                    foreach ($_POST['val'] as $id) {
                        $this->purchases_model->deleteReceive($id);
                    }
                    $this->session->set_flashdata('message', lang("stock_received_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('receives'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('pu_reference'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('supplier'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('received_by'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $receive = $this->purchases_model->getReceiveByID($id);
                        $user = $this->site->getUser($receive->received_by);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($receive->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $receive->biller);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, ($receive->re_reference_no));
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, ($receive->pu_reference_no));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, ($receive->supplier));
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $receive->note);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $user->last_name . " " .$user->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, lang($receive->status));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
                    $filename = 'stock_receives_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_receive_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    
}