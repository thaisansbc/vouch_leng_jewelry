<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Calendar extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->bpas->md('login');
        }
        $this->load->library('form_validation');
        $this->load->admin_model('calendar_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path         = 'assets/uploads/';
        $this->thumbs_path         = 'assets/uploads/thumbs/';
        $this->image_types         = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types  = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size   = '1024';
        $this->popup_attributes    = ['width' => '900', 'height' => '600', 'window_name' => 'sma_popup', 'menubar' => 'yes', 'scrollbars' => 'yes', 'status' => 'no', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0'];

    }
    public function index()
    {
        $this->data['cal_lang'] = $this->get_cal_lang();
        $bc                     = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('calendar')]];
        $this->data['users']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);
        $this->data['customers'] = $this->site->getCustomers();
        $meta                   = ['page_title' => lang('calendar'), 'bc' => $bc];
        $this->page_construct('calendar/calendar', $meta, $this->data);
    }
    public function appointment($biller_id = null)
    {
        $this->data['users'] = $this->site->getAllUsers();
        
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('calendar'), 'page' => lang('calendar')), array('link' => '#', 'page' => lang('holidays')));
        $this->data['customers'] = $this->site->getCustomers();
        $this->data['doctors']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);
        
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
        $meta = array('page_title' => lang('appointment'), 'bc' => $bc);
        $this->page_construct('calendar/calendar_lists', $meta, $this->data);
    }
    public function getAppointmentLists()
    {   
        $user_query     = $this->input->get('user') ? $this->input->get('user') : null;
        $customer       = $this->input->get('customer') ? $this->input->get('customer') : null;
        $biller         = $this->input->get('biller') ? $this->input->get('biller') : null;
        $saleman_by     = $this->input->get('saleman') ? $this->input->get('saleman') : null;
        $start_date     = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date       = $this->input->get('end_date') ? $this->input->get('end_date') : null;

        if($this->Admin || $this->Owner || $this->GP['calendar-approved']){
            $approve_link = "<a href='#' class='po approve_event' title='" . $this->lang->line("approve_event") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-success po-delete' href='" . admin_url('calendar/approve_event/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
            . lang('approved') . "</a>";

            $unapprove_link = "<a href='#' class='po unapprove_event' title='<b>" . $this->lang->line("unapprove_event") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('calendar/unapprove_event/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-times\"></i> "
            . lang('unapproved') . "</a>";
        }
        $delete_link = "<a href='#' class='po delete_event' title='<b>" . lang("delete_calendar") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('calendar/delete_calendar/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_calendar') . "</a>";
        $edit_link     = anchor('admin/calendar/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_calendar'), 'data-toggle="modal" data-target="#myModal" class="edit_event"');
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $approve_link . '</li>
                        <li>' . $unapprove_link . '</li>
                        <li>'.$edit_link.'</li>
                        <li>'.$delete_link.'</li>
                    </ul>
                </div></div>';
                
        $this->load->library('datatables');
        $this->datatables
            ->select("
                    bpas_calendar.id as id,
                    {$this->db->dbprefix('calendar')}.date,
                    title,
                    {$this->db->dbprefix('companies')}.name as customer,
                    {$this->db->dbprefix('companies')}.phone as phone,
                    concat(ass.last_name,' ',ass.first_name) as assign_to,
                    start as start,
                    end as end, 
                    {$this->db->dbprefix('calendar')}.status
                ")
            ->from('calendar')
            ->join('users','users.id=user_id','left')
            ->join('users ass','ass.id=calendar.assign_to','left')
            ->join('companies','companies.id=calendar.customer','left')
            ->where("bpas_calendar.holiday !=",1);
        
        if ((!$this->Owner && !$this->Admin)) {
            $user_id = $this->session->userdata('user_id');
            $this->datatables->where("calendar.assign_to", $user_id);
        }
        if ($user_query) {
            $this->datatables->where('calendar.user_id', $user_query);
        }
        if ($biller) {
            $this->datatables->where('calendar.biller_id', $biller);
        }
        if ($customer) {
            $this->datatables->where('calendar.customer', $customer);
        }
        if ($saleman_by) {
            $this->datatables->where('calendar.assign_to', $saleman_by);
        }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('calendar') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }

        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
    public function add(){
        $this->form_validation->set_rules('title', lang('title'), 'trim|required');
        $this->form_validation->set_rules('start', lang('start'), 'required');
        $this->form_validation->set_rules('end', lang('end'), 'required');
        if ($this->form_validation->run() == true) {
            $data = [
                'title'       => $this->input->post('title'),
                'start'       => $this->bpas->fld($this->input->post('start')),
                'end'         => $this->input->post('end') ? $this->bpas->fld($this->input->post('end')) : null,
                'description' => $this->input->post('description'),
                'color'       => $this->input->post('color') ? $this->input->post('color') : '#000000',
                'customer'    => $this->input->post('customer'),
                'assign_to'   => $this->input->post('assign_to'),
                'user_id'     => $this->session->userdata('user_id'),
                'status'      => $this->input->post('status'),
            ];
        } elseif ($this->input->post('add_event')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->calendar_model->addEvent($data)) {
            $this->session->set_flashdata('message', lang('event_added'));
            admin_redirect('calendar/events');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['cal_lang'] = $this->get_cal_lang();
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            //$this->data['users'] = $this->site->getAllUsers();
            $this->data['users']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customers'] = $this->site->getCustomers();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'calendar/add_calendar', $this->data);
        }
        
    }

    public function delete_img($id, $photo)
    {
        unlink($this->upload_path . $photo);
        unlink($this->thumbs_path . $photo);
        $this->calendar_model->updatePhoto($id);     
    }

    public function edit($id = null){
        $this->bpas->checkPermissions('edit', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->form_validation->set_rules('title', lang('title'), 'trim|required');
        $this->form_validation->set_rules('start', lang('start'), 'required');
        $this->form_validation->set_rules('end', lang('end'), 'required');
        if ($this->form_validation->run() == true) {
            $data = [
                'title'       => $this->input->post('title'),
                'start'       => $this->bpas->fld($this->input->post('start')),
                'end'         => $this->input->post('end') ? $this->bpas->fld($this->input->post('end')) : null,
                'description' => $this->input->post('description'),
                'color'       => $this->input->post('color') ? $this->input->post('color') : '#000000',
                'customer' => $this->input->post('customer'),
                'assign_to' => $this->input->post('assign_to'),
                'user_id'     => $this->session->userdata('user_id'),
                'status' => $this->input->post('status'),
            ];
        } elseif ($this->input->post('edit_event')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->calendar_model->updateEvent($id,$data)) {
            $this->session->set_flashdata('message', lang('event_updated'));
            admin_redirect('calendar/events');
        } else {
            $this->data['error']        = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['cal_lang']     = $this->get_cal_lang();
            $this->data['billers']      = $this->site->getAllCompanies('biller');
            //$this->data['users']        = $this->site->getAllUsers();
            $this->data['users']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customers']    = $this->site->getCustomers();
            $this->data['modal_js']     = $this->site->modal_js();
            $this->data['events']      = $this->calendar_model->getEventByID($id);
            $this->load->view($this->theme . 'calendar/edit_calendar', $this->data);
        }
        
    }
    public function delete_event($id)
    {
        if ($this->input->is_ajax_request()) {
            if ($event = $this->calendar_model->getEventByID($id)) {
                if (!$this->Owner && $event->user_id != $this->session->userdata('user_id')) {
                    $res = ['error' => 1, 'msg' => lang('access_denied')];
                    $this->bpas->send_json($res);
                }
                $this->db->delete('calendar', ['id' => $id]);
                $res = ['error' => 0, 'msg' => lang('event_deleted')];
                $this->bpas->send_json($res);
            }
        }
    }
    public function get_cal_lang()
    {
        switch ($this->Settings->user_language) {
            case 'simplified-chinese':
            $cal_lang = 'zh-tw';
            break;
    
            case 'thai':
            $cal_lang = 'th';
            break;
            case 'traditional-chinese':
            $cal_lang = 'zh-cn';
            break;
      
            case 'vietnamese':
            $cal_lang = 'vi';
            break;
            default:
            $cal_lang = 'en';
            break;
        }
        return $cal_lang;
    }
    public function get_events()
    {
        $cal_lang = $this->get_cal_lang();
        $this->load->library('fc', ['lang' => $cal_lang]);

        if (!isset($_GET['start']) || !isset($_GET['end'])) {
            die('Please provide a date range.');
        }

        if ($cal_lang == 'ar') {
            $start = $this->fc->convert2($this->input->get('start', true));
            $end   = $this->fc->convert2($this->input->get('end', true));
        } else {
            $start = $this->input->get('start', true);
            $end   = $this->input->get('end', true);
        }

        $input_arrays  = $this->calendar_model->getEvents($start, $end);
        $start         = $this->fc->parseDateTime($start);
        $end           = $this->fc->parseDateTime($end);
        $output_arrays = [];
        foreach ($input_arrays as $array) {
            $this->fc->load_event($array);
            if ($this->fc->isWithinDayRange($start, $end)) {
                $output_arrays[] = $this->fc->toArray();
            }
        }

        // $this->bpas->send_json($output_arrays);
        $this->bpas->send_json($output_arrays);
    }
    
    public function import_csv()
    {
        $this->bpas->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            
            if (isset($_FILES["userfile"])) {
                
                $this->load->library('excel');
                $path = $_FILES["userfile"]["tmp_name"];
                $object = PHPExcel_IOFactory::load($path);
                foreach($object->getWorksheetIterator() as $worksheet){
                    $highestRow = $worksheet->getHighestRow();
                    $highestColumn = $worksheet->getHighestColumn();
                    for($row=2; $row<= $highestRow; $row++){
                        $title = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
                        $from_date = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                        $to_date = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                        $description = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                        
                        $final[] = array(
                              'title'   => $title,
                              'from_date'   => $from_date,
                              'to_date'   => $to_date,
                              'description'   => $description,
                          );
                          
                    }
                    
                }
                
                $rw = 2;
                foreach ($final as $csv_pr) {
                    $pr_title[] = trim($csv_pr['title']);
                    $pr_from_date[] = trim($csv_pr['from_date']);
                    $pr_to_date[] = trim($csv_pr['to_date']);
                    $pr_description[] = trim($csv_pr['description']);
                }
                $ikeys = array('title','start','end','description');
                $items = array();
                foreach (array_map(null,$pr_title, $pr_from_date, $pr_to_date, $pr_description) as $ikey => $value) {
                    $items[] = array_combine($ikeys, $value);
                }
            }
        }
        
        if ($this->form_validation->run() == true && $this->calendar_model->addCalendar($items)) {
            
            $this->session->set_flashdata('message', sprintf(lang("calendar_added")));
            admin_redirect('calendar/holidays');
            
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = array(
                                                'name' => 'userfile',
                                                'id' => 'userfile',
                                                'type' => 'text',
                                                'value' => $this->form_validation->set_value('userfile')
                                            );
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('installments'), 'page' => lang('calendar')), array('link' => '#', 'page' => lang('import_csv')));
            $meta = array('page_title' => lang('import_csv'), 'bc' => $bc);
            $this->page_construct('calendar/calendar_import', $meta, $this->data);
        }
    }
    
    
    public function approve_event($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->calendar_model->updateEventStatus($id,"approved")) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('id_card_approved')]);
            }
            $this->session->set_flashdata('message', lang('event_approved'));
            admin_redirect('welcome');
        }
    }
    public function unapprove_event($id = null){
        $this->bpas->checkPermissions(null, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->calendar_model->updateEventStatus($id,"pending")) {
            $this->session->set_flashdata('message', lang('event_unapproved'));
        }
        admin_redirect('calendar/events');
    }
    public function holidays()
    {
        $this->data['users'] = $this->site->getAllUsers();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('calendar'), 'page' => lang('calendar')), array('link' => '#', 'page' => lang('holidays')));
        $meta = array('page_title' => lang('holidays'), 'bc' => $bc);
        $this->page_construct('calendar/holidays', $meta, $this->data);
    }
    public function getHolidayLists()
    {
        $delete_link = "<a href='#' class='po delete-installment_item' title='<b>" . lang("delete_calendar") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('calendar/delete_calendar/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_calendar') . "</a>";
        
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>'.$delete_link.'</li>
                    </ul>
                </div></div>';
                
        $this->load->library('datatables');
        $this->datatables
            ->select("
                    bpas_calendar.id as id,
                    title,
                    description,
                    date(start) as start,
                    date(end) as end, 
                    concat(bpas_users.last_name,'',bpas_users.first_name) as user")
            ->from('bpas_calendar')
            ->join('users','users.id=user_id','left')
            ->where("bpas_calendar.holiday",1);
            
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    public function delete_calendar($id = NULL)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->calendar_model->deleteEvent($id)) {
            if ($this->input->is_ajax_request()) {
                $res = ['error' => 0, 'msg' => lang('calendar_deleted')];
                $this->bpas->send_json($res);
            }
            $this->session->set_flashdata('message', lang('calendar_deleted'));
            admin_redirect('welcome');
        }
    }
    
    public function calendar_actions()
    {

        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            
             if (!empty($_POST['val'])) {
                  echo 'asd';exit();
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->calendar_model->deleteEvent($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("calendar_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                    
                }elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('calendar');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('title'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('description'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('start_date'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('end_date'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('created_by'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $calendar = $this->calendar_model->getEventByID($id);
                        $user = $this->site->getUser($calendar->user_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $calendar->title);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $calendar->description);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $calendar->start);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $calendar->end);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, ($user->last_name."".$user->first_name));
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
                    
                    $filename = 'calendars_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_calendar_selected"));
                admin_redirect($_SERVER["HTTP_REFERER"]);
            }
        }
    }
    public function upcoming_appointments($id = false)
    {
        $this->bpas->checkPermissions('expired_document');
        $bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('upcoming_appointments')));
        $meta = array('page_title' => lang('upcoming_appointments'), 'bc' => $bc);
        $this->page_construct('calendar/upcoming_appointments', $meta, $this->data);
        
    }
    public function getUpcommingAppointments()
    {
        $this->load->library('datatables');
        $alert_day    = $this->Settings->alert_day;
        $settings_alert_day = date('Y-m-d', strtotime(" +{$alert_day} days "));

        $delete_link = "<a href='#' class='po' title='" . lang("delete_document") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_document/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_document') . "</a>";
        
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li><a href="'.admin_url('hr/edit_document/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_document').'</a></li>
                                <li>'.$delete_link.'</li>
                            </ul>
                        </div>';
    
        $this->datatables
            ->select("
                    calendar.id as id,
                    calendar.title,
                    {$this->db->dbprefix('companies')}.name as customer,
                    {$this->db->dbprefix('companies')}.phone as phone,
                    calendar.start,
                    calendar.end,
                ")
            ->from("calendar")
            ->join("companies","companies.id=calendar.customer","left");
            if ($settings_alert_day) {
                $this->db->where($this->db->dbprefix('calendar') . '.start <=', $settings_alert_day);
            }
            //->where("hr_employees_document.expired_date is not NULL")
            $this->datatables->where("holiday",0);
            $this->datatables->unset_column("id");
        echo $this->datatables->generate();
    }
    //----------------------
    public function events($biller_id = null)
    {
        $this->data['users'] = $this->site->getAllUsers();
        
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('calendar'), 'page' => lang('calendar')), array('link' => '#', 'page' => lang('holidays')));
        $this->data['customers'] = $this->site->getCustomers();
        $this->data['doctors']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);
        
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
        $meta = array('page_title' => lang('appointment'), 'bc' => $bc);
        $this->page_construct('calendar/event_lists', $meta, $this->data);
    }
    public function getEventLists()
    {   
        $user_query     = $this->input->get('user') ? $this->input->get('user') : null;
        $customer       = $this->input->get('customer') ? $this->input->get('customer') : null;
        $biller         = $this->input->get('biller') ? $this->input->get('biller') : null;
        $saleman_by     = $this->input->get('saleman') ? $this->input->get('saleman') : null;
        $start_date     = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date       = $this->input->get('end_date') ? $this->input->get('end_date') : null;

        $detail_link          = anchor('admin/calendar/modal_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('View_Event'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $delete_link = "<a href='#' class='po delete_event' title='<b>" . lang("Delete_Event") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('calendar/delete_calendar/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_event') . "</a>";
        $edit_link     = anchor('admin/calendar/edit_event/$1', '<i class="fa fa-edit"></i> ' . lang('edit_event'), 'data-toggle="modal" data-target="#myModal" class="edit_event"');
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                       <li>'.$detail_link.'</li>
                        <li>'.$edit_link.'</li>
                        <li>'.$delete_link.'</li>
                    </ul>
                </div></div>';
                
        $this->load->library('datatables');
        $this->datatables
            ->select("
                    calendar.id as id,
                    {$this->db->dbprefix('calendar')}.photo as photo,
                    {$this->db->dbprefix('custom_field')}.name as type,
                    title,
                    start as start,
                    end as end, 
                    {$this->db->dbprefix('calendar')}.status
                ")
            ->from('calendar')
            ->join('users','users.id= calendar.created_by','left')
            ->join('custom_field','custom_field.id=calendar.event_type','left')
            ->join('companies','companies.id=calendar.customer','left')
            ->where("calendar.type",'event');


        
        if ((!$this->Owner && !$this->Admin)) {
            $user_id = $this->session->userdata('user_id');
            $this->datatables->where("calendar.created_by", $user_id);
        }
        if ($user_query) {
            $this->datatables->where('calendar.created_by', $user_query);
        }
        if ($biller) {
            $this->datatables->where('calendar.biller_id', $biller);
        }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('calendar') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }

        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
    public function modal_view($id = null)
    {
        $this->bpas->checkPermissions('index', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $event                 = $this->calendar_model->getEventByID($id);
        $this->data['event']= $event;
        $scheduleID=  explode(',', $event->schedule_id);
        $eventSchedule = [];
        foreach($scheduleID as $SCH){
            $idSCH = intval($SCH);
            $eventSchedule[]                 = $this->calendar_model-> getCalendarScheduleByID($idSCH);
           
        }
        // var_dump($eventSchedule);
        // exit(0);
        $this->data['eventSchedule']= $eventSchedule;
        $this->load->view($this->theme . 'calendar/modal_view', $this->data);
    }
    public function add_event()
    {
        $this->form_validation->set_rules('title', lang('title'), 'trim|required');
        $this->form_validation->set_rules('start', lang('start'), 'required');
        $this->form_validation->set_rules('end', lang('end'), 'required');

        if ($this->form_validation->run() == true) {
            $data = [
                'title'       => $this->input->post('title'),
                'start'       => $this->bpas->fld($this->input->post('start')),
                'end'         => $this->bpas->fld($this->input->post('end')),
                'description' => $this->input->post('description'),
                'color'       => $this->input->post('color') ? $this->input->post('color') : '#000000',
                
                'status'        => $this->input->post('status'),
                'event_type'    => $this->input->post('event_type'),
                'location_name' => $this->input->post('location_name'),
                'coordinates'   => $this->input->post('coordinates'),
                'type'          => 'event',
                'created_by'    => $this->session->userdata('user_id'),
            ];



            $this->load->library('upload');
            if ($_FILES['product_image']['size'] > 0) {
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = $this->Settings->iwidth;
                $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['max_filename']  = 25;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('product_image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo         = $this->upload->file_name;
                $data['photo'] = $photo;
                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = $this->upload_path . $photo;
                $config['new_image']      = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = true;
                $config['width']          = $this->Settings->twidth;
                $config['height']         = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image']     = $this->upload_path . $photo;
                    $wm['wm_text']          = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type']          = 'text';
                    $wm['wm_font_path']     = 'system/fonts/texb.ttf';
                    $wm['quality']          = '100';
                    $wm['wm_font_size']     = '16';
                    $wm['wm_font_color']    = '999999';
                    $wm['wm_shadow_color']  = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding']       = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = null;
            }



         $data['schedule_id'] = implode(',', $this->input->post('schedules'));

        } elseif ($this->input->post('add_event')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->calendar_model->addEvent($data)) {
            $this->session->set_flashdata('message', lang('event_added'));
            admin_redirect('calendar/events');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['cal_lang'] = $this->get_cal_lang();
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            //$this->data['users'] = $this->site->getAllUsers();
            $this->data['users']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customers'] = $this->site->getCustomers();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'calendar/add_event', $this->data);
        }
    }
    public function edit_event($id=null)
    {
        $this->bpas->checkPermissions('edit', true);
        // if ($this->input->get('id')) {
        //     $id = $this->input->get('id');
        // }
        $this->form_validation->set_rules('title', lang('title'), 'trim|required');
        $this->form_validation->set_rules('start', lang('start'), 'required');
        $this->form_validation->set_rules('end', lang('end'), 'required');
        if ($this->form_validation->run() == true) {
            $data = [
                'title'       => $this->input->post('title'),
                'start'       => $this->bpas->fld($this->input->post('start')),
                'end'         => $this->bpas->fld($this->input->post('end')),
                'description' => $this->input->post('description'),
                'color'       => $this->input->post('color') ? $this->input->post('color') : '#000000',
                'status'        => $this->input->post('status'),
                'event_type'    => $this->input->post('event_type'),
                'location_name' => $this->input->post('location_name'),
                'coordinates'   => $this->input->post('coordinates'),
                'type'          => 'event',
                'updated_by'    => $this->session->userdata('user_id'),
            ];

            // $schedules=$this->input->post('schedules');

            $this->load->library('upload');
            if ($_FILES['product_image']['size'] > 0) {
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = $this->Settings->iwidth;
                $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['max_filename']  = 25;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('product_image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo         = $this->upload->file_name;
                $data['photo'] = $photo;
                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = $this->upload_path . $photo;
                $config['new_image']      = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = true;
                $config['width']          = $this->Settings->twidth;
                $config['height']         = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image']     = $this->upload_path . $photo;
                    $wm['wm_text']          = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type']          = 'text';
                    $wm['wm_font_path']     = 'system/fonts/texb.ttf';
                    $wm['quality']          = '100';
                    $wm['wm_font_size']     = '16';
                    $wm['wm_font_color']    = '999999';
                    $wm['wm_shadow_color']  = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding']       = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = null;
            }
            $data['schedule_id'] = implode(',', $this->input->post('schedules'));
        } elseif ($this->input->post('edit_event')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
      
        if ($this->form_validation->run() == true && $this->calendar_model->updateEvent($id,$data)) {
            $this->session->set_flashdata('message', lang('event_updated'));
            admin_redirect('calendar/events');
        } else {
            $this->data['error']        = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['cal_lang']     = $this->get_cal_lang();
            $this->data['billers']      = $this->site->getAllCompanies('biller');
            //$this->data['users']        = $this->site->getAllUsers();
            $this->data['users']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customers']    = $this->site->getCustomers();
            $this->data['modal_js']     = $this->site->modal_js();
            $this->data['events']      = $this->calendar_model->getEventByIDUpdate($id);

            $event = $this->calendar_model->getEventByIDUpdate($id);
            $scheduleID=  explode(',', $event->schedule_id);
            $eventSchedule = [];
            foreach($scheduleID as $SCH){
                $idSCH = intval($SCH);
                $eventSchedule[]                 = $this->calendar_model-> getCalendarScheduleByID($idSCH);
               
            }
            // var_dump($eventSchedule);
            // exit(0);
            $this->data['eventschedule']=     $eventSchedule;

            
            $this->load->view($this->theme . 'calendar/edit_event', $this->data);
        }
    }
    public function schedules($biller_id = null)
    {


        $this->checkDateSchedule();
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
        $meta = array('page_title' => lang('schedules'), 'bc' => $bc);
        $this->page_construct('calendar/schedules', $meta, $this->data);
    }
    public function getSchedules()
    {   
        $user_query     = $this->input->get('user') ? $this->input->get('user') : null;
        $customer       = $this->input->get('customer') ? $this->input->get('customer') : null;
        $biller         = $this->input->get('biller') ? $this->input->get('biller') : null;
        $saleman_by     = $this->input->get('saleman') ? $this->input->get('saleman') : null;
        $start_date     = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date       = $this->input->get('end_date') ? $this->input->get('end_date') : null;

        $detail_link          = anchor('admin/calendar/view_schedule/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_schedule'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $delete_link = "<a href='#' class='po delete_event' title='<b>" . lang("delete_schedule") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('calendar/delete_schedule/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_schedule') . "</a>";
        $edit_link     = anchor('admin/calendar/edit_schedule/$1', '<i class="fa fa-edit"></i> ' . lang('edit_calendar'), 'data-toggle="modal" data-target="#myModal" class="edit_schedule"');
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>'.$detail_link.'</li>
                        <li>'.$edit_link.'</li>
                        <li>'.$delete_link.'</li>
                    </ul>
                </div></div>';
                
        $this->load->library('datatables');
        $this->datatables
            ->select("
                    event_schedule.id as id,
                    {$this->db->dbprefix('event_schedule')}.photo as photo,
                    start as start,
                    end as end, 
                    title,
                    {$this->db->dbprefix('event_schedule')}.ticket_limit as ticket_limit,
                    {$this->db->dbprefix('event_schedule')}.created_date as created_date, 
                    {$this->db->dbprefix('event_schedule')}.status as status
                ")
            ->from('event_schedule')
            ->join('users','users.id=event_schedule.created_by','left');
        

            

        if ((!$this->Owner && !$this->Admin)) {
            $user_id = $this->session->userdata('user_id');
            $this->datatables->where("event_schedule.created_by", $user_id);
        }
        if ($user_query) {
            $this->datatables->where('event_schedule.user_id', $user_query);
        }
        if ($biller) {
            $this->datatables->where('event_schedule.biller_id', $biller);
        }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('event_schedule') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }

        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
    public function view_schedule($id = null)
    {
        $this->bpas->checkPermissions('index', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $event                  = $this->calendar_model->getscheduleByID($id);
        $this->data['schedule']    = $event;
        $this->load->view($this->theme . 'calendar/view_schedule', $this->data);
    }
    public function add_schedule()
    {
        $this->form_validation->set_rules('title', lang('title'), 'trim|required');
        $this->form_validation->set_rules('start', lang('start'), 'required');
        $this->form_validation->set_rules('end', lang('end'), 'required');
        if ($this->form_validation->run() == true) {
            $data = [
                'title'       => $this->input->post('title'),
                'start'       => $this->bpas->fld($this->input->post('start')),
                'end'         => $this->bpas->fld($this->input->post('end')),
                'description' => $this->input->post('description'),
                'created_by'  => $this->session->userdata('user_id'),
                'status'      => $this->input->post('status'),
            ];

            $this->load->library('upload');
            if ($_FILES['product_image']['size'] > 0) {
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = $this->Settings->iwidth;
                $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['max_filename']  = 25;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('product_image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo         = $this->upload->file_name;
                $data['photo'] = $photo;
                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = $this->upload_path . $photo;
                $config['new_image']      = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = true;
                $config['width']          = $this->Settings->twidth;
                $config['height']         = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image']     = $this->upload_path . $photo;
                    $wm['wm_text']          = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type']          = 'text';
                    $wm['wm_font_path']     = 'system/fonts/texb.ttf';
                    $wm['quality']          = '100';
                    $wm['wm_font_size']     = '16';
                    $wm['wm_font_color']    = '999999';
                    $wm['wm_shadow_color']  = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding']       = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = null;
            }
        } elseif ($this->input->post('add_schedule')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->calendar_model->addEventSchedule($data)) {
            $this->session->set_flashdata('message', lang('schedule_added'));
            admin_redirect('calendar/schedules');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['cal_lang'] = $this->get_cal_lang();
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            //$this->data['users'] = $this->site->getAllUsers();
            $this->data['users']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customers'] = $this->site->getCustomers();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'calendar/add_schedule', $this->data);
        }
    }


    public function checkDateSchedule(){
          $status="pending";
          $Schedules = $this->calendar_model->getscheduleByStatus($status);
          $dateTime = new DateTime();
          $currentDate = strtotime($dateTime->format('Y-m-d H:i:s'));
        foreach($Schedules as $schedule){
          $scheduleEnd = strtotime($schedule->end);
          if ($scheduleEnd <  $currentDate) {
                $id=$schedule->id;
                $UpdateStatus="expired";
               $this->calendar_model->updatescheduleStatus($id,$UpdateStatus);
          }
        }
    }

    public function checkDateTicket(){
        $status="expired";
        $Schedules = $this->calendar_model->getscheduleByStatus($status);
        $dateTime = new DateTime();
        $currentDate = strtotime($dateTime->format('Y-m-d H:i:s'));
        $ticket = $this->calendar_model->getAllTicket();

      foreach($Schedules as $schedule){
    
        $scheduleEnd = strtotime($schedule->end);
        if ($scheduleEnd <   $currentDate ) {
              $schedule_id=$schedule->id;
             foreach($ticket as $tic){
                if($tic->schedule_id==$schedule_id){
                    $id=$tic->id;
                    $UpdateStatus="expired";
                    $this->calendar_model->updateTicketStatus($id,$UpdateStatus);
                }
             }
        }
        
      }
  }



    public function edit_schedule($id=null)
    {
        $this->bpas->checkPermissions('edit', true);
        
        $this->form_validation->set_rules('title', lang('title'), 'trim|required');
        $this->form_validation->set_rules('start', lang('start'), 'required');
        $this->form_validation->set_rules('end', lang('end'), 'required');
        if ($this->form_validation->run() == true) {
            $data = [
                'title'       => $this->input->post('title'),
                'start'       => $this->bpas->fld($this->input->post('start')),
                'end'         => $this->bpas->fld($this->input->post('end')),
                'description' => $this->input->post('description'),
                'created_by'  => $this->session->userdata('user_id'),
                'status'      => $this->input->post('status'),
            ];

            $this->load->library('upload');
            if ($_FILES['product_image']['size'] > 0) {
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = $this->Settings->iwidth;
                $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['max_filename']  = 25;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('product_image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo         = $this->upload->file_name;
                $data['photo'] = $photo;
                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = $this->upload_path . $photo;
                $config['new_image']      = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = true;
                $config['width']          = $this->Settings->twidth;
                $config['height']         = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image']     = $this->upload_path . $photo;
                    $wm['wm_text']          = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type']          = 'text';
                    $wm['wm_font_path']     = 'system/fonts/texb.ttf';
                    $wm['quality']          = '100';
                    $wm['wm_font_size']     = '16';
                    $wm['wm_font_color']    = '999999';
                    $wm['wm_shadow_color']  = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding']       = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = null;
            }
           
           

        } elseif ($this->input->post('add_schedule')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }


        $data['updated_by'] =$this->session->userdata('user_id');
        if ($this->form_validation->run() == true && $this->calendar_model->updateschedule($id,$data)) {
            $this->session->set_flashdata('message', lang('schedule_updated'));
            admin_redirect('calendar/schedules');
        } else {
            $this->data['error']        = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['cal_lang']     = $this->get_cal_lang();
            $this->data['billers']      = $this->site->getAllCompanies('biller');
            //$this->data['users']        = $this->site->getAllUsers();
            $this->data['users']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customers']    = $this->site->getCustomers();
            $this->data['modal_js']     = $this->site->modal_js();
            $this->data['schedule']      = $this->calendar_model->getscheduleByID($id);
            // var_dump($this->calendar_model->getscheduleByID($id));
            // exit(0);
            $this->load->view($this->theme . 'calendar/edit_schedule',$this->data);
        }
      
    }
    public function delete_schedule($id = NULL)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->calendar_model->deleteScheduleEvent($id)) {
            if ($this->input->is_ajax_request()) {
                $res = ['error' => 0, 'msg' => lang('schedule_deleted')];
                $this->bpas->send_json($res);
            }
            $this->session->set_flashdata('message', lang('schedule_deleted'));
            admin_redirect('welcome');
        }
    }
    public function tickets($biller_id = null)
    {

        $this->checkDateTicket();
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
        $meta = array('page_title' => lang('tickets'), 'bc' => $bc);
        $this->page_construct('calendar/tickets', $meta, $this->data);
    }
    public function getTickets()
    {   

        // $this->datatables
        // ->select("
        //         bpas_calendar.id as id,
        //         {$this->db->dbprefix('calendar')}.date,
        //         title,
        //         {$this->db->dbprefix('companies')}.name as customer,
        //         {$this->db->dbprefix('companies')}.phone as phone,
        //         concat(ass.last_name,' ',ass.first_name) as assign_to,
        //         start as start,
        //         end as end, 
        //         {$this->db->dbprefix('calendar')}.status
        //     ")
        // ->from('calendar')
        // ->join('users','users.id=user_id','left')
        // ->join('users ass','ass.id=calendar.assign_to','left')
        // ->join('companies','companies.id=calendar.customer','left')
        // ->where("bpas_calendar.holiday !=",1);


        $user_query     = $this->input->get('user') ? $this->input->get('user') : null;
        $customer       = $this->input->get('customer') ? $this->input->get('customer') : null;
        $biller         = $this->input->get('biller') ? $this->input->get('biller') : null;
        $saleman_by     = $this->input->get('saleman') ? $this->input->get('saleman') : null;
        $start_date     = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date       = $this->input->get('end_date') ? $this->input->get('end_date') : null;

        $detail_link          = anchor('admin/calendar/view_ticket/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_ticket'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $delete_link = "<a href='#' class='po delete_event' title='<b>" . lang("delete_ticket") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('calendar/delete_ticket/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_ticket') . "</a>";
        $edit_link     = anchor('admin/calendar/edit_ticket/$1', '<i class="fa fa-edit"></i> ' . lang('edit_ticket'), 'data-toggle="modal" data-target="#myModal" class="edit_ticket"');
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>'.$detail_link.'</li>
                        <li>'.$edit_link.'</li>
                        <li>'.$delete_link.'</li>
                    </ul>
                </div></div>';
                
        $this->load->library('datatables');
        $this->datatables
            ->select("
                    event_tickets.id as id,
                    concat({$this->db->dbprefix('event_schedule')}.start,' -> ',{$this->db->dbprefix('event_schedule')}.end) as schedule,
                    {$this->db->dbprefix('event_tickets')}.code as code,
                    {$this->db->dbprefix('companies')}.name as customer,
                    {$this->db->dbprefix('event_tickets')}.created_date as created_date,
                    {$this->db->dbprefix('event_tickets')}.status
                ")
            ->from('event_tickets')
            ->join('event_schedule','event_schedule.id=event_tickets.schedule_id','left')
            ->join('companies','companies.id=event_tickets.customer_id','left')

            ->join('users','users.id=event_tickets.created_by','left');
        
        if ((!$this->Owner && !$this->Admin)) {
            $user_id = $this->session->userdata('user_id');
            $this->datatables->where("event_tickets.created_by", $user_id);
        }
        if ($user_query) {
            $this->datatables->where('event_tickets.user_id', $user_query);
        }
        if ($biller) {
            $this->datatables->where('event_tickets.biller_id', $biller);
        }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('event_tickets') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }

        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
    public function view_ticket($id = null)
    {
        $this->bpas->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        /*$inv                 = 
   
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by, true);
        }
       
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);

        $this->data['created_by']  = $this->site->getUser($inv->created_by);
        $this->data['updated_by']  = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        
        $this->data['rows']        = $this->sales_model->getAllInvoiceItems($id);*/
        $this->data['ticket']          = $this->calendar_model->getTicketByID($id);
        $ticket                       = $this->calendar_model->getTicketByID($id);
        $event                         = $this->calendar_model->getAllCalendar();
        $this->data['customer']       = $this->site->getCompanyByID($ticket->customer_id);
        $this->data['schedule']      = $this->calendar_model->getCalendarScheduleByID($ticket->schedule_id);
        // var_dump($this->site->getCompanyByID($ticket->customer_id));
        foreach($event as $eve){
            $schedule_id=  explode(',',  $eve->schedule_id);
         
            foreach($schedule_id as $SCH){
                $idSCH = intval($SCH);
                $idSchedule = intval($ticket->schedule_id);
                if($idSCH==$idSchedule){
                   $this->data['event']=$eve;
                }
           }
    }
        $this->load->view($this->theme . 'calendar/modal_view_ticket', $this->data);
    }
    public function add_ticket()
    {
        $this->form_validation->set_rules('schedule', lang('schedule'), 'trim|required');
        if ($this->form_validation->run() == true) {

            $ticket_code = $this->input->post('schedule').date('YmdHis');

            $schedule_endTime = $this->calendar_model->getCalendarScheduleByID($this->input->post('schedule'));
            
            $endTime= $schedule_endTime->end;
        
            $data = [
                'schedule_id'   => $this->input->post('schedule'),
                'code'          => $ticket_code,
                'description'   => $this->input->post('description'),
                'customer_id'   => $this->input->post('customer')?$this->input->post('customer'):null,
                'created_by'    => $this->session->userdata('user_id'),
                'status'        => $this->input->post('status'),
                'expiry'        => $endTime,
            ];
        } elseif ($this->input->post('add_event')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->calendar_model->addTicket($data)) {
            $this->session->set_flashdata('message', lang('ticket_added'));
            admin_redirect('calendar/tickets');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['cal_lang'] = $this->get_cal_lang();
            $this->data['billers'] = $this->site->getAllCompanies('biller');

            $this->data['schedules'] = $this->calendar_model->getAllSchedule();
            $this->data['customers'] = $this->site->getCustomers();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'calendar/add_ticket', $this->data);
        }
    }
    public function edit_ticket($id=null)
    {
        $this->bpas->checkPermissions('edit', true);
        
         $this->form_validation->set_rules('schedule', lang('schedule'), 'trim|required');

        if ($this->form_validation->run() == true) {

            $schedule_endTime = $this->calendar_model->getCalendarScheduleByID($this->input->post('schedule'));
            $endTime= $schedule_endTime->end;
            $data = [
                'schedule_id'   => $this->input->post('schedule'),
                'description'   => $this->input->post('description'),
                'customer_id'   => $this->input->post('customer')?$this->input->post('customer'):null,
                'updated_by'    => $this->session->userdata('user_id'),
                'status'        => $this->input->post('status'),
                'expiry'        => $endTime,
            ];

        } elseif ($this->input->post('edit_ticket')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->calendar_model->updateTicket($id,$data)) {
            $this->session->set_flashdata('message', lang('ticket_updated'));
            admin_redirect('calendar/tickets');
        } else {
            $this->data['error']        = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['cal_lang']     = $this->get_cal_lang();
            $this->data['billers']      = $this->site->getAllCompanies('biller');
            //$this->data['users']        = $this->site->getAllUsers();
            $this->data['users']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customers']    = $this->site->getCustomers();
            $this->data['modal_js']     = $this->site->modal_js();
            $this->data['ticket']      = $this->calendar_model->getTicketByID($id);
            $this->data['schedules'] = $this->calendar_model->getAllScheduleEdit();
            $this->load->view($this->theme . 'calendar/edit_ticket',$this->data);
        }
      
    }
    public function delete_ticket($id = NULL)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->calendar_model->deleteTicket($id)) {
            if ($this->input->is_ajax_request()) {
                $res = ['error' => 0, 'msg' => lang('ticket_deleted')];
                $this->bpas->send_json($res);
            }
            $this->session->set_flashdata('message', lang('ticket_deleted'));
            admin_redirect('welcome');
        }
    }
    public function verify_ticket($biller_id = null)
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
        $meta = array('page_title' => lang('verify_ticket'), 'bc' => $bc);
        $this->page_construct('calendar/verify_ticket', $meta, $this->data);
    }
    public function validate_ticket($no){
        $tk= $this->calendar_model->getTicketByCode($no);
        $dateTime = new DateTime();
        $currentDate = strtotime($dateTime->format('Y-m-d H:i:s'));
        if (!empty($tk)) {
            if ($tk->status =='pending') {
                $expiry = strtotime($tk->expiry);
                if ($expiry  >=  $currentDate ) {
                    $UpdateStatus="used";
                    $id=$tk->id;
                    $this->calendar_model->updateTicketStatus($id,$UpdateStatus);
                    $this->bpas->send_json($tk);
                } else {
                    $this->bpas->send_json(false);
                }
            } else {
                $this->bpas->send_json($tk);
            }
        } else {
            $this->bpas->send_json(false);
        }
    }
}
