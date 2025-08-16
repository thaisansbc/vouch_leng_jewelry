<?php defined('BASEPATH') or exit('No direct script access allowed');

class Salemans extends MY_Controller
{
    function __construct()
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
        $this->lang->admin_load('auth', $this->Settings->user_language);
        $this->lang->admin_load('salemans', $this->Settings->user_language);
        $this->load->library('ion_auth');
        $this->load->library('form_validation');
        $this->load->admin_model('auth_model');
        $this->load->admin_model('companies_model');
    }
    public function _get_csrf_nonce()
    {
        $this->load->helper('string');
        $key   = random_string('alnum', 8);
        $value = random_string('alnum', 20);
        $this->session->set_flashdata('csrfkey', $key);
        $this->session->set_flashdata('csrfvalue', $value);
        return [$key => $value];
    }
    public function getZone_ajax()
    {
        $result = $this->site->getAllZones();
        $this->bpas->send_json($result);
    }
    public function index($action = NULL)
    {
        if (!$this->loggedIn) {
            admin_redirect('login');
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'admin/welcome');
        }
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('salemans')));
        $meta = array('page_title' => lang('salemans'), 'bc' => $bc);
        $this->page_construct('salemans/index', $meta, $this->data);
    }
    public function getSalemans()
    {
        $this->load->library('datatables');
        $this->datatables
        ->select($this->db->dbprefix('users') . '.id as id, first_name, last_name, email, award_points, multi_zone, active')
        ->from('users')
        ->join('zones', 'zones.id=users.zone_id', 'left')
        ->where('group_id', 5)
        ->edit_column('active', '$1__$2', 'active, id')
        ->add_column('Actions', "<div class=\"text-center\"><a href='#' class='tip po' title='" . lang("clear_award_points") . "' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('salemans/clear_AP/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-eraser\"></i></a> <a href='" . admin_url('salemans/profile/$1') . "' class='tip' title='" . lang('edit_saleman') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_saleman") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('salemans/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }
    public function saleman_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if ($id != $this->session->userdata('user_id')) {
                            if (!$this->auth_model->delete_user($id)) {
                                $error = true;
                            }
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('salemans_x_deleted_have_sales'));
                    } else {
                        $this->session->set_flashdata('message', lang('salemans_deleted'));
                    }
                    redirect($_SERVER['HTTP_REFERER']);
                }

                elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('salemans'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('first_name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('last_name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('email'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('company'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('group'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('award_points'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('zone'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('status'));

                    $row = 2;
                    $zones = $this->site->getAllZones();
                    foreach ($_POST['val'] as $id) {
                        $zns = "";
                        $user = $this->site->getUser($id);
                        if(isset($user->multi_zone)){
                            $m_zones = explode(',', $user->multi_zone);
                            foreach ($zones as $zone) {
                                foreach ($m_zones as $z_id) {
                                    if($z_id == $zone->id){
                                        $z_id == end($m_zones) ? $zns = $zns . $zone->zone_name : $zns = $zns . $zone->zone_name . ", ";
                                    }
                                }
                            }
                        }
                        $group = $this->site->getUserGroup($user->id);
                        $status = ["0" => "inactive", "1" => "active"];

                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $user->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $user->last_name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $user->email);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $user->company);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $group->name);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $user->award_points);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $zns);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $status[$user->active]);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(50);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                    ob_clean();
                    $filename = 'salemans_' . date('Y_m_d_H_i_s');
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }
            } else {
                $this->session->set_flashdata('error', lang('no_saleman_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function add()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->data['title'] = 'Add Saleman';
        $this->form_validation->set_rules('username', lang('username'), 'trim|is_unique[users.username]');
        $this->form_validation->set_rules('email', lang('email'), 'trim|is_unique[users.email]');
        $this->form_validation->set_rules('status', lang('status'), 'trim|required');
        $this->form_validation->set_rules('group', lang('group'), 'trim|required');

        if ($this->form_validation->run() == true) {
            $username = strtolower($this->input->post('username'));
            $email    = strtolower($this->input->post('email'));
            $password = $this->input->post('password');
            $notify   = $this->input->post('notify');

            $zones = implode(",", $this->input->post('multi_zone[]'));
            $additional_data = [
                'first_name'     => $this->input->post('first_name'),
                'last_name'      => $this->input->post('last_name'),
                'company'        => $this->input->post('company'),
                'phone'          => $this->input->post('phone'),
                'gender'         => $this->input->post('gender'),
                'group_id'       => $this->input->post('group'),
                'multi_zone'     => $zones ?  $zones : null,
                'save_point'     => $this->input->post('save_point')
            ];
            $active = $this->input->post('status');
        }
        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data, $active, $notify)) {
            $this->session->set_flashdata('message', $this->ion_auth->messages());
            admin_redirect('salemans');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('error')));
            $this->data['groups']     = $this->ion_auth->groups()->result_array();
            $this->data['settings']   = $this->site->getSettings();
            $this->data['zones']      = $this->site->getAllZones();
            $this->data['billers']    = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $bc                       = [['link' => admin_url('home'), 'page' => lang('home')], ['link' => admin_url('salemans'), 'page' => lang('salemans')], ['link' => '#', 'page' => lang('add_saleman')]];
            $meta                     = ['page_title' => lang('salemans'), 'bc' => $bc];
            $this->page_construct('salemans/add', $meta, $this->data);
        }
    }
    public function edit($id = null)
    {
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }

        $this->data['title'] = lang('edit_saleman');
        if (!$this->loggedIn || !$this->Owner && $id != $this->session->userdata('user_id')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $user = $this->ion_auth->user($id)->row();
        if ($user->username != $this->input->post('username')) {
            $this->form_validation->set_rules('username', lang('username'), 'trim|is_unique[users.username]');
        }
        if ($user->email != $this->input->post('email')) {
            $this->form_validation->set_rules('email', lang('email'), 'trim|is_unique[users.email]');
        }

        $this->form_validation->set_rules('company', lang('company'), 'trim');
        if ($this->form_validation->run() === true) {
            if ($this->Owner) {
                if ($id == $this->session->userdata('user_id')) {
                    $data = [
                        'first_name' => $this->input->post('first_name'),
                        'last_name'  => $this->input->post('last_name'),
                        'company'    => $this->input->post('company'),
                        'phone'      => $this->input->post('phone'),
                        'gender'     => $this->input->post('gender'),
                    ];
                } else {
                    $whs = $this->input->post('warehouse');
                    $warehouses = '';
                    $i = 1;
                    foreach($whs as $wh){
                        if(count($whs)==$i){
                            $warehouses .= $wh;
                        }else{
                            $warehouses .= $wh.',';
                        }
                        $i++;
                    }
                    $zones = implode(",", $this->input->post('multi_zone[]'));
                    $data = [
                        'first_name'     => $this->input->post('first_name'),
                        'last_name'      => $this->input->post('last_name'),
                        'company'        => $this->input->post('company'),
                        'username'       => $this->input->post('username'),
                        'email'          => $this->input->post('email'),
                        'phone'          => $this->input->post('phone'),
                        'gender'         => $this->input->post('gender'),
                        'active'         => $this->input->post('status'),
                        'group_id'       => $this->input->post('group'),
                        'multi_zone'     => $zones ?  $zones : null,
                        'award_points'   => $this->input->post('award_points'),
                        'save_point'     => $this->input->post('save_point')
                    ];
                }   
            } elseif ($this->Admin) {
                $data = [
                    'first_name'   => $this->input->post('first_name'),
                    'last_name'    => $this->input->post('last_name'),
                    'company'      => $this->input->post('company'),
                    'phone'        => $this->input->post('phone'),
                    'gender'       => $this->input->post('gender'),
                    'active'       => $this->input->post('status'),
                    'award_points' => $this->input->post('award_points'),
                ];
            } else {
                $data = [
                    'first_name' => $this->input->post('first_name'),
                    'last_name'  => $this->input->post('last_name'),
                    'company'    => $this->input->post('company'),
                    'phone'      => $this->input->post('phone'),
                    'gender'     => $this->input->post('gender'),
                ];
            }
            if ($this->Owner) {
                if ($this->input->post('password')) {
                    $this->form_validation->set_rules('password', lang('edit_saleman_validation_password_label'), 'required|min_length[8]|max_length[25]|matches[password_confirm]');
                    $this->form_validation->set_rules('password_confirm', lang('edit_saleman_validation_password_confirm_label'), 'required');
                    $data['password'] = $this->input->post('password');
                }
            }
        }
        if ($this->form_validation->run() === true && $this->ion_auth->update($user->id, $data)) {
            $this->session->set_flashdata('message', lang('saleman_updated'));
            admin_redirect('salemans/profile/' . $id);
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function delete($id = null)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if (!$this->Owner || $id == $this->session->userdata('user_id')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'admin/welcome');
        }
        if ($this->auth_model->delete_user($id)) {
            $this->session->set_flashdata('message', lang('saleman_deleted'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->session->set_flashdata('warning', lang('saleman_x_deleted_have_sales'));
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    /**
     * @param null $id
     */
    public function update_avatar($id = null)
    {
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }

        if (!$this->ion_auth->logged_in() || !$this->Owner && $id != $this->session->userdata('user_id')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->form_validation->set_rules('avatar', lang('avatar'), 'trim');

        if ($this->form_validation->run() == true) {
            if ($_FILES['avatar']['size'] > 0) {
                $this->load->library('upload');

                $config['upload_path']   = 'assets/uploads/avatars';
                $config['allowed_types'] = 'gif|jpg|png';
                $config['max_width']    = $this->Settings->iwidth;
                $config['max_height']   = $this->Settings->iheight;
                $config['overwrite']    = false;
                $config['encrypt_name'] = true;
                $config['max_filename'] = 25;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload('avatar')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }

                $photo = $this->upload->file_name;

                $this->load->helper('file');
                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = 'assets/uploads/avatars/' . $photo;
                $config['new_image']      = 'assets/uploads/avatars/thumbs/' . $photo;
                $config['maintain_ratio'] = true;
                $config['width']          = 150;
                $config['height']         = 150;

                $this->image_lib->clear();
                $this->image_lib->initialize($config);

                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                $user = $this->ion_auth->user($id)->row();
            } else {
                $this->form_validation->set_rules('avatar', lang('avatar'), 'required');
            }
        }

        if ($this->form_validation->run() == true && $this->auth_model->updateAvatar($id, $photo)) {
            unlink('assets/uploads/avatars/' . $user->avatar);
            unlink('assets/uploads/avatars/thumbs/' . $user->avatar);
            $this->session->set_userdata('avatar', $photo);
            $this->session->set_flashdata('message', lang('avatar_updated'));
            admin_redirect('salemans/profile/' . $id);
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('salemans/profile/' . $id);
        }
    }
    public function delete_avatar($id = null, $avatar = null)
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('owner') && $id != $this->session->userdata('user_id')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . $_SERVER['HTTP_REFERER'] . "'; }, 0);</script>");
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            unlink('assets/uploads/avatars/' . $avatar);
            unlink('assets/uploads/avatars/thumbs/' . $avatar);
            if ($id == $this->session->userdata('user_id')) {
                $this->session->unset_userdata('avatar');
            }
            $this->db->update('users', ['avatar' => null], ['id' => $id]);
            // $this->session->set_flashdata('message', lang('avatar_deleted'));
            $this->bpas->send_json(['error' => 0, 'msg' => lang('avatar_deleted')]);
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . $_SERVER['HTTP_REFERER'] . "'; }, 0);</script>");
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function reset_password($code = null)
    {
        if (!$code) {
            show_404();
        }

        $user = $this->ion_auth->forgotten_password_check($code);
        if ($user) {
            $this->form_validation->set_rules('new', lang('password'), 'required|min_length[8]|max_length[25]|matches[new_confirm]');
            $this->form_validation->set_rules('new_confirm', lang('confirm_password'), 'required');

            if ($this->form_validation->run() == false) {
                $this->data['error']               = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
                $this->data['message']             = $this->session->flashdata('message');
                $this->data['title']               = lang('reset_password');
                $this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
                $this->data['new_password']        = [
                    'name'                   => 'new',
                    'id'                     => 'new',
                    'type'                   => 'password',
                    'class'                  => 'form-control',
                    'pattern'                => '(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}',
                    'data-bv-regexp-message' => lang('pasword_hint'),
                    'placeholder'            => lang('new_password'),
                ];
                $this->data['new_password_confirm'] = [
                    'name'                      => 'new_confirm',
                    'id'                        => 'new_confirm',
                    'type'                      => 'password',
                    'class'                     => 'form-control',
                    'data-bv-identical'         => 'true',
                    'data-bv-identical-field'   => 'new',
                    'data-bv-identical-message' => lang('pw_not_same'),
                    'placeholder'               => lang('confirm_password'),
                ];
                $this->data['user_id'] = [
                    'name'  => 'user_id',
                    'id'    => 'user_id',
                    'type'  => 'hidden',
                    'value' => $user->id,
                ];
                $this->data['csrf']           = $this->_get_csrf_nonce();
                $this->data['code']           = $code;
                $this->data['identity_label'] = $user->email;
                $this->load->view($this->theme . 'salemans/reset_password', $this->data);
            } else {
                if ($user->id != $this->input->post('user_id')) {
                    $this->ion_auth->clear_forgotten_password_code($code);
                    show_error(lang('error_csrf'));
                } else {
                    $identity = $user->email;
                    $change = $this->ion_auth->reset_password($identity, $this->input->post('new'));
                    if ($change) {
                        $this->session->set_flashdata('message', $this->ion_auth->messages());
                        admin_redirect('login');
                    } else {
                        $this->session->set_flashdata('error', $this->ion_auth->errors());
                        admin_redirect('salemans/reset_password/' . $code);
                    }
                }
            }
        } else {
            $this->session->set_flashdata('error', $this->ion_auth->errors());
            admin_redirect('login#forgot_password');
        }
    }
    public function change_password()
    { 
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }
        if (!$this->ion_auth->logged_in()) {
            admin_redirect('login');
        }
        $this->form_validation->set_rules('old_password', lang('old_password'), 'required');
        $this->form_validation->set_rules('new_password', lang('new_password'), 'required|min_length[8]|max_length[25]');
        $this->form_validation->set_rules('new_password_confirm', lang('confirm_password'), 'required|matches[new_password]');

        $user = $this->ion_auth->user($id)->row();
        if ($this->form_validation->run() == false) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('salemans/profile/' . $user->id . '/#cpassword');
        } else {
            $identity = $user->email;
            $change = $this->ion_auth->change_password($identity, $this->input->post('old_password'), $this->input->post('new_password'));
            if ($change) {
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                $this->logout();
            } else {
                $this->session->set_flashdata('error', $this->ion_auth->errors());
                admin_redirect('salemans/profile/' . $user->id . '/#cpassword');
            }
        }
    }
    public function profile($id = null)
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('owner') && $id != $this->session->userdata('user_id')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'admin');
        }
        if (!$id || empty($id)) {
            admin_redirect('salemans');
        }

        $this->data['title']      = lang('profile');
        $user                     = $this->ion_auth->user($id)->row();
        $groups                   = $this->ion_auth->groups()->result_array();
        $this->data['csrf']       = $this->_get_csrf_nonce();
        $this->data['user']       = $user;
        $this->data['groups']     = $groups;
        $this->data['billers']    = $this->site->getAllCompanies('biller');
        $this->data['zones']      = $this->site->getAllZones();
        $this->data['settings']   = $this->site->getSettings();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['data']       = $this->site->getBillerByUser($id);
        $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['password'] = [
            'name'  => 'password',
            'id'    => 'password',
            'class' => 'form-control',
            'type'  => 'password',
            'value' => '',
        ];
        $this->data['password_confirm'] = [
            'name'  => 'password_confirm',
            'id'    => 'password_confirm',
            'class' => 'form-control',
            'type'  => 'password',
            'value' => '',
        ];
        $this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
        $this->data['old_password']        = [
            'name'  => 'old',
            'id'    => 'old',
            'class' => 'form-control',
            'type'  => 'password',
        ];
        $this->data['new_password'] = [
            'name'    => 'new',
            'id'      => 'new',
            'type'    => 'password',
            'class'   => 'form-control',
            'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$',
        ];
        $this->data['new_password_confirm'] = [
            'name'    => 'new_confirm',
            'id'      => 'new_confirm',
            'type'    => 'password',
            'class'   => 'form-control',
            'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$',
        ];
        $this->data['user_id'] = [
            'name'  => 'user_id',
            'id'    => 'user_id',
            'type'  => 'hidden',
            'value' => $user->id,
        ];

        $this->data['id'] = $id;
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('salemans'), 'page' => lang('salemans')], ['link' => '#', 'page' => lang('profile')]];
        $meta = ['page_title' => lang('profile'), 'bc' => $bc];
        $this->page_construct('salemans/profile', $meta, $this->data);
    }
    public function login($m = null)
    {
        if ($this->loggedIn) {
            $this->session->set_flashdata('error', $this->session->flashdata('error'));
            admin_redirect('welcome');
        }
        $this->data['title'] = lang('login');

        if ($this->Settings->captcha) {
            $this->form_validation->set_rules('captcha', lang('captcha'), 'required|callback_captcha_check');
        }

        if ($this->form_validation->run() == true) {
            $remember = (bool)$this->input->post('remember');

            if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember)) {
                if ($this->Settings->mmode) {
                    if (!$this->ion_auth->in_group('owner')) {
                        $this->session->set_flashdata('error', lang('site_is_offline_plz_try_later'));
                        admin_redirect('salemans/logout');
                    }
                }
                if ($this->ion_auth->in_group('customer') || $this->ion_auth->in_group('supplier')) {
                    if (file_exists(APPPATH . 'controllers' . DIRECTORY_SEPARATOR . 'shop' . DIRECTORY_SEPARATOR . 'Shop.php')) {
                        $this->session->set_flashdata('message', $this->ion_auth->messages());
                        redirect(base_url());
                    } else {
                        admin_redirect('salemans/logout/1');
                    }
                }
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                $referrer = ($this->session->userdata('requested_page') && $this->session->userdata('requested_page') != 'admin') ? $this->session->userdata('requested_page') : 'welcome';
                admin_redirect($referrer);
            } else {
                $this->session->set_flashdata('error', $this->ion_auth->errors());
                admin_redirect('login');
            }
        } else {
            $this->data['error']   = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['message'] = $this->session->flashdata('message');
            if ($this->Settings->captcha) {
                $this->load->helper('captcha');
                $vals = [
                    'img_path'    => './assets/captcha/',
                    'img_url'     => base_url('assets/captcha/'),
                    'img_width'   => 150,
                    'img_height'  => 34,
                    'word_length' => 5,
                    'colors'      => ['background' => [255, 255, 255], 'border' => [204, 204, 204], 'text' => [102, 102, 102], 'grid' => [204, 204, 204]],
                ];
                $cap     = create_captcha($vals);
                $capdata = [
                    'captcha_time' => $cap['time'],
                    'ip_address'   => $this->input->ip_address(),
                    'word'         => $cap['word'],
                ];

                $query = $this->db->insert_string('captcha', $capdata);
                $this->db->query($query);
                $this->data['image']   = $cap['image'];
                $this->data['captcha'] = [
                    'name'          => 'captcha',
                    'id'            => 'captcha',
                    'type'          => 'text',
                    'class'         => 'form-control',
                    'required'      => 'required',
                    'placeholder'   => lang('type_captcha'),
                ];
            }
            $this->data['identity'] = [
                'name'              => 'identity',
                'id'                => 'identity',
                'type'              => 'text',
                'class'             => 'form-control',
                'placeholder'       => lang('email'),
                'value'             => $this->form_validation->set_value('identity'),
            ];
            $this->data['password'] = [
                'name'              => 'password',
                'id'                => 'password',
                'type'              => 'password',
                'class'             => 'form-control',
                'required'          => 'required',
                'placeholder'       => lang('password'),
            ];
            $this->data['allow_reg'] = $this->Settings->allow_reg;
            if ($m == 'db') {
                $this->data['message'] = lang('db_restored');
            } elseif ($m) {
                $this->data['error'] = lang('we_are_sorry_as_this_sction_is_still_under_development.');
            }
            $this->load->view($this->theme . 'salemans/login', $this->data);
        }
    }
    public function logout($m = null)
    {
        $logout = $this->ion_auth->logout();
        $this->session->set_flashdata('message', $this->ion_auth->messages());
        admin_redirect('login/' . $m);
    }
    public function view($id = null)
    {
        $this->bpas->checkPermissions('index', true);
        $this->data['error']    = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['saleman']  = $this->site->getUser($id);
        $this->data['group']    = $this->site->getUserGroup($id);
        $this->data['zones']     = $this->site->getAllZones();
        $this->load->view($this->theme . 'salemans/view', $this->data);
    }
    public function clear_AP($id = null)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'admin/welcome');
        }
        if ($this->auth_model->clear_award_points($id)) {
            $this->session->set_flashdata('message', lang('award_points_clear_successfully'));
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
}