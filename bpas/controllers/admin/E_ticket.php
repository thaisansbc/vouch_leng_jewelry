<?php

defined('BASEPATH') or exit('No direct script access allowed');

class E_ticket extends MY_Controller
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
        $this->load->library('form_validation');
        $this->load->admin_model('companies_model');
        $this->load->admin_model('settings_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path         = 'assets/uploads/';
        $this->thumbs_path         = 'assets/uploads/thumbs/';
        $this->image_types         = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types  = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size   = '1024';
        $this->data['logo']        = true;
    }
    public function events()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('events')]];
        $meta                = ['page_title' => lang('events'), 'bc' => $bc];
        $this->page_construct('tickets/events', $meta, $this->data);
    }
    public function getEvents()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('id, image, name, description')
            ->from('events')
            ->add_column('Actions', "<div class=\"text-center\">
                <a href='" . admin_url('e_ticket/event_date/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('group_product_prices') . "'><i class=\"fa fa-eye\"></i></a>
                <a href='" . admin_url('e_ticket/edit_event/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_brand') . "'><i class=\"fa fa-edit\"></i></a>
                <a href='#' class='tip po' title='<b>" . lang('delete_event') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('e_ticket/delete_event/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }
    public function add_event()
    {
        $this->form_validation->set_rules('name', lang('name'), 'trim|required');
        $this->form_validation->set_rules('description', lang('description'), 'trim');
        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'description' => $this->bpas->clear_tags($this->input->post('description')),
            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = $this->Settings->iwidth;
                $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo         = $this->upload->file_name;
                $data['image'] = $photo;
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
                $this->image_lib->clear();
            }
        } elseif ($this->input->post('add_event')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addEvent($data)) {
            $this->session->set_flashdata('message', lang('event_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'tickets/add_event', $this->data);
        }
    }
    public function edit_event($id = null)
    {
        $this->form_validation->set_rules('name', lang('name'), 'trim|required');
        $this->form_validation->set_rules('description', lang('description'), 'trim');

        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'description' => $this->bpas->clear_tags($this->input->post('description')),
            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = $this->Settings->iwidth;
                $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo         = $this->upload->file_name;
                $data['image'] = $photo;
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
                $this->image_lib->clear();
            }
        } elseif ($this->input->post('edit_event')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/events');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateEvent($id, $data)) {
            $this->session->set_flashdata('message', lang('event_updated'));
            admin_redirect('e_ticket/events');
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['event']    = $this->settings_model->getEventByID($id);
            $this->load->view($this->theme . 'tickets/edit_event', $this->data);
        }
    }
    public function delete_event($id = null)
    {
        if ($this->settings_model->deleteTicket($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('event_deleted')]);
        }
    }
    
    public function index($action = null)
    {
        $this->bpas->checkPermissions();

        $this->data['error']  = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc                   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('customers')]];
        $meta                 = ['page_title' => lang('customers'), 'bc' => $bc];
        $this->page_construct('tickets/registration', $meta, $this->data);
    }
    public function getRegistrations()
    {
        $this->bpas->checkPermissions('index');
        $this->load->library('datatables');
        $view_detail = anchor('admin/customers/actions/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_details'));
        $list_users ="<a class='tip' title='" . lang('list_users') . "' href='" . admin_url('customers/users/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='false'><i class='fa fa-users'></i> ".lang("list_users")."</a> ";
        $add_user ="<a class='tip' title='" . lang('add_user') . "' href='" . admin_url('customers/add_user/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='false'><i class='fa fa-user-plus'></i> ".lang("add_user")."</a>";
        $edit_customer ="<a class='tip' title='" . lang('edit_customer') . "' href='" . admin_url('customers/edit/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='false'><i class=\"fa fa-edit\"></i> ".lang("edit_customer")."</a> ";
        $delete_customer ="<a href='#' class='tip po' title='<b>" . lang('delete_customer') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('customers/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> ".lang("delete_customer")."</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>'.$view_detail.'</li>
                        <li>'.$edit_customer.'</li>
                        <li>'.$delete_customer.'</li>
                    </ul>
                </div></div>';
        $this->datatables
            ->select("{$this->db->dbprefix('companies')}.id as id, company, name, code, phone, price_group_name, customer_group_name")
            ->from('companies')
            ->join('zones z', 'z.id=companies.zone_id', 'left')
            ->where('group_name', 'customer');

        $this->datatables->add_column("Actions", $action, "id");
        $this->datatables->order_by("{$this->db->dbprefix('companies')}.id", "ASC");
        echo $this->datatables->generate();
    }
}
