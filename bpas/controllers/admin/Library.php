<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Library extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->bpas->md('login');
        }
        $this->lang->admin_load('products', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('schools_model');
        $this->load->admin_model('purchases_model');
        $this->load->admin_model('products_model');
        $this->load->admin_model('assets_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path         = 'assets/uploads/';
        $this->thumbs_path         = 'assets/uploads/thumbs/';
        $this->image_types         = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types  = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size   = '1024';
        $this->popup_attributes    = ['width' => '900', 'height' => '600', 'window_name' => 'sma_popup', 'menubar' => 'yes', 'scrollbars' => 'yes', 'status' => 'no', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0'];
    }
    public function index($warehouse_id = null)
    {
         $this->bpas->checkPermissions();
         $count = explode(',', $this->session->userdata('warehouse_id'));

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            if (count($count)>1) {
                $this->data['warehouses']   = $this->site->getAllWarehouses();
            } else {
                $this->data['warehouses']   = null;
            }
            
            $this->data['count_warehouses'] = $count;
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        }

        $this->data['supplier'] = $this->input->get('supplier') ? $this->site->getCompanyByID($this->input->get('supplier')) : null;
        $bc                     = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('books')]];
        $meta                   = ['page_title' => lang('books'), 'bc' => $bc];
        $this->page_construct('library/books', $meta, $this->data);
    }
    public function getbooks($warehouse_id = null)
    {
        $this->bpas->checkPermissions('index', true);
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : null;

        // if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
        //     $user         = $this->site->getUser();
        //     $warehouse_id = $user->warehouse_id;
        // }
        $detail_link = anchor('admin/products/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('details'));
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line('delete_asset') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete1' id='a__$1' href='" . admin_url('library/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_book') . '</a>';
        $single_barcode = anchor('admin/products/print_barcodes/$1', '<i class="fa fa-print"></i> ' . lang('print_barcode_label'));
        // $single_label = anchor_popup('products/single_label/$1/' . ($warehouse_id ? $warehouse_id : ''), '<i class="fa fa-print"></i> ' . lang('print_label'), $this->popup_attributes);
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li><a href="' . admin_url('library/add/$1') . '"><i class="fa fa-plus-square"></i> ' . lang('duplicate_book') . '</a></li>
            <li><a href="' . admin_url('library/edit/$1') . '"><i class="fa fa-edit"></i> ' . lang('edit_book') . '</a></li>';
        
       
        $evaluation_table = anchor('admin/library/expenses', '<i class="fa fa-file-text-o"></i> ' . lang('depreciation_table'));

        $action .= '
  
            <li><a href="' . base_url() . 'library/uploads/$2" data-type="image" data-toggle="lightbox"><i class="fa fa-file-photo-o"></i> '
            . lang('view_image') . '</a>
            </li>
            <li>' . $single_barcode . '</li>
            
            <li class="divider"></li>
            <li>' . $delete_link . '</li>
            </ul>
        </div></div>';
        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
            ->select($this->db->dbprefix('products') . ".id as productid, 
            {$this->db->dbprefix('products')}.image as image, 
            {$this->db->dbprefix('products')}.code as code, 
            {$this->db->dbprefix('products')}.name as name, 
            {$this->db->dbprefix('products')}.serial_no as serial_no, 
            {$this->db->dbprefix('categories')}.name as cname, 
            cost as cost, 
            price as price, 
            COALESCE(wp.quantity, 0) as quantity, 
            {$this->db->dbprefix('units')}.code as unit", false)
            ->from('products');

            
            $this->datatables->where('products.warehouse_id', $warehouse_id);
       

            $this->datatables->join('categories', 'products.category_id=categories.id', 'left')
            ->join('units', 'products.unit=units.id', 'left')
            ->join('brands', 'products.brand=brands.id', 'left');
            $this->datatables->where('products.type','book');

        } else {
            $this->datatables
                ->select($this->db->dbprefix('products') . ".id as productid, 
                {$this->db->dbprefix('products')}.image as image, 
                {$this->db->dbprefix('products')}.code as code, 
                {$this->db->dbprefix('products')}.name as name, 
                {$this->db->dbprefix('products')}.serial_no as serial_no,
                {$this->db->dbprefix('categories')}.name as cname, 
                cost as cost,
                COALESCE(quantity, 0) as quantity, 
                {$this->db->dbprefix('units')}.code as unit", false)
                ->from('products')
                ->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->where('products.type','book');
        }
        if (!$this->Owner && !$this->Admin) {
   
        //    $this->datatables->where("assets.biller_id",$this->session->userdata('biller_id'));
        }

        $this->datatables->add_column('Actions', $action, 'productid, image, code, name');
        echo $this->datatables->generate();
    }
    public function add($id = null,$type=null)
    {
        $this->bpas->checkPermissions();
        $this->load->helper('security');
        $warehouses = $this->site->getAllWarehouses();
        $this->form_validation->set_rules('category', lang('category'), 'required|is_natural_no_zero');
        if ($this->input->post('type') == 'standard') {
            $this->form_validation->set_rules('cost', lang('product_cost'), 'required');
            $this->form_validation->set_rules('unit', lang('product_unit'), 'required');
        }
        $this->form_validation->set_rules('code', lang('product_code'), 'is_unique[products.code]|alpha_dash');

        $this->form_validation->set_rules('weight', lang('weight'), 'numeric');
        $this->form_validation->set_rules('product_image', lang('product_image'), 'xss_clean');
        $this->form_validation->set_rules('digital_file', lang('digital_file'), 'xss_clean');
        $this->form_validation->set_rules('userfile', lang('product_gallery_images'), 'xss_clean');
        $this->form_validation->set_rules('serial_no', lang('serial_no'), 'is_unique[products.serial_no]|alpha_dash');
        $serial_num = $this->input->post('barcode');
        $product_code = $this->input->post('code');
        $prod_code = $serial_num?($product_code."|".$serial_num):$product_code;

        if ($this->form_validation->run() == true) {
            $tax_rate = $this->input->post('tax_rate') ? $this->site->getTaxRateByID($this->input->post('tax_rate')) : null;
            $data     = [
                'code'              => $prod_code,
                'barcode_symbology' => $this->input->post('barcode_symbology'),
                'serial_no'         => $serial_num,
                'max_serial'        => $this->input->post('max_serial'),
                'name'              => $this->input->post('name'),
                'type'              => 'book',
                'brand'             => $this->input->post('brand'),
                'category_id'       => $this->input->post('category'),
                'subcategory_id'    => $this->input->post('subcategory') ? $this->input->post('subcategory') : null,
                'cost'              => $this->bpas->formatDecimal($this->input->post('cost')),
                'price'             => $this->bpas->formatDecimal($this->input->post('price')),
                'unit'              => $this->input->post('unit'),
                'sale_unit'         => $this->input->post('default_sale_unit'),
                'purchase_unit'     => $this->input->post('default_purchase_unit'),
                'tax_rate'          => $this->input->post('tax_rate'),
                'tax_method'        => $this->input->post('tax_method'),
                'alert_quantity'    => $this->input->post('alert_quantity'),
                'track_quantity'    => $this->input->post('track_quantity') ? $this->input->post('track_quantity') : '0',
                'details'           => $this->input->post('details'),
                'product_details'   => $this->input->post('product_details'),
                'supplier1'         => $this->input->post('supplier'),
                'supplier1price'    => $this->bpas->formatDecimal($this->input->post('supplier_price')),
                'supplier2'         => $this->input->post('supplier_2'),
                'supplier2price'    => $this->bpas->formatDecimal($this->input->post('supplier_2_price')),
                'supplier3'         => $this->input->post('supplier_3'),
                'supplier3price'    => $this->bpas->formatDecimal($this->input->post('supplier_3_price')),
                'supplier4'         => $this->input->post('supplier_4'),
                'supplier4price'    => $this->bpas->formatDecimal($this->input->post('supplier_4_price')),
                'supplier5'         => $this->input->post('supplier_5'),
                'supplier5price'    => $this->bpas->formatDecimal($this->input->post('supplier_5_price')),
                'cf1'               => $this->input->post('cf1'),
                'cf2'               => $this->input->post('cf2'),
                'cf3'               => $this->input->post('cf3'),
                'cf4'               => $this->input->post('cf4'),
                'cf5'               => $this->input->post('cf5'),
                'cf6'               => $this->input->post('cf6'),
                'promotion'         => $this->input->post('promotion'),
                'promo_price'       => $this->bpas->formatDecimal($this->input->post('promo_price')),
                'start_date'        => $this->input->post('start_date') ? $this->bpas->fsd($this->input->post('start_date')) : null,
                'end_date'          => $this->input->post('end_date') ? $this->bpas->fsd($this->input->post('end_date')) : null,
                'supplier1_part_no' => $this->input->post('supplier_part_no'),
                'supplier2_part_no' => $this->input->post('supplier_2_part_no'),
                'supplier3_part_no' => $this->input->post('supplier_3_part_no'),
                'supplier4_part_no' => $this->input->post('supplier_4_part_no'),
                'supplier5_part_no' => $this->input->post('supplier_5_part_no'),
                'file'              => $this->input->post('file_link'),
                'slug'              => $this->input->post('slug'),
                'weight'            => $this->input->post('weight'),
                'featured'          => $this->input->post('featured'),
                'hsn_code'          => $this->input->post('hsn_code'),
                'hide'              => $this->input->post('hide') ? $this->input->post('hide') : 0,
                'second_name'       => $this->input->post('second_name'),
            ];
            $warehouse_qty      = null;
            $product_attributes = null;
            $this->load->library('upload');
            if ($this->input->post('type') == 'standard') {
                $wh_total_quantity = 0;
                $pv_total_quantity = 0;
                for ($s = 2; $s > 5; $s++) {
                    $data['suppliers' . $s]           = $this->input->post('supplier_' . $s);
                    $data['suppliers' . $s . 'price'] = $this->input->post('supplier_' . $s . '_price');
                }
                foreach ($warehouses as $warehouse) {
                    if ($this->input->post('wh_qty_' . $warehouse->id)) {
                        $warehouse_qty[] = [
                            'warehouse_id' => $this->input->post('wh_' . $warehouse->id),
                            'quantity'     => $this->input->post('wh_qty_' . $warehouse->id),
                            'rack'         => $this->input->post('rack_' . $warehouse->id) ? $this->input->post('rack_' . $warehouse->id) : null,
                        ];
                        $wh_total_quantity += $this->input->post('wh_qty_' . $warehouse->id);
                    }
                }

                if ($this->input->post('attributes')) {
                    $a = sizeof($_POST['attr_name']);
                    for ($r = 0; $r <= $a; $r++) {
                        if (isset($_POST['attr_name'][$r])) {
                            $product_attributes[] = [
                                'name'         => $_POST['attr_name'][$r],
                                'warehouse_id' => $_POST['attr_warehouse'][$r],
                                'quantity'     => isset($_POST['attr_quantity'][$r]) ? $_POST['attr_quantity'][$r] : 0,
                                'price'        => $_POST['attr_price'][$r],
                            ];
                            $pv_total_quantity += isset($_POST['attr_quantity'][$r]) ? $_POST['attr_quantity'][$r] : 0;
                        }
                    }
                } else {
                    $product_attributes = null;
                }

                if ($wh_total_quantity != $pv_total_quantity && $pv_total_quantity != 0) {
                    $this->form_validation->set_rules('wh_pr_qty_issue', 'wh_pr_qty_issue', 'required');
                    $this->form_validation->set_message('required', lang('wh_pr_qty_issue'));
                }
            }
            if (!isset($items)) {
                $items = null;
            }
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
                    admin_redirect('products/add');
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

            if ($_FILES['userfile']['name'][0] != '') {
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = $this->Settings->iwidth;
                $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $files                   = $_FILES;
                $cpt                     = count($_FILES['userfile']['name']);
                for ($i = 0; $i < $cpt; $i++) {
                    $_FILES['userfile']['name']     = $files['userfile']['name'][$i];
                    $_FILES['userfile']['type']     = $files['userfile']['type'][$i];
                    $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                    $_FILES['userfile']['error']    = $files['userfile']['error'][$i];
                    $_FILES['userfile']['size']     = $files['userfile']['size'][$i];

                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        admin_redirect('products/add');
                    } else {
                        $pho = $this->upload->file_name;

                        $photos[] = $pho;

                        $this->load->library('image_lib');
                        $config['image_library']  = 'gd2';
                        $config['source_image']   = $this->upload_path . $pho;
                        $config['new_image']      = $this->thumbs_path . $pho;
                        $config['maintain_ratio'] = true;
                        $config['width']          = $this->Settings->twidth;
                        $config['height']         = $this->Settings->theight;

                        $this->image_lib->initialize($config);

                        if (!$this->image_lib->resize()) {
                            echo $this->image_lib->display_errors();
                        }

                        if ($this->Settings->watermark) {
                            $this->image_lib->clear();
                            $wm['source_image']     = $this->upload_path . $pho;
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
                    }
                }
                $config = null;
            } else {
                $photos = null;
            }

            if(isset($_POST['addOn_item_code'])){
                $c = sizeof($_POST['addOn_item_code']) - 1;
                for ($r = 0; $r <= $c; $r++) {
                    if (isset($_POST['addOn_item_code'][$r])) {
                        $addOn_items[] = [
                            'item_code'   => $_POST['addOn_item_code'][$r],
                            'description' => $_POST['addOn_item_description'][$r]
                        ];
                    }
                }
            }
            
            if (!isset($addOn_items)) {
                $addOn_items = null;
            }

            $data['quantity'] = $wh_total_quantity ?? 0;
            // $this->bpas->print_arrays($data, $warehouse_qty, $product_attributes);
        }

        if ($this->form_validation->run() == true && $this->products_model->addProduct($data, $items, $warehouse_qty, $product_attributes, $photos, $addOn_items,'','')) {
            $this->session->set_flashdata('message', lang('book_added'));
            admin_redirect('library');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['type']                = $type;
            $this->data['projects']            = $this->site->getAllProject();
            $this->data['currencies']          = $this->bpas->getAllCurrencies();
            $this->data['categories']          = $this->site->getAllCategories();
            $this->data['tax_rates']           = $this->site->getAllTaxRates();
            $this->data['brands']              = $this->site->getAllBrands();
            $this->data['stock_types']         = $this->site->getAllStockType();
            $this->data['base_units']          = $this->site->getAllBaseUnits();
            $this->data['warehouses']          = $warehouses;
            $this->data['warehouses_products'] = $id ? $this->products_model->getAllWarehousesWithPQ($id) : null;
            $this->data['product']             = $id ? $this->products_model->getProductByID($id) : null;
            $this->data['variants']            = $this->products_model->getAllVariants();
            $this->data['combo_items']         = ($id && $this->data['product']->type == 'combo') ? $this->products_model->getProductComboItems($id) : null;
            $this->data['product_options']     = $id ? $this->products_model->getProductOptionsWithWH($id) : null;
            $this->data['addon_items']         = $id ? $this->products_model->getProductAddOnItems($id) : null;
            $bc                                = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('add_asset')], ['link' => '#', 'page' => lang('add_asset')]];
            $meta                              = ['page_title' => lang('add_asset'), 'bc' => $bc];
            $this->page_construct('library/add_book', $meta, $this->data);
        }
    }

    public function delete($id = null)
    {
        $this->bpas->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->products_model->deleteBook($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('book_deleted')]);
            }
            $this->session->set_flashdata('message', lang('book_deleted'));
            admin_redirect('library');
        }
    }

    public function edit($id = null)
    {
        $this->bpas->checkPermissions();
        $this->load->helper('security');

        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }
        $warehouses          = $this->site->getAllWarehouses();
        $warehouses_products = $this->products_model->getAllWarehousesWithPQ($id);
        $product             = $this->products_model->getProductByID($id);
        if (!$id || !$product) {
            $this->session->set_flashdata('error', lang('asset_not_found'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->form_validation->set_rules('category', lang('category'), 'required|is_natural_no_zero');
        if ($this->input->post('type') == 'assets') {
            $this->form_validation->set_rules('cost', lang('product_cost'), 'required');
            $this->form_validation->set_rules('unit', lang('product_unit'), 'required');
        }
        $this->form_validation->set_rules('code', lang('product_code'), 'alpha_dash');
        if ($this->input->post('code') !== $product->code) {
            $this->form_validation->set_rules('code', lang('product_code'), 'is_unique[products.code]');
        }

        $this->form_validation->set_rules('weight', lang('weight'), 'numeric');
        $this->form_validation->set_rules('product_image', lang('product_image'), 'xss_clean');
        $this->form_validation->set_rules('digital_file', lang('digital_file'), 'xss_clean');
        $this->form_validation->set_rules('userfile', lang('product_gallery_images'), 'xss_clean');
        $this->form_validation->set_rules('useful_life', lang('useful_life'), 'numeric');

        $serial_num = $this->input->post('barcode');
        $product_code = $this->input->post('code');
        $prod_code = $product_code;

        if ($this->form_validation->run('library/edit_book') == true) {

            $data = ['code'         => $prod_code,
                'barcode_symbology' => $this->input->post('barcode_symbology'),
                'serial_no'         => $serial_num,
                'max_serial'        => $this->input->post('max_serial'),
                'name'              => $this->input->post('name'),
                'type'              => 'book',
                'brand'             => $this->input->post('brand'),
                'category_id'       => $this->input->post('category'),
                'subcategory_id'    => $this->input->post('subcategory') ? $this->input->post('subcategory') : null,
                'cost'              => $this->bpas->formatDecimal($this->input->post('cost')),
                'price'             => $this->bpas->formatDecimal($this->input->post('price')),
                'unit'              => $this->input->post('unit'),
                'sale_unit'         => $this->input->post('default_sale_unit'),
                'purchase_unit'     => $this->input->post('default_purchase_unit'),
                'tax_rate'          => $this->input->post('tax_rate'),
                'tax_method'        => $this->input->post('tax_method'),
                'alert_quantity'    => $this->input->post('alert_quantity'),
                'track_quantity'    => $this->input->post('track_quantity') ? $this->input->post('track_quantity') : '0',
                'details'           => $this->input->post('details'),
                'product_details'   => $this->input->post('product_details'),
                'supplier1'         => $this->input->post('supplier'),
                'supplier1price'    => $this->bpas->formatDecimal($this->input->post('supplier_price')),
                'supplier2'         => $this->input->post('supplier_2'),
                'supplier2price'    => $this->bpas->formatDecimal($this->input->post('supplier_2_price')),
                'supplier3'         => $this->input->post('supplier_3'),
                'supplier3price'    => $this->bpas->formatDecimal($this->input->post('supplier_3_price')),
                'supplier4'         => $this->input->post('supplier_4'),
                'supplier4price'    => $this->bpas->formatDecimal($this->input->post('supplier_4_price')),
                'supplier5'         => $this->input->post('supplier_5'),
                'supplier5price'    => $this->bpas->formatDecimal($this->input->post('supplier_5_price')),
                'cf1'               => $this->input->post('cf1'),
                'cf2'               => $this->input->post('cf2'),
                'cf3'               => $this->input->post('cf3'),
                'cf4'               => $this->input->post('cf4'),
                'cf5'               => $this->input->post('cf5'),
                'cf6'               => $this->input->post('cf6'),
                'promotion'         => $this->input->post('promotion'),
                'promo_price'       => $this->bpas->formatDecimal($this->input->post('promo_price')),
                'start_date'        => $this->input->post('start_date') ? $this->bpas->fsd($this->input->post('start_date')) : null,
                'end_date'          => $this->input->post('end_date') ? $this->bpas->fsd($this->input->post('end_date')) : null,
                'supplier1_part_no' => $this->input->post('supplier_part_no'),
                'supplier2_part_no' => $this->input->post('supplier_2_part_no'),
                'supplier3_part_no' => $this->input->post('supplier_3_part_no'),
                'supplier4_part_no' => $this->input->post('supplier_4_part_no'),
                'supplier5_part_no' => $this->input->post('supplier_5_part_no'),
                'slug'              => $this->input->post('slug'),
                'weight'            => $this->input->post('weight'),
                'featured'          => $this->input->post('featured'),
                'hsn_code'          => $this->input->post('hsn_code'),
                'hide'              => $this->input->post('hide') ? $this->input->post('hide') : 0,
                'hide_pos'          => $this->input->post('hide_pos') ? $this->input->post('hide_pos') : 0,
                'second_name'       => $this->input->post('second_name'),
            ];
            $warehouse_qty      = null;
            $product_attributes = null;
            $update_variants    = [];
            $this->load->library('upload');
  
            if (!isset($items)) {
                $items = null;
            }
            if ($_FILES['product_image']['size'] > 0) {
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = $this->Settings->iwidth;
                $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('product_image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('assets/edit_asset/' . $id);
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

            if ($_FILES['userfile']['name'][0] != '') {
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = $this->Settings->iwidth;
                $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $files                   = $_FILES;
                $cpt                     = count($_FILES['userfile']['name']);
                for ($i = 0; $i < $cpt; $i++) {
                    $_FILES['userfile']['name']     = $files['userfile']['name'][$i];
                    $_FILES['userfile']['type']     = $files['userfile']['type'][$i];
                    $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                    $_FILES['userfile']['error']    = $files['userfile']['error'][$i];
                    $_FILES['userfile']['size']     = $files['userfile']['size'][$i];

                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        admin_redirect('assets/edit_asset/' . $id);
                    } else {
                        $pho = $this->upload->file_name;

                        $photos[] = $pho;

                        $this->load->library('image_lib');
                        $config['image_library']  = 'gd2';
                        $config['source_image']   = $this->upload_path . $pho;
                        $config['new_image']      = $this->thumbs_path . $pho;
                        $config['maintain_ratio'] = true;
                        $config['width']          = $this->Settings->twidth;
                        $config['height']         = $this->Settings->theight;

                        $this->image_lib->initialize($config);

                        if (!$this->image_lib->resize()) {
                            echo $this->image_lib->display_errors();
                        }

                        if ($this->Settings->watermark) {
                            $this->image_lib->clear();
                            $wm['source_image']     = $this->upload_path . $pho;
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
                    }
                }
                $config = null;
            } else {
                $photos = null;
            }


            $data['quantity'] = $wh_total_quantity ?? 0;
            // $this->bpas->print_arrays($data, $warehouse_qty, $update_variants, $product_attributes, $photos, $items);
        }

        if ($this->form_validation->run() == true && $this->products_model->updateProduct($id, $data, $items, $warehouse_qty, $product_attributes, $photos, $update_variants, '','','')) {

            $this->session->set_flashdata('message', lang('book_updated'));
            admin_redirect('library');
            
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['categories']          = $this->site->getAllCategories();
            $this->data['tax_rates']           = $this->site->getAllTaxRates();
            $this->data['brands']              = $this->site->getAllBrands();
            $this->data['base_units']          = $this->site->getAllBaseUnits();
            $this->data['warehouses']          = $warehouses;
            $this->data['warehouses_products'] = $warehouses_products;
            $this->data['product']             = $product;
            //$this->data['protype']          = $type;
            $this->data['variants']            = $this->products_model->getAllVariants();
            $this->data['subunits']            = $this->site->getUnitsByBUID($product->unit);
            $this->data['product_variants']    = $this->products_model->getProductOptions($id);
            $this->data['combo_items']         = $product->type == 'combo' ? $this->products_model->getProductComboItems($product->id) : null;
          
            $this->data['product_options']     = $id ? $this->products_model->getProductOptionsWithWH($id) : null;
           $bc                                = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('books'), 'page' => lang('books')], ['link' => '#', 'page' => lang('edit_book')]];
            $meta                              = ['page_title' => lang('edit_book'), 'bc' => $bc];
            $this->page_construct('library/edit_book', $meta, $this->data);

        }
    }
    public function modal_view($id = null)
    {
        $this->bpas->checkPermissions('index', true);

        $pr_details = $this->site->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            $this->bpas->md();
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        $this->data['product']     = $pr_details;
        $this->data['unit']        = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand']       = $this->site->getBrandByID($pr_details->brand);
        $this->data['images']      = $this->products_model->getProductPhotos($id);
        $this->data['category']    = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory'] = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : null;
        $this->data['tax_rate']    = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : null;
        $this->data['warehouses']  = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options']     = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants']    = $this->products_model->getProductOptions($id);

        $this->load->view($this->theme . 'assets/modal_view', $this->data);
    }
    public function modal_view1($id = null)
    {
        $this->bpas->checkPermissions('index', true);

        $pr_details = $this->site->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            $this->bpas->md();
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        $this->data['product']     = $pr_details;
        $this->data['unit']        = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand']       = $this->site->getBrandByID($pr_details->brand);
        $this->data['images']      = $this->products_model->getProductPhotos($id);
        $this->data['category']    = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory'] = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : null;
        $this->data['tax_rate']    = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : null;
        $this->data['warehouses']  = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options']     = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants']    = $this->products_model->getProductOptions($id);

        $this->load->view($this->theme . 'assets/asset_modal_view', $this->data);
    }
    public function actions($wh = null)
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'sync_quantity') {
                    foreach ($_POST['val'] as $id) {
                        $this->site->syncQuantity(null, null, null, $id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line('products_quantity_sync'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->assets_model->deleteProduct($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line('asset_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'labels') {
                    foreach ($_POST['val'] as $id) {
                        $row               = $this->assets_model->getProductByID($id);
                        $selected_variants = false;
                        if ($variants = $this->assets_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = ['id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants];
                    }

                    $this->data['items'] = isset($pr) ? json_encode($pr) : false;
                    $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                    $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('assets'), 'page' => lang('Assets')], ['link' => '#', 'page' => lang('print_barcodes')]];
                    $meta                = ['page_title' => lang('print_barcodes'), 'bc' => $bc];
                    $this->page_construct('products/print_barcodes', $meta, $this->data);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('Assets');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('barcode_symbology'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('brand'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('category_code'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('unit_code'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('sale') . ' ' . lang('unit_code'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('purchase') . ' ' . lang('unit_code'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('cost'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('price'));
                    $this->excel->getActiveSheet()->SetCellValue('K1', lang('alert_quantity'));
                    $this->excel->getActiveSheet()->SetCellValue('L1', lang('tax_rate'));
                    $this->excel->getActiveSheet()->SetCellValue('M1', lang('tax_method'));
                    $this->excel->getActiveSheet()->SetCellValue('N1', lang('image'));
                    $this->excel->getActiveSheet()->SetCellValue('O1', lang('subcategory_code'));
                    $this->excel->getActiveSheet()->SetCellValue('P1', lang('asset_variants'));
                    $this->excel->getActiveSheet()->SetCellValue('Q1', lang('pcf1'));
                    $this->excel->getActiveSheet()->SetCellValue('R1', lang('pcf2'));
                    $this->excel->getActiveSheet()->SetCellValue('S1', lang('pcf3'));
                    $this->excel->getActiveSheet()->SetCellValue('T1', lang('pcf4'));
                    $this->excel->getActiveSheet()->SetCellValue('U1', lang('pcf5'));
                    $this->excel->getActiveSheet()->SetCellValue('V1', lang('pcf6'));
                    $this->excel->getActiveSheet()->SetCellValue('W1', lang('hsn_code'));
                    $this->excel->getActiveSheet()->SetCellValue('X1', lang('second_name'));
                    $this->excel->getActiveSheet()->SetCellValue('Y1', lang('supplier_name'));
                    $this->excel->getActiveSheet()->SetCellValue('Z1', lang('supplier_part_no'));
                    $this->excel->getActiveSheet()->SetCellValue('AA1', lang('supplier_price'));
                    $this->excel->getActiveSheet()->SetCellValue('AB1', lang('quantity'));
                    $this->excel->getActiveSheet()->SetCellValue('AC1', lang('details'));
                    $this->excel->getActiveSheet()->SetCellValue('AD1', lang('asset_details'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $product   = $this->assets_model->getProductDetail($id);
                        $brand     = $this->site->getBrandByID($product->brand);
                        $base_unit = $sale_unit = $purchase_unit = '';
                        if ($units = $this->site->getUnitsByBUID($product->unit)) {
                            foreach ($units as $u) {
                                if ($u->id == $product->unit) {
                                    $base_unit = $u->code;
                                }
                                if ($u->id == $product->sale_unit) {
                                    $sale_unit = $u->code;
                                }
                                if ($u->id == $product->purchase_unit) {
                                    $purchase_unit = $u->code;
                                }
                            }
                        }
                        $variants         = $this->assets_model->getProductOptions($id);
                        $product_variants = '';
                        if ($variants) {
                            $i = 1;
                            $v = count($variants);
                            foreach ($variants as $variant) {
                                $product_variants .= trim($variant->name) . ($i != $v ? '|' : '');
                                $i++;
                            }
                        }
                        $quantity = $product->quantity;
                        if ($wh) {
                            if ($wh_qty = $this->assets_model->getProductQuantity($id, $wh)) {
                                $quantity = $wh_qty['quantity'];
                            } else {
                                $quantity = 0;
                            }
                        }
                        $supplier = false;
                        if ($product->supplier1) {
                            $supplier = $this->site->getCompanyByID($product->supplier1);
                        }
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $product->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $product->code);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $product->barcode_symbology);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, ($brand ? $brand->name : ''));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $product->category_code);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $base_unit);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $sale_unit);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $purchase_unit);
                        if ($this->Owner || $this->Admin || $this->session->userdata('show_cost')) {
                            $this->excel->getActiveSheet()->SetCellValue('I' . $row, $product->cost);
                        }
                        if ($this->Owner || $this->Admin || $this->session->userdata('show_price')) {
                            $this->excel->getActiveSheet()->SetCellValue('J' . $row, $product->price);
                        }
                        $this->excel->getActiveSheet()->SetCellValue('K' . $row, $product->alert_quantity);
                        $this->excel->getActiveSheet()->SetCellValue('L' . $row, $product->tax_rate_name);
                        $this->excel->getActiveSheet()->SetCellValue('M' . $row, $product->tax_method ? lang('exclusive') : lang('inclusive'));
                        $this->excel->getActiveSheet()->SetCellValue('N' . $row, $product->image);
                        $this->excel->getActiveSheet()->SetCellValue('O' . $row, $product->subcategory_code);
                        $this->excel->getActiveSheet()->SetCellValue('P' . $row, $product_variants);
                        $this->excel->getActiveSheet()->SetCellValue('Q' . $row, $product->cf1);
                        $this->excel->getActiveSheet()->SetCellValue('R' . $row, $product->cf2);
                        $this->excel->getActiveSheet()->SetCellValue('S' . $row, $product->cf3);
                        $this->excel->getActiveSheet()->SetCellValue('T' . $row, $product->cf4);
                        $this->excel->getActiveSheet()->SetCellValue('U' . $row, $product->cf5);
                        $this->excel->getActiveSheet()->SetCellValue('V' . $row, $product->cf6);
                        $this->excel->getActiveSheet()->SetCellValue('W' . $row, $product->hsn_code);
                        $this->excel->getActiveSheet()->SetCellValue('X' . $row, $product->second_name);
                        $this->excel->getActiveSheet()->SetCellValue('Y' . $row, $supplier ? $supplier->name : '');
                        $this->excel->getActiveSheet()->SetCellValue('Z' . $row, $supplier ? $product->supplier1_part_no : '');
                        $this->excel->getActiveSheet()->SetCellValue('AA' . $row, $supplier ? $product->supplier1price : '');
                        $this->excel->getActiveSheet()->SetCellValue('AB' . $row, $quantity);
                        $this->excel->getActiveSheet()->SetCellValue('AC' . $row, $product->details);
                        $this->excel->getActiveSheet()->SetCellValue('AD' . $row, $product->product_details);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(40);
                    $this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('AC')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('AD')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'product_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line('no_asset_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER'] ?? 'admin/assets');
        }
    }
    /* ------------------------------------------------------------------ */
     public function asset_view($id = null)
    {
        $this->bpas->checkPermissions('index');

        $pr_details = $this->assets_model->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->assets_model->getProductComboItems($id);
        }
        $this->data['product']          = $pr_details;
        $this->data['unit']             = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand']            = $this->site->getBrandByID($pr_details->brand);
        $this->data['images']           = $this->assets_model->getProductPhotos($id);
        $this->data['category']         = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory']      = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : null;
        $this->data['tax_rate']         = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : null;
        $this->data['popup_attributes'] = $this->popup_attributes;
        $this->data['warehouses']       = $this->assets_model->getAllWarehousesWithPQ($id);
        $this->data['options']          = $this->assets_model->getProductOptionsWithWH($id);
        $this->data['variants']         = $this->assets_model->getProductOptions($id);
        $this->data['sold']             = $this->assets_model->getSoldQty($id);
        $this->data['purchased']        = $this->assets_model->getPurchasedQty($id);

        $this->data['evaluation_list']  = $this->assets_model->getEvaluationTable($id);

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('assets'), 'page' => lang('Assets')], ['link' => '#', 'page' => $pr_details->name]];
        $meta = ['page_title' => $pr_details->name, 'bc' => $bc];
        $this->page_construct('assets/asset_view', $meta, $this->data);
    }
    public function borrow(){
        $this->bpas->checkPermissions('list_using_stock', true, 'products');
        $data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $AllUsers=$this->site->getAllUsers();
        $CurrentUser=$this->site->getUser();
        $setting=$this->site->get_setting();
        $biller=$this->site->getAllBiller();
        $employee=$this->site->getAllEmployee();
        $all_unit=$this->site->getUnits();
        $product=$this->products_model->getProductName_code();
        $getGLChart=$this->products_model->getGLChart();
        $this->data['getGLChart'] = $getGLChart; 
        $this->data['AllUsers'] = $AllUsers; 
        $this->data['CurrentUser'] = $CurrentUser; 
        $this->data['setting'] = $setting; 
        $this->data['biller'] = $biller; 
        $this->data['all_unit'] = $all_unit; 
        $this->data['employees'] = $employee; 
        $this->data['product'] = $product; 
        $this->data['productJSON'] = json_encode($product); 
        $this->data['reference'] = $this->site->getReference('es');
        
        $this->data['modal_js'] = $this->site->modal_js();
        
        $this->data['enter_using_stock']=$this->products_model->getReferno();
        
        $this->data['empno']=$this->products_model->getEmpno();
        $this->data['plans']=$this->products_model->getPlan();
        
         $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('borrow')));
        $meta = array('page_title' => lang('borrow'), 'bc' => $bc);
        $this->page_construct('library/borrow', $meta,$this->data);
        
    }
    public function get_borrow(){
        $this->load->library('datatables');
        $fdate=$this->input->get('start_date');
        $tdate=$this->input->get('end_date');
        $referno=$this->input->get('referno');
        $empno=$this->input->get('empno');
        $plan=$this->input->get('plan');
        
        $start_date = $this->bpas->fsd($fdate);
        $end_date = $this->bpas->fsd($tdate);
        
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_using_stock") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('products/delete_using_stock/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_using_stock') . "</a>";

        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">                                                               
                <li class="edit_using"><a href="'.site_url('admin/products/edit_using_stock/$1/$2').'" ><i class="fa fa-edit"></i>'.lang('edit_using_stock').'</a></li> 
                <li class="add_return" ><a href="'.site_url('admin/products/return_using_stock/$1/$2').'" ><i class="fa fa-reply"></i>'.lang('return_using_stock').'</a></li> 
                <li><a href="'.site_url('admin/products/print_using_stock_by_id/$1/$2').'" ><i class="fa fa-newspaper-o"></i>'.lang('print_using_stock').'</a></li>
                <li><a href="'.site_url('admin/products/print_sample_form_ppcp/$1/$2').'" ><i class="fa fa-newspaper-o"></i>'.lang('print_sample_form_ppcp').'</a></li>
                <li>' . $delete_link . '</li>
            </ul>
        </div>';
                        
        $this->datatables
            ->select("
            {$this->db->dbprefix('enter_using_stock')}.id as id,{$this->db->dbprefix('enter_using_stock')}.date, {$this->db->dbprefix('enter_using_stock')}.reference_no as refno,
            {$this->db->dbprefix('companies')}.company,
            {$this->db->dbprefix('warehouses')}.name as warehouse_name,
            bpas_projects_plan.title as home_type, 
            CONCAT({$this->db->dbprefix('products')}.cf3, ', ',{$this->db->dbprefix('products')}.cf4) as address, 
  
            {$this->db->dbprefix('users')}.username, 
            {$this->db->dbprefix('enter_using_stock')}.note, {$this->db->dbprefix('enter_using_stock')}.type as type", FALSE)
            ->from("enter_using_stock")
            
            ->join('companies', 'companies.id=enter_using_stock.biller_id', 'inner')
            ->join('warehouses', 'enter_using_stock.warehouse_id=warehouses.id', 'left')
            ->join('projects_plan', 'enter_using_stock.plan_id = projects_plan.id', 'left')
            ->join('products', 'enter_using_stock.address_id = products.id', 'left')
            ->join('users', 'users.id=enter_using_stock.employee_id', 'inner')
            ->order_by('enter_using_stock.date', 'desc')
            ->order_by('enter_using_stock.reference_no', 'desc');

            if($fdate && $tdate){
                $this->datatables->where('enter_using_stock.date>=',$start_date);
                $this->datatables->where('enter_using_stock.date<=',$end_date);
            }
            if($referno!=''){
                $this->datatables->where('enter_using_stock.reference_no',$referno);
            }
            if($empno!=''){
                $this->datatables->where('users.username',$empno);
            }
            if($plan!=''){
                $this->datatables->where('projects_plan.id',$plan);
            }
            $this->datatables->add_column("Actions", $action_link, "id,type");
        echo $this->datatables->generate();
    }
    public function add_borrow($purchase_id = null, $id = NULL)
    {
        $this->bpas->checkPermissions('adjustments');
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required|is_unique[enter_using_stock.reference_no]');
       // $this->form_validation->set_rules('account', lang("account"), 'required');
        $this->form_validation->set_rules('from_location', lang("from_location"), 'required');
        if ($this->form_validation->run() == true){
            if($this->Owner || $this->Admin){
                $date       = $this->bpas->fld($this->input->post('date'));
            } else {
                $date       = date('Y-m-d H:i:s');
            }
            $biller_id      = $this->input->post('biller');          
            $project_id     = $this->input->post('project');
            $authorize      = $this->input->post('authorize_id');
          //  $account        = $this->input->post('account');
            $reference_no   = $this->input->post('reference_no');
            $employee_id    = $this->input->post('student');
            $customer_id    = $this->input->post('customer');
            $plan           = $this->input->post('plan');
            $warehouse_id   = $this->input->post('from_location');
            $biller_id      = $this->input->post('biller');
            $note           = $this->input->post('note');
            $total_item_cost= 0;

            $i              = sizeof($_POST['product_id']);
            for ($r = 0; $r < $i; $r++) {
                $product_id     = $_POST['product_id'][$r];
                $product_code   = $_POST['item_code'][$r];
                $product_name   = $_POST['name'][$r];
                $product_cost   = $_POST['cost'][$r];
                $description    = $_POST['description'][$r];
                $qty_use        = $_POST['qty_use'][$r];
                $unit           = $_POST['unit'][$r];
                $exp            = isset($_POST['exp'][$r])?$_POST['exp'][$r]:null;
                $qty_balance    = $qty_use;
                $option_id      = '';
                $total_cost     = $product_cost * $qty_balance; 
                
                $variant        = $this->site->getProductVariantByID($product_id, $unit);
                if ($variant) {
                    // $qty_balance = $qty_use * $variant->qty_unit;
                    $qty_balance = $qty_use;
                    $option_id   = $variant->id;
                    $total_cost  = $product_cost * $qty_balance;    
                }
                
                //======================= Check Stock ========================//

                if ($variant) {
                    $warehouse = $this->site->getWarehouse_VariantQty($product_id, $warehouse_id, $option_id);
                } else {
                    $warehouse = $this->site->getWarehouseQty($product_id, $warehouse_id);
                }
                
                if($qty_balance == 0) {
                    $this->session->set_flashdata('error', $this->lang->line("unexpected_value") );
                    redirect($_SERVER["HTTP_REFERER"]);
                } elseif ($warehouse->quantity < $qty_balance){
                    $this->session->set_flashdata('error', $this->lang->line("quantity_bigger") );
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                //=========================== End ============================//
                
                //================== Check Stock With Expiry =================//
                if ($this->Settings->product_expiry) {
                    $stock_expiry = $this->site->checkExpiryDate($product_id, $exp, $warehouse_id);
                    if($stock_expiry->expiry_qty < $qty_balance){
                        $this->session->set_flashdata('error', $this->lang->line("expiry_date_bigger") );
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
                }
                //============================= End ==========================//
                
                
                $item_data[] = array(
                    'product_id'    => $product_id,
                    'code'          => $product_code,
                    'product_name'  => $product_name,
                    'description'   => $description,
                    'qty_use'       => $qty_balance,
                    'qty_by_unit'   => $qty_use,
                    'unit'          => $unit,
                    'expiry'        => $exp,
                    'warehouse_id'  => $warehouse_id,
                    'cost'          => $product_cost,
                    'reference_no'  => $reference_no,
                    'option_id'     => is_numeric($option_id) ? $option_id : null
                );
                
                $total_item_cost+= $total_cost;

                if($this->Settings->accounting == 1){
                    $accTrans[] = array(
                        'tran_type'     => 'UsingStock',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $this->accounting_setting->default_stock,
                        'amount'        => -($product_cost * abs($qty_use)),
                        'narrative'     => 'Product Code: '.$product_code.'#'.'Qty: '.$qty_use.'#'.'Cost: '.$product_cost,
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                    );
                    $accTrans[] = array(
                        'tran_type'     => 'UsingStock',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $this->accounting_setting->default_stock_using,
                        'amount'        => ($product_cost * abs($qty_use)),
                        'narrative'     => 'Product Code: '.$product_code.'#'.'Qty: '.$qty_use.'#'.'Cost: '.$product_cost,
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                    );
                    
                }
            }
            
            if (empty($item_data)) {
                $this->session->set_flashdata('error', $this->lang->line("no_data_select") );
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                krsort($item_data);
            }
            
            $data = array(
                'date'          => $date,
                'reference_no'  => $reference_no,
                'warehouse_id'  => $warehouse_id,
                'authorize_id'  => $authorize,
                'employee_id'   => $employee_id,
                'biller_id'     => $biller_id,
                'note'          => $note,
                'create_by'     => $this->session->userdata('user_id'),
                'type'          => 'borrow',
                'total_cost'    => $total_item_cost,
            ); 
        }
        if ($this->form_validation->run() && $this->products_model->add_borrowBook($data, $item_data, $accTrans)) {
            $this->session->set_flashdata(lang('enter_using_stock_added.'));
            $r_r = str_replace("/","-",$reference_no);
            $this->session->set_userdata('remove_usitem', '1');
            admin_redirect('products/using_stock');
        }else{
            $data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            
            $this->data['getExpense']   = $this->products_model->getAllExpenseCategory();
            $this->data['getGLChart']   = $this->products_model->getGLChart(); 
            $this->data['AllUsers']     = $this->site->getAllUsers();
            $this->data['CurrentUser']  = $this->site->getUser();
            $this->data['setting']      = $this->site->get_setting();
            $this->data['all_unit']     = $this->site->getUnits();

            $this->data['students']    = $this->schools_model->getStudents();

            $this->data['product']      =  $this->products_model->getProductName_code();
            $this->data['productJSON']  = json_encode($this->data['product']); 
            //$this->data['reference']  = $this->site->getReference('es');
            
            if ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) {
                $biller_id = $this->site->get_setting()->default_biller;
                $this->data['biller_id'] = $biller_id;
                $this->data['reference']    = $this->site->getReference('es','');
            } else {
                $biller_id = $this->session->userdata('biller_id');
                $this->data['biller_id'] = $biller_id;
                $this->data['reference']    = $this->site->getReference('es','');
            }
            
            if($purchase_id){
                $this->data['items']    = $this->products_model->getPurcahseItemByPurchaseID($purchase_id);
                $this->data['purchase'] = $this->products_model->getPurchaseByID($purchase_id);
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
            
            $this->data['plan']         = $this->products_model->getPlan();
            $this->data['modal_js']     = $this->site->modal_js();
            $this->data['positions']    = $this->products_model->getAllPositionData();
            
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('add_using_stock')));
            $meta = array('page_title' => lang('enter_using_stock'), 'bc' => $bc);
            $this->page_construct('library/add_borrow', $meta, $this->data);
        }
    }
    public function suggestionsBook()
    {
        $term           = $this->input->get('term', TRUE);
        $warehouse_id   = $this->input->get('warehouse_id', TRUE);
        $rows           = $this->products_model->getBorrowBooks($term, $warehouse_id);
        if($rows){
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            $project_qty = 0;
            foreach ($rows as $row) {
        
                    $row->project_qty = 0;
                
                $row->have_plan = 0;
                if($row->project_qty && $row->in_plan){
                    $row->have_plan = 1;
                }
                $row->qty_use   = 0;
                $row->qty_old   = 0;
                $option_unit    = $this->products_model->getUnitAndVaraintByProductId($row->id);
                $expiry_date    = $this->site->getProductExpireDate($row->id, $warehouse_id);
                $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",'row' => $row, 'option_unit' => $option_unit, 'project_qty' => $project_qty, 'expiry_date' => $expiry_date);

                $r++;
            }
            //$this->bpas->print_arrays($pr);
            echo json_encode($pr);
        }else{
            echo json_encode(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

}
