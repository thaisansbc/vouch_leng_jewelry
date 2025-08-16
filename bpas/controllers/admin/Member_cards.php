<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Member_cards extends MY_Controller
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
        $this->load->admin_model('pos_model');
        $this->load->admin_model('auth_model');
        $this->load->admin_model('projects_model');
        $this->load->admin_model('quotes_model');
        $this->load->admin_model('promos_model');
        $this->load->admin_model('accounts_model');
        $this->load->admin_model('table_model'); 

        $this->pos_settings           = $this->pos_model->getSetting();
        $this->data['pos_settings']   = $this->pos_settings;
        
        $this->digital_upload_path = 'files/';
        $this->upload_path         = 'assets/uploads/';
        $this->thumbs_path         = 'assets/uploads/thumbs/';
        $this->image_types         = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types  = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size   = '1024';
        $this->data['logo']        = true;
    }

    public function index()
    {
        $this->bpas->checkPermissions();

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('member_cards')]];
        $meta = ['page_title' => lang('member_cards'), 'bc' => $bc];
        $this->page_construct('pos/member_cards', $meta, $this->data);
    }
    

    function import_excel()
    {
        $this->bpas->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $this->load->library('excel');
            if(isset($_FILES["userfile"]["name"]))
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
                    exit;
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('member_cards/import_csv');
                }

                $path = $_FILES["userfile"]["tmp_name"];
                $object = PHPExcel_IOFactory::load($path);
               
                if (!$object) {
                    $error = $this->excel->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect("member_cards/import_excel");
                }
                
                foreach($object->getWorksheetIterator() as $worksheet)
                {
                    $highestRow = $worksheet->getHighestRow();
                    $highestColumn = $worksheet->getHighestColumn();
                    $rw = 0; 
                    $items = array();
                    $existingPro = '';
                    $failedImport = 0;
                    $successImport = 0;
                    for($row=2; $row<=$highestRow; $row++)
                    {    
                        $card_no    = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
                        $discount   = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                        $expiry     = $worksheet->getCellByColumnAndRow(2, $row)->getValue();

                        //convert the value from excel to date expiry
                        $excel_date = $expiry; 
                        $unix_date  = ($excel_date - 25569) * 86400;
                        $excel_date = 25569 + ($unix_date / 86400);
                        $unix_date  = ($excel_date - 25569) * 86400;
                        $date_expiry=gmdate("Y-m-d", $unix_date);
                        
                        /*--------------Checked card_no existing code---------------*/
                        
                        if ( !$this->sales_model->getMemberCardCode(trim($card_no)))
                        {    
                            $successImport++;
                            $items[] = array (
                                'date'          => date('Y-m-d H:i:s'),
                                'card_no'       => trim($card_no),
                                'discount'      => trim($discount),
                                'expiry'        => $date_expiry,
                                'created_by'    => $this->session->userdata('user_id'),
                            );

                        }else{
                            /*-------------------updated items existing card_no code-----------------*/
                            $successImport++;
                                $items_update[] = array (
                                    'date'          => date('Y-m-d H:i:s'),
                                    'card_no'       => trim($card_no),
                                    'discount'      => trim($discount),
                                    'expiry'        => $date_expiry,
                                    'created_by'    => $this->session->userdata('user_id'),
                                );
                            $existingPro .= $card_no;
                            $failedImport++;   
                        }                              
                        $rw++;   
                    }
                }
              
                /* Finde number of add member_card */
                $successImport1 = $successImport - $failedImport;
            }
        }   
        /*********** Add member card ************/

        if ($this->form_validation->run() == true && $this->sales_model->addMultiMemberCard($items)) {
            $this->session->set_flashdata('message', sprintf($successImport . ' ' . lang("membercard_added") . '. ' . ($failedImport >= 1 ? $failedImport . ' already to updated' . $existingPro : ''), $successImport1));
            admin_redirect('member_cards');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = array(
                'name'      => 'userfile',
                'id'        => 'userfile',
                'type'      => 'text',
                'value'     => $this->form_validation->set_value('userfile')
            );

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('member_card'), 'page' => lang('member_card')), array('link' => '#', 'page' => lang('import_member_card_by_excel')));
            $meta = array('page_title' => lang('import_member_card_by_excel'), 'bc' => $bc);
            if(isset($existingPro)){
                if($existingPro !== ''){
                    $this->session->set_flashdata('error', 'member already exist:' . $existingPro);
                }
            }
            $this->page_construct('sales/import_excel', $meta, $this->data);
        }
    }

    public function getMemberCards()
    {
        $this->load->library('datatables');
        $this->datatables
        ->select(
            $this->db->dbprefix('member_cards') . '.id as id, card_no, 
            expiry,
            discount,
            CONCAT('.$this->db->dbprefix('users').".first_name,' ',".$this->db->dbprefix('users').'.last_name) as created_by,
            
            ', false)
        ->join('users', 'users.id=member_cards.created_by', 'left')
        ->from('member_cards')
        ->add_column('Actions', "<div class=\"text-center\">
            <a href='" . admin_url('member_cards/view_member_card/$1') . "' class='tip' title='" . lang('view_member_card') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-eye\"></i></a> 
            <a href='" . admin_url('member_cards/edit_member_card/$1') . "' class='tip' title='" . lang('edit_member_card') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> 
            <a href='#' class='tip po' title='<b>" . lang('delete_member_card') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('member_cards/delete_member_card/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }

    public function add_member_card()
    {
        $this->bpas->checkPermissions(false, true);

        $this->form_validation->set_rules('card_no', lang('card_no'), 'trim|is_unique[gift_cards.card_no]|required');
        $this->form_validation->set_rules('value', lang('value'), 'required');

        if ($this->form_validation->run() == true) {
            $customer_details = $this->input->post('customer') ? $this->site->getCompanyByID($this->input->post('customer')) : null;
            $customer         = $customer_details ? $customer_details->company : null;
            $data             = [
                'card_no'                  => $this->input->post('card_no'),
                'discount'                 => $this->input->post('value'),
                'customer_id'              => $this->input->post('customer') ? $this->input->post('customer') : null,
                'customer'                 => $customer,
                'expiry'                   => $this->input->post('expiry') ? $this->bpas->fsd($this->input->post('expiry')) : null,
                'created_by'               => $this->session->userdata('user_id'),
            ];

            // $data             = [
            //     'card_no'                  => $this->input->post('card_no'),
            //     'discount'                 => $this->input->post('value'),
            //     'expiry'                   => $this->input->post('expiry') ? $this->bpas->fsd($this->input->post('expiry')) : null,
            //     'created_by'               => $this->session->userdata('user_id'),
            // ];

        $sa_data = [];
        $ca_data = [];
        
        }else if ($this->input->post('add_member_card')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('member_cards');
        }

        if ($this->form_validation->run() == true && $this->sales_model->addMemberCard($data)) {
            $this->session->set_flashdata('message', lang('member_card_added'));
            admin_redirect('member_cards');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']   = $this->site->modal_js();
            $this->data['users']      = $this->sales_model->getStaff();
            $this->data['page_title'] = lang('new_member_card');
            $this->load->view($this->theme . 'pos/add_member_card', $this->data);
        }
    }


    public function delete_member_card($id = null)
    {
        $this->bpas->checkPermissions();

        if ($this->sales_model->deleteMemberCard($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('member_card_deleted')]);
        }
    }

    public function edit_member_card($id = null)
    {
        $this->bpas->checkPermissions(false, true);

        $this->form_validation->set_rules('card_no', lang('card_no'), 'trim|required');
        $gc_details = $this->site->getMemberCardByID($id);
        if ($this->input->post('card_no') != $gc_details->card_no) {
            $this->form_validation->set_rules('card_no', lang('card_no'), 'is_unique[gift_cards.card_no]');
        }
        $this->form_validation->set_rules('value', lang('value'), 'required');
            //$this->form_validation->set_rules('customer', lang("customer"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $gift_card        = $this->site->getMemberCardByID($id);
            $customer_details = $this->input->post('customer') ? $this->site->getCompanyByID($this->input->post('customer')) : null;
            $customer         = $customer_details ? $customer_details->company : null;
            $data             = [
                'card_no'                  => $this->input->post('card_no'),
                'discount'                 => $this->input->post('value'),
                'customer_id'              => $this->input->post('customer') ? $this->input->post('customer') : null,
                'customer'                 => $customer,
                'expiry'                   => $this->input->post('expiry') ? $this->bpas->fsd($this->input->post('expiry')) : null,
                'created_by'               => $this->session->userdata('user_id'),
            ];
        } elseif ($this->input->post('edit_member_card')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('member_cards');
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateMemberCard($id, $data)) {
            $this->session->set_flashdata('message', lang('member_card_updated'));
            admin_redirect('member_cards');
        } else {
            $this->data['error']     = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['gift_card'] = $this->site->getMemberCardByID($id);
            $this->data['id']        = $id;
            $this->data['modal_js']  = $this->site->modal_js();
            $this->load->view($this->theme . 'pos/edit_member_card', $this->data);
        }
    }
    public function view_member_card($id = null)
    {
        $this->data['page_title'] = lang('gift_card');
        $gift_card                = $this->site->getMemberCardByID($id);
        $this->data['gift_card']  = $this->site->getMemberCardByID($id);
        $this->load->view($this->theme . 'sales/view_member_card', $this->data);
    }

    public function suggestions($pos = 0)
    {
        $code         = $this->input->get('member_code', true);
        $rows           = $this->sales_model->getMemberCardCode($code);
        $this->bpas->send_json($rows);
    }

    public function member_card_actions()
    { 
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete_member_card');
                    foreach ($_POST['val'] as $id) {
                        $this->sales_model->deleteMemberCard($id);
                    }
                    $this->session->set_flashdata('message', lang('gift_cards_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
              
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('gift_cards'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('card_no'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('discount'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('customer'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->site->getMemberCardByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->card_no);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->discount);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->customer);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'member_cards_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_gift_card_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function coupon()
    {
        $this->bpas->checkPermissions();

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('coupon')]];
        $meta = ['page_title' => lang('coupon'), 'bc' => $bc];
        $this->page_construct('pos/coupon', $meta, $this->data);
    }
    public function getCoupons()
    {
        $this->load->library('datatables');
        $this->datatables
        ->select($this->db->dbprefix('coupon') . '.id as id, card_no, value, CONCAT(' . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . '.last_name) as created_by, expiry', false)
        ->join('users', 'users.id=coupon.created_by', 'left')
        ->from('coupon')
        ->add_column('Actions', "<div class=\"text-center\">
            <a href='" . admin_url('member_cards/view_coupon/$1') . "' class='tip' title='" . lang('view_coupon') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-eye\"></i></a> 
            <a href='" . admin_url('member_cards/edit_coupon/$1') . "' class='tip' title='" . lang('edit_coupon') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> 
            <a href='#' class='tip po' title='<b>" . lang('delete_coupon') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('member_cards/delete_coupon/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }
    public function view_coupon($id = null){
        $this->data['page_title'] = lang('coupon');
        $this->data['gift_card']  = $this->site->getCouponByID($id);
        $this->load->view($this->theme . 'pos/view_coupon', $this->data);
    }
    public function add_coupon()
    {
        $this->bpas->checkPermissions(false, true);

        $this->form_validation->set_rules('card_no', lang('card_no'), 'trim|is_unique[coupon.card_no]|required');
        $this->form_validation->set_rules('value', lang('value'), 'required');

        if ($this->form_validation->run() == true) {
            $customer_details = $this->input->post('customer') ? $this->site->getCompanyByID($this->input->post('customer')) : null;
            $customer         = $customer_details ? $customer_details->company : null;
            $data             = [
                'card_no'        => $this->input->post('card_no'),
                'value'          => $this->input->post('value'),
                'customer_id'    => $this->input->post('customer') ? $this->input->post('customer') : null,
                'customer'       => $customer,
                'expiry'         => $this->input->post('expiry')?$this->bpas->fsd($this->input->post('expiry')) : null,
                'created_by'     => $this->session->userdata('user_id'),
            ]; 
        $sa_data = [];
        $ca_data = []; 
        }else if ($this->input->post('add_coupon')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('member_cards/coupon');
        } 
        if ($this->form_validation->run() == true && $this->sales_model->addCoupon($data)) {
            $this->session->set_flashdata('message', lang('coupon_added'));
            admin_redirect('member_cards/coupon');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']   = $this->site->modal_js();
            $this->data['users']      = $this->sales_model->getStaff();
            $this->data['page_title'] = lang('add_coupon');
            $this->load->view($this->theme . 'pos/add_coupon', $this->data);
        }
    }
    public function edit_coupon($id = null)
    {
        $this->bpas->checkPermissions(false, true);

        $this->form_validation->set_rules('card_no', lang('card_no'), 'trim|required');
        $gc_details = $this->site->getCouponByID($id);
        if ($this->input->post('card_no') != $gc_details->card_no) {
            $this->form_validation->set_rules('card_no', lang('card_no'), 'is_unique[gift_cards.card_no]');
        }
        $this->form_validation->set_rules('value', lang('value'), 'required');
            //$this->form_validation->set_rules('customer', lang("customer"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $gift_card        = $this->site->getCouponByID($id);
            $customer_details = $this->input->post('customer') ? $this->site->getCompanyByID($this->input->post('customer')) : null;
            $customer         = $customer_details ? $customer_details->company : null;
            $data             = [
                'card_no'      => $this->input->post('card_no'),
                'value'        => $this->input->post('value'),
                'customer_id'  => $this->input->post('customer') ? $this->input->post('customer') : null,
                'customer'     => $customer,
                'expiry'       => $this->input->post('expiry') ? $this->bpas->fsd($this->input->post('expiry')) : null,
                'created_by'   => $this->session->userdata('user_id'),
            ];
        } elseif ($this->input->post('edit_member_card')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('member_cards/coupon');
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateCoupon($id, $data)) {
            $this->session->set_flashdata('message', lang('member_card_updated'));
            admin_redirect('member_cards/coupon');
        } else {
            $this->data['error']     = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['gift_card'] = $this->site->getCouponByID($id);
            $this->data['id']        = $id;
            $this->data['modal_js']  = $this->site->modal_js();
            $this->load->view($this->theme . 'pos/edit_coupon', $this->data);
        }
    }
     public function delete_coupon($id = null)
    {
        $this->bpas->checkPermissions();

        if ($this->sales_model->deleteCoupon($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('coupon_deleted')]);
        }
    }
    public function arrival_membership($biller_id = null)
    {
        $this->data['users'] = $this->site->getAllUsers();
        
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('calendar'), 'page' => lang('calendar')), array('link' => '#', 'page' => lang('holidays')));
        $this->data['customers'] = $this->site->getCustomers();
        $this->data['salemans']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);
        
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
        $meta = array('page_title' => lang('verify_member'), 'bc' => $bc);
        $this->page_construct('customers/verify_member', $meta, $this->data);
    }
    public function pos($sid = NULL)
    {   
        $this->bpas->checkPermissions('index', true, 'room');
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
        $did = $this->input->post('delete_id') ? $this->input->post('delete_id') : NULL;
        $suspend = $this->input->post('suspend') ? TRUE : FALSE;
        $count = $this->input->post('count') ? $this->input->post('count') : NULL;
        $floor_id = $this->input->get('floor') ? $this->input->get('floor') : NULL;
        if($floor_id === NULL){
            $floor_id = $this->pos_settings->show_floor;
        }else if($floor_id ==0){
            $floor_id = 0; 
        }
        $data2 = array('show_floor' => $floor_id);
        $this->db->update('pos_settings', $data2);
        $duplicate_sale = $this->input->get('duplicate') ? $this->input->get('duplicate') : NULL;

        //validate form input
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'trim|required');
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required');
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');

        if ($this->form_validation->run() == TRUE) {
            
            $date = date('Y-m-d H:i:s');
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $biller_id = $this->input->post('biller');
            $total_items = $this->input->post('total_items');
            $sale_status = 'completed';
            $payment_status = 'due';
            $payment_term = 0;
            $due_date = date('Y-m-d', strtotime('+' . $payment_term . ' days'));
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note = $this->bpas->clear_tags($this->input->post('pos_note'));
            $staff_note = $this->bpas->clear_tags($this->input->post('staff_note'));
            $reference = $this->site->getReference('pos');

            $total = 0;
            $product_tax = 0;
            $product_discount = 0;
            $digital = FALSE;
            $gst_data = [];
            $total_cgst = $total_sgst = $total_igst = 0;
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
                $item_comment = $_POST['product_comment'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : NULL;
                $real_unit_price = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
               
                $item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : NULL;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : NULL;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->pos_model->getProductByCode($item_code) : NULL;
                    // $unit_price = $real_unit_price;
                    if ($item_type == 'digital') {
                        $digital = TRUE;
                    }
                    $pr_discount = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price = $this->bpas->formatDecimal($unit_price - $pr_discount);
                    $item_net_price = $unit_price;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = $item_tax = 0;
                    $tax = "";

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {

                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $item_tax = $ctax['amount'];
                        $tax = $ctax['tax'];
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
                    $unit = $this->site->getUnitByID($item_unit);

                    $product = array(
                        'product_id'      => $item_id,
                        'product_code'    => $item_code,
                        'product_name'    => $item_name,
                        'product_type'    => $item_type,
                        'option_id'       => $item_option,
                        'net_unit_price'  => $item_net_price,
                        'unit_price'      => $this->bpas->formatDecimal($item_net_price + $item_tax),
                        'quantity'        => $item_quantity,
                        'product_unit_id' => $unit ? $unit->id : NULL,
                        'product_unit_code' => $unit ? $unit->code : NULL,
                        'unit_quantity' => $item_unit_quantity,
                        'warehouse_id'    => $warehouse_id,
                        'item_tax'        => $pr_item_tax,
                        'tax_rate_id'     => $item_tax_rate,
                        'tax'             => $tax,
                        'discount'        => $item_discount,
                        'item_discount'   => $pr_item_discount,
                        'subtotal'        => $this->bpas->formatDecimal($subtotal),
                        'serial_no'       => $item_serial,
                        'real_unit_price' => $real_unit_price,
                        'comment'         => $item_comment,
                    );

                    $products[] = ($product + $gst_data);
                    $total += $this->bpas->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } elseif ($this->pos_settings->item_order == 1) {
                krsort($products);
            }
            $cur_rate = $this->pos_model->getExchange_rate('KHR');

            $order_discount = $this->site->calculateDiscount($this->input->post('discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            $rounding = 0;
            if ($this->pos_settings->rounding) {
                $round_total = $this->bpas->roundNumber($grand_total, $this->pos_settings->rounding);
                $rounding = $this->bpas->formatMoney($round_total - $grand_total);
            }
            $currency =$this->input->post('kh_currenncy') =="" ? $this->input->post('en_currenncy') : $this->input->post('kh_currenncy');
            $currency_rate= ($currency =="usd") ? $cur_rate->rate : 1;
            

            $data = array('date'  => $date,
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
                'order_discount_id' => $this->input->post('discount'),
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
                'rounding'          => $rounding,
                'suspend_note'      => $this->input->post('suspend_note'),
                'currency'          => $currency,
                'other_cur_paid_rate' => $currency_rate,
                'pos'               => 1,
                'paid'              => $this->input->post('amount-paid') ? $this->input->post('amount-paid') : 0,
                'created_by'        => $this->session->userdata('user_id'),
                'hash'              => hash('sha256', microtime() . mt_rand()),
                );

            if (!$suspend) {
                $p = isset($_POST['amount']) ? sizeof($_POST['amount']) : 0;
                $paid = 0;
                for ($r = 0; $r < $p; $r++) {
                    if (isset($_POST['amount'][$r]) && !empty($_POST['amount'][$r]) && isset($_POST['paid_by'][$r]) && !empty($_POST['paid_by'][$r])) {
                        $amount = $this->bpas->formatDecimal($_POST['balance_amount'][$r] > 0 ? $_POST['amount'][$r] - $_POST['balance_amount'][$r] : $_POST['amount'][$r]);
                        if ($_POST['paid_by'][$r] == 'deposit') {
                            if ( ! $this->site->check_customer_deposit($customer_id, $amount)) {
                                $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                                redirect($_SERVER["HTTP_REFERER"]);
                            }
                        }
                        if ($_POST['paid_by'][$r] == 'gift_card') {
                            $gc = $this->site->getGiftCardByNO($_POST['paying_gift_card_no'][$r]);
                            $amount_paying = $_POST['amount'][$r] >= $gc->balance ? $gc->balance : $_POST['amount'][$r];
                            $gc_balance = $gc->balance - $amount_paying;
                            $payment[] = array(
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
                                'gc_balance'  => $gc_balance,
                            //  'currency'     => $this->input->post('kh_currenncy')
                                );

                        } else {
                            $payment[] = array(
                                'date'         => $date,
                                // 'reference_no' => $this->site->getReference('pay'),
                                'amount'       => $amount,
                                'paid_amount'  => $_POST['paid_amount'][$r],
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
                                'pos_balance'  => $_POST['balance_amount'][$r],
                            //    'currency'       => $this->input->post('kh_currenncy')
                                );

                        }

                    }
                }
            }
            if (!isset($payment) || empty($payment)) {
                $payment = array();
            }

            // $this->bpas->print_arrays($data, $products, $payment);
        }
        if ($this->form_validation->run() == TRUE && !empty($products) && !empty($data)) {
            if ($suspend) {
                if ($this->pos_model->suspendSale($data, $products, $did)) {
                    $this->session->set_userdata('remove_posls', 1);
                    $this->session->set_flashdata('message', $this->lang->line("sale_suspended"));
                    admin_redirect("table");
                }
            } else {
                if ($sale = $this->pos_model->addSale($data, $products, $payment, $did)) {
                    $this->session->set_userdata('remove_posls', 1);
                    $msg = $this->lang->line("sale_added");
                    if (!empty($sale['message'])) {
                        foreach ($sale['message'] as $m) {
                            $msg .= '<br>' . $m;
                        }
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
            $this->data['oid'] = NULL;
            if ($duplicate_sale) {
                if ($old_sale = $this->pos_model->getInvoiceByID($duplicate_sale)) {
                    $inv_items = $this->pos_model->getSaleItems($duplicate_sale);
                    $this->data['oid'] = $duplicate_sale;
                    $this->data['old_sale'] = $old_sale;
                    $this->data['message'] = lang('old_sale_loaded');
                    $this->data['customer'] = $this->pos_model->getCompanyByID($old_sale->customer_id);
                } else {
                    $this->session->set_flashdata('error', lang("bill_x_found"));
                    admin_redirect("table");
                }
            }
            $this->data['suspend_sale'] = NULL;
            if ($sid) {
                if ($suspended_sale = $this->pos_model->getOpenBillByID($sid)) {
                    $inv_items = $this->pos_model->getSuspendedSaleItems($sid);
                    $this->data['sid'] = $sid;
                    $this->data['suspend_sale'] = $suspended_sale;
                    $this->data['message'] = lang('suspended_sale_loaded');
                    $this->data['customer'] = $this->pos_model->getCompanyByID($suspended_sale->customer_id);
                    $this->data['reference_note'] = $suspended_sale->suspend_note;
                } else {
                    $this->session->set_flashdata('error', lang("bill_x_found"));
                    admin_redirect("pos");
                }
            }

            if (($sid || $duplicate_sale) && $inv_items) {
                    // krsort($inv_items);
                    $c = rand(100000, 9999999);
                    foreach ($inv_items as $item) {
                        $row = $this->site->getProductByID($item->product_id);
                        if (!$row) {
                            $row = json_decode('{}');
                            $row->tax_method = 0;
                            $row->quantity = 0;
                        } else {
                            $category = $this->site->getCategoryByID($row->category_id);
                            $row->category_name = $category->name;
                            unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                        }
                        $pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                        if ($pis) {
                            foreach ($pis as $pi) {
                                $row->quantity += $pi->quantity_balance;
                            }
                        }
                        $row->id = $item->product_id;
                        $row->code = $item->product_code;
                        $row->name = $item->product_name;
                        $row->type = $item->product_type;
                        $row->quantity += $item->quantity;
                        $row->discount = $item->discount ? $item->discount : '0';
                        $row->price = $this->bpas->formatDecimal($item->net_unit_price + $this->bpas->formatDecimal($item->item_discount / $item->quantity));
                        $row->unit_price = $row->tax_method ? $item->unit_price + $this->bpas->formatDecimal($item->item_discount / $item->quantity) + $this->bpas->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
                        $row->real_unit_price = $item->real_unit_price;
                        $row->base_quantity = $item->quantity;
                        $row->base_unit = isset($row->unit) ? $row->unit : $item->product_unit_id;
                        $row->base_unit_price = $row->price ? $row->price : $item->unit_price;
                        $row->unit = $item->product_unit_id;
                        $row->qty = $item->unit_quantity;
                        $row->tax_rate = $item->tax_rate_id;
                        $row->serial = $item->serial_no;
                        $row->option = $item->option_id;
                        $options = $this->pos_model->getProductOptions($row->id, $item->warehouse_id);

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

                        $row->comment = isset($item->comment) ? $item->comment : '';
                        $row->ordered = 1;
                        $combo_items = false;
                        if ($row->type == 'combo') {
                            $combo_items = $this->pos_model->getProductComboItems($row->id, $item->warehouse_id);
                        }
                        $units = $this->site->getUnitsByBUID($row->base_unit);
                        $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                        $ri = $this->Settings->item_addition ? $row->id : $c;

                        $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                                'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                        $c++;
                    }

                    $this->data['items'] = json_encode($pr);

            } else {
                $this->data['customer'] = $this->pos_model->getCompanyByID($this->pos_settings->default_customer);
                $this->data['reference_note'] = NULL;
            }

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['message'] = isset($this->data['message']) ? $this->data['message'] : $this->session->flashdata('message');

            $this->data['suspend_note']= $this->table_model->getAll_suspend_note();
            $this->data['biller'] = $this->site->getCompanyByID($this->pos_settings->default_biller);
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['user'] = $this->site->getUser();
            
            $this->data["tcp"] = $this->pos_model->products_count($this->pos_settings->default_category);
            $this->data['pos_settings'] = $this->pos_settings;
            $this->data['exchange_rate'] = $this->pos_model->getExchange_rate('KHR');
            
            $user = $this->site->getUser();

            $this->data['checkin_cards']    = $this->table_model->getAllCheckinCard();
            $this->data['tables']           = $this->table_model->getAllSuspendtable();
            $this->data['available_room']   = $this->table_model->available_room();
            //$this->data['cards']= $this->table_model->getAllAvailbleCards();


            $currency_id=$this->site->getCurrencyWarehouseByUserID($user->id);
            $curr=$this->site->getCurrencyByID($currency_id);
            $this->data['default_img'] = $curr->code;
            $this->data['pos_type'] = $this->pos_settings->pos_type;
            

            $this->data['GP'] = $this->site->getPermission();
            $this->load->view($this->theme . 'suspended/pos', $this->data);
        }
    }
}