<?php

defined('BASEPATH') or exit('No direct script access allowed');

class system_settings extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->bpas->md('login');
        }
        $this->lang->admin_load('settings', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('settings_model');
        $this->load->admin_model('products_model');
        $this->load->admin_model('accounts_model');
        $this->upload_path        = 'assets/uploads/';
        $this->thumbs_path        = 'assets/uploads/thumbs/';
        $this->image_types        = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif';
        $this->allowed_file_size  = '1024';
        $this->load->library('pdf');
    }

    public function brands()
    {
        $this->bpas->checkPermissions('brands', null,'system_settings');
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('brands')]];
        $meta                = ['page_title' => lang('brands'), 'bc' => $bc];
        $this->page_construct('settings/brands', $meta, $this->data);
    }

    public function categories()
    {
        $this->bpas->checkPermissions('categories', null,'system_settings');
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('categories')]];
        $meta                = ['page_title' => lang('categories'), 'bc' => $bc];
        $this->page_construct('settings/categories', $meta, $this->data);

    }
    public function getCategories(){
        $print_barcode = anchor('admin/products/print_barcodes/?category=$1', '<i class="fa fa-print"></i>', 'title="' . lang('print_barcodes') . '" class="tip"');
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('categories')}.id as id, 
                {$this->db->dbprefix('categories')}.image, 
                {$this->db->dbprefix('categories')}.code, 
                {$this->db->dbprefix('categories')}.name, 
                {$this->db->dbprefix('categories')}.order_number as order_number, 
                c.name as parent", false)
            ->from('categories')
            ->join('categories c', 'c.id=categories.parent_id', 'left')
            ->group_by('categories.id')
            ->order_by('categories.code')
            ->add_column('Actions', '<div class="text-center">' . $print_barcode . " <a href='" . admin_url('system_settings/edit_category/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_category') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_category') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_category/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }
    public function add_category()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang('category_code'), 'trim|is_unique[categories.code]|required');
        $this->form_validation->set_rules('name', lang('name'), 'required|min_length[3]');   
        $this->form_validation->set_rules('userfile', lang('category_image'), 'xss_clean');
        $this->form_validation->set_rules('description', lang('description'), 'trim');
        if (SHOP) {
            $this->form_validation->set_rules('slug', lang('slug'), 'required|is_unique[categories.slug]|alpha_dash');
        }
        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'code'        => $this->input->post('code'),
                'slug'        => $this->input->post('slug'),
                'order_number'=> $this->input->post('order_number'),
                'description' => $this->input->post('description'),
                'parent_id'   => $this->input->post('parent'),
                'status'      => $this->input->post('status'),
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
        } elseif ($this->input->post('add_category')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addCategory($data)) {
            $this->session->set_flashdata('message', lang('category_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']      = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['categories'] = $this->settings_model->getParentCategories();
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['modal_js']   = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_category', $this->data);
        }
    }
    public function edit_category($id = null)
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang('category_code'), 'trim|required');
        $pr_details = $this->settings_model->getCategoryByID($id);
        if ($this->input->post('code') != $pr_details->code) {
            $this->form_validation->set_rules('code', lang('category_code'), 'required|is_unique[categories.code]');
        }
        if (SHOP) {
            $this->form_validation->set_rules('slug', lang('slug'), 'required|alpha_dash');
            if ($this->input->post('slug') != $pr_details->slug) {
                $this->form_validation->set_rules('slug', lang('slug'), 'required|alpha_dash|is_unique[categories.slug]');
            }
        }
        $this->form_validation->set_rules('name', lang('category_name'), 'required|min_length[3]');
        $this->form_validation->set_rules('userfile', lang('category_image'), 'xss_clean');
        $this->form_validation->set_rules('description', lang('description'), 'trim');

        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'code'        => $this->input->post('code'),
                'slug'        => $this->input->post('slug'),
                'order_number'=> $this->input->post('order_number'),
                'description' => $this->input->post('description'),
                'parent_id'   => $this->input->post('parent'),
                'status'   => $this->input->post('status'),
                
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
        } elseif ($this->input->post('edit_category')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/categories');
        }
        if ($this->form_validation->run() == true && $this->settings_model->updateCategory($id, $data)) {
            $this->session->set_flashdata('message', lang('category_updated'));
            admin_redirect('system_settings/categories');
        } else {
            $this->data['error']      = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['category']   = $this->settings_model->getCategoryByID($id);
            $this->data['categories'] = $this->settings_model->getParentCategories();
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['modal_js']   = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_category', $this->data);
        }
    }
    public function delete_category($id = null)
    {
        if ($this->site->getSubCategories($id)) {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('category_has_subcategory')]);
        }

        if ($this->settings_model->deleteCategory($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('category_deleted')]);
        }
    }
    public function delete_currency($id = null)
    {
        if ($this->settings_model->deleteCurrency($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('currency_deleted')]);
        }
    }
    public function category_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCategory($id);
                    }
                    $this->session->set_flashdata('message', lang('categories_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('categories'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('slug'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('image'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('parent_category'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc              = $this->settings_model->getCategoryByID($id);
                        $parent_category = '';
                        if ($sc->parent_id) {
                            $pc              = $this->settings_model->getCategoryByID($sc->parent_id);
                            $parent_category = $pc->code;
                        }
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->slug);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->image);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $parent_category);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'categories_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_record_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function variants()
    {
        $this->bpas->checkPermissions('variants', null,'system_settings');
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('variants')]];
        $meta = ['page_title' => lang('variants'), 'bc' => $bc];
        $this->page_construct('settings/variants', $meta, $this->data);
    }
    public function units(){
        $this->bpas->checkPermissions('units', null,'system_settings');
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('units')]];
        $meta = ['page_title' => lang('units'), 'bc' => $bc];
        $this->page_construct('settings/units', $meta, $this->data);
    }

    public function update_product_multibuy_price($group_id = null)
    {
        if (!$group_id) {
            $this->bpas->send_json(['status' => 0]);
        }

        $product_id = $this->input->post('product_id', true);
        $price      = $this->input->post('price', true);
        $qty_from      = $this->input->post('qty_from', true);
        $qty_to      = $this->input->post('qty_to', true);
        if (!empty($product_id) && !empty($price)) {
            if ($this->settings_model->setProductPriceForMultiBuyPriceGroup($product_id,$qty_from,$qty_to, $group_id, $price)) {
                $this->bpas->send_json(['status' => 1]);
            }
        }

        $this->bpas->send_json(['status' => 0]);
    }

    public function ChangeUnitToGetNewPrice($group_id = null)
    {
        if (!$group_id) {
            $this->bpas->send_json(false);
        }

        $product_id     = $this->input->get('product_id', true);
        $unit_id        = $this->input->get('unit_id', true);
    
        if (!empty($product_id) && !empty($unit_id)) {
            if ( $data = $this->settings_model->getPriceByUnit($product_id, $unit_id, $group_id)) {
                $this->bpas->send_json($data);
            }else{
                $data = $this->settings_model->Else_GetPriceByUnit($product_id, $unit_id);
                $this->bpas->send_json($data);
            }
        }

        $this->bpas->send_json(false);
    }

    public function options(){
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('variants')]];
        $meta = ['page_title' => lang('options'), 'bc' => $bc];
        $this->page_construct('settings/options', $meta, $this->data);
    }
    //upload slide
    public function upload_slide()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('upload_slide')]];
        $meta                = ['page_title' => lang('upload_slide'), 'bc' => $bc];
        $this->page_construct('settings/upload_slide', $meta, $this->data);
    }
    public function add_slide()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            $this->bpas->md();
        }
        $this->load->helper('security');
        $this->form_validation->set_rules('add_slide', lang('add_slide'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($_FILES['add_slide']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path . 'slides/';
                $config['allowed_types'] = "jpg";
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = 3000;
                $config['max_height']    = 3000;
                $config['overwrite']     = false;
                $config['max_filename']  = 25;
                //$config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('add_slide')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo = $this->upload->file_name;
            }
            $this->session->set_flashdata('message', lang('slide_uploaded'));
            redirect($_SERVER['HTTP_REFERER']);
        } elseif ($this->input->post('slide_uploaded')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_slide', $this->data);
        }
    }
     public function delete_slide($bg = null)
    {
        unlink('assets/uploads/slides/'.$bg);
        $this->bpas->send_json(['error' => 0, 'msg' => lang('slide_deleted')]);
        // $this->bpas->send_json(['message' => 1, 'msg' => lang('slide_deleted')]);
        // redirect($_SERVER["HTTP_REFERER"]);          
    }
    public function add_brand()
    {
        $this->form_validation->set_rules('name', lang('brand_name'), 'trim|required|is_unique[brands.name]|alpha_numeric_spaces');
        //  $this->form_validation->set_rules('slug', lang('slug'), 'trim|is_unique[brands.slug]|alpha_dash');
        $this->form_validation->set_rules('description', lang('description'), 'trim');
        if(SHOP) {
            $this->form_validation->set_rules('slug', lang('slug'), 'trim|required|is_unique[brands.slug]|alpha_dash');
        }

        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'code'        => $this->input->post('code'),
                'slug'        => $this->input->post('slug'),
                'description' => $this->input->post('description'),
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
        } elseif ($this->input->post('add_brand')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addBrand($data)) {
            $this->session->set_flashdata('message', lang('brand_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_brand', $this->data);
        }
    }

    
    public function add_currency()
    {
        $this->form_validation->set_rules('code', lang('currency_code'), 'trim|is_unique[currencies.code]|required');
        $this->form_validation->set_rules('name', lang('name'), 'required');
        $this->form_validation->set_rules('rate', lang('exchange_rate'), 'required|numeric');

        if ($this->form_validation->run() == true) {
            $data = ['code'   => $this->input->post('code'),
                'name'        => $this->input->post('name'),
                'rate'        => $this->input->post('rate'),
                'symbol'      => $this->input->post('symbol'),
                'auto_update' => $this->input->post('auto_update') ? $this->input->post('auto_update') : 0,
            ];
        } elseif ($this->input->post('add_currency')) {
            $this->session->set_flashdata('error', validation_errors());
            
            admin_redirect('system_settings/currencies');
        }

        if ($this->form_validation->run() == true && $this->settings_model->addCurrency($data)) { //check to see if we are creating the customer
            $this->session->set_flashdata('message', lang('currency_added'));
            admin_redirect('system_settings/currencies');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']   = $this->site->modal_js();
            $this->data['page_title'] = lang('new_currency');
            $this->load->view($this->theme . 'settings/add_currency', $this->data);
        }
    }

    public function add_customer_group()
    {
        $this->form_validation->set_rules('name', lang('group_name'), 'trim|is_unique[customer_groups.name]|required');
        $this->form_validation->set_rules('percent', lang('group_percentage'), 'required|numeric');

        if ($this->form_validation->run() == true) {
            $data = ['name' => $this->input->post('name'),
                'percent'   => $this->input->post('percent'),
            ];
        } elseif ($this->input->post('add_customer_group')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/customer_groups');
        }

        if ($this->form_validation->run() == true && $this->settings_model->addCustomerGroup($data)) {
            $this->session->set_flashdata('message', lang('customer_group_added'));
            admin_redirect('system_settings/customer_groups');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_customer_group', $this->data);
        }
    }
    public function add_commission_product()
    {
        $this->form_validation->set_rules('name', lang('group_name'), 'trim|is_unique[price_groups.name]|required|alpha_numeric_spaces');

        if ($this->form_validation->run() == true) {
            $data = [
                'name' => $this->input->post('name'),
                'type' => 'commission'
            ];
        } elseif ($this->input->post('add_commission_product')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/commission_products');
        }
        if ($this->form_validation->run() == true && $this->settings_model->addPriceGroup($data)) {
            $this->session->set_flashdata('message', lang('commission_product_added'));
            admin_redirect('system_settings/commission_product');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_commission_product', $this->data);
        }
    }
    public function add_price_group()
    {
        // $this->form_validation->set_rules('name', lang('group_name'), 'trim|is_unique[price_groups.name]|required|alpha_numeric_spaces');
        $this->form_validation->set_rules('name', lang('group_name'), 'trim|is_unique[price_groups.name]|required');
        if ($this->form_validation->run() == true) {
            $data = ['name' => $this->input->post('name')];
        } elseif ($this->input->post('add_price_group')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/price_groups');
        }
        if ($this->form_validation->run() == true && $this->settings_model->addPriceGroup($data)) {
            $this->session->set_flashdata('message', lang('price_group_added'));
            admin_redirect('system_settings/price_groups');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_price_group', $this->data);
        }
    }
    public function add_multi_buys()
    {
        $this->form_validation->set_rules('name', lang('group_name'), 'trim|is_unique[price_groups.name]|required|alpha_numeric_spaces');
        if ($this->form_validation->run() == true) {
        
            $data = [
                'name' => $this->input->post('name'),
                'type' => 'multi_buy'
            ];
        } elseif ($this->input->post('add_multi_buys')) {
            $this->session->set_flashdata('error', validation_errors());
           redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addPriceGroup($data)) {
            $this->session->set_flashdata('message', lang('price_group_added'));
            admin_redirect('system_settings/multi_buy_groups');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_multi_buys', $this->data);
        }
    }
    public function add_tax_rate()
    {
        $this->form_validation->set_rules('name', lang('name'), 'trim|is_unique[tax_rates.name]|required');
        $this->form_validation->set_rules('type', lang('type'), 'required');
        $this->form_validation->set_rules('rate', lang('tax_rate'), 'required|numeric');

        if ($this->form_validation->run() == true) {
            $data = ['name' => $this->input->post('name'),
                'code'      => $this->input->post('code'),
                'type'      => $this->input->post('type'),
                'rate'      => $this->input->post('rate'),
            ];
        } elseif ($this->input->post('add_tax_rate')) {
            $this->session->set_flashdata('error', validation_errors());
           redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addTaxRate($data)) {
            $this->session->set_flashdata('message', lang('tax_rate_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_tax_rate', $this->data);
        }
    }

    public function add_unit()
    {
        $this->form_validation->set_rules('code', lang('unit_code'), 'trim|is_unique[units.code]|required');
        $this->form_validation->set_rules('name', lang('unit_name'), 'trim|required');
        if ($this->input->post('base_unit')) {
            $this->form_validation->set_rules('operator', lang('operator'), 'required');
            $this->form_validation->set_rules('operation_value', lang('operation_value'), 'trim|required');
        }

        if ($this->form_validation->run() == true) {
            $data = [
                'name'            => $this->input->post('name'),
                'code'            => $this->input->post('code'),
                'base_unit'       => $this->input->post('base_unit') ? $this->input->post('base_unit') : null,
                'operator'        => $this->input->post('base_unit') ? $this->input->post('operator') : null,
                'operation_value' => $this->input->post('operation_value') ? $this->input->post('operation_value') : null,
            ];
        } elseif ($this->input->post('add_unit')) {
            $this->session->set_flashdata('error', validation_errors());
           redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addUnit($data)) {
            $this->session->set_flashdata('message', lang('unit_added'));
           redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']      = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['base_units'] = $this->site->getAllBaseUnits();
            $this->data['modal_js']   = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_unit', $this->data);
        }
    }

    public function add_variant()
    {
        $this->form_validation->set_rules('name', lang('name'), 'trim|is_unique[variants.name]|required');

        if ($this->form_validation->run() == true) {
            $data = ['name' => $this->input->post('name')];
        } elseif ($this->input->post('add_variant')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/variants');
        }

        if ($this->form_validation->run() == true && $this->settings_model->addVariant($data)) {
            $this->session->set_flashdata('message', lang('variant_added'));
            admin_redirect('system_settings/variants');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_variant', $this->data);
        }
    }

    public function backup_database()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $this->load->dbutil();
        $prefs = [
            'format'   => 'txt',
            'filename' => 'sma_db_backup.sql',
        ];
        $back    = $this->dbutil->backup($prefs);
        $backup  = &$back;
        $db_name = 'db-backup-on-' . date('Y-m-d-H-i-s') . '.txt';
        $save    = './files/backups/' . $db_name;
        $this->load->helper('file');
        write_file($save, $backup);
        $this->session->set_flashdata('messgae', lang('db_saved'));
        admin_redirect('system_settings/backups');
    }

    public function backup_files()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $name = 'file-backup-' . date('Y-m-d-H-i-s');
        $this->bpas->zip('./', './files/backups/', $name);
        $this->session->set_flashdata('messgae', lang('backup_saved'));
        admin_redirect('system_settings/backups');
        exit();
    }

    public function backups()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $this->data['files'] = glob('./files/backups/*.zip', GLOB_BRACE);
        $this->data['dbs']   = glob('./files/backups/*.txt', GLOB_BRACE);
        krsort($this->data['files']);
        krsort($this->data['dbs']);
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('backups')]];
        $meta = ['page_title' => lang('backups'), 'bc' => $bc];
        $this->page_construct('settings/backups', $meta, $this->data);
    }

    public function brand_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteBrand($id);
                    }
                    $this->session->set_flashdata('message', lang('brands_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('brands'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('image'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $brand = $this->site->getBrandByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $brand->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $brand->code);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $brand->image);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'brands_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_record_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function floor_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                         if ($this->settings_model->floorHasRoom($id)) {
                             $this->session->set_flashdata('error', lang('floor_has_rooms'));
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                        $this->settings_model->deleteFloor($id);
                    }
                    $this->session->set_flashdata('message', lang('floor_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }
                 if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('Floors'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('description'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $floor = $this->site->getFloorByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $floor->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $floor->description);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'floors_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_record_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function floors()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('floors')]];
        $meta                = ['page_title' => lang('floors'), 'bc' => $bc];
        $this->page_construct('settings/floors', $meta, $this->data);
    }
    

    public function change_logo()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            $this->bpas->md();
        }
        $this->load->helper('spaces_helper');
        $this->load->helper('security');
        $this->form_validation->set_rules('site_logo', lang('site_logo'), 'xss_clean');
        $this->form_validation->set_rules('login_logo', lang('login_logo'), 'xss_clean');
        $this->form_validation->set_rules('biller_logo', lang('biller_logo'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($_FILES['site_logo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path . 'logos/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = 300;
                $config['max_height']    = 300;
                $config['overwrite']     = false;
                $config['max_filename']  = 25;
                //$config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('site_logo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $site_logo = $this->upload->file_name;
                $this->db->update('settings', ['logo' => $site_logo], ['setting_id' => 1]);
            }

            if ($_FILES['login_logo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path . 'logos/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = 300;
                $config['max_height']    = 300;
                $config['overwrite']     = false;
                $config['max_filename']  = 25;
                //$config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('login_logo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $login_logo = $this->upload->file_name;
                $this->db->update('settings', ['logo2' => $login_logo], ['setting_id' => 1]);
            }

            if ($_FILES['biller_logo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path . 'logos/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = 300;
                $config['max_height']    = 300;
                $config['overwrite']     = false;
                $config['max_filename']  = 25;
                //$config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('biller_logo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo = $this->upload->file_name;
            }
            if ($_FILES['mobile_splish_logo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path . 'logos/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = 200;
                $config['max_height']    = 500;
                $config['overwrite']     = false;
                $config['max_filename']  = 25;
                //$config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('mobile_splish_logo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $mobile_splish_logo = $this->upload->file_name;
                $this->db->update('settings', ['mobile_splish_logo' => $mobile_splish_logo], ['setting_id' => 1]);
            }

            if ($_FILES['mobile_login_logo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path . 'logos/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = 200;
                $config['max_height']    = 500;
                $config['overwrite']     = false;
                $config['max_filename']  = 25;
                //$config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('mobile_login_logo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $mobile_login_logo = $this->upload->file_name;
                $this->db->update('settings', ['mobile_login_logo' => $mobile_login_logo], ['setting_id' => 1]);
            }

            if ($_FILES['mobile_header_logo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path . 'logos/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = 200;
                $config['max_height']    = 500;
                $config['overwrite']     = false;
                $config['max_filename']  = 25;
                //$config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('mobile_header_logo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $mobile_header_logo = $this->upload->file_name;
                $this->db->update('settings', ['mobile_header_logo' => $mobile_header_logo], ['setting_id' => 1]);
            }

            if ($_FILES['upload_to_space']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = './uploads/';
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = 300;
                $config['max_height']    = 300;
                $config['overwrite']     = false;
                $config['max_filename']  = 25;
                //$config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('upload_to_space')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $data = $this->upload->data();
                $file_url = upload_to_spaces($data['full_path'], $data['file_name']);
                if ($file_url) {
                    unlink($data['full_path']);
                    $view_data = array(
                        'file_url' => $file_url,
                        'file_name' => $data['file_name']
                    );

                    //$site_logo = $this->upload->file_name;
                    $this->db->update('settings', ['logo' => $file_url], ['setting_id' => 1]);
                }
            
            }


            $this->session->set_flashdata('message', lang('logo_uploaded'));
            redirect($_SERVER['HTTP_REFERER']);
        } elseif ($this->input->post('upload_logo')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/change_logo', $this->data);
        }
    }

    public function create_group()
    {
        $this->form_validation->set_rules('group_name', lang('group_name'), 'required|alpha_dash|is_unique[groups.name]');

        if ($this->form_validation->run() == true) {
            $data = ['name' => strtolower($this->input->post('group_name')), 'description' => $this->input->post('description')];
        } elseif ($this->input->post('create_group')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/user_groups');
        }

        if ($this->form_validation->run() == true && ($new_group_id = $this->settings_model->addGroup($data))) {
            //----------multi approved----------
            $this->db->insert('approved_by', array('group_id' => $new_group_id));

            $this->session->set_flashdata('message', lang('group_added'));
            admin_redirect('system_settings/permissions/' . $new_group_id);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['group_name'] = [
                'name'  => 'group_name',
                'id'    => 'group_name',
                'type'  => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('group_name'),
            ];
            $this->data['description'] = [
                'name'  => 'description',
                'id'    => 'description',
                'type'  => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('description'),
            ];
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/create_group', $this->data);
        }
    }
    public function customer_group_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCustomerGroup($id);
                    }
                    $this->session->set_flashdata('message', lang('customer_groups_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('tax_rates'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('group_name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('group_percentage'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $pg = $this->settings_model->getCustomerGroupByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $pg->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $pg->percent);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'customer_groups_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_customer_group_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function customer_groups()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('customer_groups')]];
        $meta = ['page_title' => lang('customer_groups'), 'bc' => $bc];
        $this->page_construct('settings/customer_groups', $meta, $this->data);
    }

    public function delete_backup($zipfile)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        unlink('./files/backups/' . $zipfile . '.zip');
        $this->session->set_flashdata('messgae', lang('backup_deleted'));
        admin_redirect('system_settings/backups');
    }

    public function delete_brand($id = null)
    {
        if ($this->settings_model->brandHasProducts($id)) {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('brand_has_products')]);
        }

        if ($this->settings_model->deleteBrand($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('brand_deleted')]);
        }
    }
    public function delete_customer_group($id = null)
    {
        if ($this->settings_model->deleteCustomerGroup($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('customer_group_deleted')]);
        }
    }

    public function delete_database($dbfile)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        unlink('./files/backups/' . $dbfile . '.txt');
        $this->session->set_flashdata('messgae', lang('db_deleted'));
        admin_redirect('system_settings/backups');
    }

    public function delete_expense_category($id = null)
    {
        if ($this->settings_model->hasExpenseCategoryRecord($id)) {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('category_has_expenses')]);
        }

        if ($this->settings_model->deleteExpenseCategory($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('expense_category_deleted')]);
        }
    }

    public function delete_group($id = null)
    {
        if ($this->settings_model->checkGroupUsers($id)) {
            $this->session->set_flashdata('error', lang('group_x_b_deleted'));
            admin_redirect('system_settings/user_groups');
        }

        if ($this->settings_model->deleteGroup($id)) {
            $this->db->delete('permissions', ['group_id' => $id]);
            //-------delete multi approved---------
            $this->db->delete('approved_by', ['group_id' => $id]);

            $this->session->set_flashdata('message', lang('group_deleted'));
            admin_redirect('system_settings/user_groups');
        }
    }

    public function delete_price_group($id = null)
    {
        if ($this->settings_model->deletePriceGroup($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('price_group_deleted')]);
        }
    }

    public function delete_tax_rate($id = null)
    {
        if ($this->settings_model->deleteTaxRate($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('tax_rate_deleted')]);
        }
    }

    public function delete_unit($id = null)
    {
        if ($this->settings_model->getUnitChildren($id)) {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('unit_has_subunit')]);
        }

        if ($this->settings_model->deleteUnit($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('unit_deleted')]);
        }
    }

    public function delete_variant($id = null)
    {
        if ($this->settings_model->deleteVariant($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('variant_deleted')]);
        }
    }
    public function download_backup($zipfile)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $this->load->helper('download');
        force_download('./files/backups/' . $zipfile . '.zip', null);
        exit();
    }

    public function download_database($dbfile)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $this->load->library('zip');
        $this->zip->read_file('./files/backups/' . $dbfile . '.txt');
        $name = $dbfile . '.zip';
        $this->zip->download($name);
        exit();
    }

    public function edit_brand($id = null)
    {
        $this->form_validation->set_rules('name', lang('brand_name'), 'trim|required|alpha_numeric_spaces');
        $brand_details = $this->site->getBrandByID($id);
        if ($this->input->post('name') != $brand_details->name) {
            $this->form_validation->set_rules('name', lang('brand_name'), 'required|is_unique[brands.name]');
        }
        if (SHOP) {
            $this->form_validation->set_rules('slug', lang('slug'), 'required|alpha_dash');
            if ($this->input->post('slug') != $brand_details->slug) {
                $this->form_validation->set_rules('slug', lang('slug'), 'required|alpha_dash|is_unique[brands.slug]');
            }
        }
        $this->form_validation->set_rules('description', lang('description'), 'trim');

        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'code'        => $this->input->post('code'),
                'slug'        => $this->input->post('slug'),
                'description' => $this->input->post('description'),
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
        } elseif ($this->input->post('edit_brand')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/brands');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateBrand($id, $data)) {
            $this->session->set_flashdata('message', lang('brand_updated'));
            admin_redirect('system_settings/brands');
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['brand']    = $brand_details;
            $this->load->view($this->theme . 'settings/edit_brand', $this->data);
        }
    }

    public function edit_currency($id = null)
    {
        $this->form_validation->set_rules('code', lang('currency_code'), 'trim|required');
        $cur_details = $this->settings_model->getCurrencyByID($id);
        if ($this->input->post('code') != $cur_details->code) {
            $this->form_validation->set_rules('code', lang('currency_code'), 'required|is_unique[currencies.code]');
        }
        $this->form_validation->set_rules('name', lang('currency_name'), 'required');
        $this->form_validation->set_rules('rate', lang('exchange_rate'), 'required|numeric');
        if ($this->form_validation->run() == true) {
            $data = ['code'   => $this->input->post('code'),
                'name'        => $this->input->post('name'),
                'rate'        => $this->input->post('rate'),
                'symbol'      => $this->input->post('symbol'),
                'auto_update' => $this->input->post('auto_update') ? $this->input->post('auto_update') : 0,
            ];
        } elseif ($this->input->post('edit_currency')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
            //admin_redirect('system_settings/currencies');
        }
        if ($this->form_validation->run() == true && $this->settings_model->updateCurrency($id, $data)) { //check to see if we are updateing the customer
            $this->session->set_flashdata('message', lang('currency_updated'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['currency'] = $this->settings_model->getCurrencyByID($id);
            $this->data['id']       = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_currency', $this->data);
        }
    }

    public function edit_customer_group($id = null)
    {
        $this->form_validation->set_rules('name', lang('group_name'), 'trim|required');
        $pg_details = $this->settings_model->getCustomerGroupByID($id);
        if ($this->input->post('name') != $pg_details->name) {
            $this->form_validation->set_rules('name', lang('group_name'), 'required|is_unique[tax_rates.name]');
        }
        $this->form_validation->set_rules('percent', lang('group_percentage'), 'required|numeric');
        if ($this->form_validation->run() == true) {
            $data = [
                'name' => $this->input->post('name'),
                'percent'   => $this->input->post('percent'),
            ];
        } elseif ($this->input->post('edit_customer_group')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/customer_groups');
        }
        if ($this->form_validation->run() == true && $this->settings_model->updateCustomerGroup($id, $data)) {
            $this->session->set_flashdata('message', lang('customer_group_updated'));
            admin_redirect('system_settings/customer_groups');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['customer_group'] = $this->settings_model->getCustomerGroupByID($id);
            $this->data['id']       = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_customer_group', $this->data);
        }
    }
    public function edit_group($id)
    {
        if (!$id || empty($id)) {
            admin_redirect('system_settings/user_groups');
        }
        $group = $this->settings_model->getGroupByID($id);
        $this->form_validation->set_rules('group_name', lang('group_name'), 'required|alpha_dash');
        if ($this->form_validation->run() === true) {
            $data         = ['name' => strtolower($this->input->post('group_name')), 'description' => $this->input->post('description')];
            $group_update = $this->settings_model->updateGroup($id, $data);
            if ($group_update) {
                if($this->db->get_where('approved_by', ['group_id'=> $id])){
                    $this->db->insert('approved_by', array('group_id' => $id));
                }
                $this->session->set_flashdata('message', lang('group_udpated'));
            } else {
                $this->session->set_flashdata('error', lang('attempt_failed'));
            }
            admin_redirect('system_settings/user_groups');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['group'] = $group;
            $this->data['group_name'] = [
                'name'  => 'group_name',
                'id'    => 'group_name',
                'type'  => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('group_name', $group->name),
            ];
            $this->data['group_description'] = [
                'name'  => 'group_description',
                'id'    => 'group_description',
                'type'  => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('group_description', $group->description),
            ];
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_group', $this->data);
        }
    }

    public function edit_price_group($id = null)
    {
        $this->form_validation->set_rules('name', lang('group_name'), 'trim|required|alpha_numeric_spaces');
        $pg_details = $this->settings_model->getPriceGroupByID($id);
        if ($this->input->post('name') != $pg_details->name) {
            $this->form_validation->set_rules('name', lang('group_name'), 'required|is_unique[price_groups.name]');
        }

        if ($this->form_validation->run() == true) {
            $data = ['name' => $this->input->post('name')];
        } elseif ($this->input->post('edit_price_group')) {
            $this->session->set_flashdata('error', validation_errors());

            admin_redirect('system_settings/price_groups');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updatePriceGroup($id, $data)) {
            if($pg_details->type == "commission"){
                $this->session->set_flashdata('message', lang('commission_product_updated'));
                admin_redirect('system_settings/commission_product');
            }elseif($pg_details->type == "multi_buy"){
                $this->session->set_flashdata('message', lang('multi_buy_group_updated'));
                admin_redirect('system_settings/multi_buy_groups');
            }else{
                $this->session->set_flashdata('message', lang('price_group_updated'));
                admin_redirect('system_settings/price_groups');
            }
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['price_group'] = $pg_details;
            $this->data['id']          = $id;
            $this->data['modal_js']    = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_price_group', $this->data);
        }
    }

    public function edit_tax_rate($id = null)
    {
        $this->form_validation->set_rules('name', lang('name'), 'trim|required');
        $tax_details = $this->settings_model->getTaxRateByID($id);
        if ($this->input->post('name') != $tax_details->name) {
            $this->form_validation->set_rules('name', lang('name'), 'required|is_unique[tax_rates.name]');
        }
        $this->form_validation->set_rules('type', lang('type'), 'required');
        $this->form_validation->set_rules('rate', lang('tax_rate'), 'required|numeric');

        if ($this->form_validation->run() == true) {
            $data = ['name' => $this->input->post('name'),
                'code'      => $this->input->post('code'),
                'type'      => $this->input->post('type'),
                'rate'      => $this->input->post('rate'),
            ];
        } elseif ($this->input->post('edit_tax_rate')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/tax_rates');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateTaxRate($id, $data)) { //check to see if we are updateing the customer
            $this->session->set_flashdata('message', lang('tax_rate_updated'));
            admin_redirect('system_settings/tax_rates');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['tax_rate'] = $this->settings_model->getTaxRateByID($id);

            $this->data['id']       = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_tax_rate', $this->data);
        }
    }

    public function edit_unit($id = null)
    {
        $this->form_validation->set_rules('code', lang('code'), 'trim|required');
        $unit_details = $this->site->getUnitByID($id);
        if ($this->input->post('code') != $unit_details->code) {
            $this->form_validation->set_rules('code', lang('code'), 'required|is_unique[units.code]');
        }
        $this->form_validation->set_rules('name', lang('name'), 'trim|required');
        if ($this->input->post('base_unit')) {
            $this->form_validation->set_rules('operator', lang('operator'), 'required');
            $this->form_validation->set_rules('operation_value', lang('operation_value'), 'trim|required');
        }

        if ($this->form_validation->run() == true) {
            $data = [
                'name'            => $this->input->post('name'),
                'code'            => $this->input->post('code'),
                'base_unit'       => $this->input->post('base_unit') ? $this->input->post('base_unit') : null,
                'operator'        => $this->input->post('base_unit') ? $this->input->post('operator') : null,
                'operation_value' => $this->input->post('operation_value') ? $this->input->post('operation_value') : null,
            ];
        } elseif ($this->input->post('edit_unit')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/units');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateUnit($id, $data)) {
            $this->session->set_flashdata('message', lang('unit_updated'));
            admin_redirect('system_settings/units');
        } else {
            $this->data['error']      = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js']   = $this->site->modal_js();
            $this->data['unit']       = $unit_details;
            $this->data['base_units'] = $this->site->getAllBaseUnits();
            $this->load->view($this->theme . 'settings/edit_unit', $this->data);
        }
    }

    public function edit_variant($id = null)
    {
        $this->form_validation->set_rules('name', lang('name'), 'trim|required');
        $tax_details = $this->settings_model->getVariantByID($id);
        if ($this->input->post('name') != $tax_details->name) {
            $this->form_validation->set_rules('name', lang('name'), 'required|is_unique[variants.name]');
        }

        if ($this->form_validation->run() == true) {
            $data = ['name' => $this->input->post('name')];
        } elseif ($this->input->post('edit_variant')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/variants');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateVariant($id, $data)) {
            $this->session->set_flashdata('message', lang('variant_updated'));
            admin_redirect('system_settings/variants');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['variant']  = $tax_details;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_variant', $this->data);
        }
    }
    public function email_templates($template = 'credentials')
    {
        $this->form_validation->set_rules('mail_body', lang('mail_message'), 'trim|required');
        $this->load->helper('file');
        $temp_path = is_dir('./themes/' . $this->theme . 'email_templates/');
        $theme     = $temp_path ? $this->theme : 'default';
        if ($this->form_validation->run() == true) {
            $data = $_POST['mail_body'];
            if (write_file('./themes/' . $this->theme . 'email_templates/' . $template . '.html', $data)) {
                $this->session->set_flashdata('message', lang('message_successfully_saved'));
                admin_redirect('system_settings/email_templates#' . $template);
            } else {
                $this->session->set_flashdata('error', lang('failed_to_save_message'));
                admin_redirect('system_settings/email_templates#' . $template);
            }
        } else {
            $this->data['credentials']     = file_get_contents('./themes/' . $this->theme . 'email_templates/credentials.html');
            $this->data['sale']            = file_get_contents('./themes/' . $this->theme . 'email_templates/sale.html');
            $this->data['quote']           = file_get_contents('./themes/' . $this->theme . 'email_templates/quote.html');
            $this->data['purchase']        = file_get_contents('./themes/' . $this->theme . 'email_templates/purchase.html');
            $this->data['transfer']        = file_get_contents('./themes/' . $this->theme . 'email_templates/transfer.html');
            $this->data['payment']         = file_get_contents('./themes/' . $this->theme . 'email_templates/payment.html');
            $this->data['forgot_password'] = file_get_contents('./themes/' . $this->theme . 'email_templates/forgot_password.html');
            $this->data['activate_email']  = file_get_contents('./themes/' . $this->theme . 'email_templates/activate_email.html');

            $this->data['property_agreement']   = file_get_contents('./themes/' . $this->theme . 'email_templates/property_agreement.html');
            $this->data['service_agreement']    = file_get_contents('./themes/' . $this->theme . 'email_templates/service_agreement.html');
            $this->data['payslip']              = file_get_contents('./themes/' . $this->theme . 'email_templates/payslip.html');

            $bc                            = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('email_templates')]];
            $meta                          = ['page_title' => lang('email_templates'), 'bc' => $bc];
            $this->page_construct('settings/email_templates', $meta, $this->data);
        }
    }

    
    public function getExpenseCategories()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("
                {$this->db->dbprefix('expense_categories')}.id as id, 
                {$this->db->dbprefix('expense_categories')}.code, 
                {$this->db->dbprefix('expense_categories')}.name,
                CONCAT({$this->db->dbprefix('gl_charts')}.accountcode,' ',{$this->db->dbprefix('gl_charts')}.accountname) as expense_account, 
                c.name as parent", false)

            ->from('expense_categories')
            ->join('expense_categories c', 'c.id=expense_categories.parent_id', 'left')
            ->join('gl_charts', 'gl_charts.accountcode=expense_categories.expense_account', 'left')
            ->group_by('expense_categories.id')
            
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/edit_expense_category/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_expense_category') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_expense_category') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_expense_category/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }
    public function add_expense_category()
    {
        $this->form_validation->set_rules('code', lang('category_code'), 'trim|is_unique[categories.code]|required');
        $this->form_validation->set_rules('name', lang('name'), 'required|min_length[3]');
        if($this->Settings->module_account == 1){
            $this->form_validation->set_rules('expense_account', lang('expense_account'), 'required');
        }
        if ($this->form_validation->run() == true) {
            $data = [
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
                'parent_id'   => $this->input->post('parent'),
                'expense_account' => $this->input->post('expense_account'),
                'note' => $this->input->post('note'),
            ];
        } elseif ($this->input->post('add_expense_category')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('expenses/expense_categories');
        }

        if ($this->form_validation->run() == true && $this->settings_model->addExpenseCategory($data)) {
            $this->session->set_flashdata('message', lang('expense_category_added'));
            admin_redirect('expenses/expense_categories');
        } else {
            $this->data['error']   = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            if($this->Settings->module_account == 1){
                $this->data['expense_accounts'] = $this->accounts_model->getAllChartAccountexpense('50,60,80');
            }
            $this->data['expenses'] = $this->settings_model->getParentExpenseCategories();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_expense_category', $this->data);
        }
    }
    public function edit_expense_category($id = null)
    {
        $this->form_validation->set_rules('code', lang('category_code'), 'trim|required');
        $category = $this->settings_model->getExpenseCategoryByID($id);
        if ($this->input->post('code') != $category->code) {
            $this->form_validation->set_rules('code', lang('category_code'), 'required|is_unique[expense_categories.code]');
        }
        $this->form_validation->set_rules('name', lang('category_name'), 'required|min_length[3]');
        if($this->Settings->module_account == 1){
            $this->form_validation->set_rules('expense_account', lang('expense_account'), 'required');
        }
        if ($this->form_validation->run() == true) {
            $data = [
                'code'          => $this->input->post('code'),
                'name'          => $this->input->post('name'),
                'parent_id'     => $this->input->post('parent'),
                'expense_account' => $this->input->post('expense_account'),
                'note'          => $this->input->post('note'),
            ];
        } elseif ($this->input->post('edit_expense_category')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('expenses/expense_categories');
        }
        if ($this->form_validation->run() == true && $this->settings_model->updateExpenseCategory($id, $data, $photo)) {
            $this->session->set_flashdata('message', lang('expense_category_updated'));
            admin_redirect('expenses/expense_categories');
        } else {
            $this->data['error']    =validation_errors() ? validation_errors() : $this->session->flashdata('error');
            if($this->Settings->module_account == 1){
                $this->data['expense_accounts'] = $this->accounts_model->getAllChartAccountexpense('50,60,80');
            }
            $this->data['expenses'] = $this->settings_model->getParentExpenseCategories();
            $this->data['category'] = $category;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_expense_category', $this->data);
        }
    }
    public function expense_category_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCategory($id);
                    }
                    $this->session->set_flashdata('message', lang('categories_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('categories'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getCategoryByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'expense_categories_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_record_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function getBrands()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('id, image, code, name, slug')
            ->from('brands')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/edit_brand/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_brand') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_brand') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_brand/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }
    public function getFloors()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('id, name, description')
            ->from('floors')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/edit_floor/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_floor') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_floor') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_floor/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }
  
    public function currencies()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('currencies')]];
        $meta = ['page_title' => lang('currencies'), 'bc' => $bc];
        $this->page_construct('settings/currencies', $meta, $this->data);
    }
    public function getCurrencies()
    {
        $this->load->library('datatables');
        if($this->Settings->module_tax){
            $calender_link = anchor('admin/system_settings/currency_calender/$1', '<i class="fa fa-calendar"></i> ' . lang('daily_currency_rate'), ' class="currency_calender"');
        }else{
            $calender_link='';
        }
        $edit_link = anchor('admin/system_settings/edit_currency/$1', '<i class="fa fa-edit"></i> ' . lang('edit_currency'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $delete_link = "<a href='#' class='delete_currency po' title='<b>" . $this->lang->line("delete_currency") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_currency/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_currency') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $calender_link . '</li>
                        <li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';

        $this->datatables
            ->select('id, code, name, rate, symbol')
            ->from('currencies');
            //->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/edit_currency/$1') . "' class='tip' title='" . lang('edit_currency') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_currency') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_currency/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        $this->datatables->add_column("Actions", $action, "id");

        echo $this->datatables->generate();
    }
    public function currency_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCurrency($id);
                    }
                    $this->session->set_flashdata('message', lang('currencies_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('currencies'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('rate'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getCurrencyByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->rate);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'currencies_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_record_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    
    public function currency_calender($currency_id = false)
    {
        $this->data['cal_lang'] = $this->get_cal_lang();
        $this->data['currency'] = $this->settings_model->getCurrencyByID($currency_id);
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')),array('link' => site_url('system_settings/currencies'), 'page' => lang('Currencies')), array('link' => '#', 'page' => lang('daily_currency_rate')));
        $meta = array('page_title' => lang('daily_currency_rate'), 'bc' => $bc);
        $this->page_construct('settings/currency_calender', $meta, $this->data);
    }
    public function get_cal_lang() 
    {
        switch ($this->Settings->user_language) {
            case 'thai':
            $cal_lang = 'th';
            break;
            case 'vietnamese':
            $cal_lang = 'vi';
            break;
         
            case 'simplified-chinese':
            $cal_lang = 'zh-tw';
            break;
            case 'traditional-chinese':
            $cal_lang = 'zh-cn';
            break;
         
            default:
            $cal_lang = 'en';
            break;
        }
        return $cal_lang;
    }
    public function get_currency_calender()
    {
        $cal_lang = $this->get_cal_lang();
        $this->load->library('fc', array('lang' => $cal_lang));
        $input_arrays = $this->settings_model->getCurrencyCalender();
        $output_arrays = array();
        foreach ($input_arrays as $array) {
            $array['title']= $array['rate'];
            $output_arrays[] = $array;
        }
        $this->bpas->send_json($output_arrays);
    }
    public function add_currency_calender()
    {
        $this->form_validation->set_rules('exchange_rate', lang("exchange_rate"), 'trim|required');
        $this->form_validation->set_rules('currency_id', lang("currency"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
                'currency_id' => $this->input->post('currency_id'),
                'date' => $this->bpas->fld(trim($this->input->post('date'))),
                'rate' => $this->input->post('exchange_rate'),
            );
            
            if ($this->settings_model->addCurrencyCalender($data)) {
                $res = array('error' => 0, 'msg' => lang('currency_calender_added'));
                $this->bpas->send_json($res);
            } else {
                $res = array('error' => 1, 'msg' => lang('action_failed'));
                $this->bpas->send_json($res);
            }
        }
    }
    
    public function update_currency_calender()
    {
        $this->form_validation->set_rules('exchange_rate', lang("exchange_rate"), 'trim|required');
        $this->form_validation->set_rules('currency_id', lang("currency"), 'required');
        if ($this->form_validation->run() == true) {
            $id = $this->input->post('id');
            $data = array(
                'currency_id' => $this->input->post('currency_id'),
                'rate' => $this->input->post('exchange_rate'),
            );
            
            if ($this->settings_model->updateCurrencyCalender($id,$data)) {
                $res = array('error' => 0, 'msg' => lang('currency_calender_edited'));
                $this->bpas->send_json($res);
            } else {
                $res = array('error' => 1, 'msg' => lang('action_failed'));
                $this->bpas->send_json($res);
            }
        }
    }
    
    public function delete_currency_calender($id)
    {
        if($this->input->is_ajax_request()) {
            $this->db->delete('currency_calenders', array('id' => $id));
            $res = array('error' => 0, 'msg' => lang('currency_calender_deleted'));
            $this->bpas->send_json($res);
        }
    }

    public function getCustomerGroups()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('id, name, percent')
            ->from('customer_groups')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/edit_customer_group/$1') . "' class='tip' title='" . lang('edit_customer_group') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_customer_group') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_customer_group/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        //->unset_column('id');

        echo $this->datatables->generate();
    }

    
    public function getCommissionProduct()
        {
            $this->load->library('datatables');
            $this->datatables
                ->select('id, name')
                ->from('price_groups')
                ->where('type','commission')
                ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/group_commission_prices/$1') . "' class='tip' title='" . lang('commission_product') . "'><i class=\"fa fa-eye\"></i></a>  <a href='" . admin_url('system_settings/edit_price_group/$1') . "' class='tip' title='" . lang('edit_commission_product') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_commission_product') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_price_group/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
            //->unset_column('id');

            echo $this->datatables->generate();
        }
    public function getPriceGroups()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('id, name')
            ->from('price_groups')
            ->where('type','price_group')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/group_product_prices/$1') . "' class='tip' title='" . lang('group_product_prices') . "'><i class=\"fa fa-eye\"></i></a>  <a href='" . admin_url('system_settings/edit_price_group/$1') . "' class='tip' title='" . lang('edit_price_group') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_price_group') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_price_group/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        //->unset_column('id');

        echo $this->datatables->generate();
    }
    public function getMulti_buyGroups()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('id, name')
            ->from('price_groups')
            ->where('type','multi_buy')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/product_multi_buy_prices/$1') . "' class='tip' title='" . lang('group_product_prices') . "'><i class=\"fa fa-eye\"></i></a>  <a href='" . admin_url('system_settings/edit_price_group/$1') . "' class='tip' title='" . lang('edit_price_group') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_price_group') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_price_group/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        //->unset_column('id');

        echo $this->datatables->generate();
    }

    
    public function getTaxRates()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('id, name, code, rate, type')
            ->from('tax_rates')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/edit_tax_rate/$1') . "' class='tip' title='" . lang('edit_tax_rate') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_tax_rate') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_tax_rate/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        //->unset_column('id');

        echo $this->datatables->generate();
    }

    public function getUnits()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('units')}.id as id, {$this->db->dbprefix('units')}.code, {$this->db->dbprefix('units')}.name, b.name as base_unit, {$this->db->dbprefix('units')}.operator, {$this->db->dbprefix('units')}.operation_value", false)
            ->from('units')
            ->join('units b', 'b.id=units.base_unit', 'left')
            ->group_by('units.id')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/edit_unit/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_unit') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_unit') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_unit/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }

    public function getVariants()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('id, name')
            ->from('variants')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/edit_variant/$1') . "' class='tip' title='" . lang('edit_variant') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_variant') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_variant/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        //->unset_column('id');

        echo $this->datatables->generate();
    }

    public function getWarehouses()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('warehouses')}.id as id, 
                map, code, 
                {$this->db->dbprefix('warehouses')}.name as name,{$this->db->dbprefix('warehouses')}.atten_name, 
                {$this->db->dbprefix('price_groups')}.name as price_group, 
                phone,
                email, 
                address,
                saleable")
            ->from('warehouses')
            ->join('price_groups', 'price_groups.id=warehouses.price_group_id', 'left')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/edit_warehouse/$1') . "' class='tip' title='" . lang('edit_warehouse') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_warehouse') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_warehouse/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }

    public function group_commission_prices($group_id = null)
    {
        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            admin_redirect('system_settings/price_groups');
        }
        $this->data['price_group'] = $this->settings_model->getPriceGroupByID($group_id);
        $this->data['error']       = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                        = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')],  ['link' => admin_url('system_settings/group_commission_prices'), 'page' => lang('commission_product')], ['link' => '#', 'page' => lang('group_commission_prices')]];
        $meta                      = ['page_title' => lang('group_commission_prices'), 'bc' => $bc];
        $this->page_construct('settings/group_commission_prices', $meta, $this->data);
    }
    public function product_multi_buy_prices($group_id = null)
    {

        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            admin_redirect('system_settings/multi_buy_groups');
        }

        $this->data['price_group'] = $this->settings_model->getPriceGroupByID($group_id);
        $this->data['units']       = $this->site->getUnits();
        $this->data['error']       = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                        = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')],  ['link' => admin_url('system_settings/multi_buy_groups'), 'page' => lang('multi_buys')], ['link' => '#', 'page' => lang('group_product_prices')]];
        $meta                      = ['page_title' => lang('group_product_prices'), 'bc' => $bc];
        $this->page_construct('settings/group_multi_buys_prices', $meta, $this->data);
    }
    public function import_brands()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');
                $config['upload_path']   = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('system_settings/brands');
                }

                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle    = fopen('files/' . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys   = ['name', 'code', 'image'];
                $final  = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }

                foreach ($final as $csv_ct) {
                    if (!$this->settings_model->getBrandByName(trim($csv_ct['name']))) {
                        $data[] = [
                            'code'  => trim($csv_ct['code']),
                            'name'  => trim($csv_ct['name']),
                            'image' => trim($csv_ct['image']),
                        ];
                    }
                }
            }

            // $this->bpas->print_arrays($data);
        }

        if ($this->form_validation->run() == true && !empty($data) && $this->settings_model->addBrands($data)) {
            $this->session->set_flashdata('message', lang('brands_added'));
            admin_redirect('system_settings/brands');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = ['name' => 'userfile',
                'id'                          => 'userfile',
                'type'                        => 'text',
                'value'                       => $this->form_validation->set_value('userfile'),
            ];
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/import_brands', $this->data);
        }
    }

    public function import_categories_06_04_2023()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');
                $config['upload_path']   = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('system_settings/categories');
                }
                $csv       = $this->upload->file_name;
                $arrResult = [];
                $handle    = fopen('files/' . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles     = array_shift($arrResult);
                $updated    = '';
                $categories = $subcategories = [];
                
                $keys   = ['code', 'name', 'slug', 'image','parent_id', 'description'];
                $final  = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $value) {
                    $code  = trim($value['code']);
                    $name  = trim($value['name']);
                    $pcode = isset($value['parent_id']) ? trim($value['parent_id']) : null;
                    $description  = trim($value['description']);
                    if (!$this->settings_model->getCategoryByCode($code)) {
                        $data[] = [
                            'code'        => trim($value['code']),
                            'name'        => trim($value['name']),
                            'slug'        => isset($name) ? trim($name) : $code,
                            'image'       => isset($value['image']) ? trim($value['image']) : 'no_image.png',
                            'parent_id'   => $pcode,
                            'description' => isset($value['description']) ? trim($value['description']) : null,
                        ];
                        /*
                        if (!empty($pcode) && ($pcategory = $this->settings_model->getCategoryByCode($pcode))) {
                            $data['parent_id'] = $pcategory->id;
                        }
                        if ($c = $this->settings_model->getCategoryByCode($code)) {
                                $updated .= '<p>' . lang('category_updated') . ' (' . $code . ')</p>';
                                $this->settings_model->updateCategory($c->id, $data1);
                        } else {
                            if ($category['parent_id']) {
                                $subcategories[] = $category;
                            } else {
                                $categories[] = $category;
                            }
                        }
                        */
                    }
                    $rw++;
                }
               
            }

            // $this->bpas->print_arrays($categories, $subcategories);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addCategories($data, $subcategories)) {
            $this->session->set_flashdata('message', lang('categories_added') . $updated);
            admin_redirect('system_settings/categories');
        } else {
            if ((isset($categories) && empty($categories)) || (isset($subcategories) && empty($subcategories))) {
                if ($updated) {
                    $this->session->set_flashdata('message', $updated);
                } else {
                    $this->session->set_flashdata('warning', lang('category_code_x_exist'));
                }
                admin_redirect('system_settings/categories');
            }

            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = ['name' => 'userfile',
                'id'                          => 'userfile',
                'type'                        => 'text',
                'value'                       => $this->form_validation->set_value('userfile'),
            ];
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/import_categories', $this->data);
        }
    }

    public function import_categories()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');
                $config['upload_path']   = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('system_settings/categories');
                }
                $csv       = $this->upload->file_name;
                $arrResult = [];
                $handle    = fopen('files/' . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles     = array_shift($arrResult);
                $updated    = '';
                $categories = $subcategories = [];
                $keys       = ['code', 'name', 'slug', 'image','parent_id', 'description'];
                $final      = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $value) {
                    $code  = trim($value['code']);
                    $name  = trim($value['name']);
                    $pcode = isset($value['parent_id']) ? trim($value['parent_id']) : null;
                    $description  = trim($value['description']);
                    if (!$this->settings_model->getCategoryByCode($code)) {
                        $category = [
                            'code'        => trim($value['code']),
                            'name'        => trim($value['name']),
                            'slug'        => isset($name) ? trim($name) : $code,
                            'image'       => isset($value['image']) ? trim($value['image']) : 'no_image.png',
                            'parent_id'   => null,
                            'description' => isset($value['description']) ? trim($value['description']) : null,
                        ];
                        if (!empty($pcode) && ($pcategory = $this->settings_model->getCategoryByCode($pcode))) {
                            $category['parent_id'] = (int) $pcategory->id;
                        }
                        $data[] = $category;
                    }
                    $rw++;
                }
            }
            // $this->bpas->print_arrays($data, $subcategories);
        }
        if ($this->form_validation->run() == true && $this->settings_model->addCategories($data, $subcategories)) {
            $this->session->set_flashdata('message', lang('categories_added') . $updated);
            admin_redirect('system_settings/categories');
        } else {
            if ((isset($categories) && empty($categories)) || (isset($subcategories) && empty($subcategories))) {
                if ($updated) {
                    $this->session->set_flashdata('message', $updated);
                } else {
                    $this->session->set_flashdata('warning', lang('category_code_x_exist'));
                }
                admin_redirect('system_settings/categories');
            }
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = [
                'name'  => 'userfile',
                'id'    => 'userfile',
                'type'  => 'text',
                'value' => $this->form_validation->set_value('userfile'),
            ];
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/import_categories', $this->data);
        }
    }

    public function import_expense_categories()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');
                $config['upload_path']   = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('system_settings/expense_categories');
                }

                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle    = fopen('files/' . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys   = ['code', 'name'];
                $final  = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }

                foreach ($final as $csv_ct) {
                    if (!$this->settings_model->getExpenseCategoryByCode(trim($csv_ct['code']))) {
                        $data[] = [
                            'code' => trim($csv_ct['code']),
                            'name' => trim($csv_ct['name']),
                        ];
                    }
                }
            }

            // $this->bpas->print_arrays($data);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addExpenseCategories($data)) {
            $this->session->set_flashdata('message', lang('categories_added'));
            admin_redirect('system_settings/expense_categories');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = ['name' => 'userfile',
                'id'                          => 'userfile',
                'type'                        => 'text',
                'value'                       => $this->form_validation->set_value('userfile'),
            ];
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/import_expense_categories', $this->data);
        }
    }

    public function import_subcategories()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');
                $config['upload_path']   = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('system_settings/categories');
                }

                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle    = fopen('files/' . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys   = ['code', 'name', 'category_code', 'image'];
                $final  = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }

                $rw = 2;
                foreach ($final as $csv_ct) {
                    if (!$this->settings_model->getSubcategoryByCode(trim($csv_ct['code']))) {
                        if ($parent_actegory = $this->settings_model->getCategoryByCode(trim($csv_ct['category_code']))) {
                            $data[] = [
                                'code'        => trim($csv_ct['code']),
                                'name'        => trim($csv_ct['name']),
                                'image'       => trim($csv_ct['image']),
                                'category_id' => $parent_actegory->id,
                            ];
                        } else {
                            $this->session->set_flashdata('error', lang('check_category_code') . ' (' . $csv_ct['category_code'] . '). ' . lang('category_code_x_exist') . ' ' . lang('line_no') . ' ' . $rw);
                            admin_redirect('system_settings/categories');
                        }
                    }
                    $rw++;
                }
            }

            // $this->bpas->print_arrays($data);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addSubCategories($data)) {
            $this->session->set_flashdata('message', lang('subcategories_added'));
            admin_redirect('system_settings/categories');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = ['name' => 'userfile',
                'id'                          => 'userfile',
                'type'                        => 'text',
                'value'                       => $this->form_validation->set_value('userfile'),
            ];
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/import_subcategories', $this->data);
        }
    }

    public function index()
    {
        $this->load->library('gst');
        $this->form_validation->set_rules('site_name', lang('site_name'), 'trim|required');
        $this->form_validation->set_rules('dateformat', lang('dateformat'), 'trim|required');
        $this->form_validation->set_rules('timezone', lang('timezone'), 'trim|required');
        $this->form_validation->set_rules('mmode', lang('maintenance_mode'), 'trim|required');
        //$this->form_validation->set_rules('logo', lang('logo'), 'trim');
        $this->form_validation->set_rules('iwidth', lang('image_width'), 'trim|numeric|required');
        $this->form_validation->set_rules('iheight', lang('image_height'), 'trim|numeric|required');
        $this->form_validation->set_rules('twidth', lang('thumbnail_width'), 'trim|numeric|required');
        $this->form_validation->set_rules('theight', lang('thumbnail_height'), 'trim|numeric|required');
        $this->form_validation->set_rules('display_all_products', lang('display_all_products'), 'trim|numeric|required');
        $this->form_validation->set_rules('watermark', lang('watermark'), 'trim|required');
        $this->form_validation->set_rules('currency', lang('default_currency'), 'trim|required');
        $this->form_validation->set_rules('email', lang('default_email'), 'trim|required');
        $this->form_validation->set_rules('language', lang('language'), 'trim|required');
        $this->form_validation->set_rules('warehouse', lang('default_warehouse'), 'trim|required');
        $this->form_validation->set_rules('biller', lang('default_biller'), 'trim|required');
        $this->form_validation->set_rules('tax_rate', lang('product_tax'), 'trim|required');
        $this->form_validation->set_rules('tax_rate2', lang('invoice_tax'), 'trim|required');
        $this->form_validation->set_rules('sales_prefix', lang('sales_prefix'), 'trim');
        $this->form_validation->set_rules('quote_prefix', lang('quote_prefix'), 'trim');
        $this->form_validation->set_rules('purchase_prefix', lang('purchase_prefix'), 'trim');
        $this->form_validation->set_rules('transfer_prefix', lang('transfer_prefix'), 'trim');
        $this->form_validation->set_rules('delivery_prefix', lang('delivery_prefix'), 'trim');
        $this->form_validation->set_rules('payment_prefix', lang('payment_prefix'), 'trim');
        $this->form_validation->set_rules('return_prefix', lang('return_prefix'), 'trim');
        $this->form_validation->set_rules('expense_prefix', lang('expense_prefix'), 'trim');
        $this->form_validation->set_rules('detect_barcode', lang('detect_barcode'), 'trim|required');
        $this->form_validation->set_rules('theme', lang('theme'), 'trim|required');
        $this->form_validation->set_rules('rows_per_page', lang('rows_per_page'), 'trim|required');
        $this->form_validation->set_rules('accounting_method', lang('accounting_method'), 'trim|required');
        $this->form_validation->set_rules('product_serial', lang('product_serial'), 'trim|required');
        $this->form_validation->set_rules('product_discount', lang('product_discount'), 'trim|required');
        $this->form_validation->set_rules('bc_fix', lang('bc_fix'), 'trim|numeric|required');
        $this->form_validation->set_rules('protocol', lang('email_protocol'), 'trim|required');
        $this->form_validation->set_rules('default_project', lang('default_project'), 'trim|required');
        $this->form_validation->set_rules('multi_currency', lang('multi_currency'), 'trim|required');
        $this->form_validation->set_rules('alert_day', lang('alert_day'), 'trim|required');
        $this->form_validation->set_rules('warranty', lang('warranty'), 'trim|required');
        // $this->form_validation->set_rules('late_day', lang('late_day'), 'trim|required');
        // $this->form_validation->set_rules('penalty_amount', lang('penalty_amount'), 'trim|required');
        if ($this->input->post('protocol') == 'smtp') {
            $this->form_validation->set_rules('smtp_host', lang('smtp_host'), 'required');
            $this->form_validation->set_rules('smtp_user', lang('smtp_user'), 'required');
            $this->form_validation->set_rules('smtp_pass', lang('smtp_pass'), 'required');
            $this->form_validation->set_rules('smtp_port', lang('smtp_port'), 'required');
        }
        if ($this->input->post('protocol') == 'sendmail') {
            $this->form_validation->set_rules('mailpath', lang('mailpath'), 'required');
        }
        $this->form_validation->set_rules('decimals', lang('decimals'), 'trim|required');
        $this->form_validation->set_rules('decimals_sep', lang('decimals_sep'), 'trim|required');
        $this->form_validation->set_rules('thousands_sep', lang('thousands_sep'), 'trim|required');
        if ($this->Settings->indian_gst) {
            $this->form_validation->set_rules('state', lang('state'), 'trim|required');
        }

        if ($this->form_validation->run() == true) {
            $language = $this->input->post('language');

            if ((file_exists(APPPATH . 'language' . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'bpas_lang.php') && is_dir(APPPATH . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . $language)) || $language == 'english') {
                $lang = $language;
            } else {
                $this->session->set_flashdata('error', lang('language_x_found'));
                admin_redirect('system_settings');
                $lang = 'english';
            }

            $tax1 = ($this->input->post('tax_rate') != 0) ? 1 : 0;
            $tax2 = ($this->input->post('tax_rate2') != 0) ? 1 : 0;
          
            $data = [
                'license_name'                      => $this->input->post('license_name'),
                'license_key'                       => $this->input->post('license_key'),
                'site_name'                         => DEMO ? 'SBC Solutions' : $this->input->post('site_name'),
                'rows_per_page'                     => $this->input->post('rows_per_page'),
                'dateformat'                        => $this->input->post('dateformat'),
                'timezone'                          => DEMO ? 'Asia/Phnom_Penh' : $this->input->post('timezone'),
                'mmode'                             => trim($this->input->post('mmode')),
                'iwidth'                            => $this->input->post('iwidth'),
                'iheight'                           => $this->input->post('iheight'),
                'twidth'                            => $this->input->post('twidth'),
                'theight'                           => $this->input->post('theight'),
                'watermark'                         => $this->input->post('watermark'),
                'alert_day'                         => $this->input->post('alert_day'),
                'accounting_method'                 => $this->input->post('accounting_method'),
                'default_email'                     => DEMO ? 'noreply@sbcsolution.biz' : $this->input->post('email'),
                'language'                          => $lang,
                'default_warehouse'                 => $this->input->post('warehouse'),
                'default_tax_rate'                  => $this->input->post('tax_rate'),
                'default_tax_rate2'                 => $this->input->post('tax_rate2'),
                'sales_prefix'                      => $this->input->post('sales_prefix'),
                'quote_prefix'                      => $this->input->post('quote_prefix'),
                'purchase_prefix'                   => $this->input->post('purchase_prefix'),
                'transfer_prefix'                   => $this->input->post('transfer_prefix'),
                'delivery_prefix'                   => $this->input->post('delivery_prefix'),
                'payment_prefix'                    => $this->input->post('payment_prefix'),
                'ppayment_prefix'                   => $this->input->post('ppayment_prefix'),
                'qa_prefix'                         => $this->input->post('qa_prefix'),
                'return_prefix'                     => $this->input->post('return_prefix'),
                'returnp_prefix'                    => $this->input->post('returnp_prefix'),
                'expense_prefix'                    => $this->input->post('expense_prefix'),
                'auto_detect_barcode'               => trim($this->input->post('detect_barcode')),
                'theme'                             => trim($this->input->post('theme')),
                'product_serial'                    => $this->input->post('product_serial'),
                'customer_group'                    => $this->input->post('customer_group'),
                'product_expiry'                    => $this->input->post('product_expiry'),
                'product_discount'                  => $this->input->post('product_discount'),
                'default_currency'                  => $this->input->post('currency'),
                'bc_fix'                            => $this->input->post('bc_fix'),
                'tax1'                              => $tax1,
                'tax2'                              => $tax2,
                'overselling'                       => $this->input->post('restrict_sale'),
                'reference_format'                  => $this->input->post('reference_format'),
                'reference_reset'                   => $this->input->post('reference_reset'),
                'invoice_discount_formate'          => $this->input->post('invoice_discount_formate'),
                'racks'                             => $this->input->post('racks'),
                'attributes'                        => $this->input->post('attributes'),
                'restrict_calendar'                 => $this->input->post('restrict_calendar'),
                'captcha'                           => $this->input->post('captcha'),
                'item_addition'                     => $this->input->post('item_addition'),
                'protocol'                          => DEMO ? 'mail' : $this->input->post('protocol'),
                'mailpath'                          => $this->input->post('mailpath'),
                'smtp_host'                         => $this->input->post('smtp_host'),
                'smtp_user'                         => $this->input->post('smtp_user'),
                'smtp_port'                         => $this->input->post('smtp_port'),
                'smtp_crypto'                       => $this->input->post('smtp_crypto') ? $this->input->post('smtp_crypto') : null,
                'decimals'                          => $this->input->post('decimals'),
                'decimals_sep'                      => $this->input->post('decimals_sep'),
                'thousands_sep'                     => $this->input->post('thousands_sep'),
                'default_biller'                    => $this->input->post('biller'),
                'invoice_view'                      => $this->input->post('invoice_view'),
                'each_spent'                        => $this->input->post('each_spent') ? $this->input->post('each_spent') : null,
                'ca_point'                          => $this->input->post('ca_point') ? $this->input->post('ca_point') : null,
                'each_sale'                         => $this->input->post('each_sale') ? $this->input->post('each_sale') : null,
                'sa_point'                          => $this->input->post('sa_point') ? $this->input->post('sa_point') : null,
                'sac'                               => $this->input->post('sac'),
                'qty_decimals'                      => $this->input->post('qty_decimals'),
                'display_all_products'              => $this->input->post('display_all_products'),
                'display_symbol'                    => $this->input->post('display_symbol'),
                'symbol'                            => $this->input->post('symbol'),
                'remove_expired'                    => $this->input->post('remove_expired'),
                'barcode_separator'                 => $this->input->post('barcode_separator'),
                'set_focus'                         => $this->input->post('set_focus'),
                'disable_editing'                   => $this->input->post('disable_editing'),
                'price_group'                       => $this->input->post('price_group'),
                'barcode_img'                       => $this->input->post('barcode_renderer'),
                'update_cost'                       => $this->input->post('update_cost'),
                'avc_costing'                       => $this->input->post('avg_cost'),
                'apis'                              => $this->input->post('apis'),
                'pdf_lib'                           => $this->input->post('pdf_lib'),
                'state'                             => $this->input->post('state'),
                'hide'                              => $this->input->post('hide'),
                'enable_telegram'                   => $this->input->post('enable_telegram'),
                'disable_price'                     => $this->input->post('hide_price'),
                'ui'                                => $this->input->post('theme_ui'),
                'accounting'                        => $this->input->post('accounting'),
                'stok_sale_order'                   => $this->input->post('stok_sale_order'),
                'profit_loss_method'                => $this->input->post('profit_loss_method'),
                'fefo'                              => $this->input->post('fefo'),
                'combo_price_match'                 => $this->input->post('combo_price_match'),
                'show_payroll_atttendancence'       => $this->input->post('payroll_atttendance'),
                'default_project'                   => $this->input->post('default_project'),
                'default_supplier'                  => $this->input->post('supplier'),
                'developed_by'                      => $this->input->post('developed_by'),
                'customer_detail'                   => $this->input->post('customer_detail'),
                'product_option'                    => $this->input->post('product_option'),
                'multi_currency'                    => $this->input->post('multi_currency'),
                'payment_term'                      => $this->input->post('payment_term'),
                'warranty'                          => $this->input->post('warranty'),
                'zone'                              => $this->input->post('zone'),
                'allow_change_date'                 => $this->input->post('allow_change_date'),
                'tax_calculate'                     => $this->input->post('tax_calculate'),
                'separate_code'                     => $this->input->post('separate_code'),
                'show_code'                         => $this->input->post('show_code'),
                'auto_print'                        => trim($this->input->post('auto_print')),
                'cost_sale_commission'              => $this->input->post('cost_sale_commission'),
                'sale_order_prefix'                 => $this->input->post('sale_order_prefix'),
                'purchase_order_prefix'             => $this->input->post('purchase_order_prefix'),
                'purchase_request_prefix'           => $this->input->post('purchase_request_prefix'),
                'journal_prefix'                    => $this->input->post('journal_prefix'),
                'convert_prefix'                    => $this->input->post('convert_prefix'),
                'project_code_prefix'               => $this->input->post('project_code_prefix'),
                'sales_order_prefix'                => $this->input->post('sales_order_prefix'),
                'employee_code_prefix'              => $this->input->post('employee_code_prefix'),
                'late_day'                          => $this->input->post('late_day'),
                'penalty_amount'                    => $this->input->post('penalty_amount'),
                'select_price'                      => $this->input->post('select_price'),
                'product_combo'                     => $this->input->post('product_combo'),
                'date_with_time'                    => $this->input->post('date_with_time'),
                'limit_print'                       => $this->input->post('limit_print'),
                'show_unit'                         => $this->input->post('show_unit'),
                'show_qoh'                          => $this->input->post('show_qoh'),
                'auto_count'                        => $this->input->post('auto_count'),
                'discount_option'                   => $this->input->post('discount_option'),
                'comment_option'                    => $this->input->post('comment_option'),
                'reason_option'                     => $this->input->post('reason_option'),
                'stock_received'                    => $this->input->post('stock_received'),
                'sav_prefix'                        => $this->input->post('sav_prefix'),
                'sav_tr_prefix'                     => $this->input->post('sav_tr_prefix'),
                'edit_sale_request_prefix'          => $this->input->post('edit_sale_request_prefix'),
                'loan_prefix'                       => $this->input->post('loan_prefix'),
                'app_prefix'                        => $this->input->post('app_prefix'),
                'pawn_prefix'                       => $this->input->post('pawn_prefix'),
                'installment_prefix'                => $this->input->post('installment_prefix'),
                'store_sales'                       => $this->input->post('store_sales'),
                'loan_alert_days'                   => $this->input->post('loan_alert_days'),
                'installment_alert_days'            => $this->input->post('installment_alert_days'),
                'installment_late_days'             => $this->input->post('installment_late_days'),
                'installment_holiday'               => $this->input->post('installment_holiday'),
                'installment_penalty_option'        => $this->input->post('installment_penalty_option'),
                'customer_group_discount'           => $this->input->post('customer_group_discount'),
                'expiry_alert_days'                 => $this->input->post('expiry_alert_days')?$this->input->post('expiry_alert_days') : NULL,
                'expiry_alert_by'                   => $this->input->post('expiry_alert_by'),
              
                'product_commission'                => $this->input->post('product_commission'),
                'payment_after_delivery'            => $this->input->post('payment_after_delivery'),
                'search_custom_field'               => $this->input->post('search_custom_field'),
                'seperate_product_by_biller'        => $this->input->post('seperate_product_by_biller'),
                'multiple_code_unit'                => $this->input->post('multiple_code_unit'),
                'cbm'                               => $this->input->post('cbm'),
                'student_prefix'                    => $this->input->post('student_prefix'),
                'payment_expense'                   => $this->input->post('payment_expense'),
                'using_roster'                      => $this->input->post('using_roster'),
                'roster_from_day'                   => $this->input->post('roster_from_day'),
                'seniority_pay'                     => $this->input->post('seniority_pay'),
                'severance_pay'                     => $this->input->post('severance_pay'),
                'scan_per_shift'                    => $this->input->post('scan_per_shift'),
                'take_leave_prefix'                 => $this->input->post('take_leave_prefix'),
                'show_item_combo'                   => $this->input->post('show_item_combo'),
                'show_warehouse_qty'                => $this->input->post('show_warehouse_qty'),
                'using_weight'                      => $this->input->post('using_weight'),
                'expense_budget'                    => $this->input->post('expense_budget'),
                'each_qty'                          => $this->input->post('each_qty') ? $this->input->post('each_qty') : null,
                'qca_point'                          => $this->input->post('qca_point') ? $this->input->post('qca_point') : null,
                'apoint_option'                          => $this->input->post('apoint_option') ? $this->input->post('apoint_option') : null,
            ];
            if ($this->input->post('smtp_pass')) {
                $data['smtp_pass'] = $this->input->post('smtp_pass');
            }
            if($this->Settings->module_concrete){
                $data["moving_waitings"] = $this->input->post('moving_waitings');
                $data["missions"]       = $this->input->post('missions');
                $data["fuel_expenses"]  = $this->input->post('fuel_expenses');
                $data["errors"]         = $this->input->post('errors');
                $data["absents"]        = $this->input->post('absents');
            }
            if($this->Settings->module_school){
                $data["default_program"] = $this->input->post('default_program');
                $data["auto_invoice"] = $this->input->post('auto_invoice');
                //$data["testing_fee"] = $this->input->post('testing_fee');
                //$data["sticket_prefix"] = $this->input->post('sticket_prefix');
            }
            if($this->Settings->module_fuel){
                $data["cfuel_prefix"]        = $this->input->post('cfuel_prefix');
                $data["csale_prefix"]        = $this->input->post('csale_prefix');
            }
        }
        // $this->bpas->print_arrays($data);
        if ($this->form_validation->run() == true && $this->settings_model->updateSetting($data)) {
            if (!DEMO && TIMEZONE != $data['timezone']) {
                if (!$this->write_index($data['timezone'])) {
                    $this->session->set_flashdata('error', lang('setting_updated_timezone_failed'));
                    admin_redirect('system_settings');
                }
            }
            $this->session->set_flashdata('message', lang('setting_updated'));
            admin_redirect('system_settings');
        } else {
            if($this->Settings->module_school){
                $this->data['programs'] = $this->settings_model->getPrograms();
            }
            $this->data['error']           = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['billers']         = $this->site->getAllCompanies('biller');
            $this->data['suppliers']       = $this->site->getAllCompanies('supplier');
            $this->data['projects']        = $this->site->getAllProject();
            $this->data['settings']        = $this->settings_model->getSettings();
            $this->data['currencies']      = $this->settings_model->getAllCurrencies();
            $this->data['date_formats']    = $this->settings_model->getDateFormats();
            $this->data['tax_rates']       = $this->settings_model->getAllTaxRates();
            $this->data['customer_groups'] = $this->settings_model->getAllCustomerGroups();
            $this->data['price_groups']    = $this->settings_model->getAllPriceGroups();
            $this->data['warehouses']      = $this->settings_model->getAllWarehouses();
            $bc                            = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('system_settings')]];
            $meta                          = ['page_title' => lang('system_settings'), 'bc' => $bc];
            $this->page_construct('settings/index', $meta, $this->data);
        }
    }

    public function modules()
    {
        $this->form_validation->set_rules('user_name', lang('user_name'), 'trim|required');
        $this->form_validation->set_rules('license_key', lang('license_key'), 'trim|required');
        $this->form_validation->set_rules('multi_warehouse', lang('multi_warehouse'), 'trim|required');
        $this->form_validation->set_rules('multi_biller', lang('multi_biller'), 'trim|required');
        if ($this->form_validation->run() == true) {
            $user_name      = $this->input->post('user_name');
            $license_key    = $this->input->post('license_key');
            if ($user_name !='sbcsolution' && $license_key !='Sbc@key4578') {
                $this->session->set_flashdata('error', lang('please_enter_valid_license_key'));
                admin_redirect('system_settings/modules');
            } else {
       
                $data = [
                    'module_inventory'      => $this->input->post('module_inventory')?$this->input->post('module_inventory'):0,
                    'module_purchase'       => $this->input->post('module_purchase')?$this->input->post('module_purchase'):0,
                    'module_sale'           => $this->input->post('module_sale')?$this->input->post('module_sale'):0,
                    'pos'                   => $this->input->post('pos')?$this->input->post('pos'):0,
                    'project'               => $this->input->post('project')?$this->input->post('project'):0,            
                    'module_manufacturing'  => $this->input->post('module_manufacturing')?$this->input->post('module_manufacturing'):0,
                    'module_account'        => $this->input->post('module_account')?$this->input->post('module_account'):0,
                    'module_hr'             => $this->input->post('module_hr')?$this->input->post('module_hr'):0,
                    'payroll'               => $this->input->post('payroll')?$this->input->post('payroll'):0,
                    'attendance'            => $this->input->post('attendance')?$this->input->post('attendance'):0,
                    'module_crm'            => $this->input->post('module_crm')?$this->input->post('module_crm'):0,
                    'module_property'       => $this->input->post('module_property')?$this->input->post('module_property'):0,
                    'module_clinic'         => $this->input->post('module_clinic')?$this->input->post('module_clinic'):0,
                    'module_school'         => $this->input->post('module_school')?$this->input->post('module_school'):0,
                    'module_email'          => $this->input->post('module_email')?$this->input->post('module_email'):0,
                    'module_loan'           => $this->input->post('module_loan')?$this->input->post('module_loan'):0,
                    'module_pawn'           => $this->input->post('module_pawn')?$this->input->post('module_pawn'):0,
                    'module_save'           => $this->input->post('module_save')?$this->input->post('module_save'):0,
                    'shop'                  => $this->input->post('shop')?$this->input->post('shop'):0,
                    'module_asset'          => $this->input->post('module_asset')?$this->input->post('module_asset'):0,
                    'module_hotel_apartment'=> $this->input->post('module_hotel_apartment')?$this->input->post('module_hotel_apartment'):0,
                    'module_express'        => $this->input->post('module_express')?$this->input->post('module_express'):0,
                    'module_installment'    => $this->input->post('module_installment')?$this->input->post('module_installment'):0,
                    'module_gym'            => $this->input->post('module_gym')?$this->input->post('module_gym'):0,
                    'module_tax'            => $this->input->post('module_tax')?$this->input->post('module_tax'):0,
                    'module_repair'         => $this->input->post('module_repair')?$this->input->post('module_repair'):0,
                    'module_rental'         => $this->input->post('module_rental')?$this->input->post('module_rental'):0,
                    'module_concrete'       => $this->input->post('module_concrete')?$this->input->post('module_concrete'):0,
                    'module_truckings'      => $this->input->post('module_truckings')?$this->input->post('module_truckings'):0,
                    'module_clearance'      => $this->input->post('module_clearance')?$this->input->post('module_clearance'):0,
                    'module_fuel'      => $this->input->post('module_fuel')?$this->input->post('module_fuel'):0,
                    'module_e_ticket'      => $this->input->post('e_ticket')?$this->input->post('e_ticket'):0,
                    'module_expense'      => $this->input->post('module_expense')?$this->input->post('module_expense'):0,
                    //-----------
                    'multi_level'           => $this->input->post('multi_level')?$this->input->post('multi_level'):0,
                    'multi_warehouse'       => $this->input->post('multi_warehouse')?$this->input->post('multi_warehouse'):0,
                    'multi_biller'          => $this->input->post('multi_biller')?$this->input->post('multi_biller'):0,
                    'stock_using'           => $this->input->post('stock_using')?$this->input->post('stock_using'):0,
                    'quotation'             => $this->input->post('quotation')?$this->input->post('quotation'):0,
                    'sale_order'            => $this->input->post('sale_order')?$this->input->post('sale_order'):0,
                    'purchase_request'      => $this->input->post('purchase_request')?$this->input->post('purchase_request'):0,
                    'purchase_order'        => $this->input->post('purchase_order')?$this->input->post('purchase_order'):0,
                    'expense_budget'        => $this->input->post('expense_budget')?$this->input->post('expense_budget'):0,
                    'sale_man'              => $this->input->post('sale_man')?$this->input->post('sale_man'):0,
    				'driver'                => $this->input->post('driver')?$this->input->post('driver'):0,
                    'commission'            => $this->input->post('commission')?$this->input->post('commission'):0,
                    'maintenance'           => $this->input->post('maintenance')?$this->input->post('maintenance'):0,
                    'school_level'          => $this->input->post('school_level')?$this->input->post('school_level'):0,
                    'reward_exchange'       => $this->input->post('reward_exchange')?$this->input->post('reward_exchange'):0,
                    'delivery'              => $this->input->post('delivery')?$this->input->post('delivery'):0,
                    'sale_consignment'      => $this->input->post('sale_consignment'),
                    'monthly_auto_invoice'  => $this->input->post('monthly_auto_invoice'),
                    
                ];
            }
        }
        if ($this->form_validation->run() == true && $this->settings_model->updateSetting($data)) {
            $this->session->set_flashdata('message', lang('setting_updated'));
            admin_redirect('system_settings/modules');
        } else {
            $this->data['error']           = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['billers']         = $this->site->getAllCompanies('biller');
            $this->data['settings']        = $this->settings_model->getSettings();
            $this->data['currencies']      = $this->settings_model->getAllCurrencies();
            $this->data['date_formats']    = $this->settings_model->getDateFormats();
            $this->data['tax_rates']       = $this->settings_model->getAllTaxRates();
            $this->data['customer_groups'] = $this->settings_model->getAllCustomerGroups();
            $this->data['price_groups']    = $this->settings_model->getAllPriceGroups();
            $this->data['warehouses']      = $this->settings_model->getAllWarehouses();
            $this->data['modules']          = $this->site->getAllModules();
            $bc                            = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('system_settings')]];
            $meta                          = ['page_title' => lang('system_settings'), 'bc' => $bc];
            $this->page_construct('settings/modules', $meta, $this->data);
        }
    }

    function setup_menu(){
    }
    public function install_update($file, $m_version, $version)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $this->load->helper('update');
        save_remote_file($file . '.zip');
        $this->bpas->unzip('./files/updates/' . $file . '.zip');
        if ($m_version) {
            $this->load->library('migration');
            if (!$this->migration->latest()) {
                $this->session->set_flashdata('error', $this->migration->error_string());
                admin_redirect('system_settings/updates');
            }
        }
        $this->db->update('settings', ['version' => $version, 'update' => 0], ['setting_id' => 1]);
        unlink('./files/updates/' . $file . '.zip');
        $this->session->set_flashdata('success', lang('update_done'));
        admin_redirect('system_settings/updates');
    }

    public function paypal()
    {
        $this->form_validation->set_rules('active', $this->lang->line('activate'), 'trim');
        $this->form_validation->set_rules('account_email', $this->lang->line('paypal_account_email'), 'trim|valid_email');
        if ($this->input->post('active')) {
            $this->form_validation->set_rules('account_email', $this->lang->line('paypal_account_email'), 'required');
        }
        $this->form_validation->set_rules('fixed_charges', $this->lang->line('fixed_charges'), 'trim');
        $this->form_validation->set_rules('extra_charges_my', $this->lang->line('extra_charges_my'), 'trim');
        $this->form_validation->set_rules('extra_charges_other', $this->lang->line('extra_charges_others'), 'trim');

        if ($this->form_validation->run() == true) {
            $data = ['active'         => $this->input->post('active'),
                'account_email'       => $this->input->post('account_email'),
                'fixed_charges'       => $this->input->post('fixed_charges'),
                'extra_charges_my'    => $this->input->post('extra_charges_my'),
                'extra_charges_other' => $this->input->post('extra_charges_other'),
            ];
        }

        if ($this->form_validation->run() == true && $this->settings_model->updatePaypal($data)) {
            $this->session->set_flashdata('message', $this->lang->line('paypal_setting_updated'));
            admin_redirect('system_settings/paypal');
        } else {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

            $this->data['paypal'] = $this->settings_model->getPaypalSettings();

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('paypal_settings')]];
            $meta = ['page_title' => lang('paypal_settings'), 'bc' => $bc];
            $this->page_construct('settings/paypal', $meta, $this->data);
        }
    }

    public function permissions($id = null)
    {
        $this->form_validation->set_rules('group', lang('group'), 'is_natural_no_zero');
        if ($this->form_validation->run() == true) {
            $data = [
                'products-index'             => $this->input->post('products-index'),
                'products-edit'              => $this->input->post('products-edit'),
                'products-add'               => $this->input->post('products-add'),
                'products-delete'            => $this->input->post('products-delete'),
                'products-cost'              => $this->input->post('products-cost'),
                'products-price'             => $this->input->post('products-price'),
                'products-import'            => $this->input->post('products-import'),
                'products-export'            => $this->input->post('products-export'),
                'products-adjustments'       => $this->input->post('products-adjustments'),
                'products-barcode'           => $this->input->post('products-barcode'),
                'products-stock_count'       => $this->input->post('products-stock_count'),
                'products-making'            => $this->input->post('products-making'),
                'products-update_cost_and_price' => $this->input->post('products-update_cost_and_price'),

                'customers-index'            => $this->input->post('customers-index'),
                'customers-edit'             => $this->input->post('customers-edit'),
                'customers-add'              => $this->input->post('customers-add'),
                'customers-delete'           => $this->input->post('customers-delete'),
                'customers-import'           => $this->input->post('customers-import'),
                'customers-export'           => $this->input->post('customers-export'),
                'customers-deposits'         => $this->input->post('customers-deposits'),
                'customers-delete_deposit'   => $this->input->post('customers-delete_deposit'),

                'suppliers-index'            => $this->input->post('suppliers-index'),
                'suppliers-edit'             => $this->input->post('suppliers-edit'),
                'suppliers-add'              => $this->input->post('suppliers-add'),
                'suppliers-delete'           => $this->input->post('suppliers-delete'),
                'suppliers-import'           => $this->input->post('suppliers-import'),
                'suppliers-export'           => $this->input->post('suppliers-export'),
                'suppliers-deposits'         => $this->input->post('suppliers-deposits'),
                'suppliers-delete_deposit'   => $this->input->post('suppliers-delete_deposit'),

                'projects-index'             => $this->input->post('projects-index'),
                'projects-edit'              => $this->input->post('projects-edit'),
                'projects-add'               => $this->input->post('projects-add'),
                'projects-delete'            => $this->input->post('projects-delete'),
                'projects-import'            => $this->input->post('projects-import'),
                'projects-export'            => $this->input->post('projects-export'),

                'sales-index'                => $this->input->post('sales-index'),
                'sales-edit'                 => $this->input->post('sales-edit'),
                'sales-add'                  => $this->input->post('sales-add'),
                'sales-delete'               => $this->input->post('sales-delete'),
                'sales-import'               => $this->input->post('sales-import'),
                'sales-export'               => $this->input->post('sales-export'),
                'sales-email'                => $this->input->post('sales-email'),
                'sales-pdf'                  => $this->input->post('sales-pdf'),
                'sales-deliveries'           => $this->input->post('sales-deliveries'),
                'sales-edit_delivery'        => $this->input->post('sales-edit_delivery'),
                'sales-add_delivery'         => $this->input->post('sales-add_delivery'),
                'sales-delete_delivery'      => $this->input->post('sales-delete_delivery'),
                'sales-import_delivery'      => $this->input->post('sales-import_delivery'),
                'sales-export_delivery'      => $this->input->post('sales-export_delivery'),
                'sales-email_delivery'       => $this->input->post('sales-email_delivery'),
                'sales-pdf_delivery'         => $this->input->post('sales-pdf_delivery'),
                'sales-gift_cards'           => $this->input->post('sales-gift_cards'),
                'sales-edit_gift_card'       => $this->input->post('sales-edit_gift_card'),
                'sales-add_gift_card'        => $this->input->post('sales-add_gift_card'),
                'sales-delete_gift_card'     => $this->input->post('sales-delete_gift_card'),
                'sales-import_gift_card'     => $this->input->post('sales-import_gift_card'),
                'sales-export_gift_card'     => $this->input->post('sales-export_gift_card'),
                'sales-return_sales'         => $this->input->post('sales-return_sales'),
                'sales-payments'             => $this->input->post('sales-payments'),
                'sales-credit_note'          => $this->input->post('sales-credit_note'),

                'quotes-index'               => $this->input->post('quotes-index'),
                'quotes-edit'                => $this->input->post('quotes-edit'),
                'quotes-add'                 => $this->input->post('quotes-add'),
                'quotes-delete'              => $this->input->post('quotes-delete'),
                'quotes-email'               => $this->input->post('quotes-email'),
                'quotes-pdf'                 => $this->input->post('quotes-pdf'),
                'quotes-import'              => $this->input->post('quotes-import'),
                'quotes-export'              => $this->input->post('quotes-export'),

                'purchases-index'            => $this->input->post('purchases-index'),
                'purchases-edit'             => $this->input->post('purchases-edit'),
                'purchases-add'              => $this->input->post('purchases-add'),
                'purchases-delete'           => $this->input->post('purchases-delete'),
                'purchases-email'            => $this->input->post('purchases-email'),
                'purchases-pdf'              => $this->input->post('purchases-pdf'),
                'purchases-import'           => $this->input->post('purchases-import'),
                'purchases-export'           => $this->input->post('purchases-export'),
                'purchases-payments'         => $this->input->post('purchases-payments'),
                'purchases-expenses_budget'  => $this->input->post('purchases-expenses_budget'),
                'purchases-expenses'         => $this->input->post('purchases-expenses'),
                'purchases-budgets'          => $this->input->post('purchases-budgets'),
                'purchases-return_purchases' => $this->input->post('purchases-return_purchases'),
                'purchases-payments_requested' => $this->input->post('purchases-payments_requested'),
                
                'stock_received-index'       => $this->input->post('stock_received-index'),
                'stock_received-edit'        => $this->input->post('stock_received-edit'),
                'stock_received-add'         => $this->input->post('stock_received-add'),
                'stock_received-delete'      => $this->input->post('stock_received-delete'),
                'stock_received-email'       => $this->input->post('stock_received-email'),
                'stock_received-pdf'         => $this->input->post('stock_received-pdf'),
                'stock_received-import'      => $this->input->post('stock_received-import'),
                'stock_received-export'      => $this->input->post('stock_received-export'),

                'reward_exchange-index'      => $this->input->post('reward_exchange-index'),
                'reward_exchange-edit'       => $this->input->post('reward_exchange-edit'),
                'reward_exchange-add'        => $this->input->post('reward_exchange-add'),
                'reward_exchange-delete'     => $this->input->post('reward_exchange-delete'),
                'reward_exchange-email'      => $this->input->post('reward_exchange-email'),
                'reward_exchange-pdf'        => $this->input->post('reward_exchange-pdf'),
                'reward_exchange-import'     => $this->input->post('reward_exchange-import'),
                'reward_exchange-export'     => $this->input->post('reward_exchange-export'),

                'transfers-index'            => $this->input->post('transfers-index'),
                'transfers-edit'             => $this->input->post('transfers-edit'),
                'transfers-add'              => $this->input->post('transfers-add'),
                'transfers-delete'           => $this->input->post('transfers-delete'),
                'transfers-email'            => $this->input->post('transfers-email'),
                'transfers-pdf'              => $this->input->post('transfers-pdf'),
                'transfers-import'           => $this->input->post('transfers-import'),
                'transfers-export'           => $this->input->post('transfers-export'),
                'transfers-approved'         => $this->input->post('transfers-approved'),

                'reports-index'              => $this->input->post('reports-index'),
                'reports-quantity_alerts'    => $this->input->post('reports-quantity_alerts'),
                'reports-expiry_alerts'      => $this->input->post('reports-expiry_alerts'),
                'reports-products'           => $this->input->post('reports-products'),
                'reports-daily_sales'        => $this->input->post('reports-daily_sales'),
                'reports-monthly_sales'      => $this->input->post('reports-monthly_sales'),
                'reports-payments'           => $this->input->post('reports-payments'),
                'reports-sale_targets'       => $this->input->post('reports-sale_targets'),
                'reports-sales'              => $this->input->post('reports-sales'),
                'reports-purchases'          => $this->input->post('reports-purchases'),
                'reports-stock_received'     => $this->input->post('reports-stock_received'),
                'reports-customers'          => $this->input->post('reports-customers'),
                'reports-suppliers'          => $this->input->post('reports-suppliers'),
                'reports-salemans'           => $this->input->post('reports-salemans'),
                'reports-expenses'           => $this->input->post('reports-expenses'),
                'reports-budgets'            => $this->input->post('reports-budgets'),
                'reports-expenses_budget'    => $this->input->post('reports-expenses_budget'),
                'reports-daily_purchases'    => $this->input->post('reports-daily_purchases'),
                'reports-monthly_purchases'  => $this->input->post('reports-monthly_purchases'),
                'reports-stock_in_out'       => $this->input->post('reports-stock_in_out'),
                'reports-store_sales'        => $this->input->post('reports-store_sales'),
                'reports-reward_exchange'    => $this->input->post('reports-reward_exchange'),

                'bulk_actions'               => $this->input->post('bulk_actions'),
                'edit_price'                 => $this->input->post('edit_price'),
                'change_date'                => $this->input->post('change_date'),

                'returns-index'              => $this->input->post('returns-index'),
                'returns-edit'               => $this->input->post('returns-edit'),
                'returns-add'                => $this->input->post('returns-add'),
                'returns-delete'             => $this->input->post('returns-delete'),
                'returns-email'              => $this->input->post('returns-email'),
                'returns-pdf'                => $this->input->post('returns-pdf'),
                'reports-tax'                => $this->input->post('reports-tax'),
                'returns-import'             => $this->input->post('returns-import'),
                'returns-export'             => $this->input->post('returns-export'),

                'purchases_request-index'    => $this->input->post('purchases_request-index'),
                'purchases_request-edit'     => $this->input->post('purchases_request-edit'),
                'purchases_request-add'      => $this->input->post('purchases_request-add'),
                'purchases_request-delete'   => $this->input->post('purchases_request-delete'),
                'purchase_request-import'    => $this->input->post('purchase_request-import'),
                'purchase_request-export'    => $this->input->post('purchase_request-export'),
                'purchases_request-email'    => $this->input->post('purchases_request-email'),
                'purchases_request-pdf'      => $this->input->post('purchases_request-pdf'),
                'purchases_request-approved' => $this->input->post('purchases_request-approved'),
                'purchases_request-rejected' => $this->input->post('purchases_request-rejected'),

                'purchases_order-index'      => $this->input->post('purchases_order-index'),
                'purchases_order-edit'       => $this->input->post('purchases_order-edit'),
                'purchases_order-add'        => $this->input->post('purchases_order-add'),
                'purchases_order-delete'     => $this->input->post('purchases_order-delete'),
                'purchases_order-import'     => $this->input->post('purchases_order-import'),
                'purchases_order-export'     => $this->input->post('purchases_order-export'),
                'purchases_order-email'      => $this->input->post('purchases_order-email'),
                'purchases_order-pdf'        => $this->input->post('purchases_order-pdf'),
                'purchases_order-approved'   => $this->input->post('purchases_order-approved'),
                'purchases_order-rejected'   => $this->input->post('purchases_order-rejected'),

                'sales_order-index'          => $this->input->post('sales_order-index'),
                'sales_order-edit'           => $this->input->post('sales_order-edit'),
                'sales_order-add'            => $this->input->post('sales_order-add'),
                'sales_order-delete'         => $this->input->post('sales_order-delete'),
                'sales_order-approved'       => $this->input->post('sales_order-approved'),
                'sales_order-rejected'       => $this->input->post('sales_order-rejected'),
                'sale_order-import'          => $this->input->post('sale_order-import'),
                'sale_order-export'          => $this->input->post('sale_order-export'),

                'accounts-index'             => $this->input->post('accounts-index'),
                'accounts-add'               => $this->input->post('accounts-add'),
                'accounts-edit'              => $this->input->post('accounts-edit'),
                'accounts-delete'            => $this->input->post('accounts-delete'),
                'accounts-import'            => $this->input->post('accounts-import'),
                'accounts-export'            => $this->input->post('accounts-export'),
                'account-list_receivable'    => $this->input->post('account-list_receivable'),
                'account-list_ar_aging'      => $this->input->post('account-list_ar_aging'),
                'account-ar_by_customer'     => $this->input->post('account-ar_by_customer'),
                'account-bill_receipt' => $this->input->post('account-bill_receipt'),
                'account-list_payable' => $this->input->post('account-list_payable'),
                'account-list_ap_aging' => $this->input->post('account-list_ap_aging'),
                'account-ap_by_supplier' => $this->input->post('account-ap_by_supplier'),
                'account-bill_payable' => $this->input->post('account-bill_payable'),
                'account-list_ac_head' => $this->input->post('account-list_ac_head'),
                'account-add_ac_head' => $this->input->post('account-add_ac_head'),
                'account-list_customer_deposit' => $this->input->post('account-list_customer_deposit'),
                'account-add_customer_deposit' => $this->input->post('account-add_customer_deposit'),
                'account-list_supplier_deposit' => $this->input->post('account-list_supplier_deposit'),
                'account-add_supplier_deposit' => $this->input->post('account-add_supplier_deposit'),
                'bank_reconcile' => $this->input->post('bank_reconcile'),
                'account_setting' => $this->input->post('account_setting'),
                'account_report-index' => $this->input->post('account_report-index'),
                'account_report-ledger' => $this->input->post('account_report-ledger'),
                'account_report-trail_balance' => $this->input->post('account_report-trail_balance'),
                'account_report-balance_sheet' => $this->input->post('account_report-balance_sheet'),
                'account_report-income_statement' => $this->input->post('account_report-income_statement'),
                'account_report-cash_book' => $this->input->post('account_report-cash_book'),
                'account_report-payment' => $this->input->post('account_report-payment'),
                'account_report-income_statement_detail' => $this->input->post('account_report-income_statement_detail'),
                'reports-cashflow' => $this->input->post('reports-cashflow'),
                'account_report-payments_received' => $this->input->post('account_report-payments_received'),
                'account_report-payments_voucher' => $this->input->post('account_report-payments_voucher'),

                'users-index' => $this->input->post('users-index'),
                'users-edit' => $this->input->post('users-edit'),
                'users-add' => $this->input->post('users-add'),
                'users-delete' => $this->input->post('users-delete'),
                'users-import' => $this->input->post('users-import'),
                'users-export' => $this->input->post('users-export'),
                
                'drivers-index' => $this->input->post('drivers-index'),
                'drivers-edit' => $this->input->post('drivers-edit'),
                'drivers-add' => $this->input->post('drivers-add'),
                'drivers-delete' => $this->input->post('drivers-delete'),
                'drivers-import' => $this->input->post('drivers-import'),
                'drivers-export' => $this->input->post('drivers-export'),
                
                'payroll-index' => $this->input->post('payroll-index'),
                'payroll-edit' => $this->input->post('payroll-edit'),
                'payroll-add' => $this->input->post('payroll-add'),
                'payroll-delete' => $this->input->post('payroll-delete'),
                'payroll-import' => $this->input->post('payroll-import'),
                'payroll-export' => $this->input->post('payroll-export'),

                'room-index' => $this->input->post('room-index'),
                'room-add' => $this->input->post('room-add'),
                'room-edit' => $this->input->post('room-edit'),
                'room-delete' => $this->input->post('room-delete'),
                'room-import' => $this->input->post('room-import'),
                'room-export' => $this->input->post('room-export'),
                
                'reports-commission' => $this->input->post('reports-commission'),
                'reports-profit_loss'  => $this->input->post('reports-profit_loss'),

                'assets-index' => $this->input->post('assets-index'),
                'assets-edit' => $this->input->post('assets-edit'),
                'assets-add' => $this->input->post('assets-add'),
                'assets-delete' => $this->input->post('assets-delete'),
                'assets-import' => $this->input->post('assets-import'),
                'assets-export' => $this->input->post('assets-export'),
                'assets-expenses' => $this->input->post('assets-expenses'),
                'assets-depreciation' => $this->input->post('assets-depreciation'),

                'property-index' => $this->input->post('property-index'),
                'property-edit' => $this->input->post('property-edit'),
                'property-add' => $this->input->post('property-add'),
                'property-delete' => $this->input->post('property-delete'),
                'property-import' => $this->input->post('property-import'),
                'property-export' => $this->input->post('property-export'),

                'loan-index' => $this->input->post('loan-index'),
                'loan-edit' => $this->input->post('loan-edit'),
                'loan-add' => $this->input->post('loan-add'),
                'loan-delete' => $this->input->post('loan-delete'),
                'loan-import' => $this->input->post('loan-import'),
                'loan-export' => $this->input->post('loan-export'),

                'calendar-index' => $this->input->post('calendar-index'),
                'calendar-edit' => $this->input->post('calendar-edit'),
                'calendar-add' => $this->input->post('calendar-add'),
                'calendar-delete' => $this->input->post('calendar-delete'),
                'calendar-import' => $this->input->post('calendar-import'),
                'calendar-export' => $this->input->post('calendar-export'),
                'calendar-approved' => $this->input->post('calendar-approved'),

                'store_sales-index'  => $this->input->post('store_sales-index'),
                'store_sales-edit'   => $this->input->post('store_sales-edit'),
                'store_sales-add'    => $this->input->post('store_sales-add'),
                'store_sales-delete' => $this->input->post('store_sales-delete'),
                'store_sales-import' => $this->input->post('store_sales-import'),
                'store_sales-export' => $this->input->post('store_sales-export'),

                'store_sales_order-index'  => $this->input->post('store_sales_order-index'),
                'store_sales_order-edit'   => $this->input->post('store_sales_order-edit'),
                'store_sales_order-add'    => $this->input->post('store_sales_order-add'),
                'store_sales_order-delete' => $this->input->post('store_sales_order-delete'),
                'store_sales_order-import' => $this->input->post('store_sales_order-import'),
                'store_sales_order-export' => $this->input->post('store_sales_order-export'),

                'pawns-index' => $this->input->post('pawns-index'),
                'pawns-edit' => $this->input->post('pawns-edit'),
                'pawns-add' => $this->input->post('pawns-add'),
                'pawns-delete' => $this->input->post('pawns-delete'),
                'pawns-payments' => $this->input->post('pawns-payments'),
                'pawns-date' => $this->input->post('pawns-date'),
                'pawns-closes' => $this->input->post('pawns-closes'),
                'pawns-returns' => $this->input->post('pawns-returns'),
                'pawns-purchases' => $this->input->post('pawns-purchases'),
                'pawns-products' => $this->input->post('pawns-products'),
                'installments-index' => $this->input->post('installments-index'),
                'installments-edit' => $this->input->post('installments-edit'),
                'installments-add' => $this->input->post('installments-add'),
                'installments-delete' => $this->input->post('installments-delete'),
                'installments-payments' => $this->input->post('installments-payments'),
                'installments-date' => $this->input->post('installments-date'),
                'installments-inactive' => $this->input->post('installments-inactive'),
                'installments-payoff' => $this->input->post('installments-payoff'),
                'installments-penalty' => $this->input->post('installments-penalty'),

                'reports-installments' => $this->input->post('reports-installments'),
                'reports-installment_payments' => $this->input->post('reports-installment_payments'),
                'loans-index' => $this->input->post('loans-index'),
                'loans-date' => $this->input->post('loans-date'),
                'loans-schedule-add' => $this->input->post('loans-schedule-add'),
                'loans-schedule-edit' => $this->input->post('loans-schedule-edit'),
                'loans-payment-schedule' => $this->input->post('loans-payment-schedule'),
                'loans-charges' => $this->input->post('loans-charges'),
                'loans-payments' => $this->input->post('loans-payments'),
                'loans-payoff' => $this->input->post('loans-payoff'),
                'loans-borrowers' => $this->input->post('loans-borrowers'),
                'loans-borrower_types' => $this->input->post('loans-borrower_types'),
                'loans-loan_products' => $this->input->post('loans-loan_products'),
                'loans-collaterals' => $this->input->post('loans-collaterals'),
                'loans-guarantors' => $this->input->post('loans-guarantors'),
                'loans-working_status' => $this->input->post('loans-working_status'),

                'loans-applications-index' => $this->input->post('loans-applications-index'),
                'loans-applications-add' => $this->input->post('loans-applications-add'),
                'loans-applications-edit' => $this->input->post('loans-applications-edit'),
                'loans-applications-delete' => $this->input->post('loans-applications-delete'),
                'loans-applications-date' => $this->input->post('loans-applications-date'),
                'loans-applications-approve' => $this->input->post('loans-applications-approve'),
                'loans-applications-decline' => $this->input->post('loans-applications-decline'),
                'loans-applications-disburse' => $this->input->post('loans-applications-disburse'),

                'settings'                          => $this->input->post('settings'),
                'system_settings-index'             => $this->input->post('system_settings-index'),
                'pos-settings'                      => $this->input->post('pos-settings'),
                'system_settings-change_logo'       => $this->input->post('system_settings-change_logo'),
                'system_settings-warehouses'        => $this->input->post('system_settings-warehouses'),
                'system_settings-categories'        => $this->input->post('system_settings-categories'),
                'system_settings-units'             => $this->input->post('system_settings-units'),
                'system_settings-brands'            => $this->input->post('system_settings-brands'),
                'system_settings-variants'          => $this->input->post('system_settings-variants'),
                'system_settings-boms'              => $this->input->post('system_settings-boms'),
                'system_settings-zones'             => $this->input->post('system_settings-zones'),
                'system_settings-frequencies'       => $this->input->post('system_settings-frequencies'),

                'expenses-index'                    => $this->input->post('expenses-index'),
                'expenses-add'                      => $this->input->post('expenses-add'),
                'expenses-edit'                     => $this->input->post('expenses-edit'),
                'expenses-delete'                   => $this->input->post('expenses-delete'),
                'expenses-date'                     => $this->input->post('expenses-date'),

                'system_settings-expense_categories'=> $this->input->post('system_settings-expense_categories'),
                'system_settings-tables'            => $this->input->post('system_settings-tables'),
                'system_settings-customer_groups'   => $this->input->post('system_settings-customer_groups'),
                'system_settings-customer_price'    => $this->input->post('system_settings-customer_price'),
                'system_settings-price_groups'      => $this->input->post('system_settings-price_groups'),
                'system_settings-payment_term'      => $this->input->post('system_settings-payment_term'),
                'system_settings-sale_targets'      => $this->input->post('system_settings-saleman_targets'),
                'system_settings-currencies'        => $this->input->post('system_settings-currencies'),
                'system_settings-tax_rates'         => $this->input->post('system_settings-tax_rates'),
                'system_settings-email_templates'   => $this->input->post('system_settings-email_templates'),
                'pos-printers'                      => $this->input->post('pos-printers'),
                'system_settings-cash_account'      => $this->input->post('system_settings-cash_account'),
                'system_settings-telegrams'         => $this->input->post('system_settings-telegrams'),
                'system_settings-user_groups'       => $this->input->post('system_settings-user_groups'),
                'system_settings-backups'           => $this->input->post('system_settings-backups'),
                'system_settings-rewards'           => $this->input->post('system_settings-rewards'),

                // gym permission
                'workouts-index'                    => $this->input->post('workouts-index'),
                'workouts-edit'                     => $this->input->post('workouts-edit'),
                'workouts-add'                      => $this->input->post('workouts-add'),
                'workouts-delete'                   => $this->input->post('workouts-delete'),
                'levels-index'                      => $this->input->post('levels-index'),
                'levels-edit'                       => $this->input->post('levels-edit'),
                'levels-add'                        => $this->input->post('levels-add'),
                'levels-delete'                     => $this->input->post('levels-delete'),
                'memberships-index'                 => $this->input->post('memberships-index'),
                'memberships-edit'                  => $this->input->post('memberships-edit'),
                'memberships-add'                   => $this->input->post('memberships-add'),
                'memberships-delete'                => $this->input->post('memberships-delete'),
                'activitys-index'                   => $this->input->post('activitys-index'),
                'activitys-edit'                    => $this->input->post('activitys-edit'),
                'activitys-add'                     => $this->input->post('activitys-add'),
                'activitys-delete'                  => $this->input->post('activitys-delete'),
                'trainers-index'                    => $this->input->post('trainers-index'),
                'trainers-edit'                     => $this->input->post('trainers-edit'),
                'trainers-add'                      => $this->input->post('trainers-add'),
                'trainers-delete'                   => $this->input->post('trainers-delete'),
                'trainees-index'                    => $this->input->post('trainees-index'),
                'trainees-edit'                     => $this->input->post('trainees-edit'),
                'trainees-add'                      => $this->input->post('trainees-add'),
                'trainees-delete'                   => $this->input->post('trainees-delete'),
                'class-index'                       => $this->input->post('class-index'),
                'class-edit'                        => $this->input->post('class-edit'),
                'class-add'                         => $this->input->post('class-add'),
                'class-delete'                      => $this->input->post('class-delete'),
                'class-time_tables'                 => $this->input->post('class-time_tables'),
                'category-index'                    => $this->input->post('category-index'),
                'category-edit'                     => $this->input->post('category-edit'),
                'category-add'                      => $this->input->post('category-add'),
                'category-delete'                   => $this->input->post('category-delete'),
                'schedules-index'                   => $this->input->post('schedules-index'),
            ];
            if ($this->Settings->module_tax) {
                $data['sales-view_sale_declare']= $this->input->post('sales-view_sale_declare');
                $data["taxs-index"]             = $this->input->post('taxs-index');
                $data["taxs-add_tax"]           = $this->input->post('taxs-add_tax');
                $data["taxs-edit_tax"]          = $this->input->post('taxs-edit_tax');
                $data["taxs-delete_tax"]        = $this->input->post('taxs-delete_tax');
                $data["taxs-purchases_report"]  = $this->input->post('taxs-purchases_report');
                $data["taxs-sales_report"]      = $this->input->post('taxs-sales_report');
            }
            if (POS) {
                $data['pos-index'] = $this->input->post('pos-index');
                $data['close_table'] = $this->input->post('close_table');
                $data['remove_item'] = $this->input->post('remove_item');
            }
            if($this->Settings->module_hr){ 
                $data["hr-index"] = $this->input->post('hr-index');
                $data["hr-add"] = $this->input->post('hr-add');
                $data["hr-edit"] = $this->input->post('hr-edit');
                $data["hr-delete"] = $this->input->post('hr-delete');
                $data["hr-departments"] = $this->input->post('hr-departments');
                $data["hr-positions"] = $this->input->post('hr-positions');
                $data["hr-groups"] = $this->input->post('hr-groups');
                $data["hr-employee_types"] = $this->input->post('hr-employee_types');
                $data["hr-employees_relationships"] = $this->input->post('hr-employees_relationships');
                $data["hr-tax_conditions"] = $this->input->post('hr-tax_conditions');
                $data["hr-leave_types"] = $this->input->post('hr-leave_types');
                $data["hr-kpi_types"] = $this->input->post('hr-kpi_types');
                $data["hr-kpi_index"] = $this->input->post('hr-kpi_index');
                $data["hr-kpi_add"] = $this->input->post('hr-kpi_add');
                $data["hr-kpi_edit"] = $this->input->post('hr-kpi_edit');
                $data["hr-kpi_delete"] = $this->input->post('hr-kpi_delete');
                $data["hr-kpi_report"] = $this->input->post('hr-kpi_report');
                $data["hr-employees_report"] = $this->input->post('hr-employees_report');
                $data["hr-banks_report"] = $this->input->post('hr-banks_report');
                
                $data["hr-sample_id_cards"] = $this->input->post('hr-sample_id_cards');
                $data["hr-id_cards"] = $this->input->post('hr-id_cards');
                $data["hr-id_cards_date"] = $this->input->post('hr-id_cards_date');
                $data["hr-add_id_card"] = $this->input->post('hr-add_id_card');
                $data["hr-edit_id_card"] = $this->input->post('hr-edit_id_card');
                $data["hr-delete_id_card"] = $this->input->post('hr-delete_id_card');
                $data["hr-approve_id_card"] = $this->input->post('hr-approve_id_card');
                $data["hr-id_cards_report"] = $this->input->post('hr-id_cards_report');
                
                
                $data["hr-salary_reviews"] = $this->input->post('hr-salary_reviews');
                $data["hr-add_salary_review"] = $this->input->post('hr-add_salary_review');
                $data["hr-edit_salary_review"] = $this->input->post('hr-edit_salary_review');
                $data["hr-delete_salary_review"] = $this->input->post('hr-delete_salary_review');
                $data["hr-approve_salary_review"] = $this->input->post('hr-approve_salary_review');
                $data["hr-salary_reviews_report"] = $this->input->post('hr-salary_reviews_report');
                $data["hr-salary_reviews_date"] = $this->input->post('hr-salary_reviews_date');
            
            }
            if($this->Settings->attendance){
                $data["attendances-check_in_outs"] = $this->input->post('attendances-check_in_outs');
                $data["attendances-add_check_in_out"] = $this->input->post('attendances-add_check_in_out');
                $data["attendances-edit_check_in_out"] = $this->input->post('attendances-edit_check_in_out');
                $data["attendances-delete_check_in_out"] = $this->input->post('attendances-delete_check_in_out');
                $data["attendances-generate_attendances"] = $this->input->post('attendances-generate_attendances');
                $data["attendances-take_leaves"] = $this->input->post('attendances-take_leaves');
                $data["attendances-approve_attendances"] = $this->input->post('attendances-approve_attendances');
                $data["attendances-cancel_attendances"] = $this->input->post('attendances-cancel_attendances');
                $data["attendances-approve_ot"] = $this->input->post('attendances-approve_ot');
                $data["attendances-policies"] = $this->input->post('attendances-policies');
                $data["attendances-ot_policies"] = $this->input->post('attendances-ot_policies');
                $data["attendances-list_devices"] = $this->input->post('attendances-list_devices');
                $data["attendances-check_in_out_report"] = $this->input->post('attendances-check_in_out_report');
                $data["attendances-daily_attendance_report"] = $this->input->post('attendances-daily_attendance_report');
                $data["attendances-montly_attendance_report"] = $this->input->post('attendances-montly_attendance_report');
                $data["attendances-attendance_department_report"] = $this->input->post('attendances-attendance_department_report');
                $data["attendances-employee_leave_report"] = $this->input->post('attendances-employee_leave_report');
                $data["attendances-approve_take_leave"] = $this->input->post('attendances-approve_take_leave');
                $data["attendances-date"] = $this->input->post('attendances-date');
            }   
            if($this->Settings->payroll){
                $data["payrolls-cash_advances"] = $this->input->post('payrolls-cash_advances');
                $data["payrolls-add_cash_advance"] = $this->input->post('payrolls-add_cash_advance');
                $data["payrolls-edit_cash_advance"] = $this->input->post('payrolls-edit_cash_advance');
                $data["payrolls-delete_cash_advance"] = $this->input->post('payrolls-delete_cash_advance');
                $data["payrolls-approve_cash_advance"] = $this->input->post('payrolls-approve_cash_advance');
                $data["payrolls-payback"] = $this->input->post('payrolls-payback');
                $data["payrolls-cash_advances_date"] = $this->input->post('payrolls-cash_advances_date');
                $data["payrolls-cash_advances_report"] = $this->input->post('payrolls-cash_advances_report');
                $data["payrolls-benefits"] = $this->input->post('payrolls-benefits');
                $data["payrolls-add_benefit"] = $this->input->post('payrolls-add_benefit');
                $data["payrolls-edit_benefit"] = $this->input->post('payrolls-edit_benefit');
                $data["payrolls-delete_benefit"] = $this->input->post('payrolls-delete_benefit');
                $data["payrolls-approve_benefit"] = $this->input->post('payrolls-approve_benefit');
                $data["payrolls-additions"] = $this->input->post('payrolls-additions');
                $data["payrolls-deductions"] = $this->input->post('payrolls-deductions');
                $data["payrolls-benefits_date"] = $this->input->post('payrolls-benefits_date');
                $data["payrolls-benefits_report"] = $this->input->post('payrolls-benefits_report');
                $data["payrolls-benefit_details_report"] = $this->input->post('payrolls-benefit_details_report');
                $data["payrolls-salaries"] = $this->input->post('payrolls-salaries');
                $data["payrolls-add_salary"] = $this->input->post('payrolls-add_salary');
                $data["payrolls-edit_salary"] = $this->input->post('payrolls-edit_salary');
                $data["payrolls-delete_salary"] = $this->input->post('payrolls-delete_salary');
                $data["payrolls-approve_salary"] = $this->input->post('payrolls-approve_salary');
                $data["payrolls-salaries_date"] = $this->input->post('payrolls-salaries_date');
                $data["payrolls-salaries_report"] = $this->input->post('payrolls-salaries_report');
                $data["payrolls-salary_details_report"] = $this->input->post('payrolls-salary_details_report');
                $data["payrolls-salary_banks_report"] = $this->input->post('payrolls-salary_banks_report');
                $data["payrolls-payslips_report"] = $this->input->post('payrolls-payslips_report');
                $data["payrolls-payments"] = $this->input->post('payrolls-payments');
                $data["payrolls-add_payment"] = $this->input->post('payrolls-add_payment');
                $data["payrolls-edit_payment"] = $this->input->post('payrolls-edit_payment');
                $data["payrolls-delete_payment"] = $this->input->post('payrolls-delete_payment');
                $data["payrolls-payments_date"] = $this->input->post('payrolls-payments_date');
                $data["payrolls-payments_report"] = $this->input->post('payrolls-payments_report');
                $data["payrolls-payment_details_report"] = $this->input->post('payrolls-payment_details_report');
                
            }
            if($this->Settings->module_school){
                $data["schools-index"] = $this->input->post('schools-index');
                $data["schools-add"] = $this->input->post('schools-add');
                $data["schools-edit"] = $this->input->post('schools-edit');
                $data["schools-delete"] = $this->input->post('schools-delete');
                $data["schools-teachers"] = $this->input->post('schools-teachers');
                $data["schools-teachers-add"] = $this->input->post('schools-teachers-add');
                $data["schools-teachers-edit"] = $this->input->post('schools-teachers-edit');
                $data["schools-teachers-delete"] = $this->input->post('schools-teachers-delete');
                $data["schools-programs"] = $this->input->post('schools-programs');
                $data["schools-sections"] = $this->input->post('schools-sections');
                $data["schools-grades"] = $this->input->post('schools-grades');
                $data["schools-rooms"] = $this->input->post('schools-rooms');
                $data["schools-subjects"] = $this->input->post('schools-subjects');
                $data["schools-credit_scores"] = $this->input->post('schools-credit_scores');
                $data["schools-classes"] = $this->input->post('schools-classes');
                $data["schools-time_tables"] = $this->input->post('schools-time_tables');
                $data["schools-class_years"] = $this->input->post('schools-class_years');
                $data["schools-examinations"] = $this->input->post('schools-examinations');
                $data["schools-examinations-add"] = $this->input->post('schools-examinations-add');
                $data["schools-examinations-edit"] = $this->input->post('schools-examinations-edit');
                $data["schools-examinations-delete"] = $this->input->post('schools-examinations-delete');
                $data["schools-attendances"] = $this->input->post('schools-attendances');
                $data["schools-attendances-add"] = $this->input->post('schools-attendances-add');
                $data["schools-attendances-edit"] = $this->input->post('schools-attendances-edit');
                $data["schools-attendances-delete"] = $this->input->post('schools-attendances-delete');
                $data["schools-teacher_attendances"] = $this->input->post('schools-teacher_attendances');
                $data["schools-teacher_attendances-add"] = $this->input->post('schools-teacher_attendances-add');
                $data["schools-teacher_attendances-edit"] = $this->input->post('schools-teacher_attendances-edit');
                $data["schools-teacher_attendances-delete"] = $this->input->post('schools-teacher_attendances-delete');
                $data["schools-teacher_attendance_report"] = $this->input->post('schools-teacher_attendance_report');
                $data["schools-attendance_report"] = $this->input->post('schools-attendance_report');
                $data["schools-study_info_report"] = $this->input->post('schools-study_info_report');
                $data["schools-examanition_report"] = $this->input->post('schools-examanition_report');
                $data["schools-monthly_class_result_report"] = $this->input->post('schools-monthly_class_result_report');
                $data["schools-monthly_top_five_report"] = $this->input->post('schools-monthly_top_five_report');
                $data["schools-section_by_month_report"] = $this->input->post('schools-section_by_month_report');
                $data["schools-sectionly_class_result_report"] = $this->input->post('schools-sectionly_class_result_report');
                $data["schools-class_result_report"] = $this->input->post('schools-class_result_report');
                $data["schools-yearly_class_result_report"] = $this->input->post('schools-yearly_class_result_report');
                $data["schools-yearly_top_five_report"] = $this->input->post('schools-yearly_top_five_report');
                $data["schools-yearly_subject_result_report"] = $this->input->post('schools-yearly_subject_result_report');
                $data["schools-sectionly_subject_result_report"] = $this->input->post('schools-sectionly_subject_result_report');
                $data["schools-result_by_student_form"] = $this->input->post('schools-result_by_student_form');
                $data["schools-monthly_top_five_form"] = $this->input->post('schools-monthly_top_five_form');
                $data["schools-yearly_top_five_form"] = $this->input->post('schools-yearly_top_five_form');
                $data["schools-student_report"] = $this->input->post('schools-student_report');
                $data["schools-teacher_report"] = $this->input->post('schools-teacher_report');
                $data["schools-best_student_by_grade_report"] = $this->input->post('schools-best_student_by_grade_report');
                $data["schools-failure_student_by_year_report"] = $this->input->post('schools-failure_student_by_year_report');
                $data["schools-overview_chart"] = $this->input->post('schools-overview_chart');
                
                $data["schools-sales"] = $this->input->post('schools-sales');
                $data["schools-add_sale"] = $this->input->post('schools-add_sale');
                $data["schools-edit_sale"] = $this->input->post('schools-edit_sale');
                $data["schools-delete_sale"] = $this->input->post('schools-delete_sale');
                $data["schools-sales-date"] = $this->input->post('schools-sales-date');
                $data["schools-number_of_student_report"] = $this->input->post('schools-number_of_student_report');
                $data["schools-enrollment_by_grade_report"] = $this->input->post('schools-enrollment_by_grade_report');
                $data["schools-monthly_enrollment_report"] = $this->input->post('schools-monthly_enrollment_report');
                $data["schools-yearly_enrollment_report"] = $this->input->post('schools-yearly_enrollment_report');
                $data["schools-monthly_tuition_fee_report"] = $this->input->post('schools-monthly_tuition_fee_report');
                $data["schools-monthly_payment_report"] = $this->input->post('schools-monthly_payment_report');
                $data["schools-student_fee_report"] = $this->input->post('schools-student_fee_report');
                
                $data["schools-tickets"] = $this->input->post('schools-tickets');
                $data["schools-add_ticket"] = $this->input->post('schools-add_ticket');
                $data["schools-edit_ticket"] = $this->input->post('schools-edit_ticket');
                $data["schools-delete_ticket"] = $this->input->post('schools-delete_ticket');
                $data["schools-assign_ticket"] = $this->input->post('schools-assign_ticket');
                $data["schools-ticket-solution"] = $this->input->post('schools-ticket-solution');
                $data["schools-response_ticket"] = $this->input->post('schools-response_ticket');
                $data["schools-ticket_report"] = $this->input->post('schools-ticket_report');
                
                $data["schools-waitings"] = $this->input->post('schools-waitings');
                $data["schools-add_waiting"] = $this->input->post('schools-add_waiting');
                $data["schools-edit_waiting"] = $this->input->post('schools-edit_waiting');
                $data["schools-delete_waiting"] = $this->input->post('schools-delete_waiting');
                $data["schools-waiting_report"] = $this->input->post('schools-waiting_report');
                
                $data["schools-testings"] = $this->input->post('schools-testings');
                $data["schools-add_testing"] = $this->input->post('schools-add_testing');
                $data["schools-edit_testing"] = $this->input->post('schools-edit_testing');
                $data["schools-delete_testing"] = $this->input->post('schools-delete_testing');
                $data["schools-update_result"] = $this->input->post('schools-update_result');
                $data["schools-testing_report"] = $this->input->post('schools-testing_report');
                $data["schools-bank_info"] = $this->input->post('schools-bank_info');
                $data["schools-feedback_questions"] = $this->input->post('schools-feedback_questions');
                $data["schools-testing_groups"] = $this->input->post('schools-testing_groups');
                $data["schools-testing_results"] = $this->input->post('schools-testing_results');
                
                $data["schools-student_statuses"] = $this->input->post('schools-student_statuses');
                $data["schools-add_student_status"] = $this->input->post('schools-add_student_status');
                $data["schools-edit_student_status"] = $this->input->post('schools-edit_student_status');
                $data["schools-delete_student_status"] = $this->input->post('schools-delete_student_status');
                $data["schools-student_status_report"] = $this->input->post('schools-student_status_report');
                $data["schools-set_student_status_review"] = $this->input->post('schools-set_student_status_review');
                $data["schools-add_reenrollment"] = $this->input->post('schools-add_reenrollment');
                $data["schools-suspension_report"] = $this->input->post('schools-suspension_report');
                $data["schools-dropping_out_report"] = $this->input->post('schools-dropping_out_report');
                $data["schools-reconfirmation_report"] = $this->input->post('schools-reconfirmation_report');
                $data["schools-black_list_report"] = $this->input->post('schools-black_list_report');
                $data["schools-graduation_report"] = $this->input->post('schools-graduation_report');
                $data["schools-black_lists"] = $this->input->post('schools-black_lists');
                $data["schools-skills"] = $this->input->post('schools-skills');
                
            }
            if($this->Settings->module_concrete){
                $data["concretes-drivers"] = $this->input->post('concretes-drivers');
                $data["concretes-trucks"] = $this->input->post('concretes-trucks');
                $data["concretes-slumps"] = $this->input->post('concretes-slumps');
                $data["concretes-casting_types"] = $this->input->post('concretes-casting_types');
                $data["concretes-groups"] = $this->input->post('concretes-groups');
                $data["concretes-officers"] = $this->input->post('concretes-officers');
                $data["concretes-deliveries"] = $this->input->post('concretes-deliveries');
                $data["concretes-deliveries-date"] = $this->input->post('concretes-deliveries-date');
                $data["concretes-add_delivery"] = $this->input->post('concretes-add_delivery');
                $data["concretes-edit_delivery"] = $this->input->post('concretes-edit_delivery');
                $data["concretes-delete_delivery"] = $this->input->post('concretes-delete_delivery');
                $data["concretes-skip-so"] = $this->input->post('concretes-skip-so');
                
                $data["concretes-moving_waitings"] = $this->input->post('concretes-moving_waitings');
                $data["concretes-moving_waitings-date"] = $this->input->post('concretes-moving_waitings-date');
                $data["concretes-add_moving_waiting"] = $this->input->post('concretes-add_moving_waiting');
                $data["concretes-edit_moving_waiting"] = $this->input->post('concretes-edit_moving_waiting');
                $data["concretes-delete_moving_waiting"] = $this->input->post('concretes-delete_moving_waiting');

                $data["concretes-missions"] = $this->input->post('concretes-missions');
                $data["concretes-missions-date"] = $this->input->post('concretes-missions-date');
                $data["concretes-add_mission"] = $this->input->post('concretes-add_mission');
                $data["concretes-edit_mission"] = $this->input->post('concretes-edit_mission');
                $data["concretes-delete_mission"] = $this->input->post('concretes-delete_mission');
                
                $data["concretes-fuel_expenses"] = $this->input->post('concretes-fuel_expenses');
                $data["concretes-fuel_expenses-date"] = $this->input->post('concretes-fuel_expenses-date');
                $data["concretes-add_fuel_expense"] = $this->input->post('concretes-add_fuel_expense');
                $data["concretes-edit_fuel_expense"] = $this->input->post('concretes-edit_fuel_expense');
                $data["concretes-delete_fuel_expense"] = $this->input->post('concretes-delete_fuel_expense');
                $data["concretes-fuel_expense_payments"] = $this->input->post('concretes-fuel_expense_payments');
                $data["concretes-fuel_expenses_report"] = $this->input->post('concretes-fuel_expenses_report');
                $data["concretes-fuel_expense_details_report"] = $this->input->post('concretes-fuel_expense_details_report');
                
                $data["concretes-commissions"] = $this->input->post('concretes-commissions');
                $data["concretes-commissions-date"] = $this->input->post('concretes-commissions-date');
                $data["concretes-add_commission"] = $this->input->post('concretes-add_commission');
                $data["concretes-edit_commission"] = $this->input->post('concretes-edit_commission');
                $data["concretes-delete_commission"] = $this->input->post('concretes-delete_commission');
                $data["concretes-commission_payments"] = $this->input->post('concretes-commission_payments');
                
                $data["concretes-absents"] = $this->input->post('concretes-absents');
                $data["concretes-absents-date"] = $this->input->post('concretes-absents-date');
                $data["concretes-add_absent"] = $this->input->post('concretes-add_absent');
                $data["concretes-edit_absent"] = $this->input->post('concretes-edit_absent');
                $data["concretes-delete_absent"] = $this->input->post('concretes-delete_absent');
                $data["concretes-absents_report"] = $this->input->post('concretes-absents_report');
                
                $data["concretes-fuels"] = $this->input->post('concretes-fuels');
                $data["concretes-fuels-date"] = $this->input->post('concretes-fuels-date');
                $data["concretes-add_fuel"] = $this->input->post('concretes-add_fuel');
                $data["concretes-edit_fuel"] = $this->input->post('concretes-edit_fuel');
                $data["concretes-delete_fuel"] = $this->input->post('concretes-delete_fuel');
                $data["concretes-sales"] = $this->input->post('concretes-sales');
                $data["concretes-sales-date"] = $this->input->post('concretes-sales-date');
                $data["concretes-add_sale"] = $this->input->post('concretes-add_sale');
                $data["concretes-edit_sale"] = $this->input->post('concretes-edit_sale');
                $data["concretes-delete_sale"] = $this->input->post('concretes-delete_sale');
                $data["concretes-adjustments"] = $this->input->post('concretes-adjustments');
                $data["concretes-add_adjustment"] = $this->input->post('concretes-add_adjustment');
                $data["concretes-edit_adjustment"] = $this->input->post('concretes-edit_adjustment');
                $data["concretes-delete_adjustment"] = $this->input->post('concretes-delete_adjustment');
                $data["concretes-approve_adjustment"] = $this->input->post('concretes-approve_adjustment');
                $data["concretes-errors"] = $this->input->post('concretes-errors');
                $data["concretes-errors-date"] = $this->input->post('concretes-errors-date');
                $data["concretes-add_error"] = $this->input->post('concretes-add_error');
                $data["concretes-edit_error"] = $this->input->post('concretes-edit_error');
                $data["concretes-delete_error"] = $this->input->post('concretes-delete_error');
                $data["concretes-deliveries_report"] = $this->input->post('concretes-deliveries_report');
                $data["concretes-daily_deliveries"] = $this->input->post('concretes-daily_deliveries');
                $data["concretes-daily_stock_ins"] = $this->input->post('concretes-daily_stock_ins');
                $data["concretes-daily_stock_outs"] = $this->input->post('concretes-daily_stock_outs');
                $data["concretes-inventory_in_outs"] = $this->input->post('concretes-inventory_in_outs');
                $data["concretes-fuels_report"] = $this->input->post('concretes-fuels_report');
                $data["concretes-fuel_summaries_report"] = $this->input->post('concretes-fuel_summaries_report');
                $data["concretes-fuel_details_report"] = $this->input->post('concretes-fuel_details_report');
                $data["concretes-fuel_by_customer_report"] = $this->input->post('concretes-fuel_by_customer_report');
                $data["concretes-sales_report"] = $this->input->post('concretes-sales_report');
                $data["concretes-sale_details_report"] = $this->input->post('concretes-sale_details_report');
                $data["concretes-product_sales_report"] = $this->input->post('concretes-product_sales_report');
                $data["concretes-product_customers_report"] = $this->input->post('concretes-product_customers_report');
                $data["concretes-adjustments_report"] = $this->input->post('concretes-adjustments_report');
                $data["concretes-daily_errors"] = $this->input->post('concretes-daily_errors');
                $data["concretes-daily_error_materials"] = $this->input->post('concretes-daily_error_materials');
                $data["concretes-truck_commissions"] = $this->input->post('concretes-truck_commissions');
                $data["concretes-pump_commissions"] = $this->input->post('concretes-pump_commissions');
                $data["concretes-officer_commissions"] = $this->input->post('concretes-officer_commissions');
                $data["concretes-mission_types"] = $this->input->post('concretes-mission_types');
                $data["concretes-commissions_report"] = $this->input->post('concretes-commissions_report');
                $data["concretes-moving_waitings_report"] = $this->input->post('concretes-moving_waitings_report');
                $data["concretes-missions_report"] = $this->input->post('concretes-missions_report');
            }
            //$this->bpas->print_arrays($data);
        }
  

        if ($this->form_validation->run() == true && $this->settings_model->updatePermissions($id, $data)) {
            $this->session->set_flashdata('message', lang('group_permissions_updated'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

            $this->data['id']    = $id;
            $this->data['p']     = $this->settings_model->getGroupPermissions($id);
      
            $this->data['group'] = $this->settings_model->getGroupByID($id);
            
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('group_permissions')]];
            $meta = ['page_title' => lang('group_permissions'), 'bc' => $bc];
            $this->page_construct('settings/permissions', $meta, $this->data);
        }
    }
    public function commission_product()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('commission_product')]];
        $meta = ['page_title' => lang('commission_product'), 'bc' => $bc];
        $this->page_construct('settings/commission_product', $meta, $this->data);
    }
    public function price_groups()
    {
        $price_groups = $this->products_model->getAllPrice_Groups();
        $this->form_validation->set_rules('product', lang('product'), 'required');
        foreach ($price_groups as $key => $price_group) {
            $this->form_validation->set_rules('price_group_' . $price_group->id, ucwords($price_group->name), 'required');
        }
        if ($this->form_validation->run() == true) {
            foreach ($price_groups as $key => $price_group) {
                $data[] = [ 
                    'product_id'     => $this->input->post('product') ,
                    'price_group_id' => $price_group->id,
                    'price'          => $this->input->post('price_group_' . $price_group->id),
                ];
            }
        }
        if ($this->form_validation->run() == true && $this->settings_model->updateProductPriceGroup($data)) { 
            $this->session->set_flashdata('message', lang('product_price_groups_updated'));
            admin_redirect('system_settings/price_groups');
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['price_groups']   = $price_groups;
            $this->data['products']       = $this->site->getProducts();
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('price_groups')]];
            $meta = ['page_title' => lang('price_groups'), 'bc' => $bc];
            $this->page_construct('settings/price_groups', $meta, $this->data);
        }
    }
    public function getPriceGroupsByProductID($id)
    {
        $result = [];
        if($result['price_groups'] = $this->products_model->getPriceGroupsByProductID($id)){
            $result['product']     = $this->products_model->getProductByID($id);
            $this->bpas->send_json($result);       
        } else {
            $this->bpas->send_json(false); 
        }
    }
    // public function getPricesProduct($group_id = null)
    // {
    //     if (!$group_id) {
    //         $this->session->set_flashdata('error', lang('no_price_group_selected'));
    //         admin_redirect('system_settings/price_groups');
    //     }

    //     $pp = "( SELECT {$this->db->dbprefix('product_prices')}.product_id as product_id, {$this->db->dbprefix('product_prices')}.price as price,{$this->db->dbprefix('product_prices')}.qty_from as qty_from,{$this->db->dbprefix('product_prices')}.qty_to as qty_to FROM {$this->db->dbprefix('product_prices')} WHERE price_group_id = {$group_id} ) PP";

    //     $this->load->library('datatables');
    //     $this->datatables
    //         ->select("{$this->db->dbprefix('products')}.id as id, 
    //             {$this->db->dbprefix('products')}.code as product_code, 
    //             {$this->db->dbprefix('products')}.name as product_name,
    //             {$this->db->dbprefix('units')}.name as unit, 
    //             {$this->db->dbprefix('products')}.price as quantity,
    //             CONCAT({$this->db->dbprefix("products")}.id,'__', COALESCE(PP.qty_from, ''),'__', COALESCE(PP.qty_to,'')) as product_price,
    //             PP.price as price ")
    //         ->from('products')
    //         // ->order_by('products.id')
    //         ->join('units', 'units.id = products.unit','left')
    //         ->join($pp, 'PP.product_id=products.id', 'left')
    //         ->edit_column('price', '$1__$2', 'id, price')
    //         ->add_column('Actions', '<div class="text-center"><button class="btn btn-primary btn-xs form-submit" type="button"><i class="fa fa-check"></i></button></div>', 'id');

    //     echo $this->datatables->generate();
    // }
    public function getPricesProduct($group_id = null)
    {
        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            admin_redirect('system_settings/price_groups');
        }

        $pp = "( SELECT 
                    {$this->db->dbprefix('product_prices')}.product_id as product_id, 
                    {$this->db->dbprefix('product_prices')}.price as price,
                    {$this->db->dbprefix('product_prices')}.qty_from as qty_from,
                    {$this->db->dbprefix('product_prices')}.unit_id as unit_id,
                    {$this->db->dbprefix('product_prices')}.qty_to as qty_to 
                FROM {$this->db->dbprefix('product_prices')}
                
                INNER JOIN {$this->db->dbprefix('products')} ON {$this->db->dbprefix('products')}.id = {$this->db->dbprefix('product_prices')}.product_id 
                AND  {$this->db->dbprefix('products')}.unit = {$this->db->dbprefix('product_prices')}.unit_id
                WHERE price_group_id = {$group_id} 
                GROUP BY {$this->db->dbprefix('product_prices')}.product_id ) PP";

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('products')}.id as id, 
                {$this->db->dbprefix('products')}.code as product_code, 
                {$this->db->dbprefix('products')}.name as product_name,
                CONCAT({$this->db->dbprefix('products')}.id,'__',{$this->db->dbprefix('products')}.unit) as unit, 
                CONCAT({$this->db->dbprefix("products")}.id,'__',{$this->db->dbprefix('products')}.price) as pro_price,
                CONCAT({$this->db->dbprefix("products")}.id,'__', COALESCE(PP.qty_from, ''),'__', COALESCE(PP.qty_to,'')) as product_price,
                PP.price as price ")
            ->from('products')
            // ->order_by('products.id')
            ->join('units', 'units.id = products.unit','left')
            ->join($pp, 'PP.product_id = products.id', 'left')
            ->edit_column('price', '$1__$2', 'id, price')
            ->add_column('Actions', '<div class="text-center"><button class="btn btn-primary btn-xs form-submit" type="button"><i class="fa fa-check"></i></button></div>', 'id');

        echo $this->datatables->generate();
    }
    public function getProductPrices($group_id = null)
    {
        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            admin_redirect('system_settings/price_groups');
        }

        $pp = "( SELECT {$this->db->dbprefix('product_prices')}.product_id as product_id, {$this->db->dbprefix('product_prices')}.price as price,{$this->db->dbprefix('product_prices')}.qty_from as qty_from,{$this->db->dbprefix('product_prices')}.qty_to as qty_to FROM {$this->db->dbprefix('product_prices')} WHERE price_group_id = {$group_id} ) PP";

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('products')}.id as id, 
                {$this->db->dbprefix('products')}.code as product_code, 
                {$this->db->dbprefix('products')}.name as product_name,
                {$this->db->dbprefix('units')}.name as unit, 
                {$this->db->dbprefix('products')}.price as real_price,
                
                PP.price as price ")
            ->from('products')
            // ->order_by('products.id')
            ->join('units', 'units.id = products.unit','left')
            ->join($pp, 'PP.product_id=products.id', 'left')
            ->edit_column('price', '$1__$2', 'id, price')
            ->add_column('Actions', '<div class="text-center"><button class="btn btn-primary btn-xs form-submit" type="button"><i class="fa fa-check"></i></button></div>', 'id');

        echo $this->datatables->generate();
    }
    public function getCommissionPrices($group_id = null)
    {
        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            admin_redirect('system_settings/price_groups');
        }

        $pp = "( SELECT {$this->db->dbprefix('product_prices')}.product_id as product_id, {$this->db->dbprefix('product_prices')}.price as price,{$this->db->dbprefix('product_prices')}.qty_from as qty_from,{$this->db->dbprefix('product_prices')}.qty_to as qty_to FROM {$this->db->dbprefix('product_prices')} WHERE price_group_id = {$group_id} ) PP";

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('products')}.id as id, 
                {$this->db->dbprefix('products')}.code as product_code, 
                {$this->db->dbprefix('products')}.name as product_name,
                {$this->db->dbprefix('units')}.name as unit, 
                
                PP.price as price ")
            ->from('products')
            // ->order_by('products.id')
            ->join('units', 'units.id = products.unit','left')
            ->join($pp, 'PP.product_id=products.id', 'left')
            ->edit_column('price', '$1__$2', 'id, price')
            ->add_column('Actions', '<div class="text-center"><button class="btn btn-primary btn-xs form-submit" type="button"><i class="fa fa-check"></i></button></div>', 'id');

        echo $this->datatables->generate();
    }
    // public function product_multi_group_price_actions($group_id)
    // // public function price_group_actions($group_id)
    // {
    //     if (!$group_id) {
    //         $this->session->set_flashdata('error', lang('no_price_group_selected'));
    //         admin_redirect('system_settings/price_groups');
    //     }

    //     $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

    //     if ($this->form_validation->run() == true) {
    //         if (!empty($_POST['val'])) {
    //             if ($this->input->post('form_action') == 'update_price') {
    //                 foreach ($_POST['val'] as $id) {
                       
    //                     $this->settings_model->setProductmultiPriceForPriceGroup($id, $group_id, $this->input->post('price' . $id), $this->input->post('pricef' . $id), $this->input->post('pricet' . $id));
    //                 }
    //                 $this->session->set_flashdata('message', lang('products_group_price_updated'));
    //                 redirect($_SERVER['HTTP_REFERER']);
    //             } elseif ($this->input->post('form_action') == 'delete') {
    //                 foreach ($_POST['val'] as $id) {
    //                     $this->settings_model->deleteProductGroupPrice($id, $group_id);
    //                 }
    //                 $this->session->set_flashdata('message', lang('products_group_price_deleted'));
    //                 redirect($_SERVER['HTTP_REFERER']);
    //             } elseif ($this->input->post('form_action') == 'export_excel') {
    //                 $this->load->library('excel');
    //                 $this->excel->setActiveSheetIndex(0);
    //                 $this->excel->getActiveSheet()->setTitle(lang('tax_rates'));
    //                 $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
    //                 $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
    //                  $this->excel->getActiveSheet()->SetCellValue('C1', lang('qty_from'));
    //                   $this->excel->getActiveSheet()->SetCellValue('D1', lang('qty_to'));
    //                 $this->excel->getActiveSheet()->SetCellValue('E1', lang('price'));
    //                 $this->excel->getActiveSheet()->SetCellValue('F1', lang('group_name'));
    //                 $row   = 2;
    //                 $group = $this->settings_model->getPriceGroupByID($group_id);
    //                 foreach ($_POST['val'] as $id) {
    //                     $pgp = $this->settings_model->getProductGroupmultiPriceByPID($id, $group_id);
                       
    //                     $this->excel->getActiveSheet()->SetCellValue('A' . $row, $pgp->code);
    //                     $this->excel->getActiveSheet()->SetCellValue('B' . $row, $pgp->name);
    //                     $this->excel->getActiveSheet()->SetCellValue('C' . $row, $pgp->qty_from);
    //                     $this->excel->getActiveSheet()->SetCellValue('D' . $row, $pgp->qty_to);
    //                     $this->excel->getActiveSheet()->SetCellValue('E' . $row, $pgp->price);
    //                     $this->excel->getActiveSheet()->SetCellValue('F' . $row, $group->name);
    //                     $row++;
    //                 }
    //                 $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
    //                 $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    //                 $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
    //                 $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
    //                 $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
    //                 $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
    //                 $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    //                 $filename = 'price_groups_' . date('Y_m_d_H_i_s');
    //                 $this->load->helper('excel');
    //                 create_excel($this->excel, $filename);
    //             }
    //         } else {
    //             $this->session->set_flashdata('error', lang('no_price_group_selected'));
    //             redirect($_SERVER['HTTP_REFERER']);
    //         }
    //     } else {
    //         $this->session->set_flashdata('error', validation_errors());
    //         redirect($_SERVER['HTTP_REFERER']);
    //     }
    // }
    public function product_multi_group_price_actions($group_id)
    // public function price_group_actions($group_id)
    {
        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            admin_redirect('system_settings/price_groups');
        }

        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'update_price') {
                    foreach ($_POST['val'] as $id) {
                        // var_dump($this->input->post('units' . $id));exit;
                        $this->settings_model->setProductmultiPriceForPriceGroup($id, $group_id, $this->input->post('price' . $id), $this->input->post('pricef' . $id), $this->input->post('pricet' . $id),$this->input->post('units' . $id));
                    }
                    $this->session->set_flashdata('message', lang('products_group_price_updated'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteProductGroupPrice($id, $group_id);
                    }
                    $this->session->set_flashdata('message', lang('products_group_price_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('tax_rates'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('qty_from'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('qty_to'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('price'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('group_name'));
                    $row   = 2;
                    $group = $this->settings_model->getPriceGroupByID($group_id);
                    foreach ($_POST['val'] as $id) {
                        $pgp = $this->settings_model->getProductGroupmultiPriceByPID($id, $group_id);
                       
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $pgp->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $pgp->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $pgp->qty_from);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $pgp->qty_to);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $pgp->price);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $group->name);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'price_groups_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_price_group_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function multi_buy_groups()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('multi_buys')]];
        $meta = ['page_title' => lang('multi_buys'), 'bc' => $bc];
        $this->page_construct('settings/multi_buy_groups', $meta, $this->data);
    }

    public function restore_backup($zipfile)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $file = './files/backups/' . $zipfile . '.zip';
        $this->bpas->unzip($file, './');
        $this->session->set_flashdata('success', lang('files_restored'));
        admin_redirect('system_settings/backups');
        exit();
    }

    public function restore_database($dbfile)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $file = file_get_contents('./files/backups/' . $dbfile . '.txt');
        // $this->db->conn_id->multi_query($file);
        mysqli_multi_query($this->db->conn_id, $file);
        $this->db->conn_id->close();
        admin_redirect('logout/db');
    }

    public function skrill()
    {
        $this->form_validation->set_rules('active', $this->lang->line('activate'), 'trim');
        $this->form_validation->set_rules('account_email', $this->lang->line('paypal_account_email'), 'trim|valid_email');
        if ($this->input->post('active')) {
            $this->form_validation->set_rules('account_email', $this->lang->line('paypal_account_email'), 'required');
        }
        $this->form_validation->set_rules('secret_word', $this->lang->line('secret_word'), 'trim');
        $this->form_validation->set_rules('fixed_charges', $this->lang->line('fixed_charges'), 'trim');
        $this->form_validation->set_rules('extra_charges_my', $this->lang->line('extra_charges_my'), 'trim');
        $this->form_validation->set_rules('extra_charges_other', $this->lang->line('extra_charges_others'), 'trim');

        if ($this->form_validation->run() == true) {
            $data = ['active'         => $this->input->post('active'),
                'secret_word'         => $this->input->post('secret_word'),
                'account_email'       => $this->input->post('account_email'),
                'fixed_charges'       => $this->input->post('fixed_charges'),
                'extra_charges_my'    => $this->input->post('extra_charges_my'),
                'extra_charges_other' => $this->input->post('extra_charges_other'),
            ];
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateSkrill($data)) {
            $this->session->set_flashdata('message', $this->lang->line('skrill_setting_updated'));
            admin_redirect('system_settings/skrill');
        } else {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

            $this->data['skrill'] = $this->settings_model->getSkrillSettings();

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('skrill_settings')]];
            $meta = ['page_title' => lang('skrill_settings'), 'bc' => $bc];
            $this->page_construct('settings/skrill', $meta, $this->data);
        }
    }

    public function tax_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteTaxRate($id);
                    }
                    $this->session->set_flashdata('message', lang('tax_rates_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('tax_rates'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('tax_rate'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('type'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $tax = $this->settings_model->getTaxRateByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $tax->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $tax->code);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $tax->rate);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, ($tax->type == 1) ? lang('percentage') : lang('fixed'));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'tax_rates_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_record_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function tax_rates()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('tax_rates')]];
        $meta = ['page_title' => lang('tax_rates'), 'bc' => $bc];
        $this->page_construct('settings/tax_rates', $meta, $this->data);
    }

    public function unit_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteUnit($id);
                    }
                    $this->session->set_flashdata('message', lang('units_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('categories'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('base_unit'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('operator'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('operation_value'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $unit = $this->site->getUnitByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $unit->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $unit->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $unit->base_unit);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $unit->operator);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $unit->operation_value);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'units_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_record_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }


    public function updates()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $this->form_validation->set_rules('purchase_code', lang('purchase_code'), 'required');
        $this->form_validation->set_rules('envato_username', lang('envato_username'), 'required');
        if ($this->form_validation->run() == true) {
            $this->db->update('settings', ['purchase_code' => $this->input->post('purchase_code', true), 'envato_username' => $this->input->post('envato_username', true)], ['setting_id' => 1]);
            admin_redirect('system_settings/updates');
        } else {
            $fields = ['version' => $this->Settings->version, 'code' => $this->Settings->purchase_code, 'username' => $this->Settings->envato_username, 'site' => base_url()];
            $this->load->helper('update');
            $protocol              = is_https() ? 'https://' : 'http://';
            $updates               = get_remote_contents($protocol . 'api.tecdiary.com/v1/update/', $fields);
            $this->data['updates'] = json_decode($updates);
            $bc                    = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('updates')]];
            $meta                  = ['page_title' => lang('updates'), 'bc' => $bc];
            $this->page_construct('settings/updates', $meta, $this->data);
        }
    }

    public function user_groups()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('auth');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['groups'] = $this->settings_model->getGroups();
        $bc                   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('groups')]];
        $meta                 = ['page_title' => lang('groups'), 'bc' => $bc];
        $this->page_construct('settings/user_groups', $meta, $this->data);
    }
    public function warehouses()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('warehouses')]];
        $meta = ['page_title' => lang('warehouses'), 'bc' => $bc];
        $this->page_construct('settings/warehouses', $meta, $this->data);
    }
    public function add_warehouse()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang('code'), 'trim|is_unique[warehouses.code]|required');
        $this->form_validation->set_rules('name', lang('name'), 'required');
        $this->form_validation->set_rules('address', lang('address'), 'required');
        $this->form_validation->set_rules('userfile', lang('map_image'), 'xss_clean');
        $this->form_validation->set_rules('atten_name', lang('atten_name'));
        $this->form_validation->set_rules('fax', lang('fax'));

        if ($this->form_validation->run() == true) {
            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');

                $config['upload_path']   = 'assets/uploads/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = '2000';
                $config['max_height']    = '2000';
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('message', $error);
                    admin_redirect('system_settings/warehouses');
                }

                $map = $this->upload->file_name;

                $this->load->helper('file');
                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = 'assets/uploads/' . $map;
                $config['new_image']      = 'assets/uploads/thumbs/' . $map;
                $config['maintain_ratio'] = true;
                $config['width']          = 76;
                $config['height']         = 76;

                $this->image_lib->clear();
                $this->image_lib->initialize($config);

                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
            } else {
                $map = null;
            }
            $data = [
                'code'           => $this->input->post('code'),
                'name'           => $this->input->post('name'),
                'phone'          => $this->input->post('phone'),
                'email'          => $this->input->post('email'),
                'default_currency'          => $this->input->post('default_currency'),
                'address'        => $this->input->post('address'),
                'price_group_id' => $this->input->post('price_group'),
                'map'            => $map,
                'atten_name'     => $this->input->post('atten_name'),
                'saleable'       => $this->input->post('saleable'),
                'overselling'    => $this->input->post('warehouse_over_selling'),
            ];
            if ($_FILES['logo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = $this->Settings->iwidth;
                $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['max_filename']  = 25;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('logo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo         = $this->upload->file_name;
                $data['logo'] = $photo;
            }
        } elseif ($this->input->post('add_warehouse')) {
            $this->session->set_flashdata('error', validation_errors());
           redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addWarehouse($data)) {
            $this->session->set_flashdata('message', lang('warehouse_added'));
           redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']        = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['price_groups'] = $this->settings_model->getAllPriceGroups();
            $this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['modal_js']     = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_warehouse', $this->data);
        }
    }
    public function edit_warehouse($id = null)
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang('code'), 'trim|required');
        $wh_details = $this->settings_model->getWarehouseByID($id);
        if ($this->input->post('code') != $wh_details->code) {
            $this->form_validation->set_rules('code', lang('code'), 'required|is_unique[warehouses.code]');
        }
        $this->form_validation->set_rules('address', lang('address'), 'required');
        $this->form_validation->set_rules('map', lang('map_image'), 'xss_clean');
        $this->form_validation->set_rules('address', lang('atten_name'));
        $this->form_validation->set_rules('address', lang('fax'));

        if ($this->form_validation->run() == true) {
            $data = [
                'code'              => $this->input->post('code'),
                'name'              => $this->input->post('name'),
                'atten_name'        => $this->input->post('atten_name'),
                'phone'             => $this->input->post('phone'),
                'email'             => $this->input->post('email'),
                'saleable'       => $this->input->post('saleable'),
                'default_currency'  => $this->input->post('default_currency'),
                'address'           => $this->input->post('address'),
                'price_group_id'    => $this->input->post('price_group'),
                'overselling'       => $this->input->post('warehouse_over_selling'),
            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');

                $config['upload_path']   = 'assets/uploads/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = '2000';
                $config['max_height']    = '2000';
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('message', $error);
                    admin_redirect('system_settings/warehouses');
                }

                $data['map'] = $this->upload->file_name;

                $this->load->helper('file');
                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = 'assets/uploads/' . $data['map'];
                $config['new_image']      = 'assets/uploads/thumbs/' . $data['map'];
                $config['maintain_ratio'] = true;
                $config['width']          = 76;
                $config['height']         = 76;

                $this->image_lib->clear();
                $this->image_lib->initialize($config);

                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
            }
            
            if ($_FILES['logo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = $this->Settings->iwidth;
                $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['max_filename']  = 25;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('logo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('system_settings/warehouses');
                }
                $photo         = $this->upload->file_name;
                $data['logo'] = $photo;
            }
        } elseif ($this->input->post('edit_warehouse')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/warehouses');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateWarehouse($id, $data)) { //check to see if we are updateing the customer
            $this->session->set_flashdata('message', lang('warehouse_updated'));
            admin_redirect('system_settings/warehouses');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
             $this->data['currencies']    = $this->site->getAllCurrencies();
            $this->data['warehouse']    = $this->settings_model->getWarehouseByID($id);
            $this->data['price_groups'] = $this->settings_model->getAllPriceGroups();
            $this->data['id']           = $id;
            $this->data['modal_js']     = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_warehouse', $this->data);
        }
    }
    public function warehouse_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteWarehouse($id);
                    }
                    $this->session->set_flashdata('message', lang('warehouses_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('warehouses'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('address'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('city'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $wh = $this->settings_model->getWarehouseByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $wh->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $wh->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $wh->address);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $wh->city);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'warehouses_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_warehouse_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
     public function delete_warehouse($id = null)
    {
        if ($this->settings_model->deleteWarehouse($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('warehouse_deleted')]);
        }
    }
    public function write_index($timezone)
    {
        $template_path = './assets/config_dumps/index.php';
        $output_path   = self;
        $index_file    = file_get_contents($template_path);
        $new           = str_replace('%TIMEZONE%', $timezone, $index_file);
        $handle        = fopen($output_path, 'w+');
        @chmod($output_path, 0777);

        if (is_writable($output_path)) {
            if (fwrite($handle, $new)) {
                @chmod($output_path, 0644);
                return true;
            }
            @chmod($output_path, 0644);
            return false;
        }
        @chmod($output_path, 0644);
        return false;
    }
    //--------------bom-------
    public function edit_bom($id = null)
    {
        $this->bpas->checkPermissions();
        $this->form_validation->set_rules('name', lang("name"), 'required');
        $id_convert_item = 0;
        if ($this->form_validation->run() == true) {
            $warehouse_id        = $_POST['warehouse'];
            // list bom item from
            $cIterm_from_id     = $_POST['bom_from_items_id'];
            $cIterm_from_code   = $_POST['bom_from_items_code'];
            $cIterm_from_name   = $_POST['bom_from_items_name'];
            $cIterm_from_uom    = $_POST['bom_from_items_uom'];
            $cIterm_from_qty    = $_POST['bom_from_items_qty'];
            // list convert item to
            $iterm_to_id        = $_POST['convert_to_items_id'];
            $iterm_to_code      = $_POST['convert_to_items_code'];
            $iterm_to_name      = $_POST['convert_to_items_name'];
            $iterm_to_uom       = $_POST['convert_to_items_uom'];
            $iterm_to_qty       = $_POST['convert_to_items_qty'];
            
            $date = date("Y-m-d H:i:s", strtotime($_POST['date']));
            $data               = array(
                                        'name' => $_POST['name'],
                                        'date' => $date,
                                        'noted' => $_POST['note'],
                                        'created_by' => $this->session->userdata('user_id')
                                    );
            
            $idConvert          = $this->settings_model->updateBom($id, $data);
            $id_convert_item    = $idConvert;
                
            $items = array();
            $i = isset($_POST['bom_from_items_code']) ? sizeof($_POST['bom_from_items_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $bomitem = array(
                            'bom_id'        => $id,
                            'product_id'    => $cIterm_from_id[$r],
                            'product_code'  => $cIterm_from_code[$r],
                            'product_name'  => $cIterm_from_name[$r],
                            'quantity'      => $cIterm_from_qty[$r],
                            'option_id'     => $cIterm_from_uom[$r],
                            'status'        => 'deduct'
                        );
                        
                $pic = $this->settings_model->selectBomItems($id, $cIterm_from_id[$r]);
                if($pic){
                    $this->settings_model->deleteBom_items($id);
                }
                $this->settings_model->updateBom_items($bomitem);
            }
            $j = isset($_POST['convert_to_items_code']) ? sizeof($_POST['convert_to_items_code']) : 0;
            for ($r = 0; $r < $j; $r++) {
                $bomitems = array(
                            'bom_id'        => $id,
                            'product_id'    => $iterm_to_id[$r],
                            'product_code'  => $iterm_to_code[$r],
                            'product_name'  => $iterm_to_name[$r],
                            'quantity'      => $iterm_to_qty[$r],
                            'option_id'     => $iterm_to_uom[$r],
                            'status'        => 'add'
                        );
                $pic = $this->settings_model->selectBomItems($id, $iterm_to_id[$r]);
                if($pic){
                    $this->settings_model->deleteBom_items($id);
                }
                $this->settings_model->updateBom_items($bomitems);
            }
            
            if($id_convert_item != 0){
                $items          = $this->settings_model->getConvertItemsById($id);
                $deduct         = $this->settings_model->getConvertItemsDeduct($id);
                $adds           = $this->settings_model->getConvertItemsAdd($id);
                $each_cost      = 0;
                $total_item     = count($adds);
                $total_fin_qty  = 0;
                $total_fin_cost = 0;
                $total_raw_cost = 0;
                $cost_variant   = 0;
                $qty_variant    = 0;
                
                foreach($items as $item){
                    $option = $this->site->getProductVariantByOptionID($item->option_id);
                    $cost   = 0;
                    $Tcost  = 0;
                    if($item->status == 'deduct'){
                        $cost = $item->tcost?$item->tcost:$item->tprice;
                        if($option){
                            $cost_variant   = ($cost / $item->c_quantity)*$option->qty_unit;
                            $qty_variant    = $item->c_quantity;
                            $total_raw_cost += $cost_variant * $qty_variant;
                            $Tcost = $cost * $option->qty_unit;
                        }else{
                            $total_raw_cost += $cost;
                            $Tcost = $cost;
                        }
                        
                        $this->db->update('bom_items', array('cost' => $cost_variant), array('product_id' => $item->product_id, 'bom_id' => $item->bom_id));
                    }else{
                        $cost = $item->tcost?$item->tcost:$item->tprice;
                        if($option){
                            $total_fin_cost += $cost * $option->qty_unit;
                            $total_fin_qty  += $item->c_quantity * $option->qty_unit;
                        }else{
                            $total_fin_cost += $cost;
                            $total_fin_qty  += $item->c_quantity;
                        }
                        
                    }
                }
                
                //============= Cost AVG =============//    
                foreach($adds as $add){
                    $qty_unit   = 0;
                    $option     = $this->site->getProductVariantByOptionID($add->option_id);
                    
                    if($option){
                        $unit_qty   = $add->c_quantity * $option->qty_unit;
                    }else{
                        $unit_qty   = $add->c_quantity;
                    }
                    
                    $each_cost  = $this->site->calculateCONAVCost($add->product_id, $total_raw_cost, $total_fin_qty, $unit_qty);
                    
                    $this->db->update('bom_items', array('cost' => ($each_cost['cost']/$add->c_quantity)), array('product_id' => $add->product_id, 'bom_id' => $add->bom_id));
                }
                
            }
            
            $this->session->set_flashdata('message', lang("bom_success_update"));
            admin_redirect('system_settings/bom');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['tax_rates'] = $this->site->getAllTaxRates();
        $this->data['all_bom'] = $this->site->getAllBom($id);
        $this->data['top_bom'] = $this->site->getBom_itemsTop($id);
        $this->data['bottom_bom'] = $this->site->getBom_itemsBottom($id);
        $this->data['id'] = $id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Edit_Bom')));
        $meta = array('page_title' => lang('Edit_Bom'), 'bc' => $bc);
        $this->page_construct('bom/edit_bom', $meta, $this->data);
    }
    
    public function delete_bom($id = null)
    {
        $this->bpas->checkPermissions('delete', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->settings_model->deleteBom($id) && $this->settings_model->deleteBom_items($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('bom_deleted')]);
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    
    function bom_convert()
    {
        $this->bpas->checkPermissions();
        $this->form_validation->set_rules('name', lang("name"), 'required');
        $id_convert_item = 0;
        if ($this->form_validation->run() == true) {
            $warehouse_id        = $_POST['warehouse'];
            // list convert item from
            $cIterm_from_id     = $_POST['bom_from_items_id'];
            $cIterm_from_code   = $_POST['bom_from_items_code'];
            $cIterm_from_name   = $_POST['bom_from_items_name'];
            $cIterm_from_uom    = $_POST['bom_from_items_uom'];
            $cIterm_from_qty    = $_POST['bom_from_items_qty'];
            // list convert item to
            $iterm_to_id        = $_POST['convert_to_items_id'];
            $iterm_to_code      = $_POST['convert_to_items_code'];
            $iterm_to_name      = $_POST['convert_to_items_name'];
            $iterm_to_uom      = $_POST['convert_to_items_uom'];
            $iterm_to_qty       = $_POST['convert_to_items_qty'];
            $date = $this->bpas->fld(trim($_POST['date']));
            $data               = array(
                                        'name' => $_POST['name'],
                                        'date' => $date,
                                        'noted' => $_POST['note'],
                                        'created_by' => $this->session->userdata('user_id')
                                    );
            $idConvert          = $this->settings_model->insertBom($data);
            $id_convert_item = $idConvert;
                
            $items = array();
            $i = isset($_POST['bom_from_items_code']) ? sizeof($_POST['bom_from_items_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $products   = $this->site->getProductByID($cIterm_from_id[$r]);
                if(!empty($cIterm_from_uom[$r])){
                    $product_variant        = $this->site->getProductVariantByID($cIterm_from_id[$r], $cIterm_from_uom[$r]);
                }else{
                    $product_variant        = $this->site->getProductVariantByID($cIterm_from_id[$r]);
                    
                }
                $PurchaseItemsQtyBalance    =  $this->site->getPurchaseBalanceQuantity($cIterm_from_id[$r], $warehouse_id);
                if(empty($product_variant)){
                    $unit_qty = 1;
                }else{
                    $unit_qty = ( !empty($product_variant->qty_unit) && $product_variant->qty_unit > 0 ? $product_variant->qty_unit : 1 );
                }
                $PurchaseItemsQtyBalance    = $PurchaseItemsQtyBalance - ($unit_qty  * $cIterm_from_qty[$r]);
                $qtyBalace                  = $product_variant->quantity - $cIterm_from_qty[$r];
                
                $purchase_items_id = 0;
                $pis = $this->site->getPurchasedItems($cIterm_from_id[$r], $warehouse_id, $option_id = NULL);
                foreach ($pis as $pi) {
                    $purchase_items_id = $pi->id;
                    break;
                }

                $clause = array('purchase_id' => NULL, 'product_code' => $cIterm_from_code[$r], 'product_id' => $cIterm_from_id[$r], 'warehouse_id' => $warehouse_id);
                if ($pis) {
                    $this->db->update('purchase_items', array('quantity_balance' => $PurchaseItemsQtyBalance), array('id' => $purchase_items_id));
                } else {
                    $clause['quantity'] = 0;
                    $clause['item_tax'] = 0;
                    $clause['option_id'] = null;
                    $clause['transfer_id'] = null;
                    $clause['product_name'] = $cIterm_from_name[$r];
                    $clause['quantity_balance'] = $PurchaseItemsQtyBalance;
                    $this->db->insert('purchase_items', $clause);
                }
                // UPDATE PRODUCT QUANTITY
                
                if($this->db->update('products', array('quantity' => $PurchaseItemsQtyBalance), array('code' => $cIterm_from_code[$r])))
                {
                    // UPDATE WAREHOUSE_PRODUCT QUANTITY
                    if ($this->site->getWarehouseProducts( $cIterm_from_id[$r], $warehouse_id)) {
                        $this->db->update('warehouses_products', array('quantity' => $PurchaseItemsQtyBalance), array('product_id' => $cIterm_from_id[$r], 'warehouse_id' => $warehouse_id));
                    } else {
                        $this->db->insert('warehouses_products', array('quantity' => $PurchaseItemsQtyBalance, 'product_id' => $cIterm_from_id[$r], 'warehouse_id' => $warehouse_id));
                    }
                    // UPDATE PRODUCT_VARIANT quantity
                    if(!empty($cIterm_from_uom[$r])){
                        $this->db->update('product_variants', array('quantity' => $qtyBalace), array('product_id' => $cIterm_from_id[$r], 'name' => $cIterm_from_uom[$r]));
                    }else{
                        $this->db->update('product_variants', array('quantity' => $qtyBalace), array('product_id' => $cIterm_from_id[$r]));
                    }
                } else {
                    exit('error - product');
                }
                
                //echo '<pre>';print_r($arry);echo '</pre>';exit;           
                $this->db->insert('bpas_bom_items',  array(
                                                        'bom_id' => $idConvert,
                                                        'product_id' => $cIterm_from_id[$r],
                                                        'product_code' => $cIterm_from_code[$r],
                                                        'product_name' => $cIterm_from_name[$r],
                                                        'quantity' => $cIterm_from_qty[$r],
                                                        'status' => 'deduct'));
                                
                //$this->site->syncQuantity(NULL, $purchase_items_id);
                $this->site->syncQuantity(NULL, NULL, NULL, $cIterm_from_id[$r]);
            }
            $j = isset($_POST['convert_to_items_code']) ? sizeof($_POST['convert_to_items_code']) : 0;
            for ($r = 0; $r < $j; $r++) {
                $products   = $this->site->getProductByID($iterm_to_id[$r]);
                if(!empty($cIterm_from_uom[$r])){
                    $product_variant        = $this->site->getProductVariantByID($iterm_to_id[$r], $iterm_to_uom[$r]);
                }else{
                    $product_variant        = $this->site->getProductVariantByID($iterm_to_id[$r]);
                }

                $PurchaseItemsQtyBalance    =  $this->site->getPurchaseBalanceQuantity($iterm_to_id[$r], $warehouse_id);
                $unit_qty = ( !empty($product_variant->qty_unit) && $product_variant->qty_unit > 0 ? $product_variant->qty_unit : 1 );
                $PurchaseItemsQtyBalance    = $PurchaseItemsQtyBalance + ($unit_qty  * $iterm_to_qty[$r]);
                $qtyBalace                  = $product_variant->quantity + $iterm_to_qty[$r];
                
                $purchase_items_id = 0;
                $pis = $this->site->getPurchasedItems($iterm_to_id[$r], $warehouse_id, $option_id = NULL);
                foreach ($pis as $pi) {
                    $purchase_items_id = $pi->id;
                    break;
                }
                $clause = array('purchase_id' => NULL, 'product_code' => $iterm_to_code[$r], 'product_id' => $iterm_to_id[$r], 'warehouse_id' => $warehouse_id);
                if ($pis) {
                    $this->db->update('purchase_items', array('quantity_balance' => $PurchaseItemsQtyBalance), array('id' => $purchase_items_id));
                } else {
                    $clause['quantity'] = 0;
                    $clause['item_tax'] = 0;
                    $clause['option_id'] = null;
                    $clause['transfer_id'] = null;
                    $clause['product_name'] = $iterm_to_name[$r];
                    $clause['quantity_balance'] = $PurchaseItemsQtyBalance;
                    $this->db->insert('purchase_items', $clause);
                }
                // UPDATE PRODUCT QUANTITY
                
                if($this->db->update('products', array('quantity' => $PurchaseItemsQtyBalance), array('code' => $iterm_to_code[$r])))
                {
                    // UPDATE WAREHOUSE_PRODUCT QUANTITY
                    if ($this->site->getWarehouseProducts($iterm_to_id[$r], $warehouse_id)) {
                        $this->db->update('warehouses_products', array('quantity' => $PurchaseItemsQtyBalance), array('product_id' => $iterm_to_id[$r], 'warehouse_id' => $warehouse_id));
                    } else {
                        $this->db->insert('warehouses_products', array('quantity' => $PurchaseItemsQtyBalance, 'product_id' => $iterm_to_id[$r], 'warehouse_id' => $warehouse_id));
                    }
                    // UPDATE PRODUCT_VARIANT quantity
                    if(!empty($cIterm_from_uom[$r])){
                        $this->db->update('product_variants', array('quantity' => $qtyBalace), array('product_id' => $iterm_to_id[$r], 'name' => $iterm_to_uom[$r]));
                    }else{
                        $this->db->update('product_variants', array('quantity' => $qtyBalace), array('product_id' => $iterm_to_id[$r]));
                    }
                } else {
                    exit('error increase product ');
                }
                
                $this->db->insert('bpas_bom_items', array(
                                                        'bom_id' => $idConvert,
                                                        'product_id' => $iterm_to_id[$r],
                                                        'product_code' => $iterm_to_code[$r],
                                                        'product_name' => $iterm_to_name[$r],
                                                        'quantity' => $iterm_to_qty[$r],
                                                        'status' => 'add'));
                
                //$this->site->syncQuantity(NULL, $purchase_items_id);
                $this->site->syncQuantity(NULL, NULL, NULL, $cIterm_from_id[$r]);
            }
            if($id_convert_item != 0){
                $items = $this->settings_model->getConvertItemsById($id_convert_item);
                $deduct = $this->settings_model->getConvertItemsDeduct($id_convert_item);
                $adds = $this->settings_model->getConvertItemsAdd($id_convert_item);
                $each_cost = 0;
                $total_item = count($adds);
                
                foreach($items as $item){
                    if($item->status == 'deduct'){
                        $this->db->update('bom_items', array('cost' => $item->tcost), array('product_id' => $item->product_id, 'bom_id' => $item->bom_id));
                    }else{
                        $each_cost = $deduct->tcost / $total_item;
                        if($this->db->update('bom_items', array('cost' => $each_cost), array('product_id' => $item->product_id, 'bom_id' => $item->bom_id))){
                            
                            //foreach($adds as $add){
                                $total_net_unit_cost = $each_cost / $item->c_quantity;
                                //$total_quantity += $each_cost;
                                //$total_unit_cost += ($pi->unit_cost ? ($pi->unit_cost *  $pi->quantity_balance) : ($pi->net_unit_cost + ($pi->item_tax / $pi->quantity) *  $pi->quantity_balance));
                            //}
                            //$avg_net_unit_cost = $total_net_unit_cost / $total_quantity;
                            //$avg_unit_cost = $total_unit_cost / $total_quantity;

                            //$cost2 = $each_cost * $item->p_cost;
                            
                            //$product_cost = ($total_net_unit_cost + $cost2) / $total_quantity;
                            $this->db->update('products', array('cost' => $total_net_unit_cost), array('id' => $item->product_id));
                        }
                    }
                }
            }
            
            $this->session->set_flashdata('message', lang("item_conitem_convert_success"));
            redirect('system_settings/bom');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['tax_rates'] = $this->site->getAllTaxRates();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('system_settings')));
        $meta = array('page_title' => lang('bom'), 'bc' => $bc);
        $this->page_construct('system_settings/bom', $meta, $this->data);
    }
    
    function bom(){
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('list_bom')));
        $meta = array('page_title' => lang('list_bom'), 'bc' => $bc);
        $this->page_construct('bom/list_bom', $meta, $this->data);
    }
    public function add_floor()
    {
        $this->form_validation->set_rules('name', lang('floor_name'), 'trim|required|is_unique[floors.name]|alpha_numeric_spaces');
    //    $this->form_validation->set_rules('slug', lang('slug'), 'trim|is_unique[floors.slug]|alpha_dash');
        $this->form_validation->set_rules('description', lang('description'), 'trim|required');
       

        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'description' => $this->input->post('description'),
            ];

        } elseif ($this->input->post('add_floor')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addFloor($data)) {
            $this->session->set_flashdata('message', lang('floor_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_floor', $this->data);
        }
    }
        public function frequencies()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('settings')));
        $meta = array('page_title' => lang('frequencies'), 'bc' => $bc);
        $this->page_construct('settings/frequencies', $meta, $this->data);
    }

    public function add_frequency()
    {
        $this->form_validation->set_rules('description', $this->lang->line("description"), 'required|is_unique[frequency.description]');
        $this->form_validation->set_rules('day', $this->lang->line("day"), 'required|numeric');

        if ($this->form_validation->run() == true) {
            $data = array(
                        'description' => $this->input->post('description'),
                        'day' => $this->input->post('day'),
                    );
        }else if($this->input->post('add_frequency')){
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $sid = $this->settings_model->addFrequency($data)) {
            $this->session->set_flashdata('message', lang("frequency_added")." ".$data['description']." ".$data['day']);
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_frequency', $this->data);
        }
    }

    public function edit_frequency($id = false)
    {
        $this->form_validation->set_rules('description', $this->lang->line("description"), 'required');
        $this->form_validation->set_rules('day', $this->lang->line("day"), 'required|numeric');

        if ($this->form_validation->run() == true) {
            $data = array(
                        'description' => $this->input->post('description'),
                        'day' => $this->input->post('day'),
                    );
        }else if($this->input->post('edit_frequency')){
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $sid = $this->settings_model->updateFrequency($id, $data)) {
            $this->session->set_flashdata('message', lang("frequency_updated")." ".$data['description']." ".$data['day']);
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['id'] = $id;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['row'] = $this->settings_model->getFrequencyById($id);
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_frequency', $this->data);
        }
    }

    function delete_frequency($id = NULL)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->settings_model->deleteFrequency($id)) {
            $this->session->set_flashdata('message', lang("frequency_deleted")." ".$id['description']." ".$id['day']);
            redirect("system_settings/frequencies");
        }
    }

    public function getFrequencies()
    {
        $deadline_link = anchor('admin/system_settings/frequency_deadlines/$1', '<i class="fa fa-file-text-o"></i> ' . lang('frequency_deadlines'), ' class="frequency_deadlines"');
        $edit_link = anchor('admin/system_settings/edit_frequency/$1', '<i class="fa fa-edit"></i> ' . lang('edit_frequency'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_frequency") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('system_settings/delete_frequency/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_frequency') . "</a>";
    
        $action = '<div class="text-center"><div class="btn-group text-left">'
                    . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
                    . lang('actions') . ' <span class="caret"></span></button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li>'.$deadline_link.'</li>
                                <li>'.$edit_link.'</li>
                                <li>'.$delete_link . '</li>
                            </ul>
                        </div>
                    </div>';
                    
        $this->load->library('datatables');
        $this->datatables
            ->select("
                    frequency.id as id,
                    frequency.description,
                    frequency.day")
            ->from("frequency");
            
        $this->datatables->add_column("Actions", $action, "id");
        
      echo $this->datatables->generate();
    }

    public function frequency_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteFrequency($id);
                    }
                    $this->session->set_flashdata('message', lang("frequencies_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('frequencies'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('description'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('day'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getFrequencyById($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->description);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->day);
                        $row++;
                    }
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'frequencies_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function import_frequencies()
    {
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
                    for($row=2; $row<=$highestRow; $row++){
                        $description = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
                        $day = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                        $final[] = array(
                          'description'   => $description,
                          'day'         => $day,
                        );
                    }
                }
                $rw = 2;
                foreach ($final as $csv_pr) {
                    $pr_description[] = trim($csv_pr['description']);
                    $pr_day[] = trim($csv_pr['day']);
                    $rw++;
                }
            }
            $ikeys = array('description', 'day');
            $items = array();
            foreach (array_map(null, $pr_description, $pr_day) as $ikey => $value) {
                $items[] = array_combine($ikeys, $value);
            }
        }
        if ($this->form_validation->run() == true && $this->settings_model->addFrequencies($items)) {
            $this->session->set_flashdata('message', lang("frequencies_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'settings/import_frequencies', $this->data);
        }
    }
     public function interest_period()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('settings')));
        $meta = array('page_title' => lang('interest_period'), 'bc' => $bc);
        $this->page_construct('settings/interest_period', $meta, $this->data);
    } 
    public function add_interest_period()
    {
        $this->form_validation->set_rules('description', $this->lang->line("description"), 'required|is_unique[frequency.description]');
        $this->form_validation->set_rules('name', $this->lang->line("name"), 'required|is_unique[interest_period.name]');
        $this->form_validation->set_rules('day', $this->lang->line("day"), 'required|numeric'); 
        if ($this->form_validation->run() == true) {
            $data = array(
                'description' => $this->input->post('description'),
                'day' => $this->input->post('day'),
                'name' => $this->input->post('name'),
            );
        }else if($this->input->post('add_interest_period')){
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $sid = $this->settings_model->addInterest_period($data)) {
            $this->session->set_flashdata('message', lang("interest_period_added")." ".$data['name']." ".$data['day']." ".$data['description']);
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_interest_period', $this->data);
        }
    }

    public function edit_interest_period($id = false)
    {
        $this->form_validation->set_rules('description', $this->lang->line("description"), 'required');
        $this->form_validation->set_rules('name', $this->lang->line("name"), 'required');
        $this->form_validation->set_rules('day', $this->lang->line("day"), 'required|numeric'); 
        if ($this->form_validation->run() == true) {
            $data = array(
                'description' => $this->input->post('description'),
                'day' => $this->input->post('day'),
                'name' => $this->input->post('name'),   
            );
        }else if($this->input->post('edit_interest_period')){
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $sid = $this->settings_model->updateInterest_period($id, $data)) {
            $this->session->set_flashdata('message', lang("interest_period_updated")." ".$data['name']." ".$data['day']." ".$data['description']);
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['id'] = $id;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['row'] = $this->settings_model->getInterest_periodByID($id); 
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_interest_period', $this->data);
        }
    }

    function delete_interest_period($id = NULL)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->settings_model->deleteInterest_period($id)) {
            $this->session->set_flashdata('message', lang("interest_period_deleted")." ".$id['description']." ".$id['day']);
            redirect("system_settings/interest_period");
        }
    }

    public function getInterest_period()
    {
        $deadline_link = anchor('admin/system_settings/interest_period_deadlines/$1', '<i class="fa fa-file-text-o"></i> ' . lang('interest_period_deadlines'), ' class="interest_period_deadlines"');
        $edit_link = anchor('admin/system_settings/edit_interest_period/$1', '<i class="fa fa-edit"></i> ' . lang('edit_interest_period'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_interest_period") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('system_settings/delete_interest_period/$1') . "'>"
        . lang('i_m_sure') . "</a><button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_interest_period') . "</a>"; 
        $action = '<div class="text-center"><div class="btn-group text-left">'
                    . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
                    . lang('actions') . ' <span class="caret"></span></button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li>'.$deadline_link.'</li>
                                <li>'.$edit_link.'</li>
                                <li>'.$delete_link . '</li>
                            </ul>
                        </div>
                    </div>'; 
        $this->load->library('datatables');
        $this->datatables
            ->select("
                    interest_period.id as id,
                    interest_period.name,
                    interest_period.day,
                    interest_period.description")
            ->from("interest_period");
            
        $this->datatables->add_column("Actions", $action, "id");
        
      echo $this->datatables->generate();
    }

    public function interest_period_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteInterest_period($id);
                    }
                    $this->session->set_flashdata('message', lang("interest_period_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('interest_period'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('day'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('description'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getInterest_periodByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->day);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->description);
                        $row++;
                    }
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'interest_period_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function import_interest_period()
    {
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
                    for($row=2; $row<=$highestRow; $row++){
                        $description = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                        $day = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                        $day = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
                        $final[] = array(
                          'description' => $description,
                          'day'         => $day,
                          'name'        => $name,
                        );
                    }
                }
                $rw = 2;
                foreach ($final as $csv_pr) {
                    $pr_description[] = trim($csv_pr['description']);
                    $pr_day[] = trim($csv_pr['day']);
                    $pr_name[] = trim($csv_pr['name']);
                    $rw++;
                }
            }
            $ikeys = array('name', 'day', 'description');
            $items = array();
            foreach (array_map(null, $pr_description, $pr_day, $pr_name) as $ikey => $value) {
                $items[] = array_combine($ikeys, $value);
            }
        }
        if ($this->form_validation->run() == true && $this->settings_model->addMultiInterest_peroid($items)) {
            $this->session->set_flashdata('message', lang("multi_interest_period_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'settings/import_interest_period', $this->data);
        }
    }
    function add_bom(){
        $this->bpas->checkPermissions();
        $this->form_validation->set_rules('name', lang("name"), 'required');
        $id_convert_item = 0;
        if ($this->form_validation->run() == true) {
            //$warehouse_id       = $_POST['warehouse'];
            // list convert item from
            $cIterm_from_id     = $_POST['bom_from_items_id'];
            $cIterm_from_code   = $_POST['bom_from_items_code'];
            $cIterm_from_name   = $_POST['bom_from_items_name'];
            $cIterm_from_uom    = $_POST['bom_from_items_uom'];
            $cIterm_from_qty    = $_POST['bom_from_items_qty'];
            // list convert item to
            $iterm_to_id        = $_POST['convert_to_items_id'];
            $iterm_to_code      = $_POST['convert_to_items_code'];
            $iterm_to_name      = $_POST['convert_to_items_name'];
            $iterm_to_uom       = $_POST['convert_to_items_uom'];
            $iterm_to_qty       = $_POST['convert_to_items_qty'];
            
            $date               = $this->bpas->fld(trim($_POST['date']));
            $data               = array(
                                    'name'          => $_POST['name'],
                                    'date'          => $date,
                                    'noted'         => $_POST['note'],
                                    'created_by'    => $this->session->userdata('user_id')
                                );
            $idConvert          =  $this->settings_model->insertBom($data);
            $id_convert_item    =  $idConvert;
                
            $items = array();
            
            $i = isset($_POST['bom_from_items_code']) ? sizeof($_POST['bom_from_items_code']) : 0;
            for ($r = 0; $r < $i; $r++) {                           
                $this->db->insert('bpas_bom_items',  array(
                                    'bom_id'        => $idConvert,
                                    'product_id'    => $cIterm_from_id[$r],
                                    'product_code'  => $cIterm_from_code[$r],
                                    'product_name'  => $cIterm_from_name[$r],
                                    'quantity'      => $cIterm_from_qty[$r],
                                    'option_id'     => $cIterm_from_uom[$r],
                                    'status'        => 'deduct'
                                ));
            }
            
            $j = isset($_POST['convert_to_items_code']) ? sizeof($_POST['convert_to_items_code']) : 0;
            for ($r = 0; $r < $j; $r++) {
                $this->db->insert('bpas_bom_items', array(
                                    'bom_id'        => $idConvert,
                                    'product_id'    => $iterm_to_id[$r],
                                    'product_code'  => $iterm_to_code[$r],
                                    'product_name'  => $iterm_to_name[$r],
                                    'quantity'      => $iterm_to_qty[$r],
                                    'option_id'     => $iterm_to_uom[$r],
                                    'status'        => 'add'
                                ));
                
            }
            
            if($id_convert_item != 0){
                $items      = $this->settings_model->getConvertItemsById($id_convert_item);
                $deduct     = $this->settings_model->getConvertItemsDeduct($id_convert_item);
                $adds       = $this->settings_model->getConvertItemsAdd($id_convert_item);
                $each_cost      = 0;
                $total_item     = count($adds);
                $total_fin_qty  = 0;
                $total_fin_cost = 0;
                $total_raw_cost = 0;
                $cost_variant   = 0;
                $qty_variant    = 0;
                
                foreach($items as $item){
                    $option = $this->site->getProductVariantByOptionID($item->option_id);
                    $cost = 0;
                    $Tcost = 0;
                    if($item->status == 'deduct'){
                        $cost = $item->tcost?$item->tcost:$item->tprice;
                        if($option){
                            $cost_variant   = ($cost / $item->c_quantity)*$option->qty_unit;
                            $qty_variant    = $item->c_quantity;
                            $total_raw_cost += $cost_variant * $qty_variant;
                            $Tcost = $cost * $option->qty_unit;
                        }else{
                            $total_raw_cost += $cost;
                            $cost_variant   = $cost;
                            $Tcost = $cost;
                        }
                        
                        $this->db->update('bom_items', array('cost' => $cost_variant), array('product_id' => $item->product_id, 'bom_id' => $item->bom_id));
                    }else{
                        $cost = $item->tcost?$item->tcost:$item->tprice;
                        if($option){
                            $total_fin_cost += $cost * $option->qty_unit;
                            $total_fin_qty  += $item->c_quantity * $option->qty_unit;
                        }else{
                            $total_fin_cost += $cost;
                            $total_fin_qty  += $item->c_quantity;
                        }
                        
                    }
                }
                
                //============= Cost AVG =============//    
                foreach($adds as $add){
                    $qty_unit   = 0;
                    $option     = $this->site->getProductVariantByOptionID($add->option_id);
                    
                    if($option){
                        $unit_qty   = $add->c_quantity * $option->qty_unit;
                    }else{
                        $unit_qty   = $add->c_quantity;
                    }
                    //echo $total_raw_cost .'=='.$total_fin_qty .'=='.$unit_qty;
                    $each_cost  = $this->site->calculateCONAVCost($add->product_id, $total_raw_cost, $total_fin_qty, $unit_qty);
                    
                    $this->db->update('bom_items', array('cost' => ($each_cost['cost']/$add->c_quantity)), array('product_id' => $add->product_id, 'bom_id' => $add->bom_id));
                }
            }
            
            $this->session->set_flashdata('message', lang("bom_added"));
            admin_redirect('system_settings/bom');
        }
        $this->data['error']        = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['warehouses']   = $this->site->getAllWarehouses();
        $this->data['tax_rates']    = $this->site->getAllTaxRates();
        $bc = array(array('link'    => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('system_settings')));
        $meta = array('page_title'  => lang('bom'), 'bc' => $bc);
        $this->page_construct('bom/bom', $meta, $this->data);
    }

    function suggestions()
    {
        $term = $this->input->get('term', TRUE);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }
        $rows = $this->settings_model->getProductNames($term);
        if ($rows) {
            $uom = array();
            foreach ($rows as $row) {
                $options = $this->products_model->getProductOptions($row->id);
                
                $pr[] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'uom' => $uom, 
                    'code' => $row->code, 'name' => $row->name, 
                    'price' => $row->price,
                    'unit' => $row->unit,
                    'qty' => 1, 'cost' => $row->cost, 'options' => $options );
            }
            
            echo json_encode($pr);
        } else {
            echo json_encode(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    public function bom_note($id = null)
    {
        $bom = $this->settings_model->getBOmByIDs($id);
        foreach($bom as $b){
            $this->data['user'] = $this->site->getUser($b['created_by']);
        }
        $this->data['bom'] = $bom;
        $this->data['page_title'] = $this->lang->line("expense_note");
        $this->load->view($this->theme . 'bom/bom_note', $this->data);
    }
    
    public function getListBom()
    {
        $this->bpas->checkPermissions();

        $detail_link = anchor('admin/system_settings/bom_note/$1', '<i class="fa fa-file-text-o"></i> ' . lang('product_analysis'), 'data-toggle="modal" data-target="#myModal2"');
        $edit_link = anchor('admin/system_settings/edit_bom/$1', '<i class="fa fa-edit"></i> ' . lang('edit_bom'));
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_bom") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_bom/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_bom') . "</a>";
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

        $this->datatables
            ->select($this->db->dbprefix('bom') . ".id as id,
                    ".$this->db->dbprefix('bom').".date AS Date, 
                    ".$this->db->dbprefix('bom').".name AS Name, 
                    SUM(".$this->db->dbprefix('bom_items').".quantity) AS Quantity, 
                    ".$this->db->dbprefix('bom').".noted AS Note, 
                    " . $this->db->dbprefix('users') . ".username", false)
            ->from('bom')
            ->join('users', 'users.id=bom.created_by', 'left')
            ->join('bom_items', 'bom_items.bom_id = bom.id')
            ->where('bom_items.status','add')
            ->group_by('bom_items.bom_id');
        if (!$this->Owner && !$this->Admin) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
        //$this->datatables->edit_column("attachment", $attachment_link, "attachment");
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
    
    public function expense_actions()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        
        if ($this->form_validation->run() == true) {
            
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteBom($id);
                        $this->settings_model->deleteBom_items($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("expenses_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                
                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('Bom'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('quantity'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('cost'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('noted'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('created_by'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $bom = $this->settings_model->getBomByID($id);
                        $user = $this->site->getUser($bom->created_by);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($bom->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $bom->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->formatMoneyPurchase($bom->qty));
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatMoneyPurchase($bom->cost));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $bom->noted);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $user->first_name . ' ' . $user->last_name);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(35);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'Bom_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php";
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
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_expense_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    
    }
    
    public function updateRoom()
    {
        $id = $this->input->post('id_suspend');
        $data = array('floor' => $this->input->post('floor'),
                      'name' => $this->input->post('name'),
                      'ppl_number' => $this->input->post('people'),
                      'description' => $this->input->post('description'),
                      'inactive' => $this->input->post('inactive'),
                      'warehouse_id' => $this->input->post('warehouse')
                    );
        //$this->bpas->print_arrays($data);
        $this->settings_model->updateRooms($id, $data);
        $this->session->set_flashdata('message', $this->lang->line("accound_updated"));
        redirect('bom/suspend');    
    }

    // //-------end bom----------
    // function promotion()
    // {
    //     $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
    //     $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('promotion')));
    //     $meta = array('page_title' => lang('promotion'), 'bc' => $bc);
    //     $this->page_construct('settings/promotion', $meta, $this->data);
    // }
    // function getPromotion()
    // {

    //     $this->load->library('datatables');
    //     $this->datatables
    //         ->select("id, description,warehouse_id,start_date,end_date")
    //         ->from("promotions")
    //         ->add_column("Actions", "<center><a href='" . admin_url('system_settings/edit_promotion/$1') . "' class='tip' title='" . lang("edit_promotion") . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_promotion") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_promotion/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></center>", "id");
    //     //->unset_column('id');

    //     echo $this->datatables->generate();
    // }
    // function add_promotion()
    // {
        
    //     $this->form_validation->set_rules('description', lang("description"), 'trim|is_unique[promotions.description]|required');
    //     $this->form_validation->set_rules('warehouse', lang("warehouse"), 'trim|required');
    //     $this->form_validation->set_rules('start_date', lang("start_date"), 'trim|required');
    //     $this->form_validation->set_rules('end_date', lang("end_date"), 'trim|required');
    //     if ($this->form_validation->run() == true) {    
    //         $data = array(
    //             'description' => $this->input->post('description'),
    //             'warehouse_id' => $this->input->post('warehouse'),
    //             'start_date'  => $this->input->post('start_date') ? $this->bpas->fsd(trim($this->input->post('start_date'))) : null,
    //             'end_date'    => $this->input->post('end_date') ? $this->bpas->fsd(trim($this->input->post('end_date'))) : null,
    //         );
    //         $cate_id = $this->input->post('arr_cate');
    //         $discount = $this->input->post('percent_tag');
           
            
    //         for($i=0;$i<count($cate_id);$i++)
    //         {
    //             $categories[]=array('category_id'=>$cate_id[$i],'discount'=>$discount[$i]);
    //         }
    //         //$this->bpas->print_arrays($data,$categories);
            
    //     } elseif ($this->input->post('add_promotion')) {
    //         $this->session->set_flashdata('error', validation_errors());
    //         admin_redirect("system_settings/promotion");
    //     }

    //     if ($this->form_validation->run() == true && $this->settings_model->addPromotion($data,$categories)) {
    //         $this->session->set_flashdata('message', lang("promotion_added"));
    //         admin_redirect("system_settings/promotion");
    //     } else {
    //         $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
    //         $this->data['categories'] = $this->site->getAllCategoriesMakeup();
    //         $this->data['warehouses'] = $this->site->getAllWarehouses();
    //         $this->data['modal_js'] = $this->site->modal_js();
    //         $this->load->view($this->theme . 'settings/add_promotion', $this->data);
    //     }
    // }
    public function delete_floor($id = null)
    {
        if ($this->settings_model->floorHasRoom($id)) {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('floor_has_rooms')]);
        }

        if ($this->settings_model->deleteFloor($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('floor_deleted')]);
        }
    }
     public function edit_floor($id = null)
    {
        $this->form_validation->set_rules('name', lang('floor_name'), 'trim|required|alpha_numeric_spaces');
        $floor_details = $this->site->getfloorByID($id);
        if ($this->input->post('name') != $floor_details->name) {
            $this->form_validation->set_rules('name', lang('brand_name'), 'required|is_unique[floors.name]');
        }
        $this->form_validation->set_rules('description', lang('description'), 'trim|required');
        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'description' => $this->input->post('description'),
            ];

            
        } elseif ($this->input->post('edit_floor')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/floors');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateFloor($id, $data)) {
            $this->session->set_flashdata('message', lang('floor_updated'));
            admin_redirect('system_settings/floors');
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['floor']    = $floor_details;
            $this->load->view($this->theme . 'settings/edit_floor', $this->data);
        }
    }
    // function edit_promotion($id = NULL)
    // {

    //     $this->form_validation->set_rules('description', lang("description"), 'trim|required');
    //     $this->form_validation->set_rules('warehouse', lang("warehouse"), 'trim|required');
    //     $this->form_validation->set_rules('start_date', lang("start_date"), 'trim|required');
    //     $this->form_validation->set_rules('end_date', lang("end_date"), 'trim|required');
    //     $promotions = $this->settings_model->getPromotion($id);
    //     if ($this->input->post('promotions') != $promotions->description) {
    //         $this->form_validation->set_rules('promotions', lang("promotions"), 'is_unique[promotions.description]');
    //     }
    //     if ($this->form_validation->run() == true) {

    //         $data = array(
    //             'description' => $this->input->post('description'),
    //             'warehouse_id' => $this->input->post('warehouse'),
    //             'start_date'  => $this->input->post('start_date') ? $this->bpas->fsd(trim($this->input->post('start_date'))) : null,
    //             'end_date'    => $this->input->post('end_date') ? $this->bpas->fsd(trim($this->input->post('end_date'))) : null,
    //         );
            
    //         $cate_id = $this->input->post('arr_cate');
    //         $percent = $this->input->post('percent_tag');
            
    //         for($i=0;$i<count($cate_id);$i++)
    //         {
    //             $categories[]=array('category_id'=>$cate_id[$i],'discount'=>$percent[$i]);
    //         }
            
            
    //     } elseif ($this->input->post('edit_promotion')) {
    //         $this->session->set_flashdata('error', validation_errors());
    //         admin_redirect("system_settings/promotion");
    //     }

    //     if ($this->form_validation->run() == true && $this->settings_model->updatePromotion($id, $data,$categories)) {
    //         $this->session->set_flashdata('message', lang("customer_group_updated"));
    //         admin_redirect("system_settings/promotion");
    //     } else {
    //         $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

    //         $this->data['promotions'] = $this->settings_model->getPromotion($id);
    //         $this->data['id'] = $id;
    //         $this->data['cate_id']    = $this->settings_model->Old_promotions($id);
    //         $this->data['warehouses'] = $this->site->getAllWarehouses();
    //         $this->data['categories'] = $this->site->getAllCategoriesMakeup();
    //         $this->data['modal_js']   = $this->site->modal_js();
    //         $this->load->view($this->theme . 'settings/edit_promotion', $this->data);
    //     }
    // }
    // function delete_promotion($id = NULL)
    // {
    //     if ($this->settings_model->deletePromotion($id)) {
    //         $this->session->set_flashdata('message', lang("promotion_deleted"));
    //                 redirect($_SERVER["HTTP_REFERER"]);
    //     }
    // }
    function payment_term()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('payment_term')));
        $meta = array('page_title' => lang('payment_term'), 'bc' => $bc);
        $this->page_construct('settings/payment_term', $meta, $this->data);
    }
    
    function getPaymentTerm(){
        $this->load->library('datatables');
        $this->datatables
            ->select("id, description, due_day,due_day_for_discount, discount")
            ->from("payment_term")
            ->order_by('id', 'asc')
            ->add_column("Actions", "<center><a href='" . admin_url('system_settings/edit_payment_term/$1') . "' class='tip' title='" . lang("edit_payment_term") . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_payment_term") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_payment_term/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></center>", "id");
        //->unset_column('id');

        echo $this->datatables->generate();
    }
      public function getTrash($warehouse_id = null)
    {
         $this->bpas->checkPermissions('index');
        if ($warehouse_id) {
            $warehouse_ids = explode('-', $warehouse_id);
        }
        $user_query         = $this->input->get('user') ? $this->input->get('user') : null;
        $customer     = $this->input->get('customer') ? $this->input->get('customer') : null;
        $biller       = $this->input->get('biller') ? $this->input->get('biller') : null;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $saleman = $this->input->get('saleman') ? $this->input->get('saleman') : null;
        $product_id      = $this->input->get('product_id') ? $this->input->get('product_id') : null;
        $warehouse    = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $delivered_by    = $this->input->get('delivered_by') ? $this->input->get('delivered_by') : null;
        $payment_status = $this->input->get('payment_status') ? $this->input->get('payment_status') : NULL;
        $start_date   = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date     = $this->input->get('end_date') ? $this->input->get('end_date') : null;
        if ($start_date) {
            $start_date = $this->bpas->fld($start_date . ' 00:00:00');
            $end_date   = $this->bpas->fld($end_date . ' 23:59:00');
        }
        $detail_link       = anchor('admin/sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        $return_detail_link       = anchor('admin/sales/return_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('return_sale') . ' ' . lang('details'));
        $duplicate_link    = anchor('admin/sales/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link     = anchor('admin/sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_payment_link  = anchor('admin/sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $packagink_link    = anchor('admin/sales/packaging/$1', '<i class="fa fa-archive"></i> ' . lang('packaging'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_delivery_link = anchor('admin/sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $email_link        = anchor('admin/sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link         = anchor('admin/sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $pdf_link          = anchor('admin/sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link       = anchor('admin/sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        $add_warranty_link  = anchor('admin/sales/add_maintenance/$1', '<i class="fa fa-money"></i> ' . lang('add_maintenance'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        // if($this->settings->hide != 0) {
        $delete_link       = "<a href='#' class='po' title='<b>" . lang('delete_sale') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_sale') . '</a>';
        // }
        // else{
        //      $delete_link       = "<a href='#' class='po' title='<b>" . lang('remove_sale') . "</b>' data-content=\"<p>"
        //     . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales/delete/$1') . "'>"
        //     . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        //     . lang('remove_sale') . '</a>';
        // }
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
           
            <li>' . $pdf_link . '</li>
        
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';
        $ds = "( SELECT sale_id,delivered_by, status
            from {$this->db->dbprefix('deliveries')} ) FSI";

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('sales')}.id as id, 
            DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date,
            companies.name as delivered_by,
            reference_no, 
            biller, 
            {$this->db->dbprefix('sales')}.customer, 
            sale_status, 
            grand_total, 
            paid, 
            (grand_total-paid) as balance,
            payment_status, 
            FSI.status as delivery_status, 
             return_id")
            ->join('projects', 'sales.project_id = projects.project_id', 'left')
            ->join('deliveries', 'sales.id = deliveries.sale_id', 'left')
            //  ->join('companies', 'sales.customer_id = companies.id', 'left')
            ->join('companies', 'deliveries.delivered_by = companies.id', 'left')
            ->join($ds, 'FSI.sale_id=sales.id', 'left')
            ->from('sales');
        
        // if(){
            $this->datatables->where('sales.hide', 0);
        // }
        if ($warehouse_id) {
            $this->datatables->where('sales.warehouse_id', $warehouse_id);
        }
        //  if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
        //     $user         = $this->site->getUser();
        //     $warehouse_id = $user->warehouse_id;
        // }
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("FIND_IN_SET(bpas_sales.warehouse_id, '" . $this->session->userdata('warehouse_id') . "')");
            $this->datatables->or_where("FIND_IN_SET(" . $this->session->userdata('user_id') . ", bpas_projects.customer_id)");
        }
        if ($this->Customer) {
            $this->datatables->where('projects.customer_id', $this->session->userdata('user_id'));
        }
        if ($this->input->get('shop') == 'yes') {
            $this->datatables->where('shop', 1);
        } elseif ($this->input->get('shop') == 'no') {
            $this->datatables->where('shop !=', 1);
        }

        if ($this->input->get('delivery') == 'no') {
            $this->datatables->join('deliveries', 'deliveries.sale_id=sales.id', 'left')
            ->where('sales.sale_status', 'completed')->where('sales.payment_status', 'paid')
            ->where("({$this->db->dbprefix('deliveries')}.status != 'delivered' OR {$this->db->dbprefix('deliveries')}.status IS NULL)", null);
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

        if ($saleman) {
            $this->datatables->where('sales.saleman_by', $saleman);
        }

        if ($warehouse) {
            $this->datatables->where('sales.warehouse_id', $warehouse);
        }
        if ($delivered_by) {
            $this->datatables->where('deliveries.delivered_by', $delivered_by);
        }
        // if ($start_date ) {
        //  $pp .= " AND p.date >= '{$start_date}' AND p.date < '{$end_date}' ";
        // $this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . ' 00:00:00" and "' . $end_date . '23:59:00"');
        // var_dump($start_date);
        // $this->datatables->where("sales.date>='{$start_date}'AND sales.date < '{$end_date}'");

        // }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('sales') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        // $this->datatables->where('pos !=', 1); // ->where('sale_status !=', 'returned');

        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
     public function trash_bin(){
     
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('trash')]];
        $meta                = ['page_title' => lang('trash'), 'bc' => $bc];
        $this->page_construct('settings/trash', $meta, $this->data);
    }
    public function edit_payment_term($id)
    {
        $this->form_validation->set_rules('description', lang("description"), 'trim|required');
        $config = array(                    
                    array(
                        'field' => 'due_day',
                        'label' => lang("due_day"),
                        'rules' => 'required',
                        ),
                    array(
                        'field'=>'due_day_for_discount',
                        'label'=>lang('due_day_for_discount'),
                        'rules'=>'numeric'
                        ),
                    );
        $this->form_validation->set_rules($config);
        if ($this->form_validation->run() == true) {
            $data = array(
                'description'  => $this->input->post('description'),
                'due_day' => $this->input->post('due_day'),
                'due_day_for_discount'=> $this->input->post('due_day_for_discount'),
                'discount'     => $this->input->post('discount') ? $this->input->post('discount'): '0'
            );
            //$this->bpas->print_arrays($data);
        } elseif ($this->input->post('add_payment_term')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->settings_model->updatePaymentTerm($id, $data)) {
            $this->session->set_flashdata('message', lang("payment_term_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['id'] = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['page_title'] = lang("new_payment_term");
            $this->data['data'] = $this->settings_model->getPaymentTermById($id);
            $this->load->view($this->theme . 'settings/edit_payment', $this->data);
        }
    }
    
    public function add_payment_term()
    {
        $this->form_validation->set_rules('description', lang("description"), 'trim|required');
        $config = array(                    
                    array(
                        'field' => 'due_day',
                        'label' => lang("due_day"),
                        'rules' => 'numeric',
                        ),
                    array(
                        'field'=>'due_day_for_discount',
                        'label'=>lang('due_day_for_discount'),
                        'rules'=>'numeric'
                        ),
                    );
        $this->form_validation->set_rules($config);
        if ($this->form_validation->run() == true) {
            $data = array(
                'description'  => $this->input->post('description'),
                'due_day' => $this->input->post('due_day'),
                'due_day_for_discount'=> $this->input->post('due_day_for_discount'),
                'discount'     => $this->input->post('discount') ? $this->input->post('discount'): '0'
            );
            //$this->bpas->print_arrays($data);
        } elseif ($this->input->post('add_payment_term')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addPaymentTerm($data)) {
            $this->session->set_flashdata('message', lang("payment_term_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['page_title'] = lang("new_payment_term");
            $this->load->view($this->theme . 'settings/add_payment', $this->data);
        }
    }
    
    public function delete_payment_term($id)
    {
       
            //$this->session->set_flashdata('message', lang("payment_term_deleted");
            if($this->db->delete('payment_term', array('id' => $id))){
                $this->bpas->send_json(['error' => 0, 'msg' => lang('payment_term_deleted')]);
                redirect($_SERVER["HTTP_REFERER"]);
            }
    }
    
    function  payment_term_action()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
         if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deletepayment_term($id);
                    }
                    $this->session->set_flashdata('message', lang("payment_term deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('Payment Term'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('Description'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('Due Days'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('Due Days for Discount '));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('Discount'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $payment_term = $this->site->getPamentTermbyID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $payment_term->description);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $payment_term->due_day);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $payment_term->due_day_for_discount);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $payment_term->discount);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'payment_term_' . date('Y_m_d_H_i_s');
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

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            }else {
                $this->session->set_flashdata('error', lang("no_tax_rate_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
  
    
    public function getOptions()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('id, name')
            ->from('options')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/edit_option/$1') . "' class='tip' title='" . lang('edit_option') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_option') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_option/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        //->unset_column('id');

        echo $this->datatables->generate();
    }
    public function add_option()
    {
        $this->form_validation->set_rules('name', lang('name'), 'trim|is_unique[variants.name]|required');

        if ($this->form_validation->run() == true) {
            $data = ['name' => $this->input->post('name')];
        } elseif ($this->input->post('add_option')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/options');
        }

        if ($this->form_validation->run() == true && $this->settings_model->addOption($data)) {
            $this->session->set_flashdata('message', lang('option_added'));
            admin_redirect('system_settings/options');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_option', $this->data);
        }
    }
    public function edit_option($id = null)
    {
        $this->form_validation->set_rules('name', lang('name'), 'trim|required');
        $tax_details = $this->settings_model->getOptionyID($id);
        if ($this->input->post('name') != $tax_details->name) {
            $this->form_validation->set_rules('name', lang('name'), 'required|is_unique[options.name]');
        }

        if ($this->form_validation->run() == true) {
            $data = ['name' => $this->input->post('name')];
        } elseif ($this->input->post('edit_option')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/options');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateOption($id, $data)) {
            $this->session->set_flashdata('message', lang('option_updated'));
            admin_redirect('system_settings/options');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['variant']  = $tax_details;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_option', $this->data);
        }
    }
    public function delete_option($id = null)
    {
        if ($this->settings_model->deleteOption($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('option_deleted')]);
        }
    }
    public function menu($module=null)
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $this->db
            ->select("{$this->db->dbprefix('menu')}.*,
                c.name as parent", false)
            ->from('menu')
            ->join('menu c', 'c.id=menu.parent_id', 'left')
            ->group_by('menu.id');
        if($module){
            $this->db->where('menu.module',$module);
        }
        $q = $this->db->get();
        $data = $q->result();
        $this->data['menus'] = $data;
        $this->data['modules'] = $this->site->getAllModules();
        $this->data['active_module'] = isset($module) ? $module :'';
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('categories')]];
        $meta                = ['page_title' => lang('menu'), 'bc' => $bc];
        $this->page_construct('settings/menu', $meta, $this->data);

    }
    //--------------------------- stock types---------------------
    public function stocktypes()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('stocktypes')]];
        $meta                = ['page_title' => lang('stocktypes'), 'bc' => $bc];
        $this->page_construct('settings/stocktypes', $meta, $this->data);
    }
    public function getStockTypes()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('id, name, description')
            ->from('stock_type')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/edit_stocktype/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_stocktype') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_stocktype') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_stocktype/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        echo $this->datatables->generate();
    }
    public function edit_stocktype($id = null)
    {
        $this->form_validation->set_rules('name', lang('stock_name'), 'trim|required|alpha_numeric_spaces');
        $Stocktype_details = $this->site->getStockTypeByID($id);
        $this->form_validation->set_rules('description', lang('description'));
        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'description' => $this->input->post('description'),
            ];
        } elseif ($this->input->post('edit_stocktype')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/edit_stocktype');
        }
        if ($this->form_validation->run() == true && $this->settings_model->updateStockType($id, $data)) {
            $this->session->set_flashdata('message', lang('stocktype_updated'));
            admin_redirect('system_settings/stocktypes');
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['stocktype']    = $Stocktype_details;
            $this->load->view($this->theme . 'settings/edit_stocktype', $this->data);
        }
    }
    public function delete_stocktype($id = null)
    {
        if ($this->settings_model->stocktypeHasProduct($id)) {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('stocktype_has_rooms')]);
        }

        if ($this->settings_model->deleteStocktype($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('stocktype_deleted')]);
        }
    }
    public function add_stocktype()
    {
        $this->form_validation->set_rules('name', lang('brand_name'), 'trim|required|is_unique[stock_type.name]|alpha_numeric_spaces');
        $this->form_validation->set_rules('description', lang('description'));
        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'description' => $this->input->post('description'),
            ];
        } elseif ($this->input->post('add_stocktype')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->settings_model->addStockType($data)) {
            $this->session->set_flashdata('message', lang('stocktype_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_stocktype', $this->data);
        }
    }
    public function stocktype_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                         if ($this->settings_model->stocktypeHasProduct($id)) {
                             $this->session->set_flashdata('error', lang('stocktype_has_rooms'));
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                        $this->settings_model->deleteStocktype($id);
                    }
                    $this->session->set_flashdata('message', lang('stocktype_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }
                 if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('stock_type'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('description'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $floor = $this->site->getStockTypeByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $floor->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $floor->description);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'stocktype_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_record_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
  
    //-------------------------- end stock type ---------------------
    public function getMenus()
    {
        $print_barcode = '';

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('menu')}.id as id, 
                {$this->db->dbprefix('menu')}.image, 
                {$this->db->dbprefix('menu')}.name, 
                {$this->db->dbprefix('menu')}.slug, 
                {$this->db->dbprefix('menu')}.module,
                c.name as parent", false)
            ->from('menu')
            ->join('menu c', 'c.id=menu.parent_id', 'left')
            ->group_by('menu.id')
            ->add_column('Actions', '<div class="text-center">' . $print_barcode . " 
                    <a href='" . admin_url('system_settings/edit_menu/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_menu') . "'><i class=\"fa fa-edit\"></i></a> 
                    <a href='#' class='tip po' title='<b>" . lang('delete_menu') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_menu/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }
    public function add_menu()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('name', lang('name'), 'required|min_length[3]');
        $this->form_validation->set_rules('userfile', lang('image'), 'xss_clean');
        $this->form_validation->set_rules('slug', lang('slug'), 'trim');
        
        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'slug'        => $this->input->post('slug'),
                'parent_id'   => $this->input->post('parent'),
                'selected_name'        => $this->input->post('selected_name'),
                'permission' => $this->input->post('permission'),
                'module' => $this->input->post('module'),
                'icon' => $this->input->post('favicon'),
                'status' => $this->input->post('status'),
                'is_modal' => $this->input->post('is_modal'),
                'order_number' => $this->input->post('order')   
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
        } elseif ($this->input->post('add_menu')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addMenu($data)) {
            $this->session->set_flashdata('message', lang('menu_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']      = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['menus'] = $this->settings_model->getParentMenus();
            $this->data['modules'] = $this->site->getAllModules();
            $this->data['modal_js']   = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_menu', $this->data);
        }
    }
    public function edit_menu($id = null)
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('name', lang('name'), 'required|min_length[3]');
        $this->form_validation->set_rules('userfile', lang('image'), 'xss_clean');
        $this->form_validation->set_rules('slug', lang('slug'), 'trim');

        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'slug'        => $this->input->post('slug'),
                'parent_id'   => $this->input->post('parent'),
                'selected_name'        => $this->input->post('selected_name'),
                'permission' => $this->input->post('permission'),
                'module' => $this->input->post('module'),
                'icon' => $this->input->post('favicon'),
                'status' => $this->input->post('status'),
                'is_modal' => $this->input->post('is_modal'),
                'order_number' => $this->input->post('order')   
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
        } elseif ($this->input->post('edit_menu')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/menu');
        }

        if ($this->form_validation->run() == true && $this->settings_model->UpdateMenu($id, $data)) {
            $this->session->set_flashdata('message', lang('menu_updated'));
            admin_redirect('system_settings/menu');
        } else {
            $this->data['error']      = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['menu']   = $this->settings_model->getMenuByID($id);
            $this->data['menus'] = $this->settings_model->getParentMenus();
            $this->data['modules'] = $this->site->getAllModules();
            $this->data['modal_js']   = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_menu', $this->data);
        }
    }
    public function delete_menu($id = null)
    {
        if ($this->site->getSubMenus($id)) {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('menu_has_submenu')]);
        }

        if ($this->settings_model->deleteMenu($id)) {
            admin_redirect('system_settings/menu');
            $this->bpas->send_json(['error' => 0, 'msg' => lang('menu_deleted')]);

        }
    }
    public function zones(){
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('zones')]];
        $meta = ['page_title' => lang('zones'), 'bc' => $bc];
        $this->page_construct('settings/zones', $meta, $this->data);
    }
    public function getZones()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('zones')}.id as id, 
                {$this->db->dbprefix('zones')}.zone_code, 
                {$this->db->dbprefix('zones')}.zone_name, 
                {$this->db->dbprefix('custom_field')}.name as zonegroup,
                cities.zone_name as city, districts.zone_name as district, commune.zone_name as commune
                ", false)
            ->from('zones')
            ->join('zones z', 'z.id=zones.parent_id', 'left')
            ->join('custom_field', 'custom_field.id=zones.zone_group_id', 'left')
            ->join("(SELECT id,zone_name FROM ".$this->db->dbprefix('zones')." WHERE IFNULL(city_id,0) = 0) as cities","cities.id = zones.city_id","left")
            ->join("(SELECT id,zone_name FROM ".$this->db->dbprefix('zones')." WHERE city_id > 0 AND IFNULL(district_id,0) = 0) as districts","districts.id = zones.district_id","left")
            ->join("(SELECT id,zone_name FROM ".$this->db->dbprefix('zones')." WHERE district_id > 0 AND IFNULL(commune_id,0) = 0) as commune","commune.id = zones.commune_id","left")

            ->group_by('zones.id')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/edit_zone/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_zone') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_zone') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_zone/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }
    public function get_district(){
        $city_id = $this->input->get('city_id');
        $districts = $this->site->getDistricts($city_id);
        echo json_encode($districts);
    }
    public function get_commune(){
        $district_id = $this->input->get('district_id');
        $communes = $this->site->getCommunes($district_id);
        echo json_encode($communes);
    }
    public function zone_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteZone($id);
                    }
                    $this->session->set_flashdata('message', lang('zones_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('zones'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('zone_code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('zone_name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('city'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('district'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('commune'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $zone = $this->site->getZoneByID($id);
                        $parent_zone = '';
                        if ($zone->parent_id) {
                            $pz = $this->site->getZoneByID($zone->parent_id);
                            $parent_zone = $pz->zone_name;
                        }
                        $area = $this->settings_model->getZoneByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $zone->zone_code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $zone->zone_name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $area->city);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $area->district);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $area->commune);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'zones_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_record_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function add_zone()
    {
        $this->form_validation->set_rules('zone_code', lang('zone_code'), 'trim|is_unique[zones.zone_code]');
        $this->form_validation->set_rules('zone_name', lang('zone_name'), 'trim|required');
        if ($this->form_validation->run() == true) {
            $data = [
                'zone_code'            => $this->input->post('zone_code'),
                'zone_name'            => $this->input->post('zone_name'),
                'parent_id'            => $this->input->post('parent'),
                'zone_group_id'        => $this->input->post('zone_group'),
                'city_id' => ($this->input->post('city_id') ? $this->input->post('city_id') : 0),
                'district_id' => ($this->input->post('district_id') ? $this->input->post('district_id') : 0),
                'commune_id' => ($this->input->post('commune_id') ? $this->input->post('commune_id') : 0)
            ];
        } elseif ($this->input->post('add_zone')) {
            $this->session->set_flashdata('error', validation_errors());
           redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addZone($data)) {
            $this->session->set_flashdata('message', lang('zone_added'));
           redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']      = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js']   = $this->site->modal_js();
            $this->data['zones']      = $this->settings_model->getAllZones();
            $this->data['cities'] = $this->site->getCities();
            $this->load->view($this->theme . 'settings/add_zone', $this->data);
        }
    }
    public function edit_zone($id = null)
    {
        $this->form_validation->set_rules('zone_code', lang('zone_code'), 'trim');
        $this->form_validation->set_rules('zone_name', lang('zone_name'), 'trim|required');
        $zone_details = $this->site->getZoneByID($id);
        if ($this->form_validation->run() == true) {
            $data = [
                'zone_code'            => $this->input->post('zone_code'),
                'zone_name'            => $this->input->post('zone_name'),
                'parent_id'            => $this->input->post('parent'),
                'zone_group_id'        => $this->input->post('zone_group'),
                'city_id' => ($this->input->post('city_id') ? $this->input->post('city_id') : 0),
                'district_id' => ($this->input->post('district_id') ? $this->input->post('district_id') : 0),
                'commune_id' => ($this->input->post('commune_id') ? $this->input->post('commune_id') : 0)
            ];
        } elseif ($this->input->post('edit_zone')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/zones');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updateZone($id, $data)) {
            $this->session->set_flashdata('message', lang('zone_updated'));
            admin_redirect('system_settings/zones');
        } else {
            $this->data['error']      = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js']   = $this->site->modal_js();
            $this->data['zones']      = $this->settings_model->getAllZones();
            $this->data['zone']       = $zone_details;

            $area = $this->site->getZoneByID($id);
            $this->data['id'] = $id;
            $this->data['area'] = $area;
            $this->data['cities'] = $this->site->getCities();
            $this->data['districts'] = $area->city_id > 0 ? $this->site->getDistricts($area->city_id) : false;
            $this->data['communes'] = $area->district_id > 0 ? $this->site->getCommunes($area->district_id) : false;

            $this->load->view($this->theme . 'settings/edit_zone', $this->data);
        }
    }
    public function delete_zone($id = null)
    {
        if ($this->settings_model->deleteZone($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('zone_deleted')]);
        }
    }
    function sale_targets()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('sale_targets')]];
        $meta = ['page_title' => lang('sale_targets'), 'bc' => $bc];
        $this->page_construct('settings/sale_targets', $meta, $this->data);
    }
    function getSaleTargets()
    {
        $this->load->library('datatables');
        $this->datatables->select("
                {$this->db->dbprefix('sale_targets')}.id as id, 
                {$this->db->dbprefix('sale_targets')}.start_date, {$this->db->dbprefix('sale_targets')}.end_date, 
                IF({$this->db->dbprefix('companies')}.company != '-', CONCAT({$this->db->dbprefix('companies')}.company, '/', {$this->db->dbprefix('companies')}.name), {$this->db->dbprefix('companies')}.name) AS biller, 
                CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) AS staff, amount, 
                {$this->db->dbprefix('sale_targets')}.multi_zone, description", false)
            ->from("sale_targets")
            ->join("users", 'users.id=sale_targets.staff_id')
            ->join("companies", 'companies.id=sale_targets.biller_id')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/edit_sale_target/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_sale_target') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_sale_target') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_sale_target/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }
    function add_saleTarget()
    {
        $this->form_validation->set_rules('biller', lang("biller"), 'trim|required');
        $this->form_validation->set_rules('start_date', lang("start_date"), 'trim|required');
        $this->form_validation->set_rules('end_date', lang("end_date"), 'trim|required');
        if ($this->form_validation->run() == true) {    
            $zones = $this->input->post('multi_zone') ? implode(",", $this->input->post('multi_zone[]')) : null;
            $data = array(
                'start_date'  => $this->input->post('start_date') ? $this->bpas->fsd(trim($this->input->post('start_date'))) : null,
                'end_date'    => $this->input->post('end_date') ? $this->bpas->fsd(trim($this->input->post('end_date'))) : null,
                'biller_id'   => $this->input->post('biller'),
                'staff_id'    => $this->input->post('saleman'),
                'amount'      => $this->input->post('amount'),
                'multi_zone'  => $zones,
                'description' => $this->input->post('description'),
            );
        } elseif ($this->input->post('add_sale_target')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("system_settings/sale_targets");
        }
        if ($this->form_validation->run() == true && $this->settings_model->addSaleTarget($data)) {
            $this->session->set_flashdata('message', lang("sale_target_added"));
            admin_redirect("system_settings/sale_targets");
        } else {
            $this->data['error']     = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers']   = $this->site->getAllCompanies('biller');
            $this->data['salemans']  = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['zones']     = $this->site->getAllZones_Order_Group();
            $this->data['modal_js']  = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_sale_target', $this->data);
        }
    }
    function edit_sale_target($id = NULL)
    {
        $this->form_validation->set_rules('biller', lang("biller"), 'trim|required');
        $this->form_validation->set_rules('start_date', lang("start_date"), 'trim|required');
        $this->form_validation->set_rules('end_date', lang("end_date"), 'trim|required');
        if ($this->form_validation->run() == true) {
            $zones = $this->input->post('multi_zone') ? implode(",", $this->input->post('multi_zone[]')) : null;
            $data = array(
                'start_date'  => $this->input->post('start_date') ? $this->bpas->fsd(trim($this->input->post('start_date'))) : null,
                'end_date'    => $this->input->post('end_date') ? $this->bpas->fsd(trim($this->input->post('end_date'))) : null,
                'biller_id'   => $this->input->post('biller'),
                'staff_id'    => $this->input->post('saleman'),
                'amount'      => $this->input->post('amount'),
                'multi_zone'  => $zones,
                'description' => $this->input->post('description'),
            );
        } elseif ($this->input->post('edit_sale_target')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("system_settings/sale_targets");
        }
        if ($this->form_validation->run() == true && $this->settings_model->updateSaleTarget($id, $data)) {
            $this->session->set_flashdata('message', lang("sale_target_updated"));
            admin_redirect("system_settings/sale_targets");
        } else {
            $this->data['error']       = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['id']          = $id;
            $this->data['sale_target'] = $this->settings_model->getSaleTargetByID($id);
            $this->data['billers']     = $this->site->getAllCompanies('biller');
            $this->data['salemans']    = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['zones']       = $this->site->getAllZones_Order_Group();
            $this->data['modal_js']    = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_sale_target', $this->data);
        }
    }
    function delete_sale_target($id = NULL)
    {
        if ($this->settings_model->deleteSaleTarget($id)) {
            // $this->session->set_flashdata('message', lang("sale_target_deleted"));
            $this->bpas->send_json(['error' => 0, 'msg' => lang('sale_target_deleted')]);
            admin_redirect('system_settings/sale_targets');
        }
    }
    function saleTarget_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteSaleTarget($id);
                    }
                    $this->session->set_flashdata('message', lang("sale_targets_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_sale_target_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function getZones_ajax()
    {
        $result = $this->site->getAllZones();
        $this->bpas->send_json($result);
    }
    
    function audit_trail()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $this->data["users"] = $this->site->getAllUser();
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('brands')]];
        $meta                = ['page_title' => lang('audit_trail'), 'bc' => $bc];
        $this->page_construct('settings/audit_trail', $meta, $this->data);
    }
    public function getAuditTrail()
    {
        $user       = $this->input->get('user') ? $this->input->get('user') : null;
        $action     = $this->input->get('action') ? $this->input->get('action') : null;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date   = $this->input->get('end_date') ? $this->input->get('end_date') : null;
        if ($start_date) {
            $start_date = $this->bpas->fld($start_date);
            $end_date   = $this->bpas->fld($end_date);
        }

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('user_audit_trails')}.id as id, created_at, event, table_name, old_values, new_values, CONCAT(u.first_name, ' ', u.last_name) as created_by, url")
            ->join('users u', 'u.id=bpas_user_audit_trails.user_id', 'left')
            ->from('user_audit_trails')
            ->order_by('id', 'desc');
      
        $this->datatables->where('user_audit_trails.new_values !=','[]');
        if ($user) {
            $this->datatables->where('user_audit_trails.user_id', $user);
        }
        if ($action) {
            $this->datatables->where('user_audit_trails.event', $action);
        }
        if ($start_date) {
            $this->datatables->where('user_audit_trails' . '.created_at BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        echo $this->datatables->generate();
    }
    public function audit_trail_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('audit_trails'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('action'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('table'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('old_value'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('new_value'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('user'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('url'));

                    $row = 2;
                    foreach($_POST['val'] as $id){
                        $audit_trail = $this->settings_model->getAuditTrailByID($id);
                        $user = $this->site->getUser($audit_trail->user_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($audit_trail->created_at));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $audit_trail->event);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $audit_trail->table_name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $audit_trail->old_values);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $audit_trail->new_values);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $user->first_name . ' ' . $user->last_name);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $audit_trail->url);
                        $row++; 
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(35);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'audit_trails_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line('no_audit_trail_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function custom_field()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $this->data['constants'] = $this->settings_model->getParentCustomField();
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('custom_field')]];
        $meta                = ['page_title' => lang('custom_field'), 'bc' => $bc];
        $this->page_construct('settings/custom_field', $meta, $this->data);
    }

    public function getcustom_field($id=null)
    {
        $this->load->library('datatables');
        $id     = $this->input->get('parent_id');
        $code   = $this->input->get('code');
        $this->datatables
            ->select("
                {$this->db->dbprefix('custom_field')}.id as id, 
                {$this->db->dbprefix('custom_field')}.name,
                {$this->db->dbprefix('custom_field')}.description, 
                c.name as parent", false)
            ->from('custom_field')
            ->join('custom_field c', 'c.id=custom_field.parent_id', 'left')
            ->group_by('custom_field.id');
            
        if($id){
            $this->datatables->where('custom_field.parent_id',$id);
        }
        if($code){
            $this->datatables->where('custom_field.code',$code);
        }
        $this->datatables->add_column('Actions', "<div class=\"text-center\">
                <a href='" . admin_url('system_settings/view_custom_field/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('view_custom_field') . "'><i class=\"fa fa-th-list\"></i></a> 
                <a href='" . admin_url('system_settings/edit_custom_field/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_custom_field') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_custom_field') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_custom_field/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }
    public function getcustom_fieldbycode($id=null)
    {
        $this->load->library('datatables');
        $id     = $this->input->get('parent_id');
        $code   = $this->input->get('code');
        $this->datatables
            ->select("
                {$this->db->dbprefix('custom_field')}.id as id, 
                {$this->db->dbprefix('custom_field')}.name,
                {$this->db->dbprefix('custom_field')}.description", false)
            ->from('custom_field')
            ->join('custom_field c', 'c.id=custom_field.parent_id', 'left')
            ->group_by('custom_field.id');
            
        if($id){
            $this->datatables->where('custom_field.parent_id',$id);
        }
        if($code){
            $this->datatables->where('custom_field.code',$code);
        }
        $this->datatables->add_column('Actions', "<div class=\"text-center\">
                <a href='" . admin_url('system_settings/view_custom_field/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('view_custom_field') . "'><i class=\"fa fa-th-list\"></i></a> 
                <a href='" . admin_url('system_settings/edit_custom_field/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_custom_field') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_custom_field') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_custom_field/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }
    public function view_custom_field($id = null){

        $this->bpas->checkPermissions(false, true);
       $this->data['custom_fields'] = $this->site->getCustomeFieldByParentID($id);
        $this->load->view($this->theme . 'settings/view_custom_field', $this->data);
    }

    public function add_custom_field($code=null)
    {
        
        $this->form_validation->set_rules('name', lang('name'), 'required|is_unique[custom_field.name]');
        $this->form_validation->set_rules('description', lang('description'), '');
        if ($this->form_validation->run() == true) {
            $data = [
                'code'        => $this->input->post('code')?$this->input->post('code'):null,
                'name'        => $this->input->post('name'),
                'description' => $this->input->post('description'),
                'discount'    => $this->input->post('discount'),
                'parent_id'   => $this->input->post('parent'),
            ];
        } elseif ($this->input->post('add_custom_field')) {
            $this->session->set_flashdata('error', validation_errors());
            //admin_redirect('system_settings/custom_field');
            redirect($_SERVER['HTTP_REFERER']); 
        }

        if ($this->form_validation->run() == true && $this->settings_model->addcustom_field($data)) {
            $this->session->set_flashdata('message', lang('custom_field_added'));
            //admin_redirect('system_settings/custom_field');
            redirect($_SERVER['HTTP_REFERER']); 
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['expenses'] = $this->settings_model->getParentCustomField();
            $this->data['code']     = $code ? $code:'';
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_custom_field', $this->data);
        }
    }

    public function edit_custom_field($id = null)
    {
        $this->form_validation->set_rules('description', lang('description'), '');
        $category = $this->settings_model->getCustomeFieldByID($id);
        if ($this->input->post('name') != $category->name) {
            $this->form_validation->set_rules('name', lang('category_code'), 'required|is_unique[custom_field.name]');
        }
        $this->form_validation->set_rules('name', lang('category_name'), 'required|min_length[2]');

        if ($this->form_validation->run() == true) {
            $data = [
                'name' => $this->input->post('name'),
                'description' => $this->input->post('description'),
                'discount'    => $this->input->post('discount'),
                'parent_id'   => $this->input->post('parent'),
            ];
  
        } elseif ($this->input->post('edit_custom_field')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/custom_field');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updatecustomField($id, $data)) {
            $this->session->set_flashdata('message', lang('custom_field_updated'));
            //admin_redirect('system_settings/custom_field');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['expenses'] = $this->settings_model->getParentCustomField();
            $this->data['category'] = $category;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_custom_field', $this->data);
        }
    }

    public function delete_custom_field($id = null)
    {
        // if ($this->settings_model->hasExpenseCategoryRecord($id)) {
        //     $this->bpas->send_json(['error' => 1, 'msg' => lang('category_has_expenses')]);
        // }

        if ($this->settings_model->deleteCustomField($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('custom_field_delete')]);
            redirect($_SERVER['HTTP_REFERER']);
            //admin_redirect('system_settings/custom_field');
        }
    }

    public function language()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('language')]];
        $meta = ['page_title' => lang('language'), 'bc' => $bc];
        $this->page_construct('settings/language', $meta, $this->data);
    }
     public function getLanguage()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('id, code, khmer, english, chinese,thai,vietnamese')
            ->from('language')
            ->order_by('id','DESC')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('system_settings/edit_language/$1') . "' class='tip' title='" . lang('edit_language') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_currency') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_language/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        //->unset_column('id');

        echo $this->datatables->generate();
    }
    public function edit_language($id = null)
    {
        $this->form_validation->set_rules('code', lang('currency_code'), 'trim|required');
        $cur_details = $this->settings_model->getlanguageByID($id);
        if ($this->input->post('code') != $cur_details->code) {
            $this->form_validation->set_rules('code', lang('currency_code'), 'required|is_unique[currencies.code]');
        }

        if ($this->form_validation->run() == true) {
            $data = [
                'code'   => $this->input->post('code'),
                'khmer'        => $this->input->post('khmer'),
                'english'        => $this->input->post('english'),
                'chinese'      => $this->input->post('chinese'),
                'thai' => $this->input->post('thai'),
                'vietnamese' => $this->input->post('vietnamese'),
            ];
        } elseif ($this->input->post('edit_currency')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/language');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updatelanguage($id, $data)) { //check to see if we are updateing the customer
            $this->session->set_flashdata('message', lang('language_updated'));
            admin_redirect('system_settings/language');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['currency'] = $this->settings_model->getlanguageByID($id);
            $this->data['id']       = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_language', $this->data);
        }
    }
    public function add_language()
    {
        $this->form_validation->set_rules('code', lang('currency_code'), 'trim|is_unique[currencies.code]|required');
        $this->form_validation->set_rules('khmer', lang('khmer'), 'required');

        if ($this->form_validation->run() == true) {
            $data = [
                'code'   => $this->input->post('code'),
                'khmer'        => $this->input->post('khmer'),
                'english'        => $this->input->post('english'),
                'chinese'      => $this->input->post('chinese'),
                'thai' => $this->input->post('thai'),
                'vietnamese' => $this->input->post('vietnamese'),
            ];
        } elseif ($this->input->post('add_currency')) {
            $this->session->set_flashdata('error', validation_errors());
            
            admin_redirect('system_settings/language');
        }

        if ($this->form_validation->run() == true && $this->settings_model->add_language($data)) { //check to see if we are creating the customer
            $this->session->set_flashdata('message', lang('language_added'));
            admin_redirect('system_settings/language');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']   = $this->site->modal_js();
            $this->data['page_title'] = lang('new_currency');
            $this->load->view($this->theme . 'settings/add_language', $this->data);
        }
    }
    public function delete_language($id = null)
    {
        if ($this->settings_model->deleteLanguage($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('language_deleted')]);
        }
    }
    public function import_language_by_csv()
    {
        $this->bpas->checkPermissions('add', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('csv_file', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {

            if (isset($_FILES['csv_file'])) {
                $this->load->library('upload');

                $config['upload_path']   = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size']      = '2000';
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload('csv_file')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('customers');
                }
                $csv = $this->upload->file_name;
                $arrResult = [];
                $handle    = fopen('files/' . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5001, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles         = array_shift($arrResult);
                $rw             = 2;
                $updated        = '';
                $data           = [];
                echo 'not yet test';exit();
                foreach ($arrResult as $key => $value) {
                    $customer = [
                        'code'             => isset($value[0]) ? trim($value[0]) : '',
                    ];
                    if($value[1]){
                        $customer = [
                            'khmer'            => isset($value[1]) ? trim($value[1]) : '',
                        ];
                    }
                    if($value[2]){
                        $customer = [
                            'english'          => isset($value[2]) ? trim($value[2]) : '',
                        ];
                    }
                    if($value[3]){
                        $customer = [
                            'chinese'          => isset($value[3]) ? trim($value[3]) : '',
                        ];
                    }
                    if($value[4]){
                        $customer = [
                            'thai'             => isset($value[4]) ? trim($value[4]) : '',
                        ];
                    }
                    if($value[5]){
                        $customer = [
                            'vietnamese'       => isset($value[5]) ? trim($value[5]) : '',
                        ];
                    }
                    if (empty($customer['code'])) {
                        $this->session->set_flashdata('error',lang('code') .' (' . lang('line_no') . ' ' . $rw . ')');
                        admin_redirect('system_settings/language');
                    } else {
                       
                        if ($language_details = $this->settings_model->getLanguageBycode($customer['email'])) {
                            $updated .= '<p>' . lang('customer_updated') . ' (' . $customer['email'] . ')</p>';
                            $this->settings_model->updatelanguage($customer_details->id, $customer);
                        } else {
                            $data[] = $customer;
                        }
                        $rw++;
                    }
                }
            }
        } elseif ($this->input->post('import')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/language');
        }

        if ($this->form_validation->run() == true && !empty($data)) {
            if ($this->settings_model->addBathchLanguage($data)) {
                $this->session->set_flashdata('message', lang('customers_added') . $updated);
                admin_redirect('system_settings/language');
            }
        } else {
            if (isset($data) && empty($data)) {
                if ($updated) {
                    $this->session->set_flashdata('message', $updated);
                } else {
                    $this->session->set_flashdata('warning', lang('data_x_language'));
                }
                admin_redirect('system_settings/language');
            }

            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/import_language', $this->data);
        }
    }
    public function products_alert() 
    {
        $this->data['products']   = $this->site->getProducts();
        $this->data['categories'] = $this->settings_model->getParentCategories();
        $this->data['warehouses'] = $this->settings_model->getAllWarehouses();
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('products_alert')]];
        $meta                = ['page_title' => lang('products_alert'), 'bc' => $bc];
        $this->page_construct('settings/products_alert', $meta, $this->data);
    }

    public function getProductsAlert() 
    {
        $product  = $this->input->get('product') ? $this->input->get('product') : null;
        $category = $this->input->get('category') ? $this->input->get('category') : null;

        $sq = '';
        $warehouses = $this->settings_model->getAllWarehouses();
        if(!empty($warehouses)) {
            foreach ($warehouses as $warehouse) {
                $sq .= " 
                    COALESCE(
                        (
                            SELECT CONCAT({$this->db->dbprefix('products')}.id, '__', {$warehouse->id}, '__', {$this->db->dbprefix('warehouses_products')}.qty_alert) 
                            FROM {$this->db->dbprefix('warehouses_products')} 
                            WHERE {$this->db->dbprefix('warehouses_products')}.warehouse_id = {$warehouse->id} AND {$this->db->dbprefix('warehouses_products')}.product_id = {$this->db->dbprefix('products')}.id
                        ), CONCAT({$this->db->dbprefix('products')}.id, '__', {$warehouse->id}, '__', 0) 
                    ) AS '{$warehouse->code}_alert', ";
            }
        }

        $this->load->library('datatables');
        $this->datatables->select("
                {$this->db->dbprefix('products')}.id as id,
                CONCAT({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') as product,
                {$this->db->dbprefix('units')}.name as unit, {$sq}
            ")
            ->from('products')
            ->join('units', 'units.id = products.unit','left')
            ->add_column('Actions', '<div class="text-center"><button class="btn btn-primary btn-xs form-submit" type="button"><i class="fa fa-check"></i></button></div>', 'id');

        if($product) {
            $this->datatables->where('products.id', $product);
        }
        if($category) {
            $this->datatables->where('products.category_id', $category);
        }

        echo $this->datatables->generate();
    }

    public function product_alert_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        $warehouses = $this->settings_model->getAllWarehouses();
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'update_qty_alert') {
                    foreach ($_POST['val'] as $id) {
                        if(!empty($warehouses)) {
                            foreach ($warehouses as $warehouse) {
                                $qty_alert = $this->input->post('alert_' . $id . '_' . $warehouse->id) ? $this->input->post('alert_' . $id . '_' . $warehouse->id) : null;
                                if ($qty_alert) {
                                    $this->settings_model->setProductQtyAlert($id, $warehouse->id, $qty_alert);
                                }
                            }
                        }
                    }
                    $this->session->set_flashdata('message', lang('products_qty_alert_updated'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('products_alert'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('product_unit'));

                    $col = 'D';
                    if(!empty($warehouses)) {
                        foreach ($warehouses as $warehouse) {
                            $this->excel->getActiveSheet()->SetCellValue($col . '1', $warehouse->name);
                            $col++;   
                        }
                    }

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $product    = $this->site->getProductByID($id);
                        $unit       = $this->site->getUnitByID($product->unit);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $product->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $product->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $unit->name);

                        $col = 'D';
                        if(!empty($warehouses)) {
                            foreach ($warehouses as $warehouse) {
                                $wh_product = $this->settings_model->getWHProduct($id, $warehouse->id);
                                $this->excel->getActiveSheet()->SetCellValue($col . $row, (!empty($wh_product) ? $wh_product->qty_alert : 0));
                                $col++; 
                            }
                        }
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'products_alert_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_product_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function update_product_qty_alert()
    {
        $product_id   = $this->input->post('product_id');
        $warehouse_id = $this->input->post('warehouse_id');
        $qty_alert    = $this->input->post('qty_alert');
        if (!empty($product_id) && !empty($warehouse_id)) {
            if ($this->settings_model->setProductQtyAlert($product_id, $warehouse_id, $qty_alert)) {
                $this->bpas->send_json(['status' => 1]);
            }
        }
        $this->bpas->send_json(['status' => 0]);
    }

    public function update_product_qty_alert_csv()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');

        $warehouses = $this->settings_model->getAllWarehouses();
        if ($this->form_validation->run() == true) {
            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');
                $config['upload_path']   = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('system_settings/products_alert');
                }

                $csv = $this->upload->file_name;
                $arrResult = [];
                $handle    = fopen('files/' . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }

                $titles = array_shift($arrResult);
                $keys   = ['code'];
                if(!empty($warehouses)) {
                    foreach ($warehouses as $warehouse) {
                        array_push($keys, $warehouse->code);
                    }
                }
                $final  = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }

                $rw = 2;
                foreach ($final as $csv_pr) {
                    if ($product = $this->site->getProductByCode(trim($csv_pr['code']))) {
                        $final_arr = [];
                        $arr       = array('product_id' => $product->id);
                        if(!empty($warehouses)) {
                            foreach ($warehouses as $warehouse) {
                                $final_arr[] = array_merge($arr, array('warehouse_id' => $warehouse->id, 'qty_alert' => $csv_pr[$warehouse->code]));
                            }
                        }
                        $data[] = $final_arr;
                    } else {
                        $this->session->set_flashdata('error', lang('check_product_code') . ' (' . $csv_pr['code'] . '). ' . lang('code_x_exist') . ' ' . lang('line_no') . ' ' . $rw);
                        admin_redirect('system_settings/products_alert');
                    }
                    $rw++;
                }
            }
        } elseif ($this->input->post('update_products_alert')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/products_alert');
        }

        if ($this->form_validation->run() == true && !empty($data)) {
            $this->settings_model->updateProducstQtyAlert($data);
            $this->session->set_flashdata('message', lang('products_qty_alert_updated'));
            admin_redirect('system_settings/products_alert');
        } else {
            $this->data['userfile'] = [
                'name'  => 'userfile',
                'id'    => 'userfile',
                'type'  => 'text',
                'value' => $this->form_validation->set_value('userfile'),
            ];

            $this->data['warehouses'] = $this->settings_model->getAllWarehouses();
            $this->data['modal_js']   = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/update_products_alert_csv', $this->data);
        }
    }

    public function sample_products_alert()
    {
        $products   = $this->site->getAllProducts();
        $warehouses = $this->settings_model->getAllWarehouses();

        if (!empty($products) && !empty($warehouses)) {
            $filename = 'sample_products_alert.csv'; 
            header("Content-Description: File Transfer"); 
            header("Content-Disposition: attachment; filename=$filename"); 
            header("Content-Type: application/csv; ");

            $file = fopen('php://output', 'w');
            $header = array(lang('product_code')); 
            $line = array($products[0]->code); 
            if(!empty($warehouses)) {
                foreach ($warehouses as $warehouse) {
                    $wh_product = $this->settings_model->getWHProduct($products[0]->id, $warehouse->id);
                    array_push($header, $warehouse->name);
                    array_push($line, (!empty($wh_product) ? $wh_product->qty_alert : 0));
                }
            }

            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $header);
            fputcsv($file, $line); 
            fclose($file); 
            exit;
        } else {
            $this->session->set_flashdata('error', lang('product_or_warehouse_not_found!'));
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    function telegrams()
    {
        $this->bpas->checkPermissions();
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('telegrams')));
        $meta = array('page_title' => lang('telegrams'), 'bc' => $bc);
        $this->page_construct('settings/telegrams', $meta, $this->data);
    }

    function getTelegrams()
    {
        $this->bpas->checkPermissions("telegrams");
        $edit_telegram = anchor('admin/system_settings/edit_telegram/$1', '<i class="fa fa-edit"></i> ' . lang('edit_telegram'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_telegram") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('system_settings/delete_telegram/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_telegram') . "</a>";
        
        $message_link = "<a href='#' class='po' title='<b>" . $this->lang->line("send_telegram") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-success' href='" . admin_url('system_settings/send_telegram/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-send\"></i> "
        . lang('send_message') . "</a>";
        
        $chat_id_link = "<a href='#' class='po' title='<b>" . $this->lang->line("update_chat_id") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-success' href='" . admin_url('system_settings/update_chat_id/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
        . lang('update_chat_id') . "</a>";
        
        $this->load->library('datatables');
        $this->datatables
            ->select("telegram_bots.id as id, telegram_bots.name, telegram_bots.token_id, telegram_bots.chat_id,telegram_bots.transaction,telegram_bots.status", FALSE)
            ->from("telegram_bots");
            
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $message_link . '</li>
                <li>' . $chat_id_link . '</li>
                <li>' . $edit_telegram . '</li>
                <li>' . $delete_link . '</li>
            </ul>
        </div></div>';
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
    
    function add_telegram()
    {
        $this->bpas->checkPermissions("telegrams",true);
        $this->load->helper('security');
        $this->form_validation->set_rules('name', lang("name"), 'required|is_unique[telegram_bots.name]');
        $this->form_validation->set_rules('token_id', lang("token_id"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
                'name' => trim($this->input->post('name')),
                'token_id' => trim($this->input->post('token_id')),
                'chat_id' => trim($this->input->post('chat_id')),
                'transaction' => $this->input->post('transaction') ? json_encode($this->input->post('transaction')) : null,
                'biller' => $this->input->post('biller') ? json_encode($this->input->post('biller')) : null,
                'warehouse' => $this->input->post('warehouse') ? json_encode($this->input->post('warehouse')) : null,
            );
        } elseif ($this->input->post('add_telegram')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->settings_model->addTelegram($data)) {
            $this->session->set_flashdata('message', lang("telegram_added")." - ".$data['name']);
            admin_redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_telegram', $this->data);
        }
    }
    
    function edit_telegram($id = false)
    {
        $this->bpas->checkPermissions("telegrams",true);
        $this->load->helper('security');
        $telegram = $this->settings_model->getTelegramByID($id);
        if($telegram->name != trim($this->input->post('name'))){
            $this->form_validation->set_rules('name', lang("name"), 'required|is_unique[telegram_bots.name]');
        }else{
            $this->form_validation->set_rules('name', lang("name"), 'required');
        }
        $this->form_validation->set_rules('token_id', lang("token_id"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
                'name' => trim($this->input->post('name')),
                'token_id' => trim($this->input->post('token_id')),
                'chat_id' => trim($this->input->post('chat_id')),
                'status' => trim($this->input->post('status')),
                'transaction' => $this->input->post('transaction') ? json_encode($this->input->post('transaction')) : null,
                'biller' => $this->input->post('biller') ? json_encode($this->input->post('biller')) : null,
                'warehouse' => $this->input->post('warehouse') ? json_encode($this->input->post('warehouse')) : null,
            );
        } elseif ($this->input->post('edit_telegram')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->settings_model->updateTelegram($id, $data)) {
            $this->session->set_flashdata('message', lang("telegram_edited")." - ".$data['name']);
            admin_redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['telegram'] = $telegram;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_telegram', $this->data);
        }
    }
    
    function delete_telegram($id = NULL)
    {
        $this->bpas->checkPermissions("telegrams",true);
        if ($this->settings_model->deleteTelegram($id)) {
            $this->session->set_flashdata('message', lang("telegram_deleted"));
            admin_redirect("system_settings/telegrams");
        }else{
            $this->session->set_flashdata('error', lang('cannot_delete'));
            $this->bpas->md();
        }
    }
    function update_chat_id($id = NULL)
    {
        $this->bpas->checkPermissions("telegrams",true);
        $this->load->library('telegrambot');
        $telegram = $this->settings_model->getTelegramByID($id);
        $results = $this->telegrambot->getupdates($telegram->token_id);
        $chat_ids = false;
        if($results){
            foreach($results['result'] as $result){
                if(isset($result["message"])){
                    $chat_ids[trim($result["message"]["chat"]["title"])] = $result["message"]["chat"]["id"];
                }
            }
        }
        if($chat_ids && isset($chat_ids[$telegram->name])){
            $data = array('chat_id' => $chat_ids[$telegram->name]);
            if ($this->settings_model->updateTelegram($id,$data)) {
                $this->session->set_flashdata('message', lang("telegram_edited"));
                admin_redirect("system_settings/telegrams");
            }else{
                $this->session->set_flashdata('error', lang('cannot_get_chat_id'));
                $this->bpas->md();
            }
        }else{
            $this->session->set_flashdata('error', lang('cannot_get_chat_id'));
            $this->bpas->md();
        }
        
    }
    function send_telegram($id = NULL)
    {
        $this->bpas->checkPermissions("telegrams",true);
        $this->load->library('telegrambot');
        $telegram = $this->settings_model->getTelegramByID($id);
        $message = "Tesing message:\n";
        if($telegram->name){
            $message .= lang("name").": ".$telegram->name."\n";
        }
        if($telegram->token_id){
            $message .= lang("token_id").": ".$telegram->token_id."\n";
        }
        if($telegram->chat_id){
            $message .= lang("chat_id").": ".$telegram->chat_id."\n";
        }
        $send = $this->telegrambot->sendmsg($telegram->token_id,$telegram->chat_id,$message);
        if($send["ok"]){
            $this->session->set_flashdata('message', lang("message_sent"));
            admin_redirect("system_settings/telegrams");
        }else{
            $this->session->set_flashdata('error', lang('message_failed_to_send'));
            $this->bpas->md();
        }
    }
    
    function telegram_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteTelegram($id);
                    }
                    $this->session->set_flashdata('message', lang("telegram_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('telegrams'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('token_id'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('chat_id'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('transaction'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $telegram = $this->settings_model->getTelegramByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $telegram->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $telegram->token_id);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $telegram->chat_id);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $telegram->transaction);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, lang($telegram->status));
                        $row++;
                    }
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'telegrams_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    public function multi_approved($id)
    {
        // if (!$id || empty($id)) {
        //     admin_redirect('system_settings/user_groups');
        // }
        $this->form_validation->set_rules('multi', lang("multi"), 'required');
        if ($this->form_validation->run() === true) {
            $multi_approved = $this->site->getMultiApproved($id);
            $data = [
                'form'                  => $this->input->post('form'), 
                'approved_by'           => implode(',', $this->input->post('approved_by[]')), 
                'preparation_by'        => implode(',', $this->input->post('preparation_by[]')), 
                'issued_by'             => implode(',', $this->input->post('issued_by[]')),
                'acknowledged_by'       => implode(',', $this->input->post('acknowledged_by[]')),
                'received_by'           => implode(',', $this->input->post('received_by[]')),
                'stock_received_by'     => implode(',', $this->input->post('stock_received_by[]')),
                'quality_checked_by'    => implode(',', $this->input->post('quality_checked_by[]')), 
                'procurement_by'        => implode(',', $this->input->post('procurement_by[]')),
            ];
        } elseif ($this->input->post('multi')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->settings_model->updateMultiApproved($id, $data)) {
            $this->session->set_flashdata('message', lang('group_updated'));
            admin_redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']              = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $group                            = $this->settings_model->getGroupByID($id);
            $this->data['group']              = $group;
            $this->data['modal_js']           = $this->site->modal_js();
            $this->data['all_multi_approved'] = $this->site->getAllMultiApproved($group->id);
            $this->data['multi_approved']     = $this->site->getMultiApproved($group->id);
            $this->data['users']              = $this->site->getUserByGroup($group->id);
            $this->load->view($this->theme . 'settings/multi_approved', $this->data);
        }
    }

    //////////////////////// Update Price Group 01_07_2022 ////////////////////////////////////////

    public function group_product_prices_23_06_2022($group_id = null)
    {
        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            admin_redirect('system_settings/price_groups');
        }

        $this->data['price_group'] = $this->settings_model->getPriceGroupByID($group_id);
        $this->data['error']       = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                        = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')],  ['link' => admin_url('system_settings/price_groups'), 'page' => lang('price_groups')], ['link' => '#', 'page' => lang('group_product_prices')]];
        $meta                      = ['page_title' => lang('group_product_prices'), 'bc' => $bc];
        $this->page_construct('settings/group_product_prices', $meta, $this->data);
    }

    public function update_product_group_price_23_06_2022($group_id = null)
    {
        if (!$group_id) {
            $this->bpas->send_json(['status' => 0]);
        }

        $product_id = $this->input->post('product_id', true);
        $price      = $this->input->post('price', true);
        if (!empty($product_id) && !empty($price)) {
            if ($this->settings_model->setProductPriceForPriceGroup($product_id, $group_id, $price)) {
                $this->bpas->send_json(['status' => 1]);
            }
        }

        $this->bpas->send_json(['status' => 0]);
    }

    public function product_group_price_actions_23_06_2022($group_id)
    {
        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            admin_redirect('system_settings/price_groups');
        }
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'update_price') {
                    foreach ($_POST['val'] as $id) {
                       
                        $this->settings_model->setProductPriceForPriceGroup($id, $group_id, $this->input->post('price' . $id));
                    }
                    $this->session->set_flashdata('message', lang('products_group_price_updated'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteProductGroupPrice($id, $group_id);
                    }
                    $this->session->set_flashdata('message', lang('products_group_price_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('group_price'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('price'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('group_name'));
                    $row   = 2;
                    $group = $this->settings_model->getPriceGroupByID($group_id);
                    foreach ($_POST['val'] as $id) {
                        $pgp = $this->settings_model->getProductGroupPriceByPID($id, $group_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $pgp->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $pgp->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $pgp->price);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $group->name);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'price_groups_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_price_group_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function update_prices_csv_23_06_2022($group_id = null)
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if (DEMO) {
                $this->session->set_flashdata('message', lang('disabled_in_demo'));
                admin_redirect('welcome');
            }
            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');
                $config['upload_path']   = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('system_settings/group_product_prices/' . $group_id);
                }
                $csv = $this->upload->file_name;
                $arrResult = [];
                $handle    = fopen('files/' . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys = ['code', 'price'];
                $final = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv_pr) {
                    if ($product = $this->site->getProductByCode(trim($csv_pr['code']))) {
                        $data[] = [
                            'product_id'     => $product->id,
                            'price'          => $csv_pr['price'],
                            'price_group_id' => $group_id,
                        ];
                    } else {
                        $this->session->set_flashdata('message', lang('check_product_code') . ' (' . $csv_pr['code'] . '). ' . lang('code_x_exist') . ' ' . lang('line_no') . ' ' . $rw);
                        admin_redirect('system_settings/group_product_prices/' . $group_id);
                    }
                    $rw++;
                }
            }
        } elseif ($this->input->post('update_price')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/group_product_prices/' . $group_id);
        }
        if ($this->form_validation->run() == true && !empty($data)) {
            $this->settings_model->updateGroupPrices($data);
            $this->session->set_flashdata('message', lang('price_updated'));
            admin_redirect('system_settings/group_product_prices/' . $group_id);
        } else {
            $this->data['userfile'] = [
                'name'  => 'userfile',
                'id'    => 'userfile',
                'type'  => 'text',
                'value' => $this->form_validation->set_value('userfile'),
            ];
            $this->data['group']    = $this->site->getPriceGroupByID($group_id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/update_price', $this->data);
        }
    }

    public function group_product_prices($group_id = null)
    {
        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            admin_redirect('system_settings/price_groups');
        }
        $academic_year               = $this->Settings->module_school ? ($this->input->post('academic_year') ? $this->input->post('academic_year') : date('Y')) : null;
        $this->data['academic_year'] = $academic_year;
        $this->data['price_group']   = $this->settings_model->getPriceGroupByID($group_id);
        $this->data['results']       = $this->settings_model->getProductsPriceByPriceGroupID($group_id, $academic_year);
        $this->data['error']         = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')],  ['link' => admin_url('system_settings/price_groups'), 'page' => lang('price_groups')], ['link' => '#', 'page' => lang('group_product_prices')]];
        $meta = ['page_title' => lang('group_product_prices'), 'bc' => $bc];
        $this->page_construct('settings/group_product_prices', $meta, $this->data);
    }

    public function update_product_group_price($group_id = null)
    {
        if (!$group_id) {
            $this->bpas->send_json(['status' => 0]);
        }
        $data = isset($_POST['data']) ? $_POST['data'] : null;
        if (!empty($data)) {
            foreach ($data as $value) {
                if (!empty($value['product_id']) && !empty($value['unit_id']) && !empty($value['price'])) {
                    $academic_year = ($this->Settings->module_school ? $value['academic_year'] : null);
                    $this->settings_model->setProductPriceForPriceGroupByUnit($group_id, $value['product_id'], $value['unit_id'], $value['price'], $academic_year);
                }
            }
            $this->bpas->send_json(['status' => 1]);
        }
        $this->bpas->send_json(['status' => 0]);
    }

    public function product_group_price_actions($group_id)
    {
        if (!$group_id) {
            $this->session->set_flashdata('error', lang('no_price_group_selected'));
            admin_redirect('system_settings/price_groups');
        }
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'update_price') {
                    $academic_year = ($this->Settings->module_school ? $_POST['academic_year'] : null);
                    foreach ($_POST['val'] as $id) {   
                        $i = isset($_POST['unit_' . $id]) ? sizeof($_POST['unit_' . $id]) : 0;
                        for ($r = 0; $r < $i; $r++) {
                            $unit_id = isset($_POST['unit_' . $id][$r]) ? $_POST['unit_' . $id][$r] : null;
                            $price   = isset($_POST['price_' . $id][$r]) ? $this->bpas->formatDecimal($_POST['price_' . $id][$r]) : 0;
                            if (!empty($id) && !empty($unit_id) && !empty($price)) {
                                $this->settings_model->setProductPriceForPriceGroupByUnit($group_id, $id, $unit_id, $price, $academic_year);
                            }
                        }
                    }
                    $this->session->set_flashdata('message', lang('products_group_price_updated'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'delete') {
                    $academic_year = ($this->Settings->module_school ? $_POST['academic_year'] : null);
                    foreach ($_POST['val'] as $id) {
                        $i = isset($_POST['unit_' . $id]) ? sizeof($_POST['unit_' . $id]) : 0;
                        for ($r = 0; $r < $i; $r++) { 
                            $unit_id = isset($_POST['unit_' . $id][$r]) ? $_POST['unit_' . $id][$r] : null;
                            $this->settings_model->deleteProductGroupPriceByUnit($group_id, $id, $unit_id, $academic_year);
                        }
                    }
                    $this->session->set_flashdata('message', lang('products_group_price_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('group_price'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('unit'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('price'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('group_name'));
                    $row   = 2;
                    $group = $this->settings_model->getPriceGroupByID($group_id);
                    $academic_year = ($this->Settings->module_school ? $_POST['academic_year'] : null);
                    foreach ($_POST['val'] as $id) {
                        $i = isset($_POST['unit_' . $id]) ? sizeof($_POST['unit_' . $id]) : 0;
                        for ($r = 0; $r < $i; $r++) {
                            $unit_id = isset($_POST['unit_' . $id][$r]) ? $_POST['unit_' . $id][$r] : null;
                            $pgp = $this->settings_model->getProductGroupPriceByPIDUnitID($group_id, $id, $unit_id, $academic_year);
                            $this->excel->getActiveSheet()->SetCellValue('A' . $row, $pgp->code);
                            $this->excel->getActiveSheet()->SetCellValue('B' . $row, $pgp->name);
                            $this->excel->getActiveSheet()->SetCellValue('C' . $row, $pgp->unit_name);
                            $this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($pgp->price));
                            $this->excel->getActiveSheet()->SetCellValue('E' . $row, $group->name);
                            $row++;
                        }
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'price_groups_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_price_group_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function update_prices_csv($group_id = null, $academic_year = null)
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $academic_year = ($this->Settings->module_school ? $academic_year : null);
            if (DEMO) {
                $this->session->set_flashdata('message', lang('disabled_in_demo'));
                admin_redirect('welcome');
            }
            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');
                $config['upload_path']   = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('system_settings/group_product_prices/' . $group_id);
                }
                $csv = $this->upload->file_name;
                $arrResult = [];
                $handle    = fopen('files/' . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys = ['code', 'unit', 'price'];
                $final = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv_pr) {
                    if ($product = $this->site->getProductByCode(trim($csv_pr['code']))) {
                        if ($unit = $this->site->getUnitByCode(trim($csv_pr['unit']))) {
                            if ($this->settings_model->valid_UnitProduct($product->id, $unit->id)) {
                                if ($csv_pr['price'] != '' && $csv_pr['price'] != null) {
                                    $data[] = [
                                        'price_group_id' => $group_id,
                                        'product_id'     => $product->id,
                                        'unit_id'        => $unit->id,
                                        'price'          => $csv_pr['price'],
                                        'academic_year'  => $academic_year
                                    ];
                                } else {
                                    $this->session->set_flashdata('error', lang('check_unit_price') . ', ' . lang('column_unit_price_is_required') . ' ' . lang('line_no') . ' ' . $rw);
                                    admin_redirect('system_settings/group_product_prices/' . $group_id);    
                                }
                            } else {
                                $this->session->set_flashdata('error', lang('check_unit_code') . ' (' . $csv_pr['unit'] . '). ' . lang('unit_code_not_valid_of_product') . ' ' . lang('line_no') . ' ' . $rw);
                                admin_redirect('system_settings/group_product_prices/' . $group_id);    
                            }
                        } else {
                            $this->session->set_flashdata('error', lang('check_unit_code') . ' (' . $csv_pr['unit'] . '). ' . lang('unit_code_x_exist') . ' ' . lang('line_no') . ' ' . $rw);
                            admin_redirect('system_settings/group_product_prices/' . $group_id);
                        }
                    } else {
                        $this->session->set_flashdata('error', lang('check_product_code') . ' (' . $csv_pr['code'] . '). ' . lang('code_x_exist') . ' ' . lang('line_no') . ' ' . $rw);
                        admin_redirect('system_settings/group_product_prices/' . $group_id);
                    }
                    $rw++;
                }
            }
        } elseif ($this->input->post('update_price')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/group_product_prices/' . $group_id);
        }
        if ($this->form_validation->run() == true && !empty($data)) {
            $this->settings_model->updateGroupPrices($data);
            $this->session->set_flashdata('message', lang('price_updated'));
            admin_redirect('system_settings/group_product_prices/' . $group_id);
        } else {
            $this->data['userfile'] = [
                'name'  => 'userfile',
                'id'    => 'userfile',
                'type'  => 'text',
                'value' => $this->form_validation->set_value('userfile'),
            ];
            $this->data['academic_year'] = ($this->Settings->module_school ? $academic_year : null);
            $this->data['group']    = $this->site->getPriceGroupByID($group_id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/update_price', $this->data);
        }
    }

    function cash_accounts()
    {
        $this->bpas->checkPermissions("cash_account");
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('cash_accounts')));
        $meta = array('page_title' => lang('cash_accounts'), 'bc' => $bc);
        $this->page_construct('settings/cash_accounts', $meta, $this->data);
    }
    function getCashAccounts()
    {
        $this->bpas->checkPermissions("cash_account");
        $this->load->library('datatables');
        $this->datatables
            ->select("cash_accounts.id as id, cash_accounts.code, cash_accounts.name,cash_accounts.account_code,cash_accounts.type", FALSE)
            ->from("cash_accounts")
            ->group_by('cash_accounts.id')
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . admin_url('system_settings/edit_cash_account/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal' class='tip' title='" . lang("edit_cash_account") . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_cash_account") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete124' href='" . admin_url('system_settings/delete_cash_account/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");

        echo $this->datatables->generate();
    }
    
    function add_cash_account()
    {
        $this->bpas->checkPermissions("cash_account",true); 
        $this->form_validation->set_rules('code', lang("cash_account_code"), 'trim|is_unique[cash_accounts.code]|required');
        $this->form_validation->set_rules('name', lang("cash_account_name"), 'trim|required');
        if ($this->form_validation->run() == true) {
            $data = array(
                            'name' => $this->input->post('name'),
                            'code' => $this->input->post('code'),
                            'account_code' => $this->input->post('account_code'),
                            'type' => $this->input->post('type')
                        );
        } elseif ($this->input->post('add_cash_account')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->settings_model->addCashAccount($data)) {
            $this->session->set_flashdata('message', lang("cash_account_added")." ".$data['code']." ".$data['name']);
           admin_redirect("system_settings/cash_accounts");
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['accounts'] = $this->site->getAllBankAccounts();
            $this->load->view($this->theme . 'settings/add_cash_account', $this->data);
        }
    }
    
    function edit_cash_account($id = NULL)
    {
        $this->bpas->checkPermissions("cash_account",true); 
        $this->form_validation->set_rules('code', lang("code"), 'trim|required');
        $cash_account_details = $this->site->getCashAccountByID($id);
        if ($this->input->post('code') != $cash_account_details->code) {
            $this->form_validation->set_rules('code', lang("code"), 'is_unique[cash_accounts.code]');
        }
        $this->form_validation->set_rules('name', lang("name"), 'trim|required');
        if ($this->form_validation->run() == true) {
            $data = array(
                            'name' => $this->input->post('name'),
                            'code' => $this->input->post('code'),
                            'account_code' => $this->input->post('account_code'),
                            'type' => $this->input->post('type')
                        );
        } elseif ($this->input->post('edit_cash_account')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("system_settings/cash_accounts");
        }
        if ($this->form_validation->run() == true && $this->settings_model->updateCashAccount($id, $data)) {
            $this->session->set_flashdata('message', lang("cash_account_updated")." ".$data['code']);
            admin_redirect("system_settings/cash_accounts");
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['cash_account'] = $cash_account_details;
            if($this->Settings->accounting == 1){
                $this->data['accounts'] = $this->site->getAllBankAccounts();
            }
            $this->load->view($this->theme . 'settings/edit_cash_account', $this->data);
        }
    }
    
    function delete_cash_account($id = NULL)
    {
        $this->bpas->checkPermissions("cash_account",true); 
        if ($this->settings_model->deleteCashAccount($id)) {
            echo lang("cash_account_deleted");
        }
        $this->session->set_flashdata('message', lang("cash_account_deleted")." ".$id['code']." ".$id['name']);
        admin_redirect("system_settings/cash_accounts");
    }
    
    function cash_account_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCashAccount($id);
                    }
                    $this->session->set_flashdata('message', lang("cash_accounts_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('cash_accounts'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('type'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $cash_account = $this->site->getCashAccountByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $cash_account->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $cash_account->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, lang($cash_account->type));
                        $row++;
                    }
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'cash_accounts_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    //////////////////////// Update Price Group 01_07_2022 ////////////////////////////////////////
    public function add_print(){
        $transaction = $this->input->get('transaction');
        $transaction_id = $this->input->get('transaction_id');
        $reference_no = $this->input->get('reference_no');
        
        if($transaction && $transaction_id){
            $data = array(
                    'transaction' => $transaction,
                    'transaction_id' => $transaction_id,
                    'reference_no' => $reference_no,
                    'print_by' => $this->session->userdata('user_id'),
                    'print_date' => date('Y-m-d H:i:s'),    
        
            );
            if($this->Owner || $this->Admin || $this->bpas->GP['unlimited-print'] || $this->Settings->limit_print=='2' || $this->Settings->limit_print=='0' || ($this->Settings->limit_print=='1' && !$this->site->checkPrint($transaction, $transaction_id))){
                $this->site->addPrint($data);
            }
        }
    }

    public function cronjob(){
        /*$this->form_validation->set_rules('site_name', lang('site_name'), 'trim|required');
        if ($this->form_validation->run() == true) {
        }
        if ($this->form_validation->run() == true && $this->settings_model->updateSetting($data)) {
            if (TIMEZONE != $data['timezone']) {
                if (!$this->write_index($data['timezone'])) {
                    $this->session->set_flashdata('error', lang('setting_updated_timezone_failed'));
                    admin_redirect('system_settings');
                }
            }
            $this->session->set_flashdata('message', lang('setting_updated'));
            admin_redirect('system_settings');
        } else {*/
            $this->data['error']           = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $bc                            = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('cron_jobs')]];
            $meta                          = ['page_title' => lang('cron_jobs'), 'bc' => $bc];
            $this->page_construct('settings/cron_jobs', $meta, $this->data);
        //}
    }
    function skins()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('system_settings'),
        'page' => lang('system_settings')), array('link' => '#', 'page' => lang('Skins')));
        $meta = array('page_title' => lang('skins'), 'bc' => $bc);
        $this->page_construct('settings/skins', $meta, $this->data);
    }
    function getSkins()
    {
        $this->load->library('datatables');
        $this->datatables
        
        ->select("skins.id as id,
               companies.name,
               skins.target_type,
               skins.amount,
               skins.start_date,
               skins.end_date,
               skins.product,
               skins.commission,
               skins.status,
               {$this->db->dbprefix('skins')}.product", false
               )
            ->from("skins")
            ->join("companies","companies.id = skins.customer_id","left")
            ->add_column("Actions", "<div class=\"text-center\"><a href='" 
            . admin_url('system_settings/edit_skins/$1') . "' class=edit_skins'" 
            . lang("edit_skins") . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" 
            . lang("delete_skins") . "</b>' data-content=\"<p>" 
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" 
            . admin_url('system_settings/delete_skins/$1') . "'>" 
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" 
            . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");

        echo $this->datatables->generate();
    }
    function add_skins()
    {
        $this->form_validation->set_rules('customer', lang("customer_id"), 'required');
        $this->form_validation->set_rules('target_type',lang("target_type"),'required');
        $this->form_validation->set_rules('amount',lang("amount"),'required');
        $this->form_validation->set_rules('start_date', lang("start_date"), 'required');
        $this->form_validation->set_rules('end_date', lang("end_date"), 'required');
        $this->form_validation->set_rules('products[]', lang("product"), 'required');
        $this->form_validation->set_rules('commission', lang("commission"), 'required');
        if ($this->form_validation->run() == true) {
           
            $data = array(
                            'customer_id' => $this->input->post('customer'),
                            'target_type' => $this->input->post('target_type'),
                            'amount' => $this->input->post('amount'),
                            'start_date' => $this->bpas->fsd($this->input->post('start_date')),
                            'end_date' =>$this->bpas->fsd($this->input->post('end_date')),
                            'product' =>implode(",", $this->input->post('products[]')),//$this->input->post('products[]'),
                            'commission' => $this->input->post('commission'),
                            'status' => $this->input->post('status'),
                           
                        );
                    
        } elseif ($this->input->post('add_skins')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("system_settings/skins");
        }

        if ($this->form_validation->run() == true && $this->settings_model->addSkins($data)) {
            $this->session->set_flashdata('message', lang("skins_added"));
            admin_redirect("system_settings/skins");
           
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['page_title'] = lang("add_skins");
            $this->data['pos']        = $this->settings_model->getSkins();
            $this->data['product'] = $this->site->getAllProducts();
            $this->load->view($this->theme . 'settings/add_skins', $this->data);
        }
    }
    function delete_skins($id = NULL)
    {

        if ($this->settings_model->deleteSkins($id)) {
            echo lang("skins_deleted");
        }
    }
    function skins_actions()
    {

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteSkins($id);
                    }
                    $this->session->set_flashdata('message', lang("skins_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('skins'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('customer_id'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('target_type'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('amount'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('start_date'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('end_date'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('product'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('commission'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getSkinsByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->customer_id);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->target_type);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->amount);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->start_date);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sc->end_date);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $sc->product); 
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $sc->commission);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'skins_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    function edit_skins($id = NULL)
   {
       
       $skins = $this->settings_model->getSkinsByID($id);
       $this->form_validation->set_rules('customer', lang("customer_id"), 'required');
       $this->form_validation->set_rules('target_type',lang("target_type"),'required');
       $this->form_validation->set_rules('amount',lang("amount"),'required');
       $this->form_validation->set_rules('start_date', lang("start_date"), 'required');
       $this->form_validation->set_rules('end_date', lang("end_date"), 'required');
       $this->form_validation->set_rules('products[]', lang("product"), 'required');
       $this->form_validation->set_rules('commission', lang("commission"), 'required');
       if ($this->form_validation->run() == true) {
           $data = array(
                   'customer_id' => $this->input->post('customer'),
                   'target_type' => $this->input->post('target_type'),
                   'amount' => $this->input->post('amount'),
                   'start_date' => $this->bpas->fsd($this->input->post('start_date')),
                   'end_date' =>$this->bpas->fsd($this->input->post('end_date')),
                   'product' =>implode(",", $this->input->post('products[]')),
                   'commission' => $this->input->post('commission'),
                   'status' => $this->input->post('status'),
                       );
       } elseif ($this->input->post('edit_skins')) {
           $this->session->set_flashdata('error', validation_errors());
           redirect("system_settings/skins");
       }

       if ($this->form_validation->run() == true && $this->settings_model->updateSkins($id, $data)) {
           $this->session->set_flashdata('message', lang("skins_updated"));
           admin_redirect("system_settings/skins");
       } else {
           $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
           $this->data['skins'] = $skins;
           $this->data['id'] = $id;
           $this->data['pos'] = $this->settings_model->getSkins();
           $this->data['product'] = $this->site->getAllProducts();
           $this->data['modal_js'] = $this->site->modal_js();
           $this->load->view($this->theme . 'settings/edit_skins', $this->data);
       }
   }
   function promotion()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('promotion')));
        $meta = array('page_title' => lang('promotion'), 'bc' => $bc);
        $this->page_construct('settings/promotion', $meta, $this->data);
    }
    function getPromotion()
    {
        $start_date     = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date       = $this->input->get('end_date') ? $this->input->get('end_date') : null;
        $product        = $this->input->get('product') ? $this->input->get('product') : null;
    
        if ($start_date) {
            $start_date = $this->bpas->fld($start_date . ' 00:00:00');
            $end_date   = $this->bpas->fld($end_date . ' 23:59:00');
        }

        $this->load->library('datatables');
        $promotion_products = "(
                SELECT {$this->db->dbprefix('promotion_categories')}.promotion_id,
                {$this->db->dbprefix('promotion_categories')}.product_id 
                FROM `bpas_promotion_categories`";
        if($product)
        {
            $promotion_products .="WHERE {$this->db->dbprefix('promotion_categories')}.product_id = {$product} ";  
        }
        $promotion_products .= " GROUP BY {$this->db->dbprefix('promotion_categories')}.promotion_id ) as pmt";

        $this->datatables
            ->select("promotions.id as id, description,warehouses.name as wname,start_date,end_date")
            ->from("promotions")
            ->join('warehouses','warehouses.id = promotions.warehouse_id', 'left')
            ->join($promotion_products,'pmt.promotion_id = promotions.id', 'left')
            ->add_column("Actions", "<center>  <a href='" . admin_url('system_settings/view/$1') . "' class='tip' title='" . lang("view") . "'><i class=\"fa fa-list-alt\"></i></a> <a href='" . admin_url('system_settings/edit_promotion/$1') . "' class='tip' title='" . lang("edit_promotion") . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_promotion") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_promotion/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></center>", "id");
         
            if ($start_date || $end_date) {
                $this->datatables->where("{$this->db->dbprefix('promotions')}.start_date >= '$start_date' AND {$this->db->dbprefix('promotions')}.start_date <= '$end_date' OR {$this->db->dbprefix('promotions')}.end_date >= '$start_date' AND {$this->db->dbprefix('promotions')}.end_date <= '$end_date'");
            }

            if ($product) {
                $this->datatables->where('pmt.product_id', $product);
            }
            
        echo $this->datatables->generate();
    }
    function promotion_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deletePromotion($id);
                    }
                    $this->session->set_flashdata('message', lang("promotions_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_promotion_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    function view($id = NULL)
    {

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('promotion')));
        $meta = array('page_title' => lang('promotion'), 'bc' => $bc);
        $this->data['id']           = $id;
        $this->page_construct('settings/promotion_view', $meta, $this->data);
        
    }
    
    function getview__($id = NULL, $xls = null, $preview= null)
    {
        if($preview){
            $this->db->select("
            {$this->db->dbprefix('promotion_categories')}.product_code as pcode,
            products.name as pname,
            {$this->db->dbprefix('warehouses_products')}.quantity as pqty,
            COALESCE({$this->db->dbprefix('cost_price_by_units')}.price, {$this->db->dbprefix('products')}.price) as pprice,
            {$this->db->dbprefix('promotion_categories')}.discount,
            CONCAT( COALESCE({$this->db->dbprefix('cost_price_by_units')}.price, {$this->db->dbprefix('products')}.price),'__',{$this->db->dbprefix('promotion_categories')}.discount) as amount")
            ->from('promotion_categories')
            ->join('cost_price_by_units','cost_price_by_units.product_code = promotion_categories.product_code','left')
            ->join('products','products.id = promotion_categories.product_id' , 'inner')
            ->join('warehouses_products','warehouses_products.product_id = products.id' , 'left');
            if ($id) {
                $this->db->where('promotion_categories.promotion_id', $id);
            }
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row){
                    $data[] = $row;
                }
            } else {
                $data = null;
            }
            // $this->bpas->print_arrays($data);
 
            $this->data['rows'] = $data;
            $this->data['biller'] =  $this->site->getCompanyByID(3);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('promotion')));
            $meta = array('page_title' => lang('promotion'), 'bc' => $bc);
            $this->page_construct('settings/preview_promotion', $meta, $this->data);
        } elseif ($xls) {
           
                $this->db->select("
                {$this->db->dbprefix('promotion_categories')}.product_code as pcode,
                products.name as pname,
                {$this->db->dbprefix('warehouses_products')}.quantity as pqty,
                COALESCE({$this->db->dbprefix('cost_price_by_units')}.price, {$this->db->dbprefix('products')}.price) as pprice,
                {$this->db->dbprefix('promotion_categories')}.discount,
                CONCAT( COALESCE({$this->db->dbprefix('cost_price_by_units')}.price, {$this->db->dbprefix('products')}.price),'__',{$this->db->dbprefix('promotion_categories')}.discount) as amount")
                ->from('promotion_categories')
                ->join('cost_price_by_units','cost_price_by_units.product_code = promotion_categories.product_code','left')
                ->join('products','products.id = promotion_categories.product_id' , 'left')
                ->join('warehouses_products','warehouses_products.product_id = products.id' , 'left');
                if ($id) {
                    $this->db->where('promotion_categories.promotion_id', $id);
                }

                
                $q = $this->db->get();
                if ($q->num_rows() > 0) {
                    foreach (($q->result()) as $row){
                        $data[] = $row;
                    }
                } else {
                    $data = null;
                }

                // $this->bpas->print_arrays($data);

                if (!empty($data)) {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
    
                    $styleArray = array(
                        'font'  => array(
                            'bold'  => true,
                            'color' => array('rgb' => 'FF3E96'),
                            'size'  => 25,
                            'name'  => 'Verdana'
                        )
                    );
                    $title = array(
                        'font'  => array(
                            'bold'  => true,
                            'color' => array('rgb' => 'FF3E96'),
                            'size'  => 16,
                            'name'  => 'Verdana'
                        )
                    );
    
                    $this->excel->getActiveSheet()->setTitle(lang('Promotion Detail By Date'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', $this->Settings->site_name);
                    $this->excel->getActiveSheet()->getStyle('C1')->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->SetCellValue('C2', lang('Promotion Detail By Date'));
                    $this->excel->getActiveSheet()->getStyle('C2')->applyFromArray($title);
    
                    $this->excel->getActiveSheet()->getStyle('A4:g4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('9AE6FD');
                    $this->excel->getActiveSheet()->setTitle(lang('Promotion Detail By Date'));
                    $this->excel->getActiveSheet()->SetCellValue('A4', lang('No'));
                    $this->excel->getActiveSheet()->SetCellValue('B4', lang('products'). ' ' . lang('code') );
                    $this->excel->getActiveSheet()->SetCellValue('C4', lang('products'). ' ' . lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('D4', lang('quantity'));
                    $this->excel->getActiveSheet()->SetCellValue('E4', lang('price'));
                    $this->excel->getActiveSheet()->SetCellValue('F4', lang('discount'));
                    $this->excel->getActiveSheet()->SetCellValue('G4', lang('amount'));
    
                    $row = 5;
                    $i = 1;
                    // $this->bpas->print_arrays($data);
                    foreach ($data as $data_row) {
                        $amount = explode("__",$data_row->amount);
                        $total_amount = ( $this->bpas->formatDecimal($amount[0]) - $this->bpas->formatDecimal(($amount[0] * floatval($amount[1]))/100));

                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $i++);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->pcode);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->pname);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($data_row->pqty));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->pprice);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->discount);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($total_amount));
                        $row++;
                        $i++;
                    }
    
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                    $this->excel->getActiveSheet()->getStyle('F2:F' . $row)->getAlignment()->setWrapText(true);
                    $filename = 'Promotion Detail By Date';
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }

                $this->session->set_flashdata('error', lang('nothing_found'));
                redirect($_SERVER['HTTP_REFERER']);
        } else{
          
            $this->load->library('datatables');
            $this->datatables
                ->select("
                    {$this->db->dbprefix('promotion_categories')}.product_code as pcode,
                    products.name as pname,
                    {$this->db->dbprefix('warehouses_products')}.quantity as pqty,
                    COALESCE({$this->db->dbprefix('cost_price_by_units')}.price, {$this->db->dbprefix('products')}.price) as pprice,
                    {$this->db->dbprefix('promotion_categories')}.discount,
                    CONCAT(COALESCE({$this->db->dbprefix('cost_price_by_units')}.price, {$this->db->dbprefix('products')}.price),'__',{$this->db->dbprefix('promotion_categories')}.discount) as amount")
                ->from('promotion_categories')
                ->join('cost_price_by_units','cost_price_by_units.product_code = promotion_categories.product_code','left')
                ->join('products','products.id = promotion_categories.product_id' , 'left')
                ->join('warehouses_products','warehouses_products.product_id = products.id' , 'left');
            if ($id) {
                $this->datatables->where('promotion_categories.promotion_id', $id);
            }
            
            echo $this->datatables->generate();
        }
    }
    function getview($id = NULL, $xls = null, $preview= null)
    {
        if($preview){
            $this->db->select("
            {$this->db->dbprefix('promotion_categories')}.product_code as pcode,
            products.name as pname,
            categories.name as category_name,
            {$this->db->dbprefix('warehouses_products')}.quantity as pqty,
            COALESCE({$this->db->dbprefix('cost_price_by_units')}.price, {$this->db->dbprefix('products')}.price) as pprice,
            {$this->db->dbprefix('promotion_categories')}.discount,
            CONCAT( COALESCE({$this->db->dbprefix('cost_price_by_units')}.price, {$this->db->dbprefix('products')}.price),'__',{$this->db->dbprefix('promotion_categories')}.discount) as amount")
            ->from('promotion_categories')
            ->join('promotions','promotions.id = promotion_categories.promotion_id', 'left')
            ->join('cost_price_by_units','cost_price_by_units.product_code = promotion_categories.product_code','left')
            ->join('products','products.id = promotion_categories.product_id' , 'left')
            ->join('categories','categories.id = promotion_categories.category_id', 'left')
            ->join('warehouses_products','warehouses_products.product_id = products.id AND warehouses_products.warehouse_id = promotions.warehouse_id' , 'left');
            
            if ($id) {
                $this->db->where('promotion_categories.promotion_id', $id);
            }
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row){
                    $data[] = $row;
                }
            } else {
                $data = null;
            }
            // $this->bpas->print_arrays($data);

            $this->data['rows'] = $data;
            $this->data['biller'] =  $this->site->getCompanyByID(3);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('promotion')));
            $meta = array('page_title' => lang('promotion'), 'bc' => $bc);
            $this->page_construct('settings/preview_promotion', $meta, $this->data);
        } elseif ($xls) {
                $this->db->select("
                {$this->db->dbprefix('promotion_categories')}.product_code as pcode,
                products.name as pname,
                categories.name as category_name,
                {$this->db->dbprefix('warehouses_products')}.quantity as pqty,
                COALESCE({$this->db->dbprefix('cost_price_by_units')}.price, {$this->db->dbprefix('products')}.price) as pprice,
                {$this->db->dbprefix('promotion_categories')}.discount,
                CONCAT( COALESCE({$this->db->dbprefix('cost_price_by_units')}.price, {$this->db->dbprefix('products')}.price),'__',{$this->db->dbprefix('promotion_categories')}.discount) as amount")
                ->from('promotion_categories')
                ->join('promotions','promotions.id = promotion_categories.promotion_id', 'left')
                ->join('cost_price_by_units','cost_price_by_units.product_code = promotion_categories.product_code','left')
                ->join('products','products.id = promotion_categories.product_id' , 'left')
                ->join('categories','categories.id = promotion_categories.category_id', 'left')
                ->join('warehouses_products','warehouses_products.product_id = products.id AND warehouses_products.warehouse_id = promotions.warehouse_id' , 'left');
                if ($id) {
                    $this->db->where('promotion_categories.promotion_id', $id);
                }

                
                $q = $this->db->get();
                if ($q->num_rows() > 0) {
                    foreach (($q->result()) as $row){
                        $data[] = $row;
                    }
                } else {
                    $data = null;
                }

                if (!empty($data)) {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);

                    $styleArray = array(
                        'font'  => array(
                            'bold'  => true,
                            'color' => array('rgb' => 'FF3E96'),
                            'size'  => 25,
                            'name'  => 'Verdana'
                        )
                    );
                    $title = array(
                        'font'  => array(
                            'bold'  => true,
                            'color' => array('rgb' => 'FF3E96'),
                            'size'  => 16,
                            'name'  => 'Verdana'
                        )
                    );

                    $this->excel->getActiveSheet()->setTitle(lang('Promotion Detail By Date'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', $this->Settings->site_name);
                    $this->excel->getActiveSheet()->getStyle('C1')->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->SetCellValue('C2', lang('Promotion Detail By Date'));
                    $this->excel->getActiveSheet()->getStyle('C2')->applyFromArray($title);

                    $this->excel->getActiveSheet()->getStyle('A4:H4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('9AE6FD');
                    $this->excel->getActiveSheet()->setTitle(lang('Promotion Detail By Date'));
                    $this->excel->getActiveSheet()->SetCellValue('A4', lang('No'));
                    $this->excel->getActiveSheet()->SetCellValue('B4', lang('products'). ' ' . lang('code') );
                    $this->excel->getActiveSheet()->SetCellValue('C4', lang('products'). ' ' . lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('D4', lang('category'));
                    $this->excel->getActiveSheet()->SetCellValue('E4', lang('quantity'));
                    $this->excel->getActiveSheet()->SetCellValue('F4', lang('price'));
                    $this->excel->getActiveSheet()->SetCellValue('G4', lang('discount'));
                    $this->excel->getActiveSheet()->SetCellValue('H4', lang('amount'));

                    $row = 5;
                    $i = 1;
                    foreach ($data as $data_row) {
                        $amount = explode("__",$data_row->amount);
                        $total_amount = !empty($data_row->amount) ? ( $this->bpas->formatDecimal($amount[0]) - $this->bpas->formatDecimal(($amount[0] * floatval($amount[1]))/100)) : 0;

                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $i++);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->pcode);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->pname);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->category_name);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($data_row->pqty));
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->pprice);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->discount);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($total_amount));
                        $row++;
                        $i++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                    $this->excel->getActiveSheet()->getStyle('F2:F' . $row)->getAlignment()->setWrapText(true);
                    $filename = 'Promotion Detail By Date';
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }

                $this->session->set_flashdata('error', lang('nothing_found'));
                redirect($_SERVER['HTTP_REFERER']);
        } else{
            $this->load->library('datatables');
            $this->datatables
                ->select("
                    {$this->db->dbprefix('promotion_categories')}.product_code as pcode,
                    products.name as pname,
                    categories.name as category_name,
                    {$this->db->dbprefix('warehouses_products')}.quantity as pqty,
                    COALESCE({$this->db->dbprefix('cost_price_by_units')}.price, {$this->db->dbprefix('products')}.price) as pprice,
                    {$this->db->dbprefix('promotion_categories')}.discount,
                    CONCAT(COALESCE({$this->db->dbprefix('cost_price_by_units')}.price, {$this->db->dbprefix('products')}.price),'__',{$this->db->dbprefix('promotion_categories')}.discount) as amount")
                ->from('promotion_categories')
                ->join('promotions','promotions.id = promotion_categories.promotion_id', 'left')
                ->join('cost_price_by_units','cost_price_by_units.product_code = promotion_categories.product_code','left')
                ->join('products','products.id = promotion_categories.product_id' , 'left')
                ->join('categories','categories.id = promotion_categories.category_id', 'left')
                ->join('warehouses_products','warehouses_products.product_id = products.id AND warehouses_products.warehouse_id = promotions.warehouse_id' , 'left');
                
            if ($id) {
                $this->datatables->where('promotion_categories.promotion_id', $id);
            }
            
            echo $this->datatables->generate();
        }
    }

    function add_promotion()
    {
        $this->form_validation->set_rules('description', lang("description"), 'trim|is_unique[promotions.description]|required');
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'trim|required');
        $this->form_validation->set_rules('start_date', lang("start_date"), 'trim|required');
        $this->form_validation->set_rules('end_date', lang("end_date"), 'trim|required');
        if ($this->form_validation->run() == true) {    
            $data = array(
                'description' => $this->input->post('description'),
                'warehouse_id' => $this->input->post('warehouse'),
                'start_date'  => $this->input->post('start_date') ? $this->bpas->fsd(trim($this->input->post('start_date'))) : null,
                'end_date'    => $this->input->post('end_date') ? $this->bpas->fsd(trim($this->input->post('end_date'))) : null,
            );

            
            $promo_type = $this->input->post('promotion_type');

            if($promo_type == 0){
                $this->session->set_flashdata('error', lang("select_type_of_promotion"));
                admin_redirect("system_settings/promotion");
            }elseif($promo_type == 1) {
                $cate_id = $this->input->post('arr_cate');
            }elseif($promo_type == 2){
                $pro_code = $this->input->post('arr_pr');
                $pro_id = $this->input->post('arr_pro_id');
            }

            // $this->bpas->print_arrays($pro_code,$pro_id);
            
            $discount = $this->input->post('percent_tag');
        
            if(!empty($cate_id)){
                for($i=0; $i<count($cate_id); $i++)
                {
                    $arr[]=array('category_id'=>$cate_id[$i],'discount'=>$discount[$i]);
                }
            }elseif($pro_code){
                for($i = 0; $i<count($pro_code); $i++)
                {
                    $arr[]=array('product_id'=>$pro_id[$i],'product_code'=>$pro_code[$i],'discount'=>$discount[$i]);
                }
            }
            // $this->bpas->print_arrays($arr);
        } elseif ($this->input->post('add_promotion')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("system_settings/promotion");
        }
       
        if ($this->form_validation->run() == true && $this->settings_model->addPromotion($data,$arr)) {
            $this->session->set_flashdata('message', lang("promotion_added"));
            admin_redirect("system_settings/promotion");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['categories'] = $this->site->getAllCategoriesMakeup();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_promotion', $this->data);
        }
    }
    public function productSuggestionByMultiUnit()
    {
        $term = $this->input->get('term', true);
        if (strlen($term) < 1) {
            die();
        }

        $rows = $this->settings_model->getProductNamesByMultiunit($term);
        if ($rows) {
            foreach ($rows as $row) {
                $pr[] = ['id' => $row->id, 'code' => $row->code, 'label' => $row->name . ' (' . $row->code . ')'];
                // $pr[] = ['id' => $row->id, 'code' => $row->code, 'label' => $row->name . ' (' . ($row->code ? $row->code : $row->pro_code )  . ')'];
            }
            $this->bpas->send_json($pr);
        } else {
            echo false;
        }
    }
    function edit_promotion($id = NULL)
    {
        $this->form_validation->set_rules('description', lang("description"), 'trim|required');
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'trim|required');
        $this->form_validation->set_rules('start_date', lang("start_date"), 'trim|required');
        $this->form_validation->set_rules('end_date', lang("end_date"), 'trim|required');
        $promotions = $this->settings_model->getPromotion($id);
        if ($this->input->post('promotions') != $promotions->description) {
            $this->form_validation->set_rules('promotions', lang("promotions"), 'is_unique[promotions.description]');
        }
        if ($this->form_validation->run() == true) {

            $data = array(
                'description' => $this->input->post('description'),
                'warehouse_id' => $this->input->post('warehouse'),
                'start_date'  => $this->input->post('start_date') ? $this->bpas->fsd(trim($this->input->post('start_date'))) : null,
                'end_date'    => $this->input->post('end_date') ? $this->bpas->fsd(trim($this->input->post('end_date'))) : null,
            );
            $promo_type = $this->input->post('promotion_type');
            if($promo_type == 0){
                $this->session->set_flashdata('error', lang("select_type_of_promotion"));
                admin_redirect("system_settings/promotion");
            }elseif($promo_type == 1) {
                $cate_id = $this->input->post('arr_cate');
            }elseif($promo_type == 2){
                $pro_code = $this->input->post('arr_pr');
                $pro_id = $this->input->post('arr_pro_id');
            }
            // $this->bpas->print_arrays($cate_id);
            // $cate_id = $this->input->post('arr_cate');
            $percent = $this->input->post('percent_tag');
            if(!empty($cate_id)){
                for($i=0;$i<count($cate_id);$i++)
                {
                    $arr[]=array('category_id'=>$cate_id[$i],'discount'=>$percent[$i]);
                }
            }elseif($pro_code){
                for($i = 0; $i<count($pro_code); $i++)
                {
                    $arr[]=array('product_id'=>$pro_id[$i],'product_code'=>$pro_code[$i],'discount'=>$percent[$i]);
                }
            }
            // $this->bpas->print_arrays($arr);
            
        } elseif ($this->input->post('edit_promotion')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("system_settings/promotion");
        }

        if ($this->form_validation->run() == true && $this->settings_model->updatePromotion($id, $data,$arr)) {
            $this->session->set_flashdata('message', lang("customer_group_updated"));
            admin_redirect("system_settings/promotion");
        } else {
            $this->data['error']        = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['promotions']   = $this->settings_model->getPromotion($id);
            $this->data['id']           = $id;
            $this->data['cate_id']      = $this->settings_model->Old_promotions($id);
            $this->data['pro_code']      = $this->settings_model->Old_promotionsByProductCode($id);
            // var_dump($this->data['pro_code']);exit();
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['categories']   = $this->site->getAllCategoriesMakeup();
            $this->data['modal_js']     = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_promotion', $this->data);
        }
    }
 
    function delete_promotion($id = NULL)
    {
        if ($this->settings_model->deletePromotion($id)) {
            $this->session->set_flashdata('message', lang("promotion_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    function import_promotions()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('csv_file', $this->lang->line("upload_file"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if (isset($_FILES["excel_file"]))  {
                $this->load->library('excel');
                $path = $_FILES["excel_file"]["tmp_name"];
                $object = PHPExcel_IOFactory::load($path);
                $description    = $this->input->post('description');
                $warehouse      = $this->input->post('warehouse');
                $start_date     = $this->bpas->fld(trim($this->input->post('start_date')));
                $end_date       = $this->bpas->fld(trim($this->input->post('end_date')));
                foreach($object->getWorksheetIterator() as $worksheet){
                    $highestRow         = $worksheet->getHighestRow();
                    $highestColumn      = $worksheet->getHighestColumn();
                    for($row = 2; $row <= $highestRow ; $row++){
                        $product_code   = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
                        $category_code  = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                        $discount       = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                        if(!empty($product_code) && !$this->site->getProductByCode($product_code)){
                            $this->session->set_flashdata('error', lang("check_product_code") . " (" . $product_code . ") " . lang("in_line_number") . " " . $row );
                            admin_redirect("system_settings/promotion");
                        }
                        if(!empty($category_code) && !$this->settings_model->getCategoryByCode($category_code)){
                            $this->session->set_flashdata('error', lang("check_category_code") . " (" . $category_code . ") " . lang("line_no") . " " . $row );
                            admin_redirect("system_settings/promotion");
                        }
                        if(!empty($product_code && $discount) || !empty($category_code && $discount)) {
                            $product  = $this->site->getProductByCode($product_code);
                            $category = $this->settings_model->getCategoryByCode($category_code);
                        
                            $data[] = array(
                            'category_id'   => !empty($category) ? $category->id : null,
                            'product_id'    => !empty($product) ? $product->id : null,
                            'product_code'  => $product_code,
                            'discount'      => $discount,
                            );
                        } else {
                            continue;
                        }
                    }
                    $promotion = array(
                        'description'   => $description,
                        'warehouse_id'  => $warehouse,
                        'start_date'    => $start_date,
                        'end_date'      => $end_date
                    );
                }   
            }
        } elseif ($this->input->post('import')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/promotion');
        }
        if ($this->form_validation->run() == true && $this->settings_model->import_promotions($data, $promotion)) {
            $this->session->set_flashdata('message', lang('promotions_added') . $updated);
            admin_redirect('system_settings/promotion');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->load->view($this->theme . 'settings/import_promotions', $this->data);
        }
    }

    public function rewards()
    {
        $this->bpas->checkPermissions('rewards', null, 'system_settings');
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc    = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('rewards')]];
        $meta  = ['page_title' => lang('rewards'), 'bc' => $bc];
        $this->page_construct('settings/rewards', $meta, $this->data);
    }

    public function getRewards($category, $type) 
    {
        $this->load->library('datatables');
        if ($type == 'product') {
            $this->datatables->select("
                    {$this->db->dbprefix('rewards')}.id AS id, 
                    {$this->db->dbprefix('rewards')}.category AS category, 
                    {$this->db->dbprefix('rewards')}.type AS type, 
                    {$this->db->dbprefix('exchange_products')}.name AS exchange_product, 
                    {$this->db->dbprefix('rewards')}.exchange_quantity, 
                    {$this->db->dbprefix('rewards')}.amount, 
                    {$this->db->dbprefix('receive_products')}.name AS receive_product, 
                    {$this->db->dbprefix('rewards')}.receive_quantity
                ");
        } else {
            $this->datatables->select("
                    {$this->db->dbprefix('rewards')}.id AS id, 
                    {$this->db->dbprefix('rewards')}.category AS category, 
                    {$this->db->dbprefix('rewards')}.type AS type, 
                    {$this->db->dbprefix('exchange_products')}.name AS exchange_product, 
                    {$this->db->dbprefix('rewards')}.exchange_quantity,
                    {$this->db->dbprefix('rewards')}.amount,
                    {$this->db->dbprefix('rewards')}.interest
                ");
        }
        $this->datatables->from('rewards');
        $this->datatables->join('products bpas_exchange_products', 'exchange_products.id = rewards.exchange_product_id', 'left');
        $this->datatables->join('products bpas_receive_products', 'receive_products.id = rewards.receive_product_id', 'left');
        $this->datatables->where('rewards.category', $category);
        $this->datatables->where('rewards.type', $type);
        $this->datatables->add_column('Actions', "
                <div class=\"text-center\">
                    <a href='" . admin_url('system_settings/edit_reward/$1/$2/$3') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_reward') . "'>
                        <i class=\"fa fa-edit\"></i>
                    </a> 
                    <a href='#' class='tip po' title='<b>" . lang('delete_reward') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p>
                        <a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_reward/$3') . "'>" . lang('i_m_sure') . "</a> 
                        <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'>
                        <i class=\"fa fa-trash-o\"></i>
                    </a>
                </div>", 'category, type, id');
        $this->datatables->unset_column('category');
        $this->datatables->unset_column('type');
        $this->datatables->order_by('id', 'ASC');
        echo $this->datatables->generate();
    }

    public function add_reward($category, $type)
    {
        $this->form_validation->set_rules('exchange_product_id', lang('exchange_product'), 'trim|required');
        $this->form_validation->set_rules('exchange_quantity', lang('exchange_quantity'), 'trim|required');
        $this->form_validation->set_rules('amount', lang('amount'), 'trim|required');
        if ($type == 'product') {
            $this->form_validation->set_rules('receive_product_id', lang('receive_product'), 'trim|required');
            $this->form_validation->set_rules('receive_quantity', lang('receive_quantity'), 'trim|required');
        } else {
            $this->form_validation->set_rules('interest', lang('interest'), 'trim|required');
        }
        if ($this->form_validation->run() == true) {
            $data = [
                'category'            => $category,
                'type'                => $type,
                'exchange_product_id' => $this->input->post('exchange_product_id'),
                'exchange_quantity'   => $this->input->post('exchange_quantity'),
                'receive_product_id'  => $this->input->post('receive_product_id'),
                'receive_quantity'    => $this->input->post('receive_quantity'),
                'amount'              => $this->input->post('amount'),
                'interest'            => $this->input->post('interest'),
            ];
        } elseif ($this->input->post('add_reward')) {
            $this->session->set_flashdata('error', validation_errors());
           redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->settings_model->addReward($data)) {
            $this->session->set_flashdata('message', lang('reward_added'));
           redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['category'] = $category;
            $this->data['type']     = $type;
            $this->load->view($this->theme . 'settings/add_reward', $this->data);
        }
    }

    public function edit_reward($category, $type, $id)
    {
        $this->form_validation->set_rules('exchange_product_id', lang('exchange_product'), 'trim|required');
        $this->form_validation->set_rules('exchange_quantity', lang('exchange_quantity'), 'trim|required');
        $this->form_validation->set_rules('amount', lang('amount'), 'trim|required');
        if ($type == 'product') {
            $this->form_validation->set_rules('receive_product_id', lang('receive_product'), 'trim|required');
            $this->form_validation->set_rules('receive_quantity', lang('receive_quantity'), 'trim|required');
        } else {
            $this->form_validation->set_rules('interest', lang('interest'), 'trim|required');
        }
        $reward_details = $this->settings_model->getRewardByID($id);
        if ($this->form_validation->run() == true) {
            $data = [
                'category'            => $category,
                'type'                => $type,
                'exchange_product_id' => $this->input->post('exchange_product_id'),
                'exchange_quantity'   => $this->input->post('exchange_quantity'),
                'receive_product_id'  => $this->input->post('receive_product_id'),
                'receive_quantity'    => $this->input->post('receive_quantity'),
                'amount'              => $this->input->post('amount'),
                'interest'            => $this->input->post('interest'),
            ];
        } elseif ($this->input->post('edit_reward')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/rewards');
        }
        if ($this->form_validation->run() == true && $this->settings_model->updateReward($id, $data)) {
            $this->session->set_flashdata('message', lang('reward_updated'));
            admin_redirect('system_settings/rewards');
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['id']       = $id;
            $this->data['reward']   = $reward_details;
            $this->data['category'] = $category;
            $this->data['type']     = $type;
            $this->load->view($this->theme . 'settings/edit_reward', $this->data);
        }
    }

    public function delete_reward($id = null)
    {
        if ($this->settings_model->deleteReward($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('reward_deleted')]);
        }
    }

    public function rewards_export_excel($category, $type)
    {
        $rewards = $this->settings_model->getRewardsByGroup($category, $type);
        if (!empty($rewards)) {
            $this->load->library('excel');
            $this->excel->setActiveSheetIndex(0);
            $this->excel->getActiveSheet()->setTitle(lang($category) . ' ' . lang($type) . ' ' . lang('rewards'));
            if ($type == 'product') {
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('exchange_product'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('exchange_quantity'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('amount'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('receive_product'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('receive_quantity'));
                $row = 2;
                foreach ($rewards as $reward) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $reward->exchange_product);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $reward->exchange_quantity);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $reward->amount);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $reward->receive_product);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $reward->receive_quantity);
                    $row++;
                }
            } else {
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('exchange_product'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('exchange_quantity'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('amount'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('interest'));
                $row = 2;
                foreach ($rewards as $reward) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $reward->exchange_product);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $reward->exchange_quantity);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $reward->amount);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $reward->interest);
                    $row++;
                }
            }
            $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $filename = $category . '_' . $type . '_rewards_' . date('Y_m_d_H_i_s');
            $this->load->helper('excel');
            create_excel($this->excel, $filename);
        } else {
            $this->session->set_flashdata('error', lang('no_data_found!'));
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function sales_rank()
    {
        $this->data['error']       = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                        = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')],  ['link' => admin_url('system_settings/multi_buy_groups'), 'page' => lang('multi_buys')], ['link' => '#', 'page' => lang('group_product_prices')]];
        $meta                      = ['page_title' => lang('Sales_Rank'), 'bc' => $bc];
        $this->data['table_compare'] = $this->settings_model->table_compare();
        $this->page_construct('settings/sales_rank', $meta, $this->data);
    }
    
    public function getSales_rank_permmission($group_id = null)
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("
                {$this->db->dbprefix('sales_rank_commission')}.id as id, 
                {$this->db->dbprefix("sales_rank_commission")}.start_rank, 
                {$this->db->dbprefix("sales_rank_commission")}.end_rank,
                COALESCE({$this->db->dbprefix("sales_rank_commission")}.commission,'') as commission")
            ->from('sales_rank_commission')
            ->order_by('sales_rank_commission.commission')
            ->add_column('Actions', '<div class="text-center"><button class="btn btn-primary btn-xs form-submit" type="button"><i class="fa fa-check"></i></button></div>', 'id');

        echo $this->datatables->generate();
    }

    public function sales_rank_action()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteSalesRank($id);
                    }
                    $this->session->set_flashdata('message', lang('sale_ranks_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('sale_rank'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('start_rank'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('end_rank'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('commission'));
                    $row   = 2;
                   
                    foreach ($_POST['val'] as $id) {
                        $sale_rank = $this->settings_model->getSaleRankByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sale_rank->start_rank);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sale_rank->end_rank);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sale_rank->commission);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'Sale_ranks_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_salerank_commission_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function insert_sales_rank_commission()
    {
        $sale_rank_id = $this->input->post('sales_rank_id', true);
        $data = array(
            'start_rank'  => $this->input->post('rank1', true),
            'end_rank'    => $this->input->post('rank2', true),
            'commission'  => $this->input->post('commiss', true)
        );
      
        if(!empty($this->input->post('rank1', true) && $this->input->post('rank1', true) != '0') && !empty($this->input->post('rank2', true) && $this->input->post('rank2', true) != '0') &&! empty($this->input->post('commiss', true))) {
            if(!empty($sale_rank_id))
            {
                if ($this->settings_model->update_sale_rank_commission($data, $sale_rank_id)) {
                    $this->bpas->send_json(['status' => 1]);
                }
            } else {
                if ($insert_id = $this->settings_model->insert_sale_rank_commission($data)) {
                    $this->bpas->send_json(['status' => 1 , 'id' => $insert_id]);                    
                }  
            }
        }
        $this->bpas->send_json(['status' => 0]);
    }
    public function racks()
    {
        $this->bpas->checkPermissions('rack', null,'system_settings');
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('rack')]];
        $meta                = ['page_title' => lang('rack'), 'bc' => $bc];
        $this->page_construct('settings/rack', $meta, $this->data);

    }
    public function getRacks()
    {
        $print_barcode = '';
        $this->load->library('datatables');
        $this->datatables->select("
                {$this->db->dbprefix('product_rack')}.id as id, 
                {$this->db->dbprefix('product_rack')}.code, 
                {$this->db->dbprefix('product_rack')}.name, 

                c.name as parent", false)
            ->from('product_rack')
            ->join('product_rack c', 'c.id=product_rack.parent_id', 'left')
            ->group_by('product_rack.id')
            ->order_by('product_rack.code')
            ->add_column('Actions', '<div class="text-center">' . $print_barcode . " <a href='" . admin_url('system_settings/edit_rack/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_rack') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_rack') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_rack/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }
    public function add_rack()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang('category_code'), 'trim|is_unique[categories.code]|required');
        $this->form_validation->set_rules('name', lang('name'), 'required|min_length[3]');   
        $this->form_validation->set_rules('description', lang('description'), 'trim');
     
        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'code'        => $this->input->post('code'),
                'description' => $this->input->post('description'),
                'parent_id'   => $this->input->post('parent')
            ];
        } elseif ($this->input->post('add_rack')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->settings_model->addRack($data)) {
            $this->session->set_flashdata('message', lang('rack_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']      = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['racks'] = $this->settings_model->getParentRacks();
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['modal_js']   = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_rack', $this->data);
        }
    }
     public function edit_rack($id = null)
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('code', lang('category_code'), 'trim|required');
        $pr_details = $this->settings_model->getRackByID($id);
        if ($this->input->post('code') != $pr_details->code) {
            $this->form_validation->set_rules('code', lang('category_code'), 'required|is_unique[categories.code]');
        }
        $this->form_validation->set_rules('name', lang('category_name'), 'required|min_length[3]');
        $this->form_validation->set_rules('userfile', lang('category_image'), 'xss_clean');
        $this->form_validation->set_rules('description', lang('description'), 'trim');

        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'code'        => $this->input->post('code'),
                'description' => $this->input->post('description'),
                'parent_id'   => $this->input->post('parent')
            ];
        } elseif ($this->input->post('edit_rack')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/racks');
        }
        if ($this->form_validation->run() == true && $this->settings_model->updateRack($id, $data)) {
            $this->session->set_flashdata('message', lang('rack_updated'));
            admin_redirect('system_settings/racks');
        } else {
            $this->data['error']      = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['category']   = $this->settings_model->getRackByID($id);
            $this->data['categories'] = $this->settings_model->getParentRacks();
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['modal_js']   = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_rack', $this->data);
        }
    }
    public function delete_rack($id = null)
    {
        if ($this->settings_model->getSubRacks($id)) {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('rack_has_subcategory')]);
        }

        if ($this->settings_model->deleteRack($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('rack_deleted')]);
        }
    }
    public function tanks()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('tanks')));
        $meta = array('page_title' => lang('tanks'), 'bc' => $bc);
        $this->page_construct('settings/fuel_tanks', $meta, $this->data);
    }

    public function getTanks()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("
                        tanks.id as id,
                        tanks.code,
                        tanks.name,
                        warehouses.name as warehouse_name
                    ")
            ->from("tanks")
            ->join("warehouses","warehouses.id = tanks.warehouse_id","left")
            ->add_column("Actions", "<center><a class=\"tip\" title='" . lang("view_nozzle_start_no") . "' href='" . admin_url('system_settings/view_nozzle_start_no/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-eye\"></i></a> <a class=\"tip\" title='" . lang("add_nozzle_start_no") . "' href='" . admin_url('system_settings/add_nozzle_start_no/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-plus\"></i></a> <a class=\"tip\" title='" . $this->lang->line("edit_tank") . "' href='" . admin_url('system_settings/edit_tank/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-edit\"></i></a>  <a href='#' class='tip po' title='<b>" . $this->lang->line("edit_tank") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_tank/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></center>", "id");
        echo $this->datatables->generate();
    }

    public function delete_tank($id = NULL)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->settings_model->deleteTank($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('tank_deleted')]);
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function add_tank()
    {
        $this->form_validation->set_rules('name', $this->lang->line("code"), 'is_unique[tanks.code]');
        if ($this->form_validation->run() == true) {
            $data = array(
                    'code' => $this->input->post('code'),
                    'name' => $this->input->post('name'),
                    'warehouse_id' => $this->input->post('warehouse')
                );
        }else if($this->input->post('add_tank')){
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $sid = $this->settings_model->addTank($data)) {
            $this->session->set_flashdata('message', lang("tank_added"));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->load->view($this->theme . 'settings/add_tank', $this->data);
        }
    }

    public function edit_tank($id = false)
    {
        $tank = $this->settings_model->getTankByID($id);
        $this->form_validation->set_rules('code', $this->lang->line("code"), 'required');
        if ($this->input->post('code') != $tank->code) {
            $this->form_validation->set_rules('code', lang("code"), 'is_unique[tanks.code]');
        }
        if ($this->form_validation->run() == true) {
            $data = array(
                    'code' => $this->input->post('code'),
                    'name' => $this->input->post('name'),
                    'warehouse_id' => $this->input->post('warehouse'),
                    'inactive' => $this->input->post('inactive')
                );
        }else if($this->input->post('edit_tank')){
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $sid = $this->settings_model->updateTank($id, $data)) {
             $this->session->set_flashdata('message', lang("tank_updated"));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['id'] = $id;
            $this->data['row'] = $tank;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->load->view($this->theme . 'settings/edit_tank', $this->data);
        }
    }

    public function tank_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteTank($id);
                    }
                    $this->session->set_flashdata('message', lang("tanks_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('tanks'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('warehouse'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->settings_model->getTankByID($id);
                        $warehouse = $this->site->getWarehouseByID($sc->warehouse_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('c' . $row, $warehouse->name);
                        $row++;
                    }
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'tanks_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    function view_nozzle_start_no($id = false)
    {
        $this->data['id'] = $id;
        $this->data['tank'] = $this->settings_model->getTankByID($id);
        $this->data['modal_js'] = $this->site->modal_js();
        $this->load->view($this->theme . 'settings/view_nozzle_start_no', $this->data);
    }

    function getNozzleStartNo($id = NULL)
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("
                    tank_nozzles.id as id,
                    products.name as product_name,
                    tank_nozzles.nozzle_no,
                    tank_nozzles.nozzle_start_no", false)
            ->from("tank_nozzles")
            ->join("products","products.id=product_id","left")
            ->where($this->db->dbprefix('tank_nozzles').'.tank_id', $id)
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . lang("edit_nozzle_start_no") . "' href='" . admin_url('system_settings/edit_nozzle_start_no/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal2'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_nozzle_start_no") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_nozzle_start_no/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        $this->datatables->unset_column("id");
        echo $this->datatables->generate();
    }
    function add_nozzle_start_no($id = false)
    {
        $this->form_validation->set_rules('nozzle_no', $this->lang->line("nozzle_no"), 'required');
        if ($this->form_validation->run() == true){
            $data = array(
                'tank_id'   => $id,
                'product_id'        => $this->input->post('product'),
                'nozzle_no'             => $this->input->post('nozzle_no'),
                'nozzle_start_no'   => $this->input->post('nozzle_start_no'),
                'saleman_id' => json_encode($this->input->post('saleman')),
            );
            $salesman = false;
            if($this->input->post('saleman')){
                foreach($this->input->post('saleman') as $saleman){
                    $salesman[] = array("tank_id"=>$id,"saleman_id" => $saleman);
                }
            }
        }else if($this->input->post('add_nozzle_start_no')){
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $sid = $this->settings_model->addNozzleStartNo($data,$salesman)) {
            $this->session->set_flashdata('message', lang("nozzle_start_no_added"));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['id'] = $id;
            $this->data['tank'] = $this->settings_model->getTankByID($id);
            $this->data['products'] = $this->site->getAllProducts();
            $this->data['salemans'] = $this->site->getSalemans();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_nozzle_start_no', $this->data);
        }
    }

    function edit_nozzle_start_no($id = false)
    {
        $row = $this->settings_model->getNozzleStartNoByID($id);
        $this->form_validation->set_rules('nozzle_no', $this->lang->line("nozzle_no"), 'required');
        if ($this->form_validation->run() == true){
            $data = array(
                'tank_id'   => $row->tank_id,
                'product_id'        => $this->input->post('product'),
                'nozzle_no'             => $this->input->post('nozzle_no'),
                'nozzle_start_no'   => $this->input->post('nozzle_start_no'),
                'saleman_id' => json_encode($this->input->post('saleman')),
            );
            $salesman = false;
            if($this->input->post('saleman')){
                foreach($this->input->post('saleman') as $saleman){
                    $salesman[] = array("tank_id"=>$row->tank_id,"nozzle_id"=>$row->id,"saleman_id" => $saleman);
                }
            }
            
        }else if($this->input->post('edit_nozzle_start_no')){
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $sid = $this->settings_model->updateNozzleStartNo($id, $data, $salesman)) {
            $this->session->set_flashdata('message', lang("nozzle_start_no_updated"));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['id'] = $id;
            $this->data['row'] = $row;
            $this->data['products'] = $this->site->getAllProducts();
            $this->data['salemans'] = $this->site->getSalemans();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_nozzle_start_no', $this->data);
        }
    }
    public function delete_nozzle_start_no($id = NULL)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->settings_model->deleteNozzleStartNo($id)) {
            echo $this->lang->line("nozzle_start_no_deleted"); exit;
        }
        $this->session->set_flashdata('message', lang("nozzle_start_no_deleted"));
        redirect($_SERVER['HTTP_REFERER']);
    }
    public function fuel_times()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')), array('link' => '#', 'page' => lang('fuel_times')));
        $meta = array('page_title' => lang('fuel_times'), 'bc' => $bc);
        $this->page_construct('settings/fuel_times', $meta, $this->data);
    }

    public function getFuelTimes()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select("
                    fuel_times.id as id,
                    fuel_times.open_time,
                    fuel_times.close_time")
            ->from("fuel_times")
            ->add_column("Actions", "<center><a class=\"tip\" title='" . $this->lang->line("edit_fuel_time") . "' href='" . admin_url('system_settings/edit_fuel_time/$1') . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-edit\"></i></a>  <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_fuel_time") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_fuel_time/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></center>", "id");
        echo $this->datatables->generate();
    }

    public function add_fuel_time()
    {
        $this->form_validation->set_rules('open_time', $this->lang->line("open_time"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
                    'open_time' => $this->input->post('open_time'),
                    'close_time' => $this->input->post('close_time')
                );
        }else if($this->input->post('add_fuel_time')){
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $sid = $this->settings_model->addFuelTime($data)) {
            $this->session->set_flashdata('message', lang("fuel_time_added"));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/add_fuel_time', $this->data);
        }
    }

    public function edit_fuel_time($id = false)
    {
        $fuel = $this->settings_model->getFuelTimesByID($id);
        $this->form_validation->set_rules('open_time', $this->lang->line("open_time"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
                    'open_time' => $this->input->post('open_time'),
                    'close_time' => $this->input->post('close_time')
                );
        }else if($this->input->post('edit_fuel_time')){
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $sid = $this->settings_model->updateFuelTime($id, $data)) {
             $this->session->set_flashdata('message', lang("fuel_time_updated"));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['id'] = $id;
            $this->data['fuel'] = $fuel;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'settings/edit_fuel_time', $this->data);
        }
    }

    public function fuel_time_actions()
    {
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteFuelTime($id);
                    }
                    $this->session->set_flashdata('message', lang("fuel_time_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('fuel_time'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('open_time'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('close_time'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $fuel = $this->settings_model->getFuelTimesByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $fuel->open_time);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $fuel->close_time);
                        $row++;
                    }
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'fuel_time_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function delete_fuel_time($id = NULL)
    {
        if ($this->settings_model->deleteFuelTime($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('fuel_time_deleted')]);
        }
    }
}