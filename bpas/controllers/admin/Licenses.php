<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Licenses extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->bpas->md('login');
        }

        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('admin');
        }

        $this->load->admin_model('api_model');
        $this->lang->admin_load('api', $this->Settings->user_language);
        $this->load->library('form_validation');
    }
    public function index()
    {
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('licenses')]];
        $meta = ['page_title' => lang('licenses'), 'bc' => $bc];
        $this->page_construct('licenses/index', $meta, $this->data);
    }
    public function getLicenses()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('id, reference,user_name, license_key, start_date, type,url_addresses,status')
            ->from('license')
            ->add_column('Actions', "<div class=\"text-center\">
                <a href='" . admin_url('licenses/edit/$1') . "' class='tip' title='" . lang('edit_license') . "' data-toggle='modal' data-backdrop='static' data-target='#myModal' ><i class=\"fa fa-edit\"></i></a>
                <a href='#' class='tip po' title='<b>" . lang('delete') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('licenses/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a>

                </div>", 'id');

        echo $this->datatables->generate();
    }
    public function add()
    {
        $this->form_validation->set_rules('reference', lang('reference'), 'required|trim');
        $this->form_validation->set_rules('user_name', lang('user_name'), 'required|trim|is_unique[license.user_name]');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $start_date = $this->bpas->fld(trim($this->input->post('start_date')));
            } else {
                $start_date = date('Y-m-d H:i:s');
            }

            $data = [
                'date'          => date('Y-m-d H:i:s'),
                'reference'     => $this->input->post('reference'),
                'user_name'     => $this->input->post('user_name'),
                'license_key'   => $this->api_model->generateKey(),
                'start_date'    => $start_date,
                'type'          => $this->input->post('type'),
                'url_addresses' => $this->input->post('url_addresses'),
                'status'        => $this->input->post('status'),
                'created_by'    => $this->session->userdata('user_id')
            ];
        } elseif ($this->input->post('add')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'admin');
        }
        if ($this->form_validation->run() == true && $this->api_model->addLicense($data)) {
            $this->session->set_flashdata('message', lang('license_added'));
            admin_redirect('licenses');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['page_title'] = lang('add_license');
            $this->load->view($this->theme . 'licenses/add', $this->data);
        }
    }
    public function edit($id=null)
    {
        $this->form_validation->set_rules('reference', lang('reference'), 'required|trim');
        $Licenses = $this->api_model->getLicenseByID($id);
        if ($this->input->post('user_name') != $Licenses->user_name) {
            $this->form_validation->set_rules('user_name', lang('user_name'), 'required|trim|is_unique[license.user_name]');
        }
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $start_date = $this->bpas->fld(trim($this->input->post('start_date')));
            } else {
                $start_date = date('Y-m-d H:i:s');
            }

            $data = [
                'date'          => date('Y-m-d H:i:s'),
                'reference'     => $this->input->post('reference'),
                'user_name'     => $this->input->post('user_name'),
                'license_key'   => $this->api_model->generateKey(),
                'start_date'    => $start_date,
                'type'          => $this->input->post('type'),
                'url_addresses' => $this->input->post('url_addresses'),
                'status'        => $this->input->post('status'),
                'created_by'    => $this->session->userdata('user_id')
            ];
        } elseif ($this->input->post('add')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'admin');
        }
        if ($this->form_validation->run() == true && $this->api_model->updateLicense($id,$data)) {
            $this->session->set_flashdata('message', lang('license_updated'));
            admin_redirect('licenses');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['licenses']   = $Licenses;
            $this->data['page_title'] = lang('edit_license');
            $this->load->view($this->theme . 'licenses/edit', $this->data);
        }
    }
    public function delete($id){
        if ($this->api_model->deleteLicense($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('license_deleted')]);
        } else {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('delete_failed')]);
        }
    }

    

    
}
