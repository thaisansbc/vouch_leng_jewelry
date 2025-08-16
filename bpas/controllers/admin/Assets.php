<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Assets extends MY_Controller
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
        $this->load->admin_model('accounts_model');
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
        $bc                     = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Assets')]];
        $meta                   = ['page_title' => lang('Assets'), 'bc' => $bc];
        $this->page_construct('assets/index', $meta, $this->data);
    }
    public function getassets($warehouse_id = null)
    {
        $this->bpas->checkPermissions('index', true);
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : null;

        // if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
        //     $user         = $this->site->getUser();
        //     $warehouse_id = $user->warehouse_id;
        // }
        $detail_link = anchor('admin/products/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('asset_details'));
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line('delete_asset') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete1' id='a__$1' href='" . admin_url('assets/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_asset') . '</a>';
        $single_barcode = anchor('admin/products/print_barcodes/$1', '<i class="fa fa-print"></i> ' . lang('print_barcode_label'));
        // $single_label = anchor_popup('products/single_label/$1/' . ($warehouse_id ? $warehouse_id : ''), '<i class="fa fa-print"></i> ' . lang('print_label'), $this->popup_attributes);
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li><a href="' . admin_url('assets/add/$1') . '"><i class="fa fa-plus-square"></i> ' . lang('duplicate_asset') . '</a></li>
            <li><a href="' . admin_url('assets/edit/$1') . '"><i class="fa fa-edit"></i> ' . lang('edit_asset') . '</a></li>';
        
       
        $evaluation_table = anchor('admin/assets/expenses', '<i class="fa fa-file-text-o"></i> ' . lang('depreciation_table'));

        $action .= '
  
            <li><a href="' . base_url() . 'assets/uploads/$2" data-type="image" data-toggle="lightbox"><i class="fa fa-file-photo-o"></i> '
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
            $this->datatables->where('products.type','asset');

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
                ->where('products.type','asset');
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
                'type'              => 'asset',
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

            if ($this->input->post('type') == 'service') {
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'combo') {
                $total_price = 0;
                $c           = sizeof($_POST['combo_item_code']) - 1;
                for ($r = 0; $r <= $c; $r++) {
                    if (isset($_POST['combo_item_code'][$r]) && isset($_POST['combo_item_quantity'][$r]) && isset($_POST['combo_item_price'][$r])) {
                        $items[] = [
                            'item_code'  => $_POST['combo_item_code'][$r],
                            'quantity'   => $_POST['combo_item_quantity'][$r],
                            'unit_price' => $_POST['combo_item_price'][$r],
                        ];
                    }
                    $total_price += $_POST['combo_item_price'][$r] * $_POST['combo_item_quantity'][$r];
                }
                if ($this->bpas->formatDecimal($total_price) != $this->bpas->formatDecimal($this->input->post('price'))) {
                    $this->form_validation->set_rules('combo_price', 'combo_price', 'required');
                    $this->form_validation->set_message('required', lang('pprice_not_match_ciprice'));
                }
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'digital') {
                if ($_FILES['digital_file']['size'] > 0) {
                    $config['upload_path']   = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size']      = $this->allowed_file_size;
                    $config['overwrite']     = false;
                    $config['encrypt_name']  = true;
                    $config['max_filename']  = 25;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('digital_file')) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        admin_redirect('products/add');
                    }
                    $file         = $this->upload->file_name;
                    $data['file'] = $file;
                } else {
                    if (!$this->input->post('file_link')) {
                        $this->form_validation->set_rules('digital_file', lang('digital_file'), 'required');
                    }
                }
                $config                 = null;
                $data['track_quantity'] = 0;
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
            $this->session->set_flashdata('message', lang('product_added'));
            admin_redirect('assets');
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
            $this->page_construct('assets/add_asset', $meta, $this->data);
        }
    }

    public function delete($id = null)
    {
        $this->bpas->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->assets_model->deleteProduct($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('product_deleted')]);
            }
            $this->session->set_flashdata('message', lang('product_deleted'));
            admin_redirect('assets');
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

        if ($this->form_validation->run('assets/edit_asset') == true) {

            $data = ['code'         => $prod_code,
                'barcode_symbology' => $this->input->post('barcode_symbology'),
                'serial_no'         => $serial_num,
                'max_serial'        => $this->input->post('max_serial'),
                'name'              => $this->input->post('name'),
                'type'              => 'asset',
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

            $this->session->set_flashdata('message', lang('asset_updated'));
            admin_redirect('assets');
            
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
            $this->data['variants']            = $this->assets_model->getAllVariants();
            $this->data['subunits']            = $this->site->getUnitsByBUID($product->unit);
            $this->data['product_variants']    = $this->assets_model->getProductOptions($id);
            $this->data['combo_items']         = $product->type == 'combo' ? $this->assets_model->getProductComboItems($product->id) : null;
          
            $this->data['product_options']     = $id ? $this->assets_model->getProductOptionsWithWH($id) : null;
           $bc                                = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('assets'), 'page' => lang('Assets')], ['link' => '#', 'page' => lang('edit_asset')]];
            $meta                              = ['page_title' => lang('edit_asset'), 'bc' => $bc];
            $this->page_construct('assets/edit_asset', $meta, $this->data);

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
            $this->data['combo_items'] = $this->assets_model->getProductComboItems($id);
        }
        $this->data['product']     = $pr_details;
        $this->data['unit']        = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand']       = $this->site->getBrandByID($pr_details->brand);
        $this->data['images']      = $this->assets_model->getProductPhotos($id);
        $this->data['category']    = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory'] = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : null;
        $this->data['tax_rate']    = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : null;
        $this->data['warehouses']  = $this->assets_model->getAllWarehousesWithPQ($id);
        $this->data['options']     = $this->assets_model->getProductOptionsWithWH($id);
        $this->data['variants']    = $this->assets_model->getProductOptions($id);

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
            $this->data['combo_items'] = $this->assets_model->getProductComboItems($id);
        }
        $this->data['product']     = $pr_details;
        $this->data['unit']        = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand']       = $this->site->getBrandByID($pr_details->brand);
        $this->data['images']      = $this->assets_model->getProductPhotos($id);
        $this->data['category']    = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory'] = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : null;
        $this->data['tax_rate']    = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : null;
        $this->data['warehouses']  = $this->assets_model->getAllWarehousesWithPQ($id);
        $this->data['options']     = $this->assets_model->getProductOptionsWithWH($id);
        $this->data['variants']    = $this->assets_model->getProductOptions($id);

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
    public function add_depreciation($expense_id = null)
    {
        $this->bpas->checkPermissions('depreciation', true);
        $product = $this->purchases_model->getPurchaseByID($expense_id);

        $getcheck_evaluation = $this->assets_model->checked_evaluation($expense_id);
   
        if($getcheck_evaluation == 1){
         
            $this->session->set_flashdata('error', lang('asset_already_Depreciation'));
            $this->data['check_evaluation'] = $getcheck_evaluation;
            $this->bpas->md();
        }

        $this->form_validation->set_rules('current_cost', lang('current_cost'), 'trim|required');
        if ($this->form_validation->run() == true) {
            $months =  $product->useful_life * 12;
            $final_amount = $product->grand_total;
            $create_date = date_create($this->bpas->fsd(trim($this->input->post('date'))));
            $now = date_format($create_date, 'Y-m-d');
            $start = strtotime($now);

            $accumulated = $this->input->post('current_cost');

            for ($x = 1; $x <= $months; $x++) {
                $current_cost1 =  $this->input->post('current_cost');//$this->bpas->formatDecimal((($product->cost - $product->residual_value) / $product->useful_life)/12);
                $principal = $final_amount - $current_cost1;
                $data = array(
                    'evaluation_date'   => date("Y-m-d", $start),
                    'expense_id'        => $expense_id,
                    'current_cost'      => $this->input->post('current_cost'),
                    'accumulated'       => $accumulated,
                    'net_value'         => $principal,
                    'dp_account'   => $this->input->post('expense_account'),
                    'acc_account'     => $this->input->post('asset_account'),
                    'biller_id'         => $this->input->post('biller_id'),
                    'created_by'        => $this->session->userdata('user_id'),
                );
                $this->db->insert('asset_evaluation', $data);
                $start = strtotime("+1 month", $start);
                $final_amount -= $current_cost1;
                $accumulated += $current_cost1;
                
            }
             
        } elseif ($this->input->post('submit')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('assets/expenses');
        }
        //&& $this->assets_model->evaluation($data)
        if ($this->form_validation->run() == true ) {
            $this->session->set_flashdata('message', lang('evaluation'));
            admin_redirect('assets/expenses');
        } else {
            $this->data['error']        = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['sectionacc'] = $this->accounts_model->getAllChartAccount();
            $this->data['product']      = $product;
            $this->data['modal_js']     = $this->site->modal_js();
            $this->load->view($this->theme . 'assets/asset_evalulation', $this->data);
        }
    }
    function evaluation_assets($warehouse_id = null)
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
        $this->data['products'] = $this->site->getProducts();
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['supplier'] = $this->input->get('supplier') ? $this->site->getCompanyByID($this->input->get('supplier')) : null;
        $bc                     = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Assets')]];
        $meta                   = ['page_title' => lang('Assets'), 'bc' => $bc];
        $this->page_construct('assets/evaluation', $meta, $this->data);
    }
    
     public function getEvaluationList($pdf = null, $xls = null)
    {
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = null;
        }

        // if ($pdf || $xls) {
        //     $this->db
        //         ->select($this->db->dbprefix('transfers') . '.date, transfer_no, (CASE WHEN ' . $this->db->dbprefix('transfers') . ".status = 'completed' THEN  GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('purchase_items') . ".product_name, ' (', " . $this->db->dbprefix('purchase_items') . ".quantity, ')') SEPARATOR '<br>') ELSE GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('transfer_items') . ".product_name, ' (', " . $this->db->dbprefix('transfer_items') . ".quantity, ')') SEPARATOR '<br>') END) as iname, from_warehouse_name as fname, from_warehouse_code as fcode, to_warehouse_name as tname,to_warehouse_code as tcode, grand_total, " . $this->db->dbprefix('transfers') . '.status')
        //         ->from('transfers')
        //         ->join('transfer_items', 'transfer_items.transfer_id=transfers.id', 'left')
        //         ->join('purchase_items', 'purchase_items.transfer_id=transfers.id', 'left')
        //         ->group_by('transfers.id')->order_by('transfers.date desc');
        //     if ($product) {
        //         $this->db->where($this->db->dbprefix('purchase_items') . '.product_id', $product);
        //         $this->db->or_where($this->db->dbprefix('transfer_items') . '.product_id', $product);
        //     }

        //     $q = $this->db->get();
        //     if ($q->num_rows() > 0) {
        //         foreach (($q->result()) as $row) {
        //             $data[] = $row;
        //         }
        //     } else {
        //         $data = null;
        //     }

        //     if (!empty($data)) {
        //         $this->load->library('excel');
        //         $this->excel->setActiveSheetIndex(0);
        //         $this->excel->getActiveSheet()->setTitle(lang('transfers_report'));
        //         $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
        //         $this->excel->getActiveSheet()->SetCellValue('B1', lang('transfer_no'));
        //         $this->excel->getActiveSheet()->SetCellValue('C1', lang('product_qty'));
        //         $this->excel->getActiveSheet()->SetCellValue('D1', lang('warehouse') . ' (' . lang('from') . ')');
        //         $this->excel->getActiveSheet()->SetCellValue('E1', lang('warehouse') . ' (' . lang('to') . ')');
        //         $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
        //         $this->excel->getActiveSheet()->SetCellValue('G1', lang('status'));

        //         $row = 2;
        //         foreach ($data as $data_row) {
        //             $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
        //             $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->transfer_no);
        //             $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->iname);
        //             $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->fname . ' (' . $data_row->fcode . ')');
        //             $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->tname . ' (' . $data_row->tcode . ')');
        //             $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->grand_total);
        //             $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->status);
        //             $row++;
        //         }

        //         $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        //         $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        //         $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        //         $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        //         $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        //         $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        //         $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        //         $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        //         $this->excel->getActiveSheet()->getStyle('C2:C' . $row)->getAlignment()->setWrapText(true);
        //         $filename = 'transfers_report';
        //         $this->load->helper('excel');
        //         create_excel($this->excel, $filename);
        //     }
        //     $this->session->set_flashdata('error', lang('nothing_found'));
        //     redirect($_SERVER['HTTP_REFERER']);
        // } else {
            $this->load->library('datatables');
            $this->datatables
                ->select("
                    {$this->db->dbprefix('asset_evaluation')}.id as id,
                    {$this->db->dbprefix('asset_evaluation')}.evaluation_date, 
                    current_cost, 
                    accumulated, 
                    net_value
                    ", false)
                ->from('asset_evaluation')
                ->group_by('asset_evaluation.id')
                ->order_by('id','ASC');
            if ($product) {
                $this->datatables->where("{$this->db->dbprefix('asset_evaluation')}.product_id",$product);
            }
            $detail_link = anchor('admin/assets/asset_expense/$1', '<label class="label label-primary">' . lang('add_expense').'</label>', 'class="tip" title="' . lang('show') . '" data-toggle="modal" data-backdrop="static" data-target="#myModal"');

            $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line('delete_asset') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete1' id='a__$1' href='" . admin_url('assets/delete_depreciation/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_asset') . '</a>';

            $this->datatables->add_column('Actions', '<div class="text-center">'.$detail_link.$delete_link.'</div>', 'id')
                ->unset_column('id');
            echo $this->datatables->generate();
        
    }
    public function delete_depreciation($id = null)
    {
        $this->bpas->checkPermissions('delete', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->assets_model->deleteevaluation($id)) {
            $this->site->deleteDepreciation('Depreciation',$id);

            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('depreciation_has_been_deleted')]);
            }
            $this->session->set_flashdata('message', lang('depreciation_has_been_deleted'));
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function expenses($warehouse_id = null)
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
            $this->data['warehouse_id']     = $warehouse_id;
            $this->data['warehouse']        = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        }

        $this->data['alert_id']         = isset($_GET['alert_id']) ? $_GET['alert_id'] : null;


        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('asset_expense')]];
        $meta = ['page_title' => lang('asset_expense'), 'bc' => $bc];
        $this->page_construct('assets/list_asset_expense', $meta, $this->data);
    }
    public function getAssetExpenses($warehouse_id = null)
    {
     //   $this->bpas->checkPermissions('index');

        // if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
        //     $user         = $this->site->getUser();
        //     $warehouse_id = $user->warehouse_id;
        // }

        $a                = $this->input->get('a') ? $this->input->get('a') : null;
        $schedule_link = anchor('admin/assets/schedule/$1', '<i class="fa fa-file-text-o"></i> ' . lang('schedule'));
        $add_depreciation = anchor('admin/assets/add_depreciation/$1', '<i class="fa fa-money"></i> ' . lang('add_depreciation'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');

        $detail_link      = anchor('admin/purchases/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('purchase_details'));
        $payments_link    = anchor('admin/purchases/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_payment_link = anchor('admin/purchases/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $email_link       = anchor('admin/purchases/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_purchase'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link        = anchor('admin/purchases/edit_asset_expense/$1', '<i class="fa fa-edit"></i> ' . lang('edit_purchase'));
        $pdf_link         = anchor('admin/purchases/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $print_barcode    = anchor('admin/products/print_barcodes/?purchase=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
        $return_link      = anchor('admin/purchases/return_purchase/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_purchase'));
        $delete_link      = "<a href='#' class='po' title='<b>" . $this->lang->line('delete_purchase') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('purchases/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_purchase') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $schedule_link . '</li>
            <li>' . $add_depreciation . '</li>
            <li>' . $detail_link . '</li>
            <li>' . $payments_link . '</li> 
            <li>' . $add_payment_link . '</li>
            <li>' . $edit_link . '</li>

            <li>' . $delete_link . '</li>
        </ul>
        </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');

        $this->datatables
            ->select("purchases.id, DATE_FORMAT({$this->db->dbprefix('purchases')}.date, '%Y-%m-%d %T') as date, 
                projects.project_name,
                reference_no, 
                useful_life,
                residual_value,
                supplier,
                purchases.status, 
                grand_total, 
                paid, 
                (grand_total-paid) as balance, 
                payment_status, 
                attachment");
    
        $this->datatables->from('purchases')
            ->join('projects', 'purchases.project_id = projects.project_id', 'left');

            $this->datatables->where('purchases.is_asset', 1);
        
        if ($warehouse_id) {
            $this->datatables->where('purchases.warehouse_id', $warehouse_id);
        }
        if ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }


        $this->datatables->add_column("Actions", $action, "purchases.id");
        echo $this->datatables->generate();
    }
    public function getExpenses(){
      //  $this->bpas->checkPermissions('expenses');
        $detail_link = anchor('admin/purchases/expense_note/$1', '<i class="fa fa-file-text-o"></i> ' . lang('expense_note'), 'data-toggle="modal" data-target="#myModal2"');
        $edit_link   = anchor('admin/assets/edit_expense/$1', '<i class="fa fa-edit"></i> ' . lang('edit_expense'));
        //$attachment_link = '<a href="'.base_url('assets/uploads/$1').'" target="_blank"><i class="fa fa-chain"></i></a>';
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line('delete_expense') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('assets/delete_expense/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_expense') . '</a>';
        $schedule_link = anchor('admin/assets/schedule/$1', '<i class="fa fa-file-text-o"></i> ' . lang('schedule'));

        $add_depreciation = anchor('admin/assets/evaluation/$1', '<i class="fa fa-money"></i> ' . lang('add_depreciation'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');

        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $schedule_link . '</li>
            <li>' . $add_depreciation . '</li>
            <li>' . $detail_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
        </div></div>';

        $this->load->library('datatables');

        $this->datatables
            ->select($this->db->dbprefix('expenses') . ".id as id, 
                {$this->db->dbprefix('expenses')}.date, 
                {$this->db->dbprefix('expenses')}.reference, 
                {$this->db->dbprefix('assets')}.name as category, 
                gl.accountname as accountname,
                {$this->db->dbprefix('gl_charts')}.accountname as bank_code, 
                {$this->db->dbprefix('expenses')}.amount, 
                CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user, 
                {$this->db->dbprefix('expenses')}.attachment", false)
            ->from('expenses')
            ->join('users', 'users.id=expenses.created_by', 'left')
            ->join('expense_categories', 'expense_categories.id=expenses.category_id', 'left')
            ->join('assets', 'assets.id=expenses.product_id', 'left')

            ->join('gl_charts gl', 'gl.accountcode=expenses.bank_account', 'left')

            ->join('gl_charts', 'gl_charts.accountcode=expenses.bank_code', 'left')
            ->group_by('expenses.reference')
            ->where('expenses.is_asset',1);

        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }

        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
    public function add_expense(){
        $this->bpas->checkPermissions('expenses', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('reference', lang("reference"), 'required|is_unique[expenses.reference]');
        $this->form_validation->set_rules('amount[]', lang('amount'), 'required');
        $this->form_validation->set_rules('residual_value', lang('residual_value'), 'required|numeric');
        $this->form_validation->set_rules('useful', lang('useful'), 'required|numeric');
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
            $useful = $this->input->post('useful');
            $residual_value = $this->input->post('residual_value');
            $type = $this->input->post('type');
            
            $arrays[] = 0;
            $array1 = $this->input->post('amount');
            $array2 = $this->input->post('paid_by');
            $array3 = $this->input->post('bank_account');
            $biller_id = $this->input->post('biller') ? $this->input->post('biller') : $this->Settings->default_biller;

            $i                = sizeof($_POST['amount']);

            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['asset_name'][$r];
                $quantity = $_POST['quantity'][$r];

                $costing = $_POST['amount'][$r];
                $bank_account = $_POST['bank_account'][$r];
                $account_paid = $_POST['paid_by'][$r];
                $amount =  $this->bpas->formatDecimal($quantity * $costing );

                $data = [
                    'date'         => $date,
                    'reference'    => $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('ex'),
                    'amount'       => $amount,
                    'created_by'   => $this->session->userdata('user_id'),
                    'note'         => $this->input->post('note', true),
                    'category_id'  => $this->input->post('category', true),
                    'warehouse_id' => $this->input->post('warehouse', true),
                    'project_id'   => $this->input->post('project'),
                    'bank_account' => $bank_account,
                    'bank_code'    =>$account_paid,
                    'biller_id'    => $biller_id,
                    'is_asset'      => 1,
                    'useful_life'  => $this->input->post('useful'),
                    'residual_value'  => $this->input->post('residual_value'),
                    'product_id'        => $product_id,
                    'qty'        => $quantity,
                    'cost'        => $costing,
                ];

                //=======add acounting=========//
                if($this->Settings->accounting == 1){
                    $expense_category = $this->purchases_model->getExpenseCategoryByID($this->input->post('category'));
                    // TODO Add required field more
                    //$biller_id = $this->input->post('biller') ? $this->input->post('biller') : $this->Settings->default_biller;
                    $project_id = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
                    $supplier_id = $this->Settings->default_supplier;
                    $supplier_details = $this->site->getCompanyByID($supplier_id);
                    $supplier         = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
                    $pay_from_account = $this->input->post('bank_account');
                    $reference = $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('ex');
                    // $paid_by = $this->input->post('paid_by');

                    $accTrans[] = array(
                        'tran_type' => 'ExpenseAsset',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' =>  $bank_account,
                        'amount' => $amount,
                        'narrative' => $this->site->getAccountName($bank_account),
                        'note' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'supplier_id' => $supplier_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );

                    $accTrans[] = array(
                        'tran_type' => 'ExpenseAsset',
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
        if ($this->form_validation->run() == true && $this->assets_model->addExpense($datas,$accTrans)) {
            $this->session->set_flashdata('message', lang('expense_added'));
            admin_redirect('assets/expenses');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['exnumber']   = $this->site->getReference('ex');
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            if ($this->Owner || $this->Admin) {
                $this->data['projects'] = $this->site->getAllProject();
            }else{
                $this->data['projects'] = $this->site->getAllProject($this->session->userdata('warehouse_id'));
            }
            $this->data['get_assets'] = $this->site->getAssets();
            $this->data['bankAccounts'] = $this->accounts_model->getAllChartAccounts();
            $this->data['paid_by']      = $this->accounts_model->getAllChartAccounts();

                                    //$this->accounts_model->getAllChartAccountBank();
            $this->data['currency']     = $this->site->getCurrency();
            $this->data['currencies']   = $this->bpas->getAllCurrencies();
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['categories']   = $this->purchases_model->getExpenseCategories();

            $bc                                = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('add_expense')]];
            $meta                              = ['page_title' => lang('add_expense'), 'bc' => $bc];
            $this->page_construct('assets/add_expense', $meta, $this->data);
        }
    
    }
    public function edit_expense($id){
        $this->bpas->checkPermissions('expenses', true);
        $this->load->helper('security');

        $expenses = $this->purchases_model->getExpenseByID($id);

    //    $this->form_validation->set_rules('reference', lang("reference"), 'required|is_unique[expenses.reference]');
        $this->form_validation->set_rules('reference', lang("reference"), 'required');
        $this->form_validation->set_rules('amount[]', lang('amount'), 'required');
        $this->form_validation->set_rules('residual_value', lang('residual_value'), 'required|numeric');
        $this->form_validation->set_rules('useful', lang('useful'), 'required|numeric');
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
            $useful = $this->input->post('useful');
            $residual_value = $this->input->post('residual_value');
            $type = $this->input->post('type');
            
            $arrays[] = 0;
            $array1 = $this->input->post('amount');
            $array2 = $this->input->post('paid_by');
            $array3 = $this->input->post('bank_account');
            $biller_id = $this->input->post('biller') ? $this->input->post('biller') : $this->Settings->default_biller;

            $i                = sizeof($_POST['amount']);

            //for ($r = 0; $r < $i; $r++) {
                $product_id = $this->input->post('asset_name');//$_POST['asset_name'][$r];
                $quantity = $this->input->post('quantity');//$_POST['quantity'][$r];

                $costing = $this->input->post('amount'); //$_POST['amount'][$r];
                $bank_account = $this->input->post('bank_account');//$_POST['bank_account'][$r];
                $account_paid = $this->input->post('paid_by');//$_POST['paid_by'][$r];
                $amount =  $this->bpas->formatDecimal($quantity * $costing);

                $data = [
                    'date'         => $date,
                    'reference'    => $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('ex'),
                    'amount'       => $amount,
                    'created_by'   => $this->session->userdata('user_id'),
                    'note'         => $this->input->post('note', true),
                    'category_id'  => $this->input->post('category', true),
                    'warehouse_id' => $this->input->post('warehouse', true),
                    'project_id'   => $this->input->post('project'),
                    'bank_account' => $bank_account,
                    'bank_code'    =>$account_paid,
                    'biller_id'    => $biller_id,
                    'is_asset'      => 1,
                    'useful_life'  => $this->input->post('useful'),
                    'residual_value'  => $this->input->post('residual_value'),
                    'product_id'        => $product_id,
                    'qty'        => $quantity,
                    'cost'        => $costing,
                ];

                //=======add acounting=========//
                if($this->Settings->accounting == 1){
                    $expense_category = $this->purchases_model->getExpenseCategoryByID($this->input->post('category'));
                    $project_id = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
                    $supplier_id = $this->Settings->default_supplier;
                    $supplier_details = $this->site->getCompanyByID($supplier_id);
                    $supplier         = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
                    $pay_from_account = $this->input->post('bank_account');
                    $reference = $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('ex');
                    // $paid_by = $this->input->post('paid_by');

                    $accTrans[] = array(
                        'tran_no'     => $id,
                        'tran_type' => 'ExpenseAsset',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' =>  $bank_account,
                        'amount' => $amount,
                        'narrative' => $this->site->getAccountName($bank_account),
                        'note' => $this->input->post('note', true),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'supplier_id' => $supplier_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );

                    $accTrans[] = array(
                        'tran_no'     => $id,
                        'tran_type' => 'ExpenseAsset',
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
                $datas = $data;
           // }
            krsort($datas);
        } elseif ($this->input->post('add_expense')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->assets_model->updateExpense($id,$datas,$accTrans)) {
            $this->session->set_flashdata('message', lang('expense_added'));
            admin_redirect('assets/expenses');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['exnumber']   = $this->site->getReference('ex');
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            if ($this->Owner || $this->Admin) {
                $this->data['projects'] = $this->site->getAllProject();
            }else{
                $this->data['projects'] = $this->site->getAllProject($this->session->userdata('warehouse_id'));
            }
            $this->data['get_assets'] = $this->site->getAssets();
            $this->data['bankAccounts'] = $this->accounts_model->getAllChartAccounts();
            $this->data['paid_by']      = $this->accounts_model->getAllChartAccounts();

            //$this->accounts_model->getAllChartAccountBank();
            $this->data['expense'] = $expenses;
            $this->data['currency']     = $this->site->getCurrency();
            $this->data['currencies']   = $this->bpas->getAllCurrencies();
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['categories']   = $this->purchases_model->getExpenseCategories();

            $bc                                = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('edit_expense')]];
            $meta                              = ['page_title' => lang('edit_expense'), 'bc' => $bc];
            $this->page_construct('assets/edit_expense', $meta, $this->data);
        }
    
    }
    public function delete_expense($id = null)
    {
        $this->bpas->checkPermissions('expenses', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $expense = $this->purchases_model->getExpenseByID($id);
        if ($this->purchases_model->deleteExpense($id)) {
            $this->site->deleteAccTran('ExpenseAsset',$id);
            $this->assets_model->deleteDPByPurchaseID($id);

            if ($expense->attachment) {
                unlink($this->upload_path . $expense->attachment);
            }
            $this->bpas->send_json(['error' => 0, 'msg' => lang('expense_deleted')]);
        }
    }
    public function asset_expense($id = null)
    {
      //  $this->bpas->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $get_depreciation = $this->site->getevaluationByID($id);
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('amount', lang('amount'), 'required');
        if ($this->form_validation->run() == true) {
            
         
            if ($this->Owner || $this->Admin) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $get_depreciation->date;
            }

            $payment = [
                'is_expense'        => 1,
                'acc_account'   => $this->input->post('asset_account'),
                'dp_account'    => $this->input->post('expense_account'),
                'reference_no'      => $this->input->post('reference_no'),
                'biller_id'         => $this->input->post('biller_id'),
                'created_by'        => $this->session->userdata('user_id'),
            ];

            $tran_date          = $this->bpas->fld(trim($this->input->post('date')));
            $tran_no = $this->accounts_model->getTranNo();
            $reference_no       =   $this->input->post('reference_no');//$this->site->getReference('jr');
            //=====add accounting=====//
            if($this->Settings->accounting == 1){
                
                $asset_account = $this->input->post('asset_account');
                $expense_account = $this->input->post('expense_account');
                
                $accTranPayments[] = array(
                    'tran_no'       => $tran_no,
                    'tran_type'     => 'Depreciation',
                    'tran_date'     => $tran_date,
                    'reference_no'  => $reference_no,
                    'account_code'  => $expense_account,
                    'amount'        => $this->input->post('amount'),
                    'narrative'     => $this->site->getAccountName($expense_account),
                    'description'   => $this->input->post('note'),
                    'biller_id'     => $this->input->post('biller_id'),
                    'created_by'    => $this->session->userdata('user_id'),
                    'depreciation_id' => $id,
                    'expense_id'    => $get_depreciation->expense_id,
                );

                $accTranPayments[] = array(
                    'tran_no'       => $tran_no,
                    'tran_type'     => 'Depreciation',
                    'tran_date'     => $tran_date,
                    'reference_no'  => $reference_no,
                    'account_code'  => $asset_account,
                    'amount'        => (-1)*$this->input->post('amount'),
                    'narrative'     => $this->site->getAccountName($asset_account),
                    'description'   => $this->input->post('note'),
                    'biller_id'     => $this->input->post('biller_id'),
                    'created_by'    => $this->session->userdata('user_id'),
                    'depreciation_id' => $id,
                    'expense_id'    =>$get_depreciation->expense_id,
                );  
            }else{
                $accTranPayments =[];
            }
            //=====end accounting=====//
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

        if ($this->form_validation->run() == true && 
            $this->accounts_model->add_depreciation($id,$payment,$accTranPayments)) {
            $this->session->set_flashdata('message', lang('payment_updated'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['ExpenseAccounts'] = $this->accounts_model->getAllChartAccountIn('50,60,80');
            $this->data['sectionacc'] = $this->accounts_model->getAllChartAccount();
            $this->data['inv']         = $get_depreciation;
            $this->data['billers'] = $this->site->getAllCompanies('biller');
         //   $this->data['payment_trans']         = $getTranByID;
            $this->data['depreciation_ref'] = $this->site->getReference('dp');
            $this->data['modal_js']    = $this->site->modal_js();

            $this->load->view($this->theme . 'assets/asset_expense', $this->data);
        }
    }
    public function schedule($id = null)
    {
        $this->bpas->checkPermissions('index');

        $pr_details = $this->assets_model->getScheduleByExpenseId($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('asset_already_add_depreciation'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->data['evaluation_list']  = $pr_details;

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('assets'), 'page' => lang('Assets')], ['link' => '#', 'page' => lang('schedule') ]];
        $meta = ['page_title' => lang('schedule'), 'bc' => $bc];
        $this->page_construct('assets/schedule', $meta, $this->data);
    }
    public function depreciation()
    {
        $this->bpas->checkPermissions('index');

        $pr_details = $this->assets_model->getScheduleByExpenseId();
   

        $this->data['evaluation_list']  = $pr_details;

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('assets'), 'page' => lang('Assets')], ['link' => '#', 'page' => lang('schedule') ]];
        $meta = ['page_title' => lang('schedule'), 'bc' => $bc];
        $this->page_construct('assets/depreciation', $meta, $this->data);
    }
    function getDepreciation($expense_id=null){
        
        $this->load->library('datatables');
        $this->datatables
            ->select('asset_evaluation.id, evaluation_date, current_cost, accumulated, net_value,
                asset_evaluation.reference_no as refer,purchases.reference_no,
                IF(is_expense = 1, 
                    CONCAT(bpas_asset_evaluation.id,"__","1"), 
                    CONCAT(bpas_asset_evaluation.id,"__","0")
                ) as status
            ')
            ->from('asset_evaluation')
            ->join('purchases', 'asset_evaluation.expense_id = purchases.id', 'left');
            //->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('assets/asset_expense/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('add_expense') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_expense') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('assets/delete_depreciation/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    
    }
    
    function evaluation($warehouse_id = null)
    {
         $this->bpas->checkPermissions('index');
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
        $biller_id = $this->session->userdata('biller_id');
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['user_billers'] = $this->site->getCompanyByID($biller_id);
        
        $this->data['products'] = $this->site->getAssets();
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['supplier'] = $this->input->get('supplier') ? $this->site->getCompanyByID($this->input->get('supplier')) : null;
        $bc                     = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Assets')]];
        $meta                   = ['page_title' => lang('Assets'), 'bc' => $bc];
        $this->page_construct('assets/evaluation', $meta, $this->data);
    }
    public function get_evaluation($warehouse_id = null)
    {
        $this->bpas->checkPermissions('index', true);
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : null;
        $product = $this->input->get('product') ? $this->input->get('product') : null;
        $category = $this->input->get('category') ? $this->input->get('category') : null;
        $biller = $this->input->get('biller') ? $this->input->get('biller') : null;

        $detail_link = anchor('admin/assets/asset_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('asset_details'));
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line('delete_asset') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete1' id='a__$1' href='" . admin_url('assets/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_asset') . '</a>';
        $single_barcode = anchor('admin/products/print_barcodes/$1', '<i class="fa fa-print"></i> ' . lang('print_barcode_label'));
        // $single_label = anchor_popup('products/single_label/$1/' . ($warehouse_id ? $warehouse_id : ''), '<i class="fa fa-print"></i> ' . lang('print_label'), $this->popup_attributes);
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            </ul>
        </div></div>';
        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables->select(
                $this->db->dbprefix('products') . ".id as productid, 
                {$this->db->dbprefix('products')}.image as image, 
                evaluation_date,
                {$this->db->dbprefix('products')}.code as code, 
                {$this->db->dbprefix('products')}.name as name, 
                {$this->db->dbprefix('companies')}.name as biller_id, 
                {$this->db->dbprefix('categories')}.name as cname, 
                cost as cost,
                price,
                CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by", false)
            ->from('asset_evaluation');
        
                $this->datatables->where('products.warehouse_id', $warehouse_id);
           
            
            $this->datatables->join('products', 'products.id=asset_evaluation.product_id', 'left')
            ->join('categories', 'products.category_id=categories.id', 'left')
            ->join('units', 'products.unit=units.id', 'left')
            ->join('brands', 'products.brand=brands.id', 'left')
            ->join('companies', 'products.biller_id=companies.id', 'left')
            ->join('users', 'asset_evaluation.created_by=users.id', 'left');
        } else {
            $this->datatables->select(
                $this->db->dbprefix('products') . ".id as productid, 
                {$this->db->dbprefix('products')}.image as image, 
                evaluation_date,
                {$this->db->dbprefix('products')}.code as code, 
                {$this->db->dbprefix('products')}.name as name, 
                {$this->db->dbprefix('companies')}.name as biller_id, 
                {$this->db->dbprefix('categories')}.name as cname, 
                cost as cost,
                price,
                CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by", false)
            ->from('asset_evaluation')
            ->join('products', 'products.id=asset_evaluation.product_id', 'left')
            ->join('categories', 'products.category_id=categories.id', 'left')
            ->join('units', 'products.unit=units.id', 'left')
            ->join('brands', 'products.brand=brands.id', 'left')
            ->join('companies', 'products.biller_id=companies.id', 'left')
            ->join('users', 'asset_evaluation.created_by=users.id', 'left');
        }
    

        if($biller){
            $this->datatables->where("products.biller_id", $biller);
        }
        if($product){
            $this->datatables->where("products.id", $product);
        }
        if($category){
            $this->datatables->where("products.category_id", $category);
        }
        $this->datatables->add_column('Actions', $action, 'productid, image, code, name');
        echo $this->datatables->generate();
    }
}
