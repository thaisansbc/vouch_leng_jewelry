<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Products extends MY_Controller
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
        $this->load->admin_model('products_model');
        $this->load->admin_model('purchases_model');
        $this->load->admin_model('transfers_model');
        $this->load->admin_model('accounts_model');
        $this->load->admin_model('sales_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path         = 'assets/uploads/';
        $this->thumbs_path         = 'assets/uploads/thumbs/';
        $this->image_types         = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types  = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size   = '1024';
        $this->popup_attributes    = ['width' => '900', 'height' => '600', 'window_name' => 'sma_popup', 'menubar' => 'yes', 'scrollbars' => 'yes', 'status' => 'no', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0'];
    }

    public function add($id = null, $type = null)
    {
        $this->bpas->checkPermissions();
        $this->load->helper('security');
        $warehouses = $this->site->getAllWarehouses();
        $this->form_validation->set_rules('category', lang('category'), 'required|is_natural_no_zero');
        if($this->input->post('default_sale_unit') && $this->input->post('default_purchase_unit')){
            $default_sale_unit = $this->site->getUnitByID($this->input->post('default_sale_unit'));
            $default_purchase_unit = $this->site->getUnitByID($this->input->post('default_purchase_unit'));
        }
        if ($this->input->post('type') == 'standard') {
            $this->form_validation->set_rules($default_sale_unit->code."_cost", lang('product_cost'), 'required');
            $this->form_validation->set_rules($default_purchase_unit->code."_price", lang('product_unit'), 'required');
        }
        $this->form_validation->set_rules('code', lang('product_code'), 'is_unique[products.code]|alpha_dash');
        if (SHOP) {
            $this->form_validation->set_rules('slug', lang('slug'), 'required|is_unique[products.slug]|alpha_dash');
        }
        $this->form_validation->set_rules('weight', lang('weight'), 'numeric');
        $this->form_validation->set_rules('product_image', lang('product_image'), 'xss_clean');
        $this->form_validation->set_rules('digital_file', lang('digital_file'), 'xss_clean');
        $this->form_validation->set_rules('userfile', lang('product_gallery_images'), 'xss_clean');
        $this->form_validation->set_rules('serial_no', lang('serial_no'), 'is_unique[products.serial_no]|alpha_dash');
        if ($this->input->post('units_div[]')) { 
            $units_div = $this->input->post('units_div[]');
            for ($j=0 ; $j < sizeof($units_div); $j++) { 
                $this->form_validation->set_rules($units_div[$j]."_code" , lang('product_code'), 'is_unique[cost_price_by_units.product_code]|alpha_dash');
            }
        }
        $item_code    = $this->input->post('item_code');
        $product_code = $this->input->post('code');
        $prod_code    = $item_code ? ($product_code."|".$item_code) : $product_code;
        if ($this->form_validation->run() == true) {
            $tax_rate = $this->input->post('tax_rate') ? $this->site->getTaxRateByID($this->input->post('tax_rate')) : null;
            $units_div = $this->input->post('units_div[]');
            for ($j=0; $j < sizeof($units_div); $j++) { 
                if (filter_var($this->input->post($units_div[$j]."_cost"), FILTER_VALIDATE_FLOAT) === false || filter_var($this->input->post($units_div[$j]."_price"), FILTER_VALIDATE_FLOAT) === false) {
                    $this->session->set_flashdata('error', 'Please input cost and price decimal number!');
                    admin_redirect('products/add');
                }
                $unit = $this->site->getUnitByCode($units_div[$j]);
                $unit_datas[] = array(
                    'unit_id'      => $unit->id,
                    'price'        => $this->input->post($units_div[$j]."_price"),
                    'cost'         => $this->input->post($units_div[$j]."_cost"),
                    'product_code' => $this->input->post($units_div[$j]."_code")
                );  
                $product_units[] = array(
                    'unit_id'      => $unit->id,
                    'unit_qty'     => (!empty($unit->operation_value) ? $unit->operation_value : 1),
                    'unit_price'   => $this->input->post($units_div[$j]."_price")
                ); 
            }
            $punit = $this->site->getUnitByID($this->input->post('unit'));
            $stock_type_selected = implode(',', $this->input->post('stock_type'));
            if (filter_var($this->input->post($punit->code."_cost"), FILTER_VALIDATE_FLOAT) === false || filter_var($this->input->post($punit->code."_price"), FILTER_VALIDATE_FLOAT) === false) {
                $this->session->set_flashdata('error', 'Please input cost and price decimal number!');
                admin_redirect('products/add');
            }
            $data  = [
                'code'              => $prod_code,
                'barcode_symbology' => $this->input->post('barcode_symbology'),
                'item_code'         => $item_code,
                'serial_no'         => $this->input->post('serial_no'),
                'max_serial'        => $this->input->post('max_serial'),
                'batch_numer'        => $this->input->post('batch_numer'),
                'name'              => $this->input->post('name'),
                'type'              => $this->input->post('type'),
                'brand'             => $this->input->post('brand'),
                'stock_type'        => $stock_type_selected,
                'category_id'       => $this->input->post('category'),
                'subcategory_id'    => $this->input->post('subcategory') ? $this->input->post('subcategory') : null,
                'cost'              => $this->bpas->formatDecimal($this->input->post($punit->code."_cost")),
                'price'             => $this->bpas->formatDecimal($this->input->post($punit->code."_price")),
                'other_cost'        => $this->bpas->formatDecimal($this->input->post('other_cost')),
                'other_price'       => $this->bpas->formatDecimal($this->input->post('other_price')),
                'currency'          => $this->input->post('currency'),
                'unit'              => $this->input->post('unit'),
                'sale_unit'         => $this->input->post('default_sale_unit'),
                'purchase_unit'     => $this->input->post('default_purchase_unit'),
                'tax_rate'          => $this->input->post('tax_rate'),
                'tax_method'        => $this->input->post('tax_method'),
                'alert_quantity'    => ($this->input->post('alert_quantity') != null && $this->input->post('alert_quantity') != '') ? $this->input->post('alert_quantity') : null,
                'expiry_alert_days' => ($this->input->post('expiry_alert_days') != null && $this->input->post('expiry_alert_days') != '') ? $this->input->post('expiry_alert_days') : null,
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
                'status'            => $this->input->post('status'),
                'stregth'           => $this->input->post('stregth'),
            ]; 
            if ($this->Settings->cbm == 1) {
                $data['p_length'] = $this->input->post('p_length');
                $data['p_width']  = $this->input->post('p_width');
                $data['p_height'] = $this->input->post('p_height');
                $data['p_weight'] = $this->input->post('p_weight');
            }
            $product_account = [];
            if ($this->Settings->module_account == 1) {
                $product_account = array(
                    'revenue_account'   => $this->input->post('revenue_account'),
                    'stock_account'     => $this->input->post('stock_account'),
                    'costing_account'   => $this->input->post('pro_cost_account'),
                    'adjustment_account'=> $this->input->post('adjustment_account'),
                    'using_account'     => $this->input->post('stock_using_account'),
                    'convert_account'   => $this->input->post('convert_account'),
                    'ar_account'        => $this->input->post('ar_account'),
                );
            }   
            // $this->bpas->print_arrays($data, $unit_datas); 
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
                    if (isset($_POST['wh_qty_' . $warehouse->id]) && !empty($_POST['wh_qty_' . $warehouse->id]) && $_POST['wh_qty_' . $warehouse->id] != '') {
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
                                'quantity'     => $_POST['attr_quantity'][$r],
                                'price'        => $_POST['attr_price'][$r],
                            ];
                            $pv_total_quantity += $_POST['attr_quantity'][$r];
                        }
                    }
                } else {
                    $product_attributes = null;
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
                if($this->Settings->combo_price_match == 1){
                    if ($this->bpas->formatDecimal($total_price) != $this->bpas->formatDecimal($this->input->post('price'))) {
                        $this->form_validation->set_rules('combo_price', 'combo_price', 'required');
                        $this->form_validation->set_message('required', lang('pprice_not_match_ciprice'));
                    }
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
            } elseif ($this->input->post('type') == 'bom') {    
                $c = sizeof($_POST['bom_item_id']) - 1;
                for ($r = 0; $r <= $c; $r++) {
                    if (isset($_POST['bom_item_id'][$r]) && isset($_POST['bom_item_quantity'][$r])) {
                        $bom_items[] = array(
                            'bom_type'      => $_POST['bom_type'][$r],
                            'product_id'    => $_POST['bom_item_id'][$r],
                            'quantity'      => $_POST['bom_item_quantity'][$r],
                            'unit_id'       => $_POST['bom_unit_id'][$r],
                            'biller_id'     => $_POST['bom_item_biller'][$r]
                        );
                    } 
                }
                $data['track_quantity'] = 0;
            }
            //------option-----------
            if ($this->input->post('product_option')) {
                $a = sizeof($_POST['product_option']);
                for ($r = 0; $r <= $a; $r++) {
                    if (isset($_POST['product_option'][$r])) {
                        $product_options[] = [
                            'option_id' => $_POST['product_option'][$r],
                        ];
                    }
                }
            } else {
                $product_options = null;
            } 
            if ($wh_total_quantity != $pv_total_quantity && $pv_total_quantity != 0) {
                $this->form_validation->set_rules('wh_pr_qty_issue', 'wh_pr_qty_issue', 'required');
                $this->form_validation->set_message('required', lang('wh_pr_qty_issue'));
            }
            if ($this->Settings->product_formulation == 1 && $this->input->post('formulation')){
                $d = sizeof($_POST['for_caculation']) - 1;
                for ($r = 0; $r <= $d; $r++) {
                    if (isset($_POST['for_caculation'][$r])) {
                        $formulation_items[] = array(
                            'for_width' => $_POST['for_width'][$r],
                            'for_height' => $_POST['for_height'][$r],
                            'for_square' => $_POST['for_square'][$r],
                            'for_qty' => $_POST['for_qty'][$r],
                            'for_field' => $_POST['for_field'][$r],
                            'for_caculation' => $_POST['for_caculation'][$r],
                            'for_operation' => $_POST['for_operation'][$r],
                            'for_unit_id' => $this->input->post('unit'),
                        );
                    } 
                }
            }
            if (!isset($items)) {
                $items = null;
            }
            if (!isset($bom_items)) {
                $bom_items = NULL;
            }
            if (!isset($formulation_items)) {
                $formulation_items = NULL;
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
            if (isset($_POST['addOn_item_code'])) {
                $c = sizeof($_POST['addOn_item_code']) - 1;
                for ($r = 0; $r <= $c; $r++) {
                    if (isset($_POST['addOn_item_code'][$r])) {
                        $addOn_items[] = [
                            'item_code'   => $_POST['addOn_item_code'][$r],
                            'price'   => $_POST['addOn_item_price'][$r],
                            'description' => $_POST['addOn_item_description'][$r]
                        ];
                    }
                }
            }
            if (!isset($addOn_items)) {
                $addOn_items = null;
            }
            $data['quantity'] = $wh_total_quantity ?? 0;
            $warehouse_racks = null;
            if ($this->input->post('type') == 'standard') {
                foreach ($warehouses as $warehouse) {
                    if (isset($_POST['wh_rack_' . $warehouse->id]) && !empty($_POST['wh_rack_' . $warehouse->id]) && $_POST['wh_rack_' . $warehouse->id] != '') {
                        $warehouse_racks[] = [
                            'warehouse_id' => $this->input->post('wh_rack_' . $warehouse->id),
                            'rack_id'      => ((isset($_POST['wh_product_rack_id_' . $warehouse->id]) && !empty($_POST['wh_product_rack_id_' . $warehouse->id])) ? $_POST['wh_product_rack_id_' . $warehouse->id] : null)
                        ];
                    }
                } 
            }
        }
        if ($this->form_validation->run() == true && $this->products_model->addProduct($data, $items, $warehouse_qty, $product_attributes, $photos, $addOn_items, $product_options, $unit_datas, $product_account, $bom_items, $formulation_items, $product_units, $warehouse_racks)) {
            $this->session->set_flashdata('message', lang('product_added'));
            admin_redirect('products');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['type']                = $type;
            $this->data['projects']            = $this->site->getAllProject();
            $this->data['currencies']          = $this->bpas->getAllCurrencies();
            $this->data['categories']          = $this->site->getAllCategories();
            $this->data['nest_categories']     = $this->site->getNestedByCategories();
            //$this->data['nest_categories']     = $this->site->getNestedCategories();
            $this->data['tax_rates']           = $this->site->getAllTaxRates();
            $this->data['brands']              = $this->site->getAllBrands();
            $this->data['stock_types']         = $this->site->getAllStockType();
            $this->data['base_units']          = $this->site->getAllBaseUnits();
            $this->data['warehouses']          = $warehouses;
            $this->data['warehouses_products'] = $id ? $this->products_model->getAllWarehousesWithPQ($id) : null;
            $this->data['product']             = $id ? $this->products_model->getProductByID($id) : null;
            $this->data['variants']            = $this->products_model->getAllVariants();
            $this->data['combo_items']         = ($id && $this->data['product']->type == 'combo') ? $this->products_model->getProductComboItems($id) : null;
            $this->data['billers'] = $this->Settings->module_concrete ? json_encode($this->site->getBillers()) : false;
            $this->data['product_options']     = $id ? $this->products_model->getProductOptionsWithWH($id) : null;
            $this->data['options']             = $this->products_model->getAllOptions();
            $this->data['addon_items']         = $id ? $this->products_model->getProductAddOnItems($id) : null;
            $this->data['chart_accounts']      = $this->accounts_model->getAllChartAccounts();
            $this->data['product_racks']       = $this->site->getProductRacks();
            
            $bc                                = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('add_product')]];
            $meta                              = ['page_title' => lang('add_product'), 'bc' => $bc];
            $this->page_construct('products/add', $meta, $this->data);
        }
    }

    public function edit($id = null, $type=null)
    {
        $this->bpas->checkPermissions();
        $this->load->helper('security');
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }
        $warehouses          = $this->site->getAllWarehouses();
        $warehouses_products = $this->products_model->getAllWarehousesWithPQ($id);
        $product             = $this->site->getProductByID($id);
        if (!$id || !$product) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->form_validation->set_rules('category', lang('category'), 'required|is_natural_no_zero');
        if($this->input->post('default_sale_unit') && $this->input->post('default_purchase_unit')){
            $default_sale_unit = $this->site->getUnitByID($this->input->post('default_sale_unit'));
            $default_purchase_unit = $this->site->getUnitByID($this->input->post('default_purchase_unit'));
        }
        if ($this->input->post('units_div[]') == NULL) {
            $units_div = $this->input->post('units_div2[]');
        } else {
            $units_div = $this->input->post('units_div[]');
        }
        $unit_id = $this->input->post('unit');
        $units   = $this->site->getUnitsByBUID($unit_id);
        foreach($units as $unit) {
            $unit_arr[] = array(
                'cost'          => $this->input->post($unit->code . '_cost'),
                'unit_id'       => $unit->id,
                'price'         => $this->input->post($unit->code . '_price'),
                'product_code'  => $this->input->post($unit->code.'_code') ? $this->input->post($unit->code.'_code') : null
            );  
            $product_units[] = array(
                'product_id'    => $id,
                'unit_id'       => $unit->id,
                'unit_qty'      => (!empty($unit->operation_value) ? $unit->operation_value : 1),
                'unit_price'    => $this->input->post($unit->code . '_price'),
            );
        }
        if ($this->input->post('type') == 'standard') {
            $this->form_validation->set_rules('unit', lang('product_unit'), 'required');
            $this->form_validation->set_rules($default_sale_unit->code."_cost", lang('product_cost'), 'required');
            $this->form_validation->set_rules($default_purchase_unit->code."_price", lang('product_price'), 'required');
        }
        foreach ($units as $unit) {
            $this->form_validation->set_rules($unit->code.'_code', lang('product_code'), 'alpha_dash');
            if(isset($_POST[$unit->code.'_code'])){
                $productcodeunit = $this->site->getProductCostPriceByID($unit->id, $id);
                $pcodes = $this->input->post($unit->code.'_code');
                if( $productcodeunit->product_code != $pcodes){
                    $checkdubplicate = $this->site->getCostPriceUnit($pcodes);
                    if ($checkdubplicate) {
                        $this->session->set_flashdata('error', lang('Product_code_has_already_!'));
                        admin_redirect('products/edit/' . $id);
                    }
                }
            }
        }
        $this->form_validation->set_rules('code', lang('product_code'), 'alpha_dash');
        if ($this->input->post('code') !== $product->code) {
            $this->form_validation->set_rules('code', lang('product_code'), 'is_unique[products.code]');
        }
        if (SHOP) {
            $this->form_validation->set_rules('slug', lang('slug'), 'required|alpha_dash');
            if ($this->input->post('slug') !== $product->slug) {
                $this->form_validation->set_rules('slug', lang('slug'), 'required|is_unique[products.slug]|alpha_dash');
            }
        }
        $this->form_validation->set_rules('weight', lang('weight'), 'numeric');
        $this->form_validation->set_rules('product_image', lang('product_image'), 'xss_clean');
        $this->form_validation->set_rules('digital_file', lang('digital_file'), 'xss_clean');
        $this->form_validation->set_rules('userfile', lang('product_gallery_images'), 'xss_clean');
        $item_code    = $this->input->post('item_code');
        $product_code = $this->input->post('code');
        $prod_code    = $item_code ? ($product_code."|".$item_code) : $product_code;
        if ($this->form_validation->run('products/add') == true) {
            $punit = $this->site->getUnitByID($unit_id);
            $stock_type_selected = implode(',', $this->input->post('stock_type'));
            $data  = [
                'code'              => $prod_code,
                'barcode_symbology' => $this->input->post('barcode_symbology'),
                'item_code'         => $item_code,
                'serial_no'         => $this->input->post('serial_no'),
                'max_serial'        => $this->input->post('max_serial'),
                'batch_numer'       => $this->input->post('batch_numer'),
                'name'              => $this->input->post('name'),
                'type'              => $this->input->post('type'),
                'brand'             => ($this->input->post('brand') ? $this->input->post('brand') : 0),
                'stock_type'        => $stock_type_selected,
                'category_id'       => $this->input->post('category'),
                'subcategory_id'    => $this->input->post('subcategory') ? $this->input->post('subcategory') : null,
                'cost'              => $this->bpas->formatDecimal($this->input->post($punit->code."_cost")),
                'price'             => $this->bpas->formatDecimal($this->input->post($punit->code."_price")),
                'other_cost'        => $this->bpas->formatDecimal($this->input->post('other_cost')),
                'other_price'       => $this->bpas->formatDecimal($this->input->post('other_price')),
                'currency'          => $this->input->post('currency'),
                'unit'              => $this->input->post('unit'),
                'sale_unit'         => $this->input->post('default_sale_unit'),
                'purchase_unit'     => $this->input->post('default_purchase_unit'),
                'tax_rate'          => $this->input->post('tax_rate'),
                'tax_method'        => $this->input->post('tax_method'),
                'alert_quantity'    => ($this->input->post('alert_quantity') != null && $this->input->post('alert_quantity') != '') ? $this->input->post('alert_quantity') : null,
                'expiry_alert_days' => ($this->input->post('expiry_alert_days') != null && $this->input->post('expiry_alert_days') != '') ? $this->input->post('expiry_alert_days') : null,
                'track_quantity'    => $this->input->post('track_quantity') ? $this->input->post('track_quantity') : '0',
                'details'           => $this->input->post('details'),
                'product_details'   => $this->input->post('product_details'),
                'supplier1'         => ($this->input->post('supplier') ? $this->input->post('supplier') : 0),
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
                'status'            => $this->input->post('status'),
                'stregth'           => $this->input->post('stregth'),
            ];
            if ($this->Settings->cbm == 1) {
                $data['p_length'] = $this->input->post('p_length');
                $data['p_width'] = $this->input->post('p_width');
                $data['p_height'] = $this->input->post('p_height');
                $data['p_weight'] = $this->input->post('p_weight');
            }
            $product_account = [];
            if ($this->Settings->module_account == 1) {
                $product_account = array(
                    'revenue_account'    => $this->input->post('revenue_account'),
                    'stock_account'      => $this->input->post('stock_account'),
                    'costing_account'    => $this->input->post('pro_cost_account'),
                    'adjustment_account' => $this->input->post('adjustment_account'),
                    'using_account'      => $this->input->post('stock_using_account'),
                    'convert_account'    => $this->input->post('convert_account'),
                    'ar_account'         => $this->input->post('ar_account'),
                );
            } 
            $warehouse_qty      = null;
            $product_attributes = null;
            $update_variants    = [];
            $this->load->library('upload');
            if ($this->input->post('type') == 'standard') {
                if ($product_variants = $this->products_model->getProductOptions($id)) {
                    foreach ($product_variants as $pv) {
                        $update_variants[] = [
                            'id'    => $this->input->post('variant_id_' . $pv->id),
                            'name'  => $this->input->post('variant_name_' . $pv->id),
                            'cost'  => $this->input->post('variant_cost_' . $pv->id),
                            'price' => $this->input->post('variant_price_' . $pv->id),
                        ];
                    }
                }
                for ($s = 2; $s > 5; $s++) {
                    $data['suppliers' . $s]           = $this->input->post('supplier_' . $s);
                    $data['suppliers' . $s . 'price'] = $this->input->post('supplier_' . $s . '_price');
                }
                foreach ($warehouses as $warehouse) {
                    $warehouse_qty[] = [
                        'warehouse_id' => $this->input->post('wh_' . $warehouse->id),
                        'rack'         => $this->input->post('rack_' . $warehouse->id) ? $this->input->post('rack_' . $warehouse->id) : null,
                    ];
                }
                if ($this->input->post('attributes')) {
                    $a = sizeof($_POST['attr_name']);
                    for ($r = 0; $r <= $a; $r++) {
                        if (isset($_POST['attr_name'][$r])) {
                            if ($product_variatnt = $this->products_model->getPrductVariantByPIDandName($id, trim($_POST['attr_name'][$r]))) {
                                $this->form_validation->set_message('required', lang('product_already_has_variant') . ' (' . $_POST['attr_name'][$r] . ')');
                                $this->form_validation->set_rules('new_product_variant', lang('new_product_variant'), 'required');
                            } else {
                                $product_attributes[] = [
                                    'name'         => $_POST['attr_name'][$r],
                                    'warehouse_id' => $_POST['attr_warehouse'][$r],
                                    'quantity'     => $_POST['attr_quantity'][$r],
                                    'price'        => $_POST['attr_price'][$r],
                                ];
                            }
                        }
                    }
                } else {
                    $product_attributes = null;
                }
             
            }
            if ($this->input->post('type') == 'service') {
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'combo') {
                if(isset($_POST['combo_item_code'])){
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
                    if ($this->Settings->combo_price_match == 1) {
                        if ($this->bpas->formatDecimal($total_price) != $this->bpas->formatDecimal($this->input->post($default_sale_unit->code . '_price'))) {
                            $this->form_validation->set_rules('combo_price', 'combo_price', 'required');
                            $this->form_validation->set_message('required', lang('pprice_not_match_ciprice'));
                        }
                    }
                }
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'digital') {
                if ($this->input->post('file_link')) {
                    $data['file'] = $this->input->post('file_link');
                }
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
                }
                $config                 = null;
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'bom') {    
                $c = sizeof($_POST['bom_item_id']) - 1;
                for ($r = 0; $r <= $c; $r++) {
                    if (isset($_POST['bom_item_id'][$r]) && isset($_POST['bom_item_quantity'][$r])) {
                        $bom_items[] = array(
                            'product_id' => $_POST['bom_item_id'][$r],
                            'bom_type'   => $_POST['bom_type'][$r],
                            'quantity'   => $_POST['bom_item_quantity'][$r],
                            'unit_id'    => $_POST['bom_unit_id'][$r],
                            'biller_id'  => (isset($_POST['bom_item_biller'][$r]) ? $_POST['bom_item_biller'][$r] : null)
                        );
                    } 
                }
                $data['track_quantity'] = 0;
            }
            //---------option-----------
            if ($this->input->post('product_option')) {
                $a = sizeof($_POST['product_option']);
                for ($r = 0; $r <= $a; $r++) {
                    if (isset($_POST['product_option'][$r])) {
                        $product_options[] = [
                            'option_id' => $_POST['product_option'][$r],
                        ];
                    }
                }
            } else {
                $product_options = null;
            }
            /*if($this->Settings->product_formulation == 1 && $this->input->post('formulation')){
                $d = sizeof($_POST['for_caculation']) - 1;
                for ($r = 0; $r <= $d; $r++) {
                    if (isset($_POST['for_caculation'][$r])) {
                        $formulation_items[] = array(
                            'for_width' => $_POST['for_width'][$r],
                            'for_height' => $_POST['for_height'][$r],
                            'for_square' => $_POST['for_square'][$r],
                            'for_qty' => $_POST['for_qty'][$r],
                            'for_field' => $_POST['for_field'][$r],
                            'for_unit_id' => $this->input->post('unit'),
                            'for_caculation' => $_POST['for_caculation'][$r],
                            'for_operation' => $_POST['for_operation'][$r],
                        );
                    } 
                }
            }*/
            if (!isset($formulation_items)) {
                $formulation_items = NULL;
            }
            if (!isset($bom_items)) {
                $bom_items = NULL;
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
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('product_image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('products/edit/' . $id);
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
                        admin_redirect('products/edit/' . $id);
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
            if (isset($_POST['addOn_item_code'])) {
                $c = sizeof($_POST['addOn_item_code']) - 1;
                for ($r = 0; $r <= $c; $r++) {
                    if (isset($_POST['addOn_item_code'][$r])) {
                        $addOn_items[] = [
                            'item_code'   => $_POST['addOn_item_code'][$r],
                            'price'       => $_POST['addOn_item_price'][$r],
                            'description' => $_POST['addOn_item_description'][$r]
                        ];
                    }
                }
            }
            if (!isset($addOn_items)) {
                $addOn_items = null;
            }
            $warehouse_racks = null;
            if ($this->input->post('type') == 'standard') {
                foreach ($warehouses as $warehouse) {
                    if (isset($_POST['wh_rack_' . $warehouse->id]) && !empty($_POST['wh_rack_' . $warehouse->id]) && $_POST['wh_rack_' . $warehouse->id] != '') {
                        $warehouse_racks[] = [
                            'warehouse_id' => $this->input->post('wh_rack_' . $warehouse->id),
                            'rack_id'      => ((isset($_POST['wh_product_rack_id_' . $warehouse->id]) && !empty($_POST['wh_product_rack_id_' . $warehouse->id])) ? $_POST['wh_product_rack_id_' . $warehouse->id] : null)
                        ];
                    }
                } 
            }
        }
        if ($this->form_validation->run() == true && $this->products_model->updateProduct($id, $data, $items, $warehouse_qty, $product_attributes, $photos, $update_variants, $addOn_items, $product_options, $unit_arr, $product_account, $bom_items, $formulation_items, $product_units, $warehouse_racks)) {
            $this->session->set_flashdata('message', lang('product_updated'));
            admin_redirect('products');
        } else {
            $this->data['error']               = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['currencies']          = $this->bpas->getAllCurrencies();
            $this->data['categories']          = $this->site->getAllCategories();
            $this->data['nest_categories']     = $this->site->getNestedByCategories();
            // $this->data['nest_categories']     = $this->site->getNestedCategories();
            $this->data['tax_rates']           = $this->site->getAllTaxRates();
            $this->data['brands']              = $this->site->getAllBrands();
            $this->data['stock_types']         = $this->site->getAllStockType();
            $this->data['base_units']          = $this->site->getAllBaseUnits();
            $this->data['product_units']       = $this->site->getUnitByProId($id) ? $this->site->getUnitByProId($id) : $this->site->getUnitByProId_($id,$product->unit);
            $this->data['warehouses']          = $warehouses;
            $this->data['warehouses_products'] = $warehouses_products;
            $this->data['product']             = $product;
            $this->data['protype']             = $type;
            $this->data['variants']            = $this->products_model->getAllVariants();
            $this->data['subunits']            = $this->site->getUnitsByBUID($product->unit);
            $this->data['product_variants']    = $this->products_model->getProductOptions($id);
            $this->data['combo_items']         = $product->type == 'combo' ? $this->products_model->getProductComboItems($product->id) : null;
            $this->data['bom_items']           = $product->type == 'bom' ? $this->products_model->getProductBomItems($product->id) : NULL;
            $this->data['billers']             = $this->Settings->module_concrete ? json_encode($this->site->getBillers()) : false;
            $this->data['addon_items']         = $id ? $this->products_model->getProductAddOnItems($product->id) : null;
            $this->data['product_options']     = $id ? $this->products_model->getProductOptionsWithWH($id) : null;
            $this->data['options']             = $this->products_model->getAllOptions();
            $this->data['option_product']      = $this->products_model->getOptionProduct($id);
            $this->data['chart_accounts']      = $this->accounts_model->getAllChartAccounts();
            $this->data['productAccount']      = $this->products_model->getProductAccByProductId($product->id);
            $this->data['product_racks']       = $this->site->getProductRacks();
            $this->data['wh_product_racks']    = $this->site->getWarehouseProductRacks($id);

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('edit_product')]];
            $meta = ['page_title' => lang('edit_product'), 'bc' => $bc];
            $this->page_construct('products/edit', $meta, $this->data);
        }
    }
    function getcost($currency_code){
        $cost = $this->input->get('cost');
        $currencies = $this->site->getCurrencyByCode($currency_code);
        echo $currencies->rate * $cost;
    }
    function getprice($currency_code){
        $price = $this->input->get('price');
        
        $currencies = $this->site->getCurrencyByCode($currency_code);
        $amount = $currencies->rate * $price;
        echo $amount;
    }
    function getother_cost($currency_code){
        $cost = $this->input->get('cost');
        $currencies = $this->site->getCurrencyByCode($currency_code);
        $amount =  $this->bpas->formatDecimal($cost/$currencies->rate);
        echo $amount;
    }
    function getother_price($currency_code){
        $price = $this->input->get('price');
        $currencies = $this->site->getCurrencyByCode($currency_code);
        $amount =  $this->bpas->formatDecimal($price/$currencies->rate);
        echo $amount;
    }
    /* ------------------------------------------------------- */

    public function add_asset($id = null,$type=null)
    {

        $this->bpas->checkPermissions();
        $this->load->helper('security');
        $warehouses = $this->site->getAllWarehouses();
        $this->form_validation->set_rules('category', lang('category'), 'required|is_natural_no_zero');
        if ($this->input->post('type') == 'standard') {
            $this->form_validation->set_rules('cost', lang('asset_cost'), 'required');
            $this->form_validation->set_rules('unit', lang('asset_unit'), 'required');
        }
        $this->form_validation->set_rules('code', lang('asset_code'), 'is_unique[products.code]|alpha_dash');
        if (SHOP) {
            $this->form_validation->set_rules('slug', lang('slug'), 'required|is_unique[products.slug]|alpha_dash');
        }
        $this->form_validation->set_rules('weight', lang('weight'), 'numeric');
        $this->form_validation->set_rules('useful_life', lang('useful_life'), 'numeric');
        
        $this->form_validation->set_rules('product_image', lang('asset_image'), 'xss_clean');
        $this->form_validation->set_rules('digital_file', lang('digital_file'), 'xss_clean');
        $this->form_validation->set_rules('userfile', lang('asset_gallery_images'), 'xss_clean');
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
                'type'              => $this->input->post('type'),
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
                'alert_quantity'    => ($this->input->post('alert_quantity') != null && $this->input->post('alert_quantity') != '') ? $this->input->post('alert_quantity') : null,
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
                'status'       => $this->input->post('status'),
                'asset'             => ($type == 'asset') ? $type : '',
                'useful_life'       => $this->input->post('useful_life'),
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
                    if (isset($_POST['wh_qty_' . $warehouse->id]) && !empty($_POST['wh_qty_' . $warehouse->id]) && $_POST['wh_qty_' . $warehouse->id] != '') {
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
                                'quantity'     => $_POST['attr_quantity'][$r],
                                'price'        => $_POST['attr_price'][$r],
                            ];
                            $pv_total_quantity += $_POST['attr_quantity'][$r];
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
                        admin_redirect('products/add_asset/0/asset');
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
                    admin_redirect('products/add_asset/0/asset');
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
                        admin_redirect('products/add_asset/0/asset');
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
                            'price'   => $_POST['addOn_item_price'][$r],
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

        if ($this->form_validation->run() == true && $this->products_model->addProduct($data, $items, $warehouse_qty, $product_attributes, $photos, $addOn_items)) {
            $this->session->set_flashdata('message', lang('asset_added'));
            admin_redirect('products/add_asset');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['type']                = $type;
            $this->data['categories']          = $this->site->getAllCategories();
            $this->data['tax_rates']           = $this->site->getAllTaxRates();
            $this->data['brands']              = $this->site->getAllBrands();
            $this->data['base_units']          = $this->site->getAllBaseUnits();
            $this->data['warehouses']          = $warehouses;
            $this->data['warehouses_products'] = $id ? $this->products_model->getAllWarehousesWithPQ($id) : null;
            $this->data['product']             = $id ? $this->products_model->getProductByID($id) : null;
            $this->data['variants']            = $this->products_model->getAllVariants();
            $this->data['combo_items']         = ($id && $this->data['product']->type == 'combo') ? $this->products_model->getProductComboItems($id) : null;
            $this->data['product_options']     = $id ? $this->products_model->getProductOptionsWithWH($id) : null;
            $this->data['addon_items']         = $id ? $this->products_model->getProductAddOnItems($id) : null;
            $bc                                = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products/assets'), 'page' => lang('Assets')], ['link' => '#', 'page' => lang('add_asset')]];
            $meta                              = ['page_title' => lang('add_asset'), 'bc' => $bc];
            $this->page_construct('products/add_asset', $meta, $this->data);
        }
    }

    public function add_issues($id = null,$type=null)
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
        if (SHOP) {
            $this->form_validation->set_rules('slug', lang('slug'), 'required|is_unique[products.slug]|alpha_dash');
        }
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
                'type'              => $this->input->post('type'),
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
                'alert_quantity'    => ($this->input->post('alert_quantity') != null && $this->input->post('alert_quantity') != '') ? $this->input->post('alert_quantity') : null,
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
                'asset'             => ($type == 'asset') ? $type : '',
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
                    // if ($this->input->post('wh_qty_' . $warehouse->id)) {
                    if (isset($_POST['wh_qty_' . $warehouse->id]) && !empty($_POST['wh_qty_' . $warehouse->id]) && $_POST['wh_qty_' . $warehouse->id] != '') {
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
                                'quantity'     => $_POST['attr_quantity'][$r],
                                'price'        => $_POST['attr_price'][$r],
                            ];
                            $pv_total_quantity += $_POST['attr_quantity'][$r];
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
                        admin_redirect('products/add_issues');
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
                    admin_redirect('products/add_issues');
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
                        admin_redirect('products/add_issues');
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
                            'price'   => $_POST['addOn_item_price'][$r],
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
        if ($this->form_validation->run() == true && $this->products_model->addProduct($data, $items, $warehouse_qty, $product_attributes, $photos, $addOn_items)) {
            $this->session->set_flashdata('message', lang('product_added'));
            admin_redirect('products/issues');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['type']                = $type;
            $this->data['categories']          = $this->site->getAllCategories();
            $this->data['tax_rates']           = $this->site->getAllTaxRates();
            $this->data['brands']              = $this->site->getAllBrands();
            $this->data['base_units']          = $this->site->getAllBaseUnits();
            $this->data['warehouses']          = $warehouses;
            $this->data['warehouses_products'] = $id ? $this->products_model->getAllWarehousesWithPQ($id) : null;
            $this->data['product']             = $id ? $this->products_model->getProductByID($id) : null;
            $this->data['variants']            = $this->products_model->getAllVariants();
            $this->data['combo_items']         = ($id && $this->data['product']->type == 'combo') ? $this->products_model->getProductComboItems($id) : null;
            $this->data['product_options']     = $id ? $this->products_model->getProductOptionsWithWH($id) : null;
            $this->data['addon_items']         = $id ? $this->products_model->getProductAddOnItems($id) : null;
            $bc                                = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('add_product')]];
            $meta                              = ['page_title' => lang('add_product'), 'bc' => $bc];
            $this->page_construct('products/add_issues', $meta, $this->data);
        }
    }

    public function add_adjustment($count_id = null)
    {
        $this->bpas->checkPermissions('adjustments', true);
        $this->form_validation->set_rules('warehouse', lang('warehouse'), 'required');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld($this->input->post('date'));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('qa');
            $project_id   = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project;
            $biller_id    = $this->input->post('biller');
            $warehouse_id = $this->input->post('warehouse');
            $warehouse    = $this->site->getWarehouseByID($warehouse_id);
            $note         = $this->bpas->clear_tags($this->input->post('note'));
            $stockmoves   = null;
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id      = $_POST['product_id'][$r];
                $type            = $_POST['type'][$r];
                $quantity        = $_POST['quantity'][$r];
                $item_quantity   = abs($quantity);
                $serial          = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : null;
                $variant         = isset($_POST['variant'][$r]) && !empty($_POST['variant'][$r]) ? $_POST['variant'][$r] : null;
                $item_expiry     = isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r]) && $_POST['expiry'][$r] != 'false' && $_POST['expiry'][$r] != 'undefined' && $_POST['expiry'][$r] != 'null' && $_POST['expiry'][$r] != 'NULL' && $_POST['expiry'][$r] != '00/00/0000' && $_POST['expiry'][$r] != '' ? $this->bpas->fsd($_POST['expiry'][$r]) : null; 
                $product_details = $this->site->getProductByID($product_id);
                $item_unit       = $product_details->unit;
                $unit            = $this->site->getProductUnit($product_id, $item_unit);
                $real_unit_cost  = $this->site->getAVGCost($product_id, $date);
                if ($this->Settings->accounting_method == '0' && $type == 'subtraction') {
                    $costs = $this->site->getFifoCost($product_id, $item_quantity, $stockmoves);
                } else if ($this->Settings->accounting_method == '1' && $type == 'subtraction') {
                    $costs = $this->site->getLifoCost($product_id, $item_quantity, $stockmoves);
                } else if ($this->Settings->accounting_method == '3' && $type == 'subtraction') {
                    $costs = $this->site->getProductMethod($product_id, $item_quantity, $stockmoves);
                } else {
                    $costs = false;
                }
                $products[] = [
                    'product_id'   => $product_id,
                    'type'         => $type,
                    'quantity'     => $quantity,
                    'warehouse_id' => $warehouse_id,
                    'option_id'    => $variant,
                    'serial_no'    => $serial,
                    'expiry'       => $item_expiry,
                ];
                if ($type == 'subtraction') { 
                    $item_quantity = $item_quantity * (-1);
                }
                if ($costs) {
                    $item_cost_qty   = 0;
                    $item_cost_total = 0;
                    $productAcc = $this->site->getProductAccByProductId($product_details->id);
                    foreach ($costs as $cost_item) {
                        $item_cost_qty   += $cost_item['quantity'];
                        $item_cost_total += $cost_item['cost'] * $cost_item['quantity'];
                        $stockmoves[] = array(
                            'transaction'    => 'QuantityAdjustment',
                            'product_id'     => $product_id,
                            'product_type'   => $product_details->type,
                            'product_code'   => $product_details->code,
                            'product_name'   => $product_details->name,
                            'option_id'      => $variant,
                            'quantity'       => $cost_item['quantity'] * (-1),
                            'unit_quantity'  => $unit->unit_qty,
                            'unit_code'      => $unit->code,
                            'expiry'         => $item_expiry,
                            'unit_id'        => $item_unit,
                            'warehouse_id'   => $warehouse_id,
                            'date'           => $date,
                            'real_unit_cost' => $cost_item['cost'],
                            'serial_no'      => $serial,
                            'reference_no'   => $reference_no,
                            'user_id'        => $this->session->userdata('user_id'),
                        );
                        if ($this->Settings->module_account == 1) { 
                            $inventory_acc = $this->accounting_setting->default_stock;
                            $costing_acc   = $this->accounting_setting->default_cost;
                            $accTrans[] = array(
                                'tran_type'    => 'adjustment',
                                'tran_date'    => $date,
                                'reference_no' => $reference_no,
                                'account_code' => $inventory_acc,
                                'amount'       => -($cost_item['cost'] * abs($cost_item['quantity'])),
                                'narrative'    => $this->site->getAccountName($inventory_acc),
                                'description'  => $note,
                                'biller_id'    => $biller_id,
                                'project_id'   => $project_id,
                                'created_by'   => $this->session->userdata('user_id'),
                            );
                            $accTrans[] = array(
                                'tran_type'    => 'adjustment',
                                'tran_date'    => $date,
                                'reference_no' => $reference_no,
                                'account_code' => $costing_acc,
                                'amount'       => ($cost_item['cost'] * abs($cost_item['quantity'])),
                                'narrative'    => $this->site->getAccountName($costing_acc),
                                'description'  => $note,
                                'biller_id'    => $biller_id,
                                'project_id'   => $project_id,
                                'created_by'   => $this->session->userdata('user_id'),
                            );
                        }
                    }
                    $real_unit_cost += ($item_cost_total / $item_cost_qty);
                } else {
                    $reactive = 1;
                    $stockmoves[] = array(
                        'transaction'    => 'QuantityAdjustment',
                        'product_id'     => $product_id,
                        'product_type'   => $product_details->type,
                        'product_code'   => $product_details->code,
                        'product_name'   => $product_details->name,
                        'option_id'      => $variant,
                        'quantity'       => $item_quantity,
                        'unit_quantity'  => $unit->unit_qty,
                        'unit_code'      => $unit->code,
                        'unit_id'        => $item_unit,
                        'warehouse_id'   => $warehouse_id,
                        'date'           => $date,
                        'expiry'         => $item_expiry,
                        'real_unit_cost' => $product_details->cost,
                        'serial_no'      => $serial,
                        'reference_no'   => $reference_no,
                        'user_id'        => $this->session->userdata('user_id'),
                        'reactive'       => $reactive,
                    );  
                    if ($this->Settings->module_account == 1) {       
                        $productAcc = $this->site->getProductAccByProductId($product_details->id);
                        $inventory_acc = $this->accounting_setting->default_stock;
                        $costing_acc   = $this->accounting_setting->default_cost;
                        $accTrans[] = array(
                            'tran_type'    => 'adjustment',
                            'tran_date'    => $date,
                            'reference_no' => $reference_no,
                            'account_code' => $inventory_acc,
                            'amount'       => ($product_details->cost * $item_quantity),
                            'narrative'    => $this->site->getAccountName($inventory_acc),
                            'description'  => $note,
                            'biller_id'    => $biller_id,
                            'project_id'   => $project_id,
                            'created_by'   => $this->session->userdata('user_id'),
                        );
                        $accTrans[] = array(
                            'tran_type'    => 'adjustment',
                            'tran_date'    => $date,
                            'reference_no' => $reference_no,
                            'account_code' => $costing_acc,
                            'amount'       => ($product_details->cost * $item_quantity) * (-1),
                            'narrative'    => $this->site->getAccountName($costing_acc),
                            'description'  => $note,
                            'biller_id'    => $biller_id,
                            'project_id'   => $project_id,
                            'created_by'   => $this->session->userdata('user_id'),
                        );
                    }
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('products'), 'required');
            } else {
                krsort($products);
            }
            $data = [
                'date'         => $date,
                'reference_no' => $reference_no,
                'warehouse_id' => $warehouse_id,
                'note'         => $note,
                'created_by'   => $this->session->userdata('user_id'),
                'biller_id'    => $this->input->post('biller'),
                'count_id'     => $this->input->post('count_id') ? $this->input->post('count_id') : null,
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
        } elseif ($this->input->post('add_adjustment')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('customers');
        }
        if ($this->form_validation->run() == true && $this->products_model->addAdjustment($data, $products, $stockmoves, ((isset($accTrans) ? $accTrans : null)))) {
            $this->session->set_userdata('remove_qals', 1);
            $this->session->set_flashdata('message', lang('quantity_adjusted'));
            admin_redirect('products/quantity_adjustments');
        } else {
            if ($this->Settings->auto_count) {
                $variable = isset($_POST['val']) ? $_POST['val'] : 0;
                if ($count_id) {
                    $stock_count = $this->products_model->getStouckCountByID($count_id);
                    if ($variable) {
                        foreach ($variable as $value) {
                            $items = $this->products_model->getStockProductCountItems($count_id, $value);
                            foreach ($items as $item) {
                                $c = sha1(uniqid(mt_rand(), true));
                                if ($item->counted != $item->expected) {
                                    $product     = $this->site->getProductByID($item->product_id);
                                    $row         = json_decode('{}');
                                    $row->id     = $item->product_id;
                                    $row->code   = $product->code;
                                    $row->name   = $product->name;
                                    $row->qty    = $item->counted - $item->expected;
                                    $row->type   = $row->qty > 0 ? 'addition' : 'subtraction';
                                    $row->qty    = $row->qty > 0 ? $row->qty : (0 - $row->qty);
                                    $options     = $this->products_model->getProductOptions($product->id);
                                    $row->option = $item->product_variant_id ? $item->product_variant_id : 0;
                                    $row->serial = '';
                                    $row->qoh    = $this->bpas->convertQty($row->id, $product->quantity);
                                    $ri          = $this->Settings->item_addition ? $product->id : $c;
                                    $pr[$ri] = [
                                        'id'  => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                                        'row' => $row, 'options' => $options, 
                                    ];
                                    $c++;
                                }
                            }
                        }
                    } else {
                        $pr =[];
                    }
                }
            } else {
                if ($count_id) {
                    $stock_count = $this->products_model->getStouckCountByID($count_id);
                    if (!empty($_POST['val'])) {
                        foreach ($_POST['val'] as $id) {
                            $items = $this->products_model->getStockCountSomeItems($count_id, $id);
                            foreach ($items as $item) {
                                $c = sha1(uniqid(mt_rand(), true));
                                if ($item->counted != $item->expected) {
                                    $product                = $this->site->getProductByID($item->product_id);
                                    $row                    = json_decode('{}');
                                    $option                 = false;
                                    $row->id                = $item->product_id;
                                    $row->base_unit         = $product->unit;
                                    $row->base_unit_cost    = $product->cost;
                                    $row->unit              =  $product->unit;
                                    $row->unit_name         = $this->site->getUnitByID($product->unit)->name;
                                    $row->discount          = '0';
                                    $row->expiry            = $item->expiry;
                                    $row->code              = $product->code;
                                    $row->name              = $product->name;
                                    $row->qty               = $item->counted - $item->expected;
                                    $row->type              = $row->qty > 0 ? 'addition' : 'subtraction';
                                    $row->qty               = $row->qty > 0 ? $row->qty : (0 - $row->qty);
                                    $options                = $this->products_model->getProductOptions($product->id);
                                    $row->option            = $item->product_variant_id ? $item->product_variant_id : 0;
                                    $row->serial            = '';
                                    $row->qoh               = $this->bpas->convertQty($row->id, $product->quantity);
                                    $ri                     = $this->Settings->item_addition ? $product->id : $c;
                                    $pr[$ri] = [
                                        'id'  => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                                        'row' => $row, 'options' => $options, 'expiry' => $item->expiry,
                                    ];
                                    $c++;
                                }
                            }
                        }
                    } else {
                        $this->session->set_flashdata('error', $this->lang->line('no_record_selected'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                }
            }
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['error']            = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->data['sectionacc']       = $this->accounts_model->getAllChartAccount();
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->data['adjustment_items'] = $count_id ? json_encode($pr) : false;
            $this->data['warehouse_id']     = $count_id ? $stock_count->warehouse_id : false;
            $this->data['count_id']         = $count_id;
            $this->data['warehouses']       = $this->site->getAllWarehouses();
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('add_adjustment')]];
            $meta = ['page_title' => lang('add_adjustment'), 'bc' => $bc];
            $this->page_construct('products/add_adjustment', $meta, $this->data);
        }
    }
    public function edit_adjustment($id)
    {
        $this->bpas->checkPermissions('adjustments', true);
        $adjustment = $this->products_model->getAdjustmentByID($id);
        if (!$id || !$adjustment) {
            $this->session->set_flashdata('error', lang('adjustment_not_found'));
            $this->bpas->md();
        }
        $this->form_validation->set_rules('warehouse', lang('warehouse'), 'required');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld($this->input->post('date'));
            } else {
                $date = $adjustment->date;
            }
            $reference_no = $this->input->post('reference_no');
            $project_id   = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project;
            $biller_id    = $this->input->post('biller');
            $warehouse_id = $this->input->post('warehouse');
            $warehouse    = $this->site->getWarehouseByID($warehouse_id);
            $note         = $this->bpas->clear_tags($this->input->post('note'));
            $stockmoves   = null;
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id      = $_POST['product_id'][$r];
                $type            = $_POST['type'][$r];
                $quantity        = $_POST['quantity'][$r];
                $item_quantity   = abs($quantity);
                $serial          = isset($_POST['serial'][$r]) && !empty($_POST['serial'][$r]) && $_POST['serial'][$r] != 'undefined' && $_POST['serial'][$r] != 'false' && $_POST['serial'][$r] != 'null' && $_POST['serial'][$r] != 'NULL' ? $_POST['serial'][$r] : null;
                $variant         = isset($_POST['variant'][$r]) && !empty($_POST['variant'][$r]) ? $_POST['variant'][$r] : null;
                $item_expiry     = isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r]) && $_POST['expiry'][$r] != 'false' && $_POST['expiry'][$r] != 'undefined' && $_POST['expiry'][$r] != 'null' ? $this->bpas->fsd($_POST['expiry'][$r]) : null; 
                $product_details = $this->site->getProductByID($product_id);
                $item_unit       = $product_details->unit;
                $unit            = $this->site->getProductUnit($product_id, $item_unit);
                $real_unit_cost  = $this->site->getAVGCost($product_id, $date, "QuantityAdjustment", $id);
                if ($this->Settings->accounting_method == '0' && $type == 'subtraction') {
                    $costs = $this->site->getFifoCost($product_id, $item_quantity, $stockmoves, 'QuantityAdjustment', $id);
                } else if ($this->Settings->accounting_method == '1' && $type == 'subtraction') {
                    $costs = $this->site->getLifoCost($product_id, $item_quantity, $stockmoves, 'QuantityAdjustment', $id);;
                } else if ($this->Settings->accounting_method == '3' && $type == 'subtraction') {
                    $costs = $this->site->getProductMethod($product_id, $item_quantity, $stockmoves, 'QuantityAdjustment', $id);
                } else {
                    $costs = false;
                }
                if ($type == 'subtraction') {
                    $item_quantity = $item_quantity * (-1);
                }
                if ($costs) {
                    $item_cost_qty   = 0;
                    $item_cost_total = 0;
                    $productAcc = $this->site->getProductAccByProductId($product_details->id);
                    foreach ($costs as $item_cost) {
                        $item_cost_qty   += $item_cost['quantity'];
                        $item_cost_total += $item_cost['cost'] * $item_cost['quantity'];
                        $stockmoves[] = array(
                            'transaction_id' => $id,
                            'transaction'    => 'QuantityAdjustment',
                            'product_id'     => $product_id,
                            'product_type'   => $product_details->type,
                            'product_code'   => $product_details->code,
                            'product_name'   => $product_details->name,
                            'option_id'      => $variant,
                            'quantity'       => $item_cost['quantity'] * (-1),
                            'unit_quantity'  => $unit->unit_qty,
                            'unit_code'      => $unit->code,
                            'expiry'         => $item_expiry,
                            'unit_id'        => $item_unit,
                            'warehouse_id'   => $warehouse_id,
                            'date'           => $date,
                            'real_unit_cost' => $item_cost['cost'],
                            'serial_no'      => $serial,
                            'reference_no'   => $reference_no,
                            'user_id'        => $this->session->userdata('user_id'),
                        );
                        if ($this->Settings->module_account == 1) {   
                            $inventory_acc = $this->accounting_setting->default_stock;
                            $costing_acc   = $this->accounting_setting->default_cost;    
                            $accTrans[] = array(
                                'tran_type'    => 'adjustment',
                                'tran_no'      => $id,
                                'tran_date'    => $date,
                                'reference_no' => $reference_no,
                                'account_code' => $inventory_acc,
                                'amount'       => -($item_cost['cost'] * abs($item_cost['quantity'])),
                                'narrative'    => $this->site->getAccountName($inventory_acc),
                                'description'  => $note,
                                'biller_id'    => $biller_id,
                                'project_id'   => $project_id,
                                'created_by'   => $this->session->userdata('user_id'),
                            );
                            $accTrans[] = array(
                                'tran_type'    => 'adjustment',
                                'tran_no'      => $id,
                                'tran_date'    => $date,
                                'reference_no' => $reference_no,
                                'account_code' => $costing_acc,
                                'amount'       => ($item_cost['cost'] * abs($item_cost['quantity'])),
                                'narrative'    => $this->site->getAccountName($costing_acc),
                                'description'  => $note,
                                'biller_id'    => $biller_id,
                                'project_id'   => $project_id,
                                'created_by'   => $this->session->userdata('user_id'),
                            );
                        }
                    }
                    $real_unit_cost += ($item_cost_total / $item_cost_qty);
                } else {
                    $reactive = 1;
                    $stockmoves[] = array(
                        'transaction_id' => $id,
                        'transaction'    => 'QuantityAdjustment',
                        'product_id'     => $product_id,
                        'product_type'   => $product_details->type,
                        'product_code'   => $product_details->code,
                        'product_name'   => $product_details->name,
                        'option_id'      => $variant,
                        'quantity'       => $item_quantity,
                        'unit_quantity'  => $unit->unit_qty,
                        'unit_code'      => $unit->code,
                        'unit_id'        => $item_unit,
                        'warehouse_id'   => $warehouse_id,
                        'date'           => $date,
                        'expiry'         => $item_expiry,
                        'real_unit_cost' => $real_unit_cost,
                        'serial_no'      => $serial,
                        'reference_no'   => $reference_no,
                        'user_id'        => $this->session->userdata('user_id'),
                        'reactive'       => $reactive,
                    );      
                    if ($this->Settings->module_account == 1) {       
                        $productAcc = $this->site->getProductAccByProductId($product_details->id);
                        $inventory_acc = $this->accounting_setting->default_stock;
                        $costing_acc   = $this->accounting_setting->default_cost;
                        $accTrans[] = array(
                            'tran_type'    => 'adjustment',
                            'tran_no'      => $id,
                            'tran_date'    => $date,
                            'reference_no' => $reference_no,
                            'account_code' => $inventory_acc,
                            'amount'       => ($real_unit_cost * $item_quantity),
                            'narrative'    => $this->site->getAccountName($inventory_acc),
                            'description'  => $note,
                            'biller_id'    => $biller_id,
                            'project_id'   => $project_id,
                            'created_by'   => $this->session->userdata('user_id'),
                        );
                        $accTrans[] = array(
                            'tran_type'    => 'adjustment',
                            'tran_no'      => $id,
                            'tran_date'    => $date,
                            'reference_no' => $reference_no,
                            'account_code' => $costing_acc,
                            'amount'       => ($real_unit_cost * $item_quantity) * (-1),
                            'narrative'    => $this->site->getAccountName($costing_acc),
                            'description'  => $note,
                            'biller_id'    => $biller_id,
                            'project_id'   => $project_id,
                            'created_by'   => $this->session->userdata('user_id'),
                        );
                    }
                }
                $products[] = [
                    'adjustment_id'=> $id,
                    'product_id'   => $product_id,
                    'type'         => $type,
                    'quantity'     => $quantity,
                    'warehouse_id' => $warehouse_id,
                    'option_id'    => $variant,
                    'serial_no'    => $serial,
                    'expiry'       => $item_expiry,
                ];
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('products'), 'required');
            } else {
                krsort($products);
            }
            $data = [
                'date'         => $date,
                'reference_no' => $reference_no,
                'warehouse_id' => $warehouse_id,
                'note'         => $note,
                'created_by'   => $this->session->userdata('user_id'),
                'biller_id'    => $this->input->post('biller'),
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
        if ($this->form_validation->run() == true && $this->products_model->updateAdjustment($id, $data, $products, $stockmoves, $accTrans)) {
            $this->session->set_userdata('remove_qals', 1);
            $this->session->set_flashdata('message', lang('quantity_adjusted'));
            admin_redirect('products/quantity_adjustments');
        } else {
            $inv_items = $this->products_model->getAdjustmentItems($id);
            foreach ($inv_items as $item) {
                $c                  = sha1(uniqid(mt_rand(), true));
                $product            = $this->site->getProductByID($item->product_id);
                $row                = json_decode('{}');
                $row->id            = $item->product_id;
                $row->code          = $product->code;
                $row->name          = $product->name;
                $row->type          = $item->type;
                $row->unit          = $product->unit;
                $row->unit_name     = $this->site->getUnitByID($product->unit)->name;
                $row->product_type  = $product->type;
                $row->qty           = $item->quantity;
                $row->quantity      = 0;
                $row->base_quantity = $item->quantity;
                $expiry             = (($item->expiry && $item->expiry != '0000-00-00') ? $item->expiry : '');
                $row->qoh           = $this->bpas->convertQty($row->id, $product->quantity);   
                $row->expiry        = $expiry;
                $row->serial        = $item->serial_no ? $item->serial_no : '';
                $options            = $this->site->getProductOptions($product->id, $item->warehouse_id, false);
                $row->option        = $item->option_id ? $item->option_id : 0;
                $pis = $this->site->getStockMovement_ProductBalanceQuantity($item->product_id, $item->warehouse_id, $item->option_id);
                if ($pis) {
                    $row->quantity += $pis->quantity_balance;
                }
                $row->quantity += $item->quantity;
                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        if ($pis) {
                            $option_quantity = $pis->quantity_balance;
                        }
                        if ($option->id == $item->option_id) {
                            $option->quantity += $item->quantity;
                        }
                    }
                }                
                $stock_items = $this->site->getStockMovementByProductID($item->product_id, $item->warehouse_id, $item->option_id);
                $tax_rate    = $this->site->getTaxRateByID($product->tax_rate);
                $units       = $this->site->getUnitsByBUID($product->unit);
                $ri          = $this->Settings->item_addition ? $product->id : $c;
                $pr[$ri] = [
                    'id'  => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row' => $row, 'units' => $units, 'options' => $options, 'pitems' => $stock_items, 'expirys' => $stock_items, 'expiry' => $expiry ];
                $c++;
            }
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->data['sectionacc']       = $this->accounts_model->getAllChartAccount();
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->data['adjustment']       = $adjustment;
            $this->data['adjustment_items'] = json_encode($pr);
            $this->data['error']            = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses']       = $this->site->getAllWarehouses();
            $bc                             = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('edit_adjustment')]];
            $meta                           = ['page_title' => lang('edit_adjustment'), 'bc' => $bc];
            $this->page_construct('products/edit_adjustment', $meta, $this->data);
        }
    }
    public function add_adjustment_by_excel()
    {
        $this->bpas->checkPermissions('adjustments', true);
        $this->form_validation->set_rules('warehouse', lang('warehouse'), 'required');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld($this->input->post('date'));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('qa');
            $warehouse_id = $this->input->post('warehouse');
            $note         = $this->bpas->clear_tags($this->input->post('note'));
            $products     = [];
            $data         = [
                'date'         => $date,
                'reference_no' => $reference_no,
                'warehouse_id' => $warehouse_id,
                'note'         => $note,
                'created_by'   => $this->session->userdata('user_id'),
                'count_id'     => null,
                'biller_id'    => $this->input->post('biller'),
            ];
            if (isset($_FILES["userfile"]["name"])) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = ['csv', 'xls', 'xlsx'];
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $path   = $_FILES["userfile"]["tmp_name"];
                $object = PHPExcel_IOFactory::load($path);
                if(!$object){
                    $error = $this->excel->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect("products/add_adjustment_by_excel");
                }
                $data['attachment'] = $this->upload->file_name;
                $warehouse = $this->site->getWarehouseByID($warehouse_id);
                $new_arr   = null;
                foreach($object->getWorksheetIterator() as $worksheet)  {
                    $HighestRow    = $worksheet->getHighestRow();
                    $HighestColumn = $worksheet->getHighestColumn();
                    $rw = 2;
                    for($row=2; $row<=$HighestRow; $row++) {
                        $xls_product_code = trim($worksheet->getCellByColumnAndRow(0, $row)->getValue());
                        $xls_quantity     = trim($worksheet->getCellByColumnAndRow(1, $row)->getValue());
                        $xls_variant      = trim($worksheet->getCellByColumnAndRow(2, $row)->getValue());
                        $xls_expiry       = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                        $xls_expiry       = isset($xls_expiry) ? PHPExcel_Shared_Date::ExcelToPHP($xls_expiry) : null;
                        if ($product = $this->products_model->getProductByCode($xls_product_code)) {
                            $variant      = !empty($xls_variant) ? $this->products_model->getProductVariantID($product->id, $xls_variant) : false;
                            $type         = $xls_quantity > 0 ? 'addition' : 'subtraction';
                            $quantity     = $xls_quantity > 0 ? $xls_quantity : (0 - $xls_quantity);
                            $expiry       = ((isset($xls_expiry) && !empty($xls_expiry) && $xls_expiry != '') ? date('Y-m-d', $xls_expiry) : null);
                            if ((!$this->Settings->overselling || ($this->Settings->overselling && !$warehouse->overselling)) && $type == 'subtraction') {
                                if ($variant) {
                                    if ($op_wh_qty = $this->products_model->getProductWarehouseOptionQty($variant, $warehouse_id)) {
                                        if ($op_wh_qty->quantity < $quantity) {
                                            $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage') . ' - ' . lang('line_no') . ' ' . $rw);
                                            redirect($_SERVER['HTTP_REFERER']);
                                        }
                                    } else {
                                        $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage') . ' - ' . lang('line_no') . ' ' . $rw);
                                        redirect($_SERVER['HTTP_REFERER']);
                                    }
                                }
                                if ($wh_qty = $this->products_model->getProductQuantity($product->id, $warehouse_id)) {
                                    if ($wh_qty['quantity'] < $quantity) {
                                        $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage') . ' - ' . lang('line_no') . ' ' . $rw);
                                        redirect($_SERVER['HTTP_REFERER']);
                                    }
                                } else {
                                    $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage') . ' - ' . lang('line_no') . ' ' . $rw);
                                    redirect($_SERVER['HTTP_REFERER']);
                                }
                            }
                            if ($expiry) {
                                if ($this->products_model->check_valid_expiry($product->id, $expiry)) {
                                    if ($type == 'subtraction') {
                                        $key = $product->id . '_' . ($expiry ? $expiry : 'no');
                                        if (isset($new_arr[$key])) {
                                            $qty += $quantity;
                                        } else {
                                            $qty = $quantity;
                                        }
                                        $new_arr[$key] = [
                                            'product_id'   => $product->id,
                                            'product_code' => $product->code,
                                            'quantity'     => $qty,
                                            'expiry'       => $expiry,
                                            'type'         => $type
                                        ];
                                    }
                                } else {
                                    $this->session->set_flashdata('error', lang('check_product_expiry') . ' (' . date('m/d/Y', strtotime($expiry)) . '). ' . lang('product_expiry_x_exist') . ' ' . lang('line_no') . ' ' . $rw);
                                    redirect($_SERVER['HTTP_REFERER']);        
                                }
                            }
                            $products[] = [
                                'product_id'   => $product->id,
                                'type'         => $type,
                                'quantity'     => $quantity,
                                'warehouse_id' => $warehouse_id,
                                'option_id'    => $variant,
                                'expiry'       => $expiry
                            ];
                            $stock_movement[] = array(
                                'date'          => $date,
                                'transaction'   => 'adjustment',
                                'reference_no'  => $reference_no,
                                'product_id'    => $product->id,
                                'option_id'     => $variant,
                                'quantity'      => (($type == 'subtraction')?'-':'').$quantity,
                                'unit_quantity' => (($type == 'subtraction')?'-':'').$quantity,
                                'warehouse_id'  => $warehouse_id,
                                //'serial_no'     => $serial,
                                'expiry'       => $expiry,
                                'user_id'       => $this->session->userdata('user_id'),
                            );

                        } else {
                            $this->session->set_flashdata('error', lang('check_product_code') . ' (' . $xls_product_code . '). ' . lang('product_code_x_exist') . ' ' . lang('line_no') . ' ' . $rw);
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                        $rw++;
                    }
                }
                if (!empty($new_arr)) {
                    if ((!$this->Settings->overselling || ($this->Settings->overselling && !$warehouse->overselling)) && !empty($new_arr)) {
                        foreach ($new_arr as $item) {
                            if ($product_qty = $this->products_model->get_stock_expiry($item['product_id'], $warehouse_id, $item['expiry'])) {
                                if ($product_qty->quantity_balance < $item['quantity']) {
                                    $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage') . ' - Product code ' . $item['product_code'] . ' (' . date('m/d/Y', strtotime($item['product_code'])) . ')');
                                    redirect($_SERVER['HTTP_REFERER']);   
                                }
                            }
                        }
                    }
                }
            } else {
                $this->form_validation->set_rules('userfile', lang('upload_file'), 'required');
            }
            // $this->bpas->print_arrays($data, $products);
        }
        if ($this->form_validation->run() == true && $this->products_model->addAdjustment($data, $products,$stock_movement)) {
            $this->session->set_flashdata('message', lang('quantity_adjusted'));
            admin_redirect('products/quantity_adjustments');
        } else {
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->data['sectionacc']       = $this->accounts_model->getAllChartAccount();
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->data['warehouses']       = $this->site->getAllWarehouses();
            $this->data['error']            = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $bc                             = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('add_adjustment')]];
            $meta                           = ['page_title' => lang('add_adjustment_by_excel'), 'bc' => $bc];
            $this->page_construct('products/add_adjustment_by_excel', $meta, $this->data);
        }
    }

    public function add_adjustment_by_csv()
    {
        $this->bpas->checkPermissions('adjustments', true);
        $this->form_validation->set_rules('warehouse', lang('warehouse'), 'required');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld($this->input->post('date'));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('qa');
            $warehouse_id = $this->input->post('warehouse');
            $note         = $this->bpas->clear_tags($this->input->post('note'));
            $data         = [
                'date'         => $date,
                'reference_no' => $reference_no,
                'warehouse_id' => $warehouse_id,
                'note'         => $note,
                'created_by'   => $this->session->userdata('user_id'),
                'count_id'     => null,
                'biller_id'    => $this->input->post('biller'),
            ];
            if ($_FILES['csv_file']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('csv_file')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $csv                = $this->upload->file_name;
                $data['attachment'] = $csv;
                $arrResult = [];
                $handle    = fopen($this->digital_upload_path . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys   = ['code', 'quantity', 'variant'];
                $final  = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                // $this->bpas->print_arrays($final);
                $warehouse = $this->site->getWarehouseByID($warehouse_id);
                $rw = 2;
                foreach ($final as $pr) {
                    if ($product = $this->products_model->getProductByCode(trim($pr['code']))) {
                        $csv_variant  = trim($pr['variant']);
                        $variant      = !empty($csv_variant) ? $this->products_model->getProductVariantID($product->id, $csv_variant) : false;
                        $csv_quantity = trim($pr['quantity']);
                        $type         = $csv_quantity > 0 ? 'addition' : 'subtraction';
                        $quantity     = $csv_quantity > 0 ? $csv_quantity : (0 - $csv_quantity);
                        if ((!$this->Settings->overselling || ($this->Settings->overselling && !$warehouse->overselling)) && $type == 'subtraction') {
                            if ($variant) {
                                if ($op_wh_qty = $this->products_model->getProductWarehouseOptionQty($variant, $warehouse_id)) {
                                    if ($op_wh_qty->quantity < $quantity) {
                                        $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage') . ' - ' . lang('line_no') . ' ' . $rw);
                                        redirect($_SERVER['HTTP_REFERER']);
                                    }
                                } else {
                                    $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage') . ' - ' . lang('line_no') . ' ' . $rw);
                                    redirect($_SERVER['HTTP_REFERER']);
                                }
                            }
                            if ($wh_qty = $this->products_model->getProductQuantity($product->id, $warehouse_id)) {
                                if ($wh_qty['quantity'] < $quantity) {
                                    $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage') . ' - ' . lang('line_no') . ' ' . $rw);
                                    redirect($_SERVER['HTTP_REFERER']);
                                }
                            } else {
                                $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage') . ' - ' . lang('line_no') . ' ' . $rw);
                                redirect($_SERVER['HTTP_REFERER']);
                            }
                        }
                        $products[] = [
                            'product_id'   => $product->id,
                            'type'         => $type,
                            'quantity'     => $quantity,
                            'warehouse_id' => $warehouse_id,
                            'option_id'    => $variant,
                        ];
                    } else {
                        $this->session->set_flashdata('error', lang('check_product_code') . ' (' . $pr['code'] . '). ' . lang('product_code_x_exist') . ' ' . lang('line_no') . ' ' . $rw);
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                    $rw++;
                }
            } else {
                $this->form_validation->set_rules('csv_file', lang('upload_file'), 'required');
            }
            // $this->bpas->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->products_model->addAdjustment($data, $products)) {
            $this->session->set_flashdata('message', lang('quantity_adjusted'));
            admin_redirect('products/quantity_adjustments');
        } else {
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->data['sectionacc']       = $this->accounts_model->getAllChartAccount();
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->data['warehouses']       = $this->site->getAllWarehouses();
            $this->data['error']            = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $bc                             = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('add_adjustment')]];
            $meta                           = ['page_title' => lang('add_adjustment_by_csv'), 'bc' => $bc];
            $this->page_construct('products/add_adjustment_by_csv', $meta, $this->data);
        }
    }

    public function addByAjax()
    {
        if (!$this->mPermissions('add')) {
            exit(json_encode(['msg' => lang('access_denied')]));
        }
        if ($this->input->get('token') && $this->input->get('token') == $this->session->userdata('user_csrf') && $this->input->is_ajax_request()) {
            $product = $this->input->get('product');
            if (!isset($product['code']) || empty($product['code'])) {
                exit(json_encode(['msg' => lang('product_code_is_required')]));
            }
            if (!isset($product['name']) || empty($product['name'])) {
                exit(json_encode(['msg' => lang('product_name_is_required')]));
            }
            if (!isset($product['category_id']) || empty($product['category_id'])) {
                exit(json_encode(['msg' => lang('product_category_is_required')]));
            }
            if (!isset($product['unit']) || empty($product['unit'])) {
                exit(json_encode(['msg' => lang('product_unit_is_required')]));
            }
            if (!isset($product['price']) || empty($product['price'])) {
                exit(json_encode(['msg' => lang('product_price_is_required')]));
            }
            if (!isset($product['cost']) || empty($product['cost'])) {
                exit(json_encode(['msg' => lang('product_cost_is_required')]));
            }
            if ($this->products_model->getProductByCode($product['code'])) {
                exit(json_encode(['msg' => lang('product_code_already_exist')]));
            }
            if ($row = $this->products_model->addAjaxProduct($product)) {
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $pr       = ['id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'code' => $row->code, 'qty' => 1, 'cost' => $row->cost, 'name' => $row->name, 'tax_method' => $row->tax_method, 'tax_rate' => $tax_rate, 'discount' => '0'];
                $this->bpas->send_json(['msg' => 'success', 'result' => $pr]);
            } else {
                exit(json_encode(['msg' => lang('failed_to_add_product')]));
            }
        } else {
            json_encode(['msg' => 'Invalid token']);
        }
    }

    public function adjustment_actions()
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
                        $this->products_model->deleteAdjustment($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line('adjustment_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('quantity_adjustments');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('warehouse'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('created_by'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('items'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $adjustment = $this->products_model->getAdjustmentByID($id);
                        $created_by = $this->site->getUser($adjustment->created_by);
                        $warehouse  = $this->site->getWarehouseByID($adjustment->warehouse_id);
                        $items      = $this->products_model->getAdjustmentItems($id);
                        $products   = '';
                        if ($items) {
                            foreach ($items as $item) {
                                $products .= $item->product_name . '(' . $this->bpas->formatQuantity($item->type == 'subtraction' ? -$item->quantity : $item->quantity) . ')' . "\n";
                            }
                        }
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($adjustment->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $adjustment->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $warehouse->name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $created_by->first_name . ' ' . $created_by->last_name);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->decode_html($adjustment->note));
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $products);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'quantity_adjustments_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line('no_record_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function barcode($product_code = null, $bcs = 'code128', $height = 40)
    {
        if ($this->Settings->barcode_img) {
            header('Content-Type: image/png');
        } else {
            header('Content-type: image/svg+xml');
        }
        echo $this->bpas->barcode($product_code, $bcs, $height, true, false, true);
    }

    public function count_stock($page = null)
    {
        $this->bpas->checkPermissions('stock_count');
        $this->form_validation->set_rules('warehouse', lang('warehouse'), 'required');
        $this->form_validation->set_rules('type', lang('type'), 'required');

        if ($this->form_validation->run() == true) {
            $warehouse_id = $this->input->post('warehouse');
            $type         = $this->input->post('type');
            $categories   = $this->input->post('category') ? $this->input->post('category') : null;
            $brands       = $this->input->post('brand') ? $this->input->post('brand') : null;
            $this->load->helper('string');
            $name     = random_string('md5') . '.csv';
            $products = $this->products_model->getStockMovement_StockCountProducts($warehouse_id, $type, $categories, $brands);
            $pr       = 0;
            $rw       = 0;
            foreach ($products as $product) {
                if ($variants = $this->products_model->getStockCountProductVariants($warehouse_id, $product->id)) {
                    foreach ($variants as $variant) {
                        $items[] = [
                            'product_id'   => $product->id,
                            'product_code' => $product->code,
                            'product_name' => $product->name,
                            'variant'      => $variant->name,
                            'expected'     => $variant->quantity,
                            'counted'      => '',
                            'cost'         => $product->cost,
                            'expiry'       => $product->expiry,
                        ];
                        $csvs[] = [
                            'product_code' => $product->code,
                            'product_name' => $product->name,
                            'variant'      => $variant->name,
                            'expected'     => $variant->quantity,
                            'counted'      => '',
                            'expiry'       => $product->expiry,
                        ];
                        $rw++;
                    }
                } else {
                    $items[] = [
                        'product_id'    => $product->id,
                        'product_code'  => $product->code,
                        'product_name'  => $product->name,
                        'variant'       => '',
                        'expected'      => $product->quantity,
                        'counted'       => '',
                        'cost'          => $product->cost,
                        'expiry'        => $product->expiry,
                    ];
                    $csvs[] = [
                        'product_code' => $product->code,
                        'product_name' => $product->name,
                        'variant'      => '',
                        'expected'     => $product->quantity,
                        'counted'      => '',
                        'expiry'       => $product->expiry,
                    ];
                    $rw++;
                }
                $pr++;
            }
            if (!empty($csvs)) {
                $csv_file = fopen('./files/' . $name, 'w');
                fprintf($csv_file, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($csv_file, [lang('product_code'), lang('product_name'), lang('variant'), lang('expected'), lang('counted'), lang('expiry')]);
                foreach ($csvs as $csv) {
                    // unset($csv['product_id']);
                    // unset($csv['cost']);
                    fputcsv($csv_file, $csv);
                }
                // file_put_contents('./files/'.$name, $csv_file);
                // fwrite($csv_file, $txt);
                fclose($csv_file);
            } else {
                $this->session->set_flashdata('error', lang('no_product_found'));
                redirect($_SERVER['HTTP_REFERER']);
            }
            if ($this->Owner || $this->Admin) {
                $date = $this->bpas->fld($this->input->post('date'));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $category_ids   = '';
            $brand_ids      = '';
            $category_names = '';
            $brand_names    = '';
            if ($categories) {
                $r = 1;
                $s = sizeof($categories);
                foreach ($categories as $category_id) {
                    $category = $this->site->getCategoryByID($category_id);
                    if ($r == $s) {
                        $category_names .= $category->name;
                        $category_ids   .= $category->id;
                    } else {
                        $category_names .= $category->name . ', ';
                        $category_ids   .= $category->id . ', ';
                    }
                    $r++;
                }
            }
            if ($brands) {
                $r = 1;
                $s = sizeof($brands);
                foreach ($brands as $brand_id) {
                    $brand = $this->site->getBrandByID($brand_id);
                    if ($r == $s) {
                        $brand_names .= $brand->name;
                        $brand_ids   .= $brand->id;
                    } else {
                        $brand_names .= $brand->name . ', ';
                        $brand_ids   .= $brand->id . ', ';
                    }
                    $r++;
                }
            }
            $data = [
                'date'           => $date,
                'warehouse_id'   => $warehouse_id,
                'reference_no'   => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('stc'),
                'type'           => $type,
                'categories'     => $category_ids,
                'category_names' => $category_names,
                'brands'         => $brand_ids,
                'brand_names'    => $brand_names,
                'initial_file'   => $name,
                'products'       => $pr,
                'rows'           => $rw,
                'created_by'     => $this->session->userdata('user_id'),
            ];
        }
        if ($this->form_validation->run() == true && $this->products_model->addStockCount($data, $items)) {
            $this->session->set_flashdata('message', lang('stock_count_intiated'));
            admin_redirect('products/stock_counts');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['brands']     = $this->site->getAllBrands();
            $bc                       = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('count_stock')]];
            $meta                     = ['page_title' => lang('count_stock'), 'bc' => $bc];
            $this->page_construct('products/count_stock', $meta, $this->data);
        }
    }

    /* ------------------------------------------------------------------------------- */

    public function delete($id = null)
    {
        $this->bpas->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->products_model->deleteProduct($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('product_deleted')]);
            }
            $this->session->set_flashdata('message', lang('product_deleted'));
            admin_redirect('products');
        }
    }

    public function delete_asset($id = null)
    {
        $this->bpas->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->products_model->deleteProduct($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('product_deleted')]);
            }
            $this->session->set_flashdata('message', lang('product_deleted'));
            admin_redirect('products/assets');
        }
    }

    public function delete_adjustment($id = null)
    {
        $this->bpas->checkPermissions('delete', true);
        if ($this->products_model->deleteAdjustment($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('adjustment_deleted')]);
        }
    }

    public function delete_image($id = null)
    {
        $this->bpas->checkPermissions('edit', true);
        if ($id && $this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            $this->db->delete('product_photos', ['id' => $id]);
            $this->bpas->send_json(['error' => 0, 'msg' => lang('image_deleted')]);
        }
        $this->bpas->send_json(['error' => 1, 'msg' => lang('ajax_error')]);
    }

    /* -------------------------------------------------------- */

    public function update_stock_count_item($id)
    {
        $this->bpas->checkPermissions('stock_count');
        $stock_count = $this->products_model->getStouckCountByID($id);
        if (!$stock_count || $stock_count->finalized) {
            $this->session->set_flashdata('error', lang('stock_count_finalized'));
            admin_redirect('products/stock_counts');
        }

        if ($this->products_model->updateStockCount($id)) {
            $this->session->set_flashdata('message', lang('stock_count_finalized'));
            admin_redirect('products/stock_counts');
        } else {
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function finalize_count($id)
    {
        $this->bpas->checkPermissions('stock_count');
        $stock_count = $this->products_model->getStouckCountByID($id);
        if (!$stock_count || $stock_count->finalized) {
            $this->session->set_flashdata('error', lang('stock_count_finalized'));
            admin_redirect('products/stock_counts');
        } 
        $this->form_validation->set_rules('count_id', lang('count_stock'), 'required'); 
        if ($this->form_validation->run() == true) {
            if ($_FILES['csv_file']['size'] > 0) {
                $note = $this->bpas->clear_tags($this->input->post('note'));
                $data = [
                    'updated_by' => $this->session->userdata('user_id'),
                    'updated_at' => date('Y-m-d H:s:i'),
                    'note'       => $note,
                ]; 
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('csv_file')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                } 
                $csv = $this->upload->file_name; 
                $arrResult = [];
                $handle    = fopen($this->digital_upload_path . $csv, 'r');
          
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys   = ['product_code', 'product_name', 'product_variant', 'expected', 'counted','expiry'];
                $final  = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                } 
                $rw          = 2;
                $differences = 0;
                $matches     = 0;
                foreach ($final as $pr) { 
                    if ($product = $this->products_model->getProductByCode(trim($pr['product_code']))) {
                        $pr['counted'] = !empty($pr['counted']) ? $pr['counted'] :  (string)0;
                        if ($pr['expected'] == $pr['counted']) {
                            $matches++;
                        } else {
                            $pr['stock_count_id']     = $id;
                            $pr['product_id']         = $product->id;
                            $pr['cost']               = $product->cost;
                            $pr['product_variant_id'] = empty($pr['product_variant']) ? null : $this->products_model->getProductVariantID($pr['product_id'], $pr['product_variant']);
                            $pr['expiry']             = $pr['expiry'] != null ? date ("Y-m-d", strtotime($pr['expiry'])) : null ;
                            $products[]               = $pr;
                            $differences++;
                        }
                    } else {
                        $this->session->set_flashdata('error', lang('check_product_code') . ' (' . $pr['product_code'] . '). ' . lang('product_code_x_exist') . ' ' . lang('line_no') . ' ' . $rw);
                        admin_redirect('products/finalize_count/' . $id);
                    }
                    $rw++;
                }   
                $data['final_file']  = $csv;
                $data['differences'] = $differences;
                $data['matches']     = $matches;
                $data['missing']     = $stock_count->rows - ($rw - 2);
                $data['finalized']   = 1;
            } 
        } 
        if ($this->form_validation->run() == true && $this->products_model->finalizeStockCount($id, $data, $products)) {
            $this->session->set_flashdata('message', lang('stock_count_finalized'));
            admin_redirect('products/stock_counts');
        } else {
            $this->data['error']       = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['stock_count'] = $stock_count;
            $this->data['warehouse']   = $this->site->getWarehouseByID($stock_count->warehouse_id);
            $bc                        = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => admin_url('products/stock_counts'), 'page' => lang('stock_counts')], ['link' => '#', 'page' => lang('finalize_count')]];
            $meta                      = ['page_title' => lang('finalize_count'), 'bc' => $bc];
            $this->page_construct('products/finalize_count', $meta, $this->data);
        }
    }

    public function finalize_count__($id)
    {
        $this->bpas->checkPermissions('stock_count');
        $stock_count = $this->products_model->getStouckCountByID($id);
        if (!$stock_count || $stock_count->finalized) {
            $this->session->set_flashdata('error', lang('stock_count_finalized'));
            admin_redirect('products/stock_counts');
        }

        $this->form_validation->set_rules('count_id', lang('count_stock'), 'required');

        if ($this->form_validation->run() == true) {
            if ($_FILES['csv_file']['size'] > 0) {
                $note = $this->bpas->clear_tags($this->input->post('note'));
                $data = [
                    'updated_by' => $this->session->userdata('user_id'),
                    'updated_at' => date('Y-m-d H:s:i'),
                    'note'       => $note,
                ];

                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('csv_file')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }

                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle    = fopen($this->digital_upload_path . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys   = ['product_code', 'product_name', 'product_variant', 'expected', 'counted'];
                $final  = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                // $this->bpas->print_arrays($final);
                $rw          = 2;
                $differences = 0;
                $matches     = 0;
                foreach ($final as $pr) {
                    if ($product = $this->products_model->getProductByCode(trim($pr['product_code']))) {
                        $pr['counted'] = !empty($pr['counted']) ? $pr['counted'] : 0;
                        if ($pr['expected'] == $pr['counted']) {
                            $matches++;
                        } else {
                            $pr['stock_count_id']     = $id;
                            $pr['product_id']         = $product->id;
                            $pr['cost']               = $product->cost;
                            $pr['product_variant_id'] = empty($pr['product_variant']) ? null : $this->products_model->getProductVariantID($pr['product_id'], $pr['product_variant']);
                            $products[]               = $pr;
                            $differences++;
                        }
                    } else {
                        $this->session->set_flashdata('error', lang('check_product_code') . ' (' . $pr['product_code'] . '). ' . lang('product_code_x_exist') . ' ' . lang('line_no') . ' ' . $rw);
                        admin_redirect('products/finalize_count/' . $id);
                    }
                    $rw++;
                }

                $data['final_file']  = $csv;
                $data['differences'] = $differences;
                $data['matches']     = $matches;
                $data['missing']     = $stock_count->rows - ($rw - 2);
                $data['finalized']   = 1;
                $data['status']      = 1;
            }

            // $this->bpas->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->products_model->finalizeStockCount($id, $data, $products)) {
            $this->session->set_flashdata('message', lang('stock_count_finalized'));
            admin_redirect('products/stock_counts');
        } else {
            $this->data['error']       = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['stock_count'] = $stock_count;
            $this->data['warehouse']   = $this->site->getWarehouseByID($stock_count->warehouse_id);
            $bc                        = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => admin_url('products/stock_counts'), 'page' => lang('stock_counts')], ['link' => '#', 'page' => lang('finalize_count')]];
            $meta                      = ['page_title' => lang('finalize_count'), 'bc' => $bc];
            $this->page_construct('products/finalize_count', $meta, $this->data);
        }
    }

    public function get_suggestions()
    {
        $term = $this->input->get('term', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }

        $rows = $this->products_model->getProductsForPrinting($term);
        if ($rows) {
            foreach ($rows as $row) {
                $variants = $this->products_model->getProductOptions($row->id);
                $pr[]     = ['id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => 1, 'variants' => $variants];
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    public function product_count_suggestions($id)
    {
        $term = $this->input->get('term', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }
        $rows = $this->products_model->getProductsForCount($term, $id);
        if ($rows) {
            $r = 0;
            foreach ($rows as $row) {
                $variants = $this->products_model->getProductOptions($row->product_id);
                $c    = uniqid(mt_rand(), true);
                $pr[] = ['id' => sha1($c . $r), 'product_id' => $row->product_id, 'label' => $row->product_name . ' (' . $row->product_code . ')' . ($row->expiry != null ? ' (' . $row->expiry . ')' : ''), 'code' => $row->product_code, 'name' => $row->product_name, 'counted' => 1, 'qty' => 0, 'variants' => $variants, 'expected' => $row->expected, 'expiry' => $row->expiry];
                $r++;
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

   
    public function getCounts($warehouse_id = null)
    {
        $this->bpas->checkPermissions('stock_count', true);

        if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
            $user         = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $export_link = anchor('admin/products/export_counted/$1', '<label class="label label-primary pointer">' . lang('export') . '</label>', 'class="tip" title="' . lang('export') . '"');
        $count_link = anchor('admin/products/scan_count/$1', '<label class="label label-primary pointer">' . lang('count') . '</label>', 'class="tip" title="' . lang('count') . '"');
        $detail_link = anchor('admin/products/view_count/$1', '<label class="label label-primary pointer">' . lang('details') . '</label>', 'class="tip" title="' . lang('details') . '" data-toggle="modal" data-backdrop="static" data-target="#myModal"');

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('stock_counts')}.id as id, date, reference_no, {$this->db->dbprefix('warehouses')}.name as wh_name, type, status, brand_names, category_names, initial_file, final_file")
            ->from('stock_counts')
            ->join('warehouses', 'warehouses.id=stock_counts.warehouse_id', 'left');
        if ($warehouse_id) {
            $this->datatables->where("FIND_IN_SET(bpas_stock_counts.warehouse_id, '".$warehouse_id."')");
        }
        $this->datatables->add_column('Actions', '<div class="text-center">' . $export_link. $count_link.$detail_link . '</div>', 'id');
        echo $this->datatables->generate();
    }
    public function index($warehouse_id = null)
    {
        $this->bpas->checkPermissions();
        $count = explode(',', $this->session->userdata('warehouse_id'));

        $products   = $this->site->getAllProducts();
        $warehouses = $this->site->getAllWarehouses();
        $sync       = $this->input->get('sync') ? $this->input->get('sync') : null;
        if ($sync == 'sync_quantity_all') {
            foreach ($products as $product) {
                $this->site->syncQuantity_13_05_21($product->id);
            }
            $this->session->set_flashdata('message', $this->lang->line('products_quantity_sync'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id ? $warehouse_id : null;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            if (count($count)>1) {
                $this->data['warehouses']   = $this->site->getAllWarehouses();
            } else {
                $this->data['warehouses']   = null;
            }
            
            $this->data['count_warehouses'] = $count;
            $this->data['user_warehouse']   = (isset($count) && count($count) == 1) ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
            $this->data['warehouse_id']     = $warehouse_id;
            $this->data['warehouse']        = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        }
        $this->data['products']   = $this->site->getProducts();
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['nest_categories']     = $this->site->getNestedCategories();
        $this->data['supplier']   = $this->input->get('supplier') ? $this->site->getCompanyByID($this->input->get('supplier')) : null;
        $this->data['product_units'] = json_encode($this->products_model->getProductUnits());

        $bc                     = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('products')]];
        $meta                   = ['page_title' => lang('products'), 'bc' => $bc];
        $this->page_construct('products/index', $meta, $this->data);
    }
    function getProducts($warehouse_id = NULL)
    {
        $this->bpas->checkPermissions('index', TRUE);
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : null;
        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $product_type = $this->input->get('product_type') ? $this->input->get('product_type') :NULL;
        $start_date = $this->input->get('start_date')? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
            $user         = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : NULL;
        $detail_link = anchor('admin/products/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('product_details'));
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete_product") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete11' id='a__$1' href='" . admin_url('products/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_product') . "</a>";
        $single_barcode = anchor('admin/products/print_barcodes/$1/'.$warehouse_id, '<i class="fa fa-print"></i> ' . lang('print_barcode_label'));
        // $single_label = anchor_popup('products/single_label/$1/' . ($warehouse_id ? $warehouse_id : ''), '<i class="fa fa-print"></i> ' . lang('print_label'), $this->popup_attributes);
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li><a href="' . admin_url('products/add/$1') . '"><i class="fa fa-plus-square"></i> ' . lang('duplicate_product') . '</a></li>
            <li><a href="' . admin_url('products/edit/$1') . '"><i class="fa fa-edit"></i> ' . lang('edit_product') . '</a></li>';
        if ($warehouse_id) {
            $action .= '<li><a href="' . admin_url('products/set_rack/$1/' . $warehouse_id) . '" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa-bars"></i> '
                . lang('set_rack') . '</a></li>';
            if($this->Settings->product_serial == 1){   
                $action .= '<li><a href="' . admin_url('products/set_serials/$1/' . $warehouse_id.'') . '"><i class="fa fa-bars"></i> '
                . lang('set_serial') . '</a></li>';
            }
        }
        $action .= '<li><a href="' . site_url() . 'assets/uploads/$2" data-type="image" data-toggle="lightbox"><i class="fa fa-file-photo-o"></i> '
            . lang('view_image') . '</a></li>
            <li>' . $single_barcode . '</li>
            <li class="divider"></li>
            <li>' . $delete_link . '</li>
            </ul>
        </div></div>';
        
        $warehouse_query = '';
        if ($this->Settings->show_warehouse_qty) {
            $warehouses = $this->site->getWarehouses();
            if($warehouses){
                foreach($warehouses as $warehouse){
                    $warehouse_query .= 'CONCAT(IFNULL((IF('.$this->db->dbprefix("products").'.type="service" OR '.$this->db->dbprefix("products").'.type="bom","0",(SELECT IFNULL(quantity,0) as quantity from '.$this->db->dbprefix("warehouses_products").' WHERE '.$this->db->dbprefix("warehouses_products").'.product_id = '.$this->db->dbprefix("products").'.id and '.$this->db->dbprefix("warehouses_products").'.warehouse_id = "'.$warehouse->id.'" GROUP BY '.$this->db->dbprefix("warehouses_products").'.product_id))),0),"|",'.$this->db->dbprefix("products").'.id) as qty_'.$warehouse->id.',';
                }
            }
        }
        $allow_category = $this->site->getCategoryByProject();
        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
            ->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('products')}.type as product_type, {$this->db->dbprefix('categories')}.name as cname, {$this->db->dbprefix('units')}.name as unit, cost as cost, price as price,".$warehouse_query."  CONCAT(IF({$this->db->dbprefix('products')}.type='service' OR {$this->db->dbprefix('products')}.type='bom' OR {$this->db->dbprefix('products')}.type='combo', '0', COALESCE(wp.quantity, 0)),'|',".$this->db->dbprefix('products') . ".id) as quantity, wp.rack as rack, alert_quantity", FALSE)
            ->from('products');
            if ($this->Settings->display_all_products) {
                $this->datatables->join("( SELECT product_id, quantity, rack, warehouse_id from {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id = {$warehouse_id}) wp", 'products.id=wp.product_id', 'left');
            } else {
                $this->datatables->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
                ->where('wp.warehouse_id', $warehouse_id)
                ->where('wp.quantity !=', 0);
            }
            $this->datatables->join('categories', 'products.category_id=categories.id', 'left')
            ->join('units', 'products.unit=units.id', 'left')
            ->join('brands', 'products.brand=brands.id', 'left')
            ->where('products.type !=','problem')
            ->group_by("products.id,wp.warehouse_id");
    
        } else if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) { 
            $this->datatables
                ->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('products')}.type as product_type,{$this->db->dbprefix('categories')}.name as cname, {$this->db->dbprefix('units')}.name as unit,  cost as cost, price as price,".$warehouse_query."  CONCAT(IF({$this->db->dbprefix('products')}.type='service' OR {$this->db->dbprefix('products')}.type='bom' OR {$this->db->dbprefix('products')}.type='combo', '0', COALESCE(sum(".$this->db->dbprefix('warehouses_products').".quantity), 0)),'|',".$this->db->dbprefix('products') . ".id) as quantity, '' as rack, products.alert_quantity", FALSE)
                ->from('products')
                ->join('warehouses_products', 'warehouses_products.product_id = products.id', 'inner')
                ->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->where('products.type !=','problem')
                ->where_in('warehouses_products.warehouse_id',json_decode($this->session->userdata('warehouse_id')))
                ->group_by("products.id");
        } else {
            $this->datatables
                ->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('products')}.type as product_type,{$this->db->dbprefix('categories')}.name as cname, {$this->db->dbprefix('units')}.name as unit,  cost as cost, price as price,".$warehouse_query."  CONCAT(IF({$this->db->dbprefix('products')}.type='service' OR {$this->db->dbprefix('products')}.type='bom' OR {$this->db->dbprefix('products')}.type='combo', '0', COALESCE(quantity, 0)),'|',".$this->db->dbprefix('products') . ".id) as quantity, '' as rack, alert_quantity", FALSE)
                ->from('products')
                ->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->where('products.type !=','problem')
                ->group_by("products.id");
        }
        
        if($allow_category){
            $this->datatables->where_in("products.category_id",$allow_category);
        }
        
        if (!$this->Owner && !$this->Admin) {
            if (!$this->session->userdata('show_cost')) {
                $this->datatables->unset_column("cost");
            }
            if (!$this->session->userdata('show_price')) {
                $this->datatables->unset_column("price");
            }
        }
         if ($product) {
            $this->datatables->where($this->db->dbprefix('products') . ".id", $product);
        }
        if ($category) {
            $this->datatables->where($this->db->dbprefix('products') . ".category_id", $category);
        }
        if ($supplier) {
            $this->datatables->where('supplier1', $supplier)
            ->or_where('supplier2', $supplier)
            ->or_where('supplier3', $supplier)
            ->or_where('supplier4', $supplier)
            ->or_where('supplier5', $supplier);
        }
        $this->datatables->add_column("Actions", $action, "productid, image, code, name");
        echo $this->datatables->generate();
    }
    public function getProducts_($warehouse_id = null)
    {
        $this->bpas->checkPermissions('index', true);
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : null;
        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $product_type = $this->input->get('product_type') ? $this->input->get('product_type') :NULL;
        $start_date = $this->input->get('start_date')? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
            $user         = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('admin/products/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('product_details'));
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line('delete_product') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete1' id='a__$1' href='" . admin_url('products/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_product') . '</a>';
        $single_barcode = anchor('admin/products/print_barcodes/$1', '<i class="fa fa-print"></i> ' . lang('print_barcode_label'));
        // $single_label = anchor_popup('products/single_label/$1/' . ($warehouse_id ? $warehouse_id : ''), '<i class="fa fa-print"></i> ' . lang('print_label'), $this->popup_attributes);
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li><a href="' . admin_url('products/add/$1') . '"><i class="fa fa-plus-square"></i> ' . lang('duplicate_product') . '</a></li>
            <li><a href="' . admin_url('products/edit/$1') . '"><i class="fa fa-edit"></i> ' . lang('edit_product') . '</a></li>';
        if ($warehouse_id) {
            $action .= '<li><a href="' . admin_url('products/set_rack/$1/' . $warehouse_id) . '" data-toggle="modal" data-backdrop="static" data-target="#myModal"><i class="fa fa-bars"></i> '
                . lang('set_rack') . '</a></li>';
        }
        $action .= '<li><a href="' . base_url() . 'assets/uploads/$2" data-type="image" data-toggle="lightbox"><i class="fa fa-file-photo-o"></i> '
            . lang('view_image') . '</a></li>
            <li>' . $single_barcode . '</li>
            <li class="divider"></li>
            <li>' . $delete_link . '</li>
            </ul>
        </div></div>';
        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select(
                    $this->db->dbprefix('products') . ".id as productid, 
                    {$this->db->dbprefix('products')}.image as image, 
                    {$this->db->dbprefix('products')}.code as code, 
                    {$this->db->dbprefix('products')}.name as name,
                    {$this->db->dbprefix('products')}.type as type, 
                    {$this->db->dbprefix('brands')}.name as brand, 
                    {$this->db->dbprefix('categories')}.name as cname, cost as cost, price as price, 
                    IF (({$this->db->dbprefix('products')}.type = 'service' || {$this->db->dbprefix('products')}.type = 'combo' || {$this->db->dbprefix('products')}.type = 'bom'), 0, COALESCE(wp.quantity, 0)) as quantity, 
                    {$this->db->dbprefix('units')}.code as unit, wp.rack as rack, alert_quantity", false)
                ->from('products')
                ->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->join("( SELECT product_id, SUM(quantity) quantity, rack from {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id IN ({$warehouse_id}) GROUP BY product_id) wp", 'products.id=wp.product_id','left')
                ->group_by('products.id')
                ->order_by('products.name');
        } else {
            $this->datatables
                ->select(
                    $this->db->dbprefix('products') . ".id as productid, 
                    {$this->db->dbprefix('products')}.image as image, 
                    {$this->db->dbprefix('products')}.code as code, 
                    {$this->db->dbprefix('products')}.name as name, 
                    {$this->db->dbprefix('products')}.type as type, 
                    {$this->db->dbprefix('brands')}.name as brand, 
                    {$this->db->dbprefix('categories')}.name as cname, cost as cost, price as price, 
                    IF (({$this->db->dbprefix('products')}.type = 'service' || {$this->db->dbprefix('products')}.type = 'combo' || {$this->db->dbprefix('products')}.type = 'bom'), 0, COALESCE({$this->db->dbprefix('products')}.quantity, 0)) as quantity, 
                    {$this->db->dbprefix('units')}.code as unit, '' as rack, alert_quantity", false)
                ->from('products')->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->group_by('products.id')
                ->order_by('products.name');

            if ((!$this->Owner && !$this->Admin)) {
                if($this->session->userdata('warehouse_id')){
                    $this->datatables->join('warehouses_products wp', 'products.id=wp.product_id');
                    $this->datatables->where_in('wp.warehouse_id', $this->session->userdata('warehouse_id'));
                }
            }
        }
        $this->datatables->where('products.type !=','asset');   
        if (!$this->Owner && !$this->Admin) {
            if (!$this->GP['products-cost']) {
                $this->datatables->unset_column('cost');
            }
            if (!$this->GP['products-price']) {
                $this->datatables->unset_column('price');
            }
        }
        if ($product) {
            $this->datatables->where($this->db->dbprefix('products') . ".id", $product);
        }
        if ($category) {
            $this->datatables->where($this->db->dbprefix('products') . ".category_id", $category);
        }
        /*
        if ($product_type) {
            $this->datatables->where($this->db->dbprefix('products') . ".inactived", $product_type);
        }else{
            $this->datatables->where($this->db->dbprefix('products') . ".inactived !=", '1');
        }*/  
        if ($supplier) {
            $this->datatables->where('supplier1', $supplier)
            ->or_where('supplier2', $supplier)
            ->or_where('supplier3', $supplier)
            ->or_where('supplier4', $supplier)
            ->or_where('supplier5', $supplier);
        }
        $this->datatables->add_column('Actions', $action, 'productid, image, code, name');
        echo $this->datatables->generate();
    }
    
    /* ---------------------------------------------------------------- */

    public function getissues($warehouse_id = null)
    {
        $this->bpas->checkPermissions('index', true);
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : null;

        // 
        $detail_link = anchor('adminif ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
        //     $user         = $this->site->getUser();
        //     $warehouse_id = $user->warehouse_id;
        // }/products/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('product_details'));
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line('delete_product') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete1' id='a__$1' href='" . admin_url('products/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_product') . '</a>';
        $single_barcode = anchor('admin/products/print_barcodes/$1', '<i class="fa fa-print"></i> ' . lang('print_barcode_label'));
        // $single_label = anchor_popup('products/single_label/$1/' . ($warehouse_id ? $warehouse_id : ''), '<i class="fa fa-print"></i> ' . lang('print_label'), $this->popup_attributes);
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li><a href="' . admin_url('products/add/$1') . '"><i class="fa fa-plus-square"></i> ' . lang('duplicate_product') . '</a></li>
            <li><a href="' . admin_url('products/edit/$1') . '"><i class="fa fa-edit"></i> ' . lang('edit_product') . '</a></li>';
        if ($warehouse_id) {
            $action .= '<li><a href="' . admin_url('products/set_rack/$1/' . $warehouse_id) . '" data-toggle="modal" data-backdrop="static" data-target="#myModal"><i class="fa fa-bars"></i> '
                . lang('set_rack') . '</a></li>';
        }
        $action .= '<li><a href="' . base_url() . 'assets/uploads/$2" data-type="image" data-toggle="lightbox"><i class="fa fa-file-photo-o"></i> '
            . lang('view_image') . '</a></li>
            <li>' . $single_barcode . '</li>
            <li class="divider"></li>
            <li>' . $delete_link . '</li>
            </ul>
        </div></div>';
        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
            ->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, serial_no as serial_no, {$this->db->dbprefix('categories')}.name as cname, cost as cost, price as price, COALESCE(wp.quantity, 0) as quantity, {$this->db->dbprefix('units')}.code as unit, wp.rack as rack, alert_quantity", false)
            ->from('products')
            ->where('products.asset','asset');
            if ($this->Settings->display_all_products) {
                $this->datatables->join("( SELECT product_id, quantity, rack from {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id = {$warehouse_id}) wp", 'products.id=wp.product_id', 'left')
                ->where('products.asset','asset');
            } else {
                $this->datatables->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
                ->where('wp.warehouse_id', $warehouse_id)
                ->where('wp.quantity !=', 0);
            }
            $this->datatables->join('categories', 'products.category_id=categories.id', 'left')
            ->join('units', 'products.unit=units.id', 'left')
            ->join('brands', 'products.brand=brands.id', 'left')
            ->where('products.asset','asset');
        // ->group_by("products.id");
        } else {
            $this->datatables
                ->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, serial_no as serial_no, {$this->db->dbprefix('categories')}.name as cname, cost as cost, price as price, COALESCE(quantity, 0) as quantity, {$this->db->dbprefix('units')}.code as unit, '' as rack, alert_quantity", false)
                ->from('products')
                ->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->where('products.asset','asset')
                ->group_by('products.id');
        }
        if (!$this->Owner && !$this->Admin) {
            if (!$this->GP['products-cost']) {
                $this->datatables->unset_column('cost');
            }
            if (!$this->GP['products-price']) {
                $this->datatables->unset_column('price');
            }
        }
        if ($supplier) {
            $this->datatables->where('supplier1', $supplier)
            ->or_where('supplier2', $supplier)
            ->or_where('supplier3', $supplier)
            ->or_where('supplier4', $supplier)
            ->or_where('supplier5', $supplier);
        }
        $this->datatables->add_column('Actions', $action, 'productid, image, code, name');
        echo $this->datatables->generate();
    }

    /* ---------------------------------------------------------------- */

    public function getSubCategories($category_id = null)
    {
        if ($rows = $this->products_model->getSubCategories($category_id)) {
            $data = json_encode($rows);
        } else {
            $data = false;
        }
        echo $data;
    }

    public function getSubUnits($unit_id)
    {
        // $unit = $this->site->getUnitByID($unit_id);
        // if ($units = $this->site->getUnitsByBUID($unit_id)) {
        //     array_push($units, $unit);
        // } else {
        //     $units = array($unit);
        // }

        $units = $this->site->getUnitsByBUID($unit_id);
        $this->bpas->send_json($units);
    }

    function import_excel(){
        $this->bpas->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $this->load->library('excel');
            if(isset($_FILES["userfile"]["name"])) {
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
                    admin_redirect('products/import_excel');
                }
                $path   = $_FILES["userfile"]["tmp_name"];
                $object = PHPExcel_IOFactory::load($path);
                if (!$object) {
                    $error = $this->excel->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect("products/import_excel");
                }
                foreach($object->getWorksheetIterator() as $worksheet) {
                    $highestRow     = $worksheet->getHighestRow();
                    $highestColumn  = $worksheet->getHighestColumn();
                    $rw             = 1; 
                    $items          = array();
                    $existingPro    = '';
                    $failedImport   = 0;
                    $successImport  = 0;
                    for($row=2; $row <= $highestRow; $row++) {    
                        $name                 = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
                        $code                 = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                        $serial_no            = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                        $max_serial           = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                        $barcode_symbology    = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                        $brand                = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                        $category_code        = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
                        $unit                 = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
                        $sale_units           = $worksheet->getCellByColumnAndRow(8,$row)->getValue();
                        $purchase_unit        = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
                        $cost                 = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
                        $price                = $worksheet->getCellByColumnAndRow(11, $row)->getValue();
                        $alert_quantity       = $worksheet->getCellByColumnAndRow(12, $row)->getValue();
                        $tax_rate             = $worksheet->getCellByColumnAndRow(13, $row)->getValue();
                        $tax_method           = $worksheet->getCellByColumnAndRow(14, $row)->getValue();
                        $image                = $worksheet->getCellByColumnAndRow(15, $row)->getValue();
                        $subcategory_code     = $worksheet->getCellByColumnAndRow(16, $row)->getValue();
                        $product_variants     = $worksheet->getCellByColumnAndRow(17, $row)->getValue();
                        $cf1                  = $worksheet->getCellByColumnAndRow(18, $row)->getValue();
                        $cf2                  = $worksheet->getCellByColumnAndRow(19, $row)->getValue();
                        $cf3                  = $worksheet->getCellByColumnAndRow(20, $row)->getValue();
                        $cf4                  = $worksheet->getCellByColumnAndRow(21, $row)->getValue();
                        $cf5                  = $worksheet->getCellByColumnAndRow(22, $row)->getValue();
                        $cf6                  = $worksheet->getCellByColumnAndRow(23, $row)->getValue();
                        $hsn_code             = $worksheet->getCellByColumnAndRow(24, $row)->getValue();
                        $second_name          = $worksheet->getCellByColumnAndRow(25, $row)->getValue();
                        $currency_code        = $worksheet->getCellByColumnAndRow(26, $row)->getValue();
                        $other_currency_cost  = $worksheet->getCellByColumnAndRow(27, $row)->getValue();
                        $other_currency_price = $worksheet->getCellByColumnAndRow(28, $row)->getValue();
                        $weight               = $worksheet->getCellByColumnAndRow(29, $row)->getValue();
                        $expiry_alert_days    = $worksheet->getCellByColumnAndRow(30, $row)->getValue(); 
                        
                        if (!$this->form_validation->required($name)) {
                            $validate_errors = str_replace('{field}', lang('product_name'), lang('form_validation_required'));
                            $this->session->set_flashdata('error', $validate_errors);
                            admin_redirect('products/import_excel');
                        }
                        
                        if (!$this->form_validation->required($code) || !$this->form_validation->alpha_dash($code)) {
                            $validate_errors = str_replace('{field}', lang('product_code'), lang('form_validation_alpha_dash'));
                            $this->session->set_flashdata('error', $validate_errors);
                            admin_redirect('products/import_excel');
                        }

                        if (!$this->form_validation->required($price) || filter_var($price, FILTER_VALIDATE_FLOAT) === false) {
                            $validate_errors = lang('Product_price_can_be_only_number_and_can_not_be_empty ! '). lang("line_no").' '.$rw;
                            $this->session->set_flashdata('error', $validate_errors);
                            admin_redirect('products/import_excel');
                        }
                        
                        if (!$this->form_validation->required($cost) || filter_var($cost, FILTER_VALIDATE_FLOAT) === false) {
                            $validate_errors = lang('Product_cost_and_can_be_only_number_and_can_not_be_empty ! '). lang("line_no").' '.$rw;
                            $this->session->set_flashdata('error', $validate_errors);
                            admin_redirect('products/import_excel');
                        }

                        if ($barcode_symbology != 'code128') {
                            $validate_errors = 'The barcode symbology must be code "code128".';
                            $this->session->set_flashdata('error', $validate_errors);
                            admin_redirect('products/import_excel');
                        }
                        
                        if (!$this->products_model->getProductByCode(trim($code))) {
                            $successImport++;
                            if($category_code != null) {
                                if ($catd           = $this->products_model->getCategoryByCode(trim($category_code))) {
                                    $brand          = $this->products_model->getBrandByCode(trim($brand));
                                    $unit           = $this->products_model->getUnitByCode(trim($unit));
                                    $base_unit      = $unit ? $unit->id : NULL;
                                    $sale_unit      = $base_unit;
                                    $purcahse_unit  = $base_unit;
                                    if ($base_unit) {
                                        $units = $this->site->getUnitsByBUID($base_unit);
                                        foreach ($units as $u) {
                                            if ($u->code == trim($sale_units)) {
                                                $sale_unit = $u->id;
                                            }
                                            if ($u->code == trim($purchase_unit)) {
                                                $purcahse_unit = $u->id;
                                            }
                                        }
                                    } else {
                                        $this->session->set_flashdata('error', lang("check_unit") . " (" . $unit . "). " . lang("unit_code_x_exist") . " " . lang("line_no") . " " . $rw);
                                        admin_redirect("products/import_excel");
                                    }
                                    $tax_details = $this->products_model->getTaxRateByName(trim($tax_rate));
                                    $prsubcat    = $this->products_model->getCategoryByCode(trim($subcategory_code));
                                    $items[] = array (
                                        'code'              => trim($code),
                                        'name'              => trim($name),
                                        'serial_no'         => trim($serial_no),
                                        'category_id'       => $catd->id,
                                        'barcode_symbology' => mb_strtolower(trim($barcode_symbology), 'UTF-8'),
                                        'brand'             => ($brand ? $brand->id : NULL),
                                        'unit'              => $base_unit,
                                        'sale_unit'         => $sale_unit,
                                        'purchase_unit'     => $purcahse_unit,
                                        'cost'              => trim($cost),
                                        'price'             => trim($price),
                                        'alert_quantity'    => trim($alert_quantity),
                                        'tax_rate'          => ($tax_details ? $tax_details->id : NULL),
                                        'tax_method'        => ($tax_method == 'exclusive' ? 1 : 0),
                                        'subcategory_id'    => ($prsubcat ? $prsubcat->id : NULL),
                                        'variants'          => trim($product_variants),
                                        'cf1'               => trim($cf1),
                                        'cf2'               => trim($cf2),
                                        'cf3'               => trim($cf3),
                                        'cf4'               => trim($cf4),
                                        'cf5'               => trim($cf5),
                                        'cf6'               => trim($cf6),
                                        'image'             => trim($image),
                                        'slug'              => $this->bpas->slug(trim($name)),
                                        'weight'            => trim($weight),
                                        'expiry_alert_days' => ($expiry_alert_days != '') ? $expiry_alert_days : null ,
                                        'hsn_code'          => trim($hsn_code)
                                    );
                                    $unit_datas = [];
                                    $units = $this->site->getUnitsByBUID($base_unit);
                                    foreach ($units as $unit) {
                                        if ($unit->id == $purcahse_unit) {
                                            $u_cost  = trim($cost);
                                        } else {
                                            $u_cost  = 0;
                                        }
                                        if ($unit->id == $sale_unit) {   
                                            $u_price = trim($price);
                                        } else {
                                            $u_price = 0;
                                        }
                                        $unit_data = [
                                            'unit_id' => $unit->id,
                                            'cost'    => $u_cost,
                                            'price'   => $u_price
                                        ];
                                        $unit_datas[] = $unit_data;
                                    }
                                    $arr_products_units[] = $unit_datas;
                                } else {
                                    $this->session->set_flashdata('error', lang("check_category_code") . " (" .$category_code . "). " . lang("category_code_x_exist") . " " . lang("line_no") . " " . $rw);
                                    admin_redirect("products/import_excel");
                                }
                            }
                        } else {
                            /*-------------------updated items existing code-----------------*/
                            $successImport++;
                            if ($catd = $this->products_model->getCategoryByCode(trim($category_code))) {
                                $brand = $this->products_model->getBrandByCode(trim($brand));
                                $unit = $this->products_model->getUnitByCode(trim($unit));
                                $base_unit = $unit ? $unit->id : NULL;
                                $sale_unit = $base_unit;
                                $purcahse_unit = $base_unit;
                                if ($base_unit) {
                                    $units = $this->site->getUnitsByBUID($base_unit);
                                    foreach ($units as $u) {
                                        if ($u->code == trim($sale_units)) {
                                            $sale_unit = $u->id;
                                        }
                                        if ($u->code == trim($purchase_unit)) {
                                            $purcahse_unit = $u->id;
                                        }
                                    }
                                } else {
                                    $this->session->set_flashdata('error', lang("check_unit") . " (" . $unit . "). " . lang("unit_code_x_exist") . " " . lang("line_no") . " " . $rw);
                                    admin_redirect("products/import_excel");
                                }
                                $tax_details = $this->products_model->getTaxRateByName(trim($tax_rate));
                                $prsubcat = $this->products_model->getCategoryByCode(trim($subcategory_code));
                                $items_update[] = array (
                                    'code'              => trim($code),
                                    'name'              => trim($name),
                                    'serial_no'         => trim($serial_no),
                                    'category_id'       => $catd->id,
                                    'barcode_symbology' => mb_strtolower(trim($barcode_symbology), 'UTF-8'),
                                    'brand'             => ($brand ? $brand->id : NULL),
                                    'unit'              => $base_unit,
                                    'sale_unit'         => $sale_unit,
                                    'purchase_unit'     => $purcahse_unit,
                                    'cost'              => trim($cost),
                                    'price'             => trim($price),
                                    'alert_quantity'    => trim($alert_quantity),
                                    'tax_rate'          => ($tax_details ? $tax_details->id : NULL),
                                    'tax_method'        => ($tax_method == 'exclusive' ? 1 : 0),
                                    'subcategory_id'    => ($prsubcat ? $prsubcat->id : NULL),
                                    'variants'          => trim($product_variants),
                                    'cf1'               => trim($cf1),
                                    'cf2'               => trim($cf2),
                                    'cf3'               => trim($cf3),
                                    'cf4'               => trim($cf4),
                                    'cf5'               => trim($cf5),
                                    'cf6'               => trim($cf6),
                                    'image'             => trim($image),
                                    'weight'            => (trim($weight)),
                                    'slug'              => $this->bpas->slug(trim($name)),
                                    'hsn_code'          => trim($hsn_code)
                                );
                                $unit_datas = [];
                                $units = $this->site->getUnitsByBUID($base_unit);
                                foreach ($units as $unit) {
                                    if ($unit->id == $purcahse_unit) {
                                        $u_cost  = trim($cost);
                                    } else {
                                        $u_cost  = 0;
                                    }
                                    if ($unit->id == $sale_unit) {   
                                        $u_price = trim($price);
                                    } else {
                                        $u_price = 0;
                                    }
                                    $unit_data = [
                                        'unit_id' => $unit->id,
                                        'cost'    => $u_cost,
                                        'price'   => $u_price
                                    ];
                                    $unit_datas[] = $unit_data;
                                }
                                $arr_products_units[] = $unit_datas;
                            } else {
                                $this->session->set_flashdata('error', lang("check_category_code") . " (" . $category_code . "). " . lang("category_code_x_exist") . " " . lang("line_no") . " " . $rw);
                                admin_redirect("products/import_excel");
                            }
                            $existingPro .= $code;
                            $failedImport++;   
                        }                              
                        $rw++;   
                    }
                }
                /* Finde number of add products */
                $successImport1 = $successImport - $failedImport;
            }
        }   
        if(!empty($items)) {
            $arr_items = null;
            foreach ($items as $item) {
                $arr_items[$item['code']][] = $item;
            }
            foreach ($arr_items as $arr) {
                if (count($arr) > 1) {
                    $this->session->set_flashdata('error', lang("Duplicate_Product_Code"));
                    admin_redirect("products/import_excel");
                }
            }
        }
  
        if ($this->form_validation->run() == true && $prs = $this->products_model->add_products($items, $arr_products_units)) {
            $this->session->set_flashdata('message', sprintf($successImport . ' ' . lang("products_added") . '. ' . ($failedImport >= 1 ? $failedImport . ' already to updated' . $existingPro : ''), $successImport1));
            admin_redirect('products');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = array(
                'name'  => 'userfile',
                'id'    => 'userfile',
                'type'  => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );
            $bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('import_products_by_excel')));
            $meta = array('page_title' => lang('import_products_by_excel'), 'bc' => $bc);
            if (isset($existingPro)) {
                if ($existingPro !== '') {
                    $this->session->set_flashdata('error', 'Products already exist:' . $existingPro);
                }
            }      
            $this->page_construct('products/import_excel', $meta, $this->data);
        }
    }

    public function import_csv()
    {
        $this->bpas->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('products/import_csv');
                }
                $csv = $this->upload->file_name;
                $arrResult = [];
                $handle    = fopen($this->digital_upload_path . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles  = array_shift($arrResult);
                $updated = 0;
                $items   = [];
                foreach ($arrResult as $key => $value) {
                    $supplier_name = isset($value[24]) ? trim($value[24]) : '';
                    $supplier      = $supplier_name ? $this->products_model->getSupplierByName($supplier_name) : false;
                    $serial_code = $value[2]?('|'.$value[2]):'';

                    $item = [
                        'name'              => isset($value[0]) ? trim($value[0]) : '',
                        'code'              => isset($value[1]) ? trim($value[1].$serial_code) : '',
                        'serial_no'         => isset($value[2]) ? trim($value[2]) : '',
                        'max_serial'         => isset($value[3]) ? trim($value[3]) : '',
                        'barcode_symbology' => isset($value[4]) ? mb_strtolower(trim($value[4]), 'UTF-8') : '',
                        'brand'             => isset($value[5]) ? trim($value[5]) : '',
                        'category_code'     => isset($value[6]) ? trim($value[6]) : '',
                        'unit'              => isset($value[7]) ? trim($value[7]) : '',
                        'sale_unit'         => isset($value[8]) ? trim($value[8]) : '',
                        'purchase_unit'     => isset($value[9]) ? trim($value[9]) : '',
                        'cost'              => isset($value[10]) ? trim($value[10]) : '',
                        'price'             => isset($value[11]) ? trim($value[11]) : '',
                        'alert_quantity'    => isset($value[12]) ? trim($value[12]) : '',
                        'tax_rate'          => isset($value[13]) ? trim($value[13]) : '',
                        'tax_method'        => isset($value[14]) ? (trim($value[14]) == 'exclusive' ? 1 : 0) : '',
                        'image'             => isset($value[15]) ? trim($value[15]) : '',
                        'subcategory_code'  => isset($value[16]) ? trim($value[16]) : '',
                        'variants'          => isset($value[17]) ? trim($value[17]) : '',
                        'cf1'               => isset($value[18]) ? trim($value[18]) : '',
                        'cf2'               => isset($value[19]) ? trim($value[19]) : '',
                        'cf3'               => isset($value[20]) ? trim($value[20]) : '',
                        'cf4'               => isset($value[21]) ? trim($value[21]) : '',
                        'cf5'               => isset($value[22]) ? trim($value[22]) : '',
                        'cf6'               => isset($value[23]) ? trim($value[23]) : '',
                        'hsn_code'          => isset($value[24]) ? trim($value[24]) : '',
                        'second_name'       => isset($value[25]) ? trim($value[25]) : '',
                        'supplier1'         => $supplier ? $supplier->id : null,
                        'supplier1_part_no' => isset($value[27]) ? trim($value[27]) : '',
                        'supplier1price'    => isset($value[28]) ? trim($value[28]) : '',
                        'slug'              => $this->bpas->slug($value[0]),
                    ];

                    if ($catd = $this->products_model->getCategoryByCode($item['category_code'])) {
                        $tax_details   = $this->products_model->getTaxRateByName($item['tax_rate']);
                        $prsubcat      = $this->products_model->getCategoryByCode($item['subcategory_code']);
                        $brand         = $this->products_model->getBrandByName($item['brand']);
                        $unit          = $this->products_model->getUnitByCode($item['unit']);
                        $base_unit     = $unit ? $unit->id : null;
                        $sale_unit     = $base_unit;
                        $purcahse_unit = $base_unit;
                        if ($base_unit) {
                            $units = $this->site->getUnitsByBUID($base_unit);
                            foreach ($units as $u) {
                                if ($u->code == $item['sale_unit']) {
                                    $sale_unit = $u->id;
                                }
                                if ($u->code == $item['purchase_unit']) {
                                    $purcahse_unit = $u->id;
                                }
                            }
                        } else {
                            $this->session->set_flashdata('error', lang('check_unit') . ' (' . $item['unit'] . '). ' . lang('unit_code_x_exist') . ' ' . lang('line_no') . ' ' . ($key + 1));
                            admin_redirect('products/import_csv');
                        }

                        unset($item['category_code'], $item['subcategory_code']);
                        $item['unit']           = $base_unit;
                        $item['sale_unit']      = $sale_unit;
                        $item['category_id']    = $catd->id;
                        $item['purchase_unit']  = $purcahse_unit;
                        $item['brand']          = $brand ? $brand->id : null;
                        $item['tax_rate']       = $tax_details ? $tax_details->id : null;
                        $item['subcategory_id'] = $prsubcat ? $prsubcat->id : null;

                        if ($product = $this->products_model->getProductByCode($item['code'])) {
                            if ($product->type == 'standard') {
                                if ($item['variants']) {
                                    $vs = explode('|', $item['variants']);
                                    foreach ($vs as $v) {
                                        if (!empty(trim($v))) {
                                            $variants[] = ['product_id' => $product->id, 'name' => trim($v)];
                                        }
                                    }
                                }
                                unset($item['variants']);
                                if ($this->products_model->updateProduct($product->id, $item, null, null, null, null, $variants)) {
                                    $updated++;
                                }
                            }
                            $item = false;
                        }
                    } else {
                        $this->session->set_flashdata('error', lang('check_category_code') . ' (' . $item['category_code'] . '). ' . lang('category_code_x_exist') . ' ' . lang('line_no') . ' ' . ($key + 1));
                        admin_redirect('products/import_csv');
                    }
                    if ($item) {
                        $items[] = $item;
                    }
                }
            }
            // $this->bpas->print_arrays($items);
        }

        if ($this->form_validation->run() == true && !empty($items)) {
            if ($return_result = $this->products_model->add_products($items)) {
                $updated = $updated ? '<p>' . sprintf(lang('products_updated'), $updated) . '</p>' : '';
                $this->session->set_flashdata('message', sprintf(lang('products_added'), count($items)) . $updated);
                admin_redirect('products');
            }else{
                $this->session->set_flashdata('warning', lang('add_product_failed.'));
                admin_redirect('products/import_csv');
            }
        } else {
            if (isset($items) && empty($items)) {
                if ($updated) {
                    $this->session->set_flashdata('message', sprintf(lang('products_updated'), $updated));
                    admin_redirect('products');
                } else {
                    $this->session->set_flashdata('warning', lang('csv_issue'));
                }
                admin_redirect('products/import_csv');
            }

            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = ['name' => 'userfile',
                'id'                          => 'userfile',
                'type'                        => 'text',
                'value'                       => $this->form_validation->set_value('userfile'),
            ];

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('import_products_by_csv')]];
            $meta = ['page_title' => lang('import_products_by_csv'), 'bc' => $bc];
            $this->page_construct('products/import_csv', $meta, $this->data);
        }
    }
    /* ---------------------- */
    public function issues($warehouse_id = null) 
    {
        $this->bpas->checkPermissions();
        $count = explode(',', $this->session->userdata('warehouse_id'));
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) { 
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        }else {
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
        $bc                     = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('assets')]];
        $meta                   = ['page_title' => lang('issues'), 'bc' => $bc];
        $this->page_construct('products/issues', $meta, $this->data);
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
        $this->data['product']        = $pr_details;
        $this->data['unit']           = $this->site->getUnitByID($pr_details->unit);
        $this->data['multi_units']    = $this->site->getUnitByProId($id);
        $this->data['brand']          = $this->site->getBrandByID($pr_details->brand);
        $this->data['images']         = $this->products_model->getProductPhotos($id);
        $this->data['category']       = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory']    = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : null;
        $this->data['tax_rate']       = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : null;
        $this->data['warehouses']     = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options']        = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants']       = $this->products_model->getProductOptions($id);
        $this->data['addon_items']    = $this->products_model->getProductAddOnItems($id);
        $this->data['product_options']= $this->products_model->getProductByOptions($id);
        $this->data['product_expiry'] = $this->site->getStockMovement_ExpiryQuantityByProduct($id);

        $this->load->view($this->theme . 'products/modal_view', $this->data);
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
        $this->data['addon_items'] = $this->products_model->getProductAddOnItems($id);

        $this->load->view($this->theme . 'products/asset_modal_view', $this->data);
    }
   

    public function pdf($id = null, $view = null)
    {
        $this->bpas->checkPermissions('index');

        $pr_details = $this->products_model->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        $this->data['product']          = $pr_details;
        $this->data['unit']             = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand']            = $this->site->getBrandByID($pr_details->brand);
        $this->data['images']           = $this->products_model->getProductPhotos($id);
        $this->data['category']         = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory']      = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : null;
        $this->data['tax_rate']         = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : null;
        $this->data['popup_attributes'] = $this->popup_attributes;
        $this->data['warehouses']       = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options']          = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants']         = $this->products_model->getProductOptions($id);
        $this->data['addon_items']      = $this->products_model->getProductAddOnItems($id);

        $name = $pr_details->code . '_' . str_replace('/', '_', $pr_details->name) . '.pdf';
        if ($view) {
            $this->load->view($this->theme . 'products/pdf', $this->data);
        } else {
            $html = $this->load->view($this->theme . 'products/pdf', $this->data, true);
            if (!$this->Settings->barcode_img) {
                $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
            }
            $this->bpas->generate_pdf($html, $name);
        }
    }

    public function print_barcodes__($product_id = null)
    {
        $this->bpas->checkPermissions('barcode', true);
        $this->form_validation->set_rules('style', lang('style'), 'required');

        if ($this->form_validation->run() == true) {
            $style      = $this->input->post('style');
            // $bci_size   = ($style == 10 || $style == 12 ? 50 : ($style == 14 || $style == 18 ? 30 : 20));
            $bci_size = ($style == 10 || $style == 12 || $style == 90 || $style == 6 ? 50 : ($style == 14 || $style == 16 || $style == 18 ? 30 : 20));
            $currencies = $this->site->getAllCurrencies();
            $s          = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
            if ($s < 1) {
                $this->session->set_flashdata('error', lang('no_product_selected'));
                admin_redirect('products/print_barcodes');
            }
            for ($m = 0; $m < $s; $m++) {
                $pid            = $_POST['product'][$m];
                $quantity       = $_POST['quantity'][$m];
                $product        = $this->products_model->getProductWithCategory($pid);
                $product->price = $this->input->post('check_promo') ? ($product->promotion ? $product->promo_price : $product->price) : $product->price;
                if ($variants = $this->products_model->getProductOptions($pid)) {
                    foreach ($variants as $option) {
                        if ($this->input->post('vt_' . $product->id . '_' . $option->id)) {
                            $barcodes[] = [
                                'site'    => $this->input->post('site_name') ? $this->Settings->site_name : false,
                                'name'    => $this->input->post('product_name') ? $product->name . ' - ' . $option->name : false,
                                'image'   => $this->input->post('product_image') ? $product->image : false,
                                'barcode' => $product->code . $this->Settings->barcode_separator . $option->id,
                                'bcs'     => 'code128',
                                'bcis'    => $bci_size,
                                // 'barcode' => $this->product_barcode($product->code . $this->Settings->barcode_separator . $option->id, 'code128', $bci_size),
                                'price'      => $this->input->post('price') ? $this->bpas->formatMoney($option->price != 0 ? ($product->price + $option->price) : $product->price, 'none') : false,
                                'rprice'     => $this->input->post('price') ? ($option->price != 0 ? ($product->price + $option->price) : $product->price) : false,
                                'unit'       => $this->input->post('unit') ? $product->unit : false,
                                'category'   => $this->input->post('category') ? $product->category : false,
                                'currencies' => $this->input->post('currencies'),
                                'variants'   => $this->input->post('variants') ? $variants : false,
                                'quantity'   => $quantity,
                            ];
                        }
                    }
                } else {
                    $barcodes[] = [
                        'site'  => $this->input->post('site_name') ? $this->Settings->site_name : false,
                        'name'  => $this->input->post('product_name') ? $product->name : false,
                        'image' => $this->input->post('product_image') ? $product->image : false,
                        // 'barcode' => $this->product_barcode($product->code, $product->barcode_symbology, $bci_size),
                        'barcode'    => $product->code,
                        'bcs'        => $product->barcode_symbology,
                        'bcis'       => $bci_size,
                        'price'      => $this->input->post('price') ? $this->bpas->formatMoney($product->price, 'none') : false,
                        'rprice'     => $this->input->post('price') ? $product->price : false,
                        'unit'       => $this->input->post('unit') ? $product->unit : false,
                        'category'   => $this->input->post('category') ? $product->category : false,
                        'currencies' => $this->input->post('currencies'),
                        'variants'   => false,
                        'quantity'   => $quantity,
                    ];
                }
            }
            $this->data['barcodes']   = $barcodes;
            $this->data['currencies'] = $currencies;
            $this->data['style']      = $style;
            $this->data['items']      = false;
            $bc                       = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('print_barcodes')]];
            $meta                     = ['page_title' => lang('print_barcodes'), 'bc' => $bc];
            $this->page_construct('products/print_barcodes', $meta, $this->data);
        } else {
            if ($this->input->get('purchase') || $this->input->get('transfer')) {
                if ($this->input->get('purchase')) {
                    $purchase_id = $this->input->get('purchase', true);
                    $items       = $this->products_model->getPurchaseItems($purchase_id);
                } elseif ($this->input->get('transfer')) {
                    $transfer_id = $this->input->get('transfer', true);
                    $items       = $this->products_model->getTransferItems($transfer_id);
                }
                if ($items) {
                    foreach ($items as $item) {
                        if ($row = $this->products_model->getProductByID($item->product_id)) {
                            $selected_variants = false;
                            if ($variants = $this->products_model->getProductOptions($row->id)) {
                                foreach ($variants as $variant) {
                                    $selected_variants[$variant->id] = isset($pr[$row->id]['selected_variants'][$variant->id]) && !empty($pr[$row->id]['selected_variants'][$variant->id]) ? 1 : ($variant->id == $item->option_id ? 1 : 0);
                                }
                            }
                            $pr[$row->id] = ['id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $item->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants];
                        }
                    }
                    $this->data['message'] = lang('products_added_to_list');
                }
            }

            if ($product_id) {
                if ($row = $this->site->getProductByID($product_id)) {
                    $selected_variants = false;
                    if ($variants = $this->products_model->getProductOptions($row->id)) {
                        foreach ($variants as $variant) {
                            $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                        }
                    }
                    $pr[$row->id] = ['id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants];

                    $this->data['message'] = lang('product_added_to_list');
                }
            }

            if ($this->input->get('category')) {
                if ($products = $this->products_model->getCategoryProducts($this->input->get('category'))) {
                    foreach ($products as $row) {
                        $selected_variants = false;
                        if ($variants = $this->products_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = ['id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants];
                    }
                    $this->data['message'] = lang('products_added_to_list');
                } else {
                    $pr = [];
                    $this->session->set_flashdata('error', lang('no_product_found'));
                }
            }

            if ($this->input->get('subcategory')) {
                if ($products = $this->products_model->getSubCategoryProducts($this->input->get('subcategory'))) {
                    foreach ($products as $row) {
                        $selected_variants = false;
                        if ($variants = $this->products_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = ['id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants];
                    }
                    $this->data['message'] = lang('products_added_to_list');
                } else {
                    $pr = [];
                    $this->session->set_flashdata('error', lang('no_product_found'));
                }
            }

            $this->data['items'] = isset($pr) ? json_encode($pr) : false;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('print_barcodes')]];
            $meta                = ['page_title' => lang('print_barcodes'), 'bc' => $bc];
            $this->page_construct('products/print_barcodes', $meta, $this->data);
        }
    }

    public function print_barcodes($product_id = null)
    {
        $this->bpas->checkPermissions('barcode', true);
        $this->load->helper('pos');
        $this->form_validation->set_rules('style', lang('style'), 'required');

        if ($this->form_validation->run() == true) {
            $style      = $this->input->post('style');
            $bci_size   = ($style == 10 || $style == 12 ? 50 : ($style == 14 || $style == 18 ? 30 : 20));
            $currencies = $this->site->getAllCurrencies();
            $s          = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
            if ($s < 1) {
                $this->session->set_flashdata('error', lang('no_product_selected'));
                admin_redirect('products/print_barcodes');
            }
            for ($m = 0; $m < $s; $m++) {
                $pid            = $_POST['product'][$m];
                $quantity       = $_POST['quantity'][$m];
                $product        = $this->products_model->getProductWithCategory($pid);
                $product->price = $this->input->post('check_promo') ? ($product->promotion ? $product->promo_price : $product->price) : $product->price;
               
                
                if ($variants = $this->products_model->getProductOptions($pid)) {
                    foreach ($variants as $option) {
                        if ($this->input->post('vt_' . $product->id . '_' . $option->id)) {
                            $barcodes[] = [
                                'site'    => $this->input->post('site_name') ? $this->Settings->site_name : false,
                                'name'    => $this->input->post('product_name') ? $product->name . ' - ' . $option->name : false,
                                'image'   => $this->input->post('product_image') ? $product->image : false,
                                'barcode' => $product->code . $this->Settings->barcode_separator . $option->id,
                                'bcs'     => 'code128',
                                'bcis'    => $bci_size,
                                'weight'     => $product->weight,
                                // 'barcode' => $this->product_barcode($product->code . $this->Settings->barcode_separator . $option->id, 'code128', $bci_size),
                                'price'      => $this->input->post('price') ? $this->bpas->formatMoney($option->price != 0 ? ($product->price + $option->price) : $product->price, 'none') : false,
                                'rprice'     => $this->input->post('price') ? ($option->price != 0 ? ($product->price + $option->price) : $product->price) : false,
                                'unit'       => $this->input->post('unit') ? $product->unit : false,
                                'category'   => $this->input->post('category') ? $product->category : false,
                                'currencies' => $this->input->post('currencies'),
                                'variants'   => $this->input->post('variants') ? $variants : false,
                                'quantity'   => $quantity,
                            ];
                        }
                    }
                } else {
                    $barcodes[] = [
                        'site'  => $this->input->post('site_name') ? $this->Settings->site_name : false,
                        'name'  => $this->input->post('product_name') ? $product->name : false,
                        'image' => $this->input->post('product_image') ? $product->image : false,
                        // 'barcode' => $this->product_barcode($product->code, $product->barcode_symbology, $bci_size),
                        'barcode'    => $product->code,
                        'weight'     => $product->weight,
                        'bcs'        => $product->barcode_symbology,
                        'bcis'       => $bci_size,
                        'price'      => $this->input->post('price') ? $this->bpas->formatMoney($product->price, 'none') : false,
                        'rprice'     => $this->input->post('price') ? $product->price : false,
                        'unit'       => $this->input->post('unit') ? $product->unit : false,
                        'category'   => $this->input->post('category') ? $product->category : false,
                        'currencies' => $this->input->post('currencies'),
                        'variants'   => false,
                        'quantity'   => $quantity,
                    ];
                }
            }

            $this->data['barcodes']   = $barcodes;
            $this->data['currencies'] = $currencies;
            $this->data['style']      = $style;
            $this->data['items']      = false;
            $bc                       = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('print_barcodes')]];
            $meta                     = ['page_title' => lang('print_barcodes'), 'bc' => $bc];
            $this->page_construct('products/print_barcodes', $meta, $this->data);
        } else {
            if ($this->input->get('purchase') || $this->input->get('transfer')) {
                if ($this->input->get('purchase')) {
                    $purchase_id = $this->input->get('purchase', true);
                    $items       = $this->products_model->getPurchaseItems($purchase_id);
                } elseif ($this->input->get('transfer')) {
                    $transfer_id = $this->input->get('transfer', true);
                    $items       = $this->products_model->getTransferItems($transfer_id);
                }
                if ($items) {
                    foreach ($items as $item) {
                        if ($row = $this->products_model->getProductByID($item->product_id)) {
                            $selected_variants = false;
                            if ($variants = $this->products_model->getProductOptions($row->id)) {
                                foreach ($variants as $variant) {
                                    $selected_variants[$variant->id] = isset($pr[$row->id]['selected_variants'][$variant->id]) && !empty($pr[$row->id]['selected_variants'][$variant->id]) ? 1 : ($variant->id == $item->option_id ? 1 : 0);
                                }
                            }
                            $pr[$row->id] = ['id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $item->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants];
                        }
                    }
                    $this->data['message'] = lang('products_added_to_list');
                }
            }

            if ($product_id) {
                if ($row = $this->site->getProductByID($product_id)) {
                    $selected_variants = false;
                    if ($variants = $this->products_model->getProductOptions($row->id)) {
                        foreach ($variants as $variant) {
                            $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                        }
                    }
                    $pr[$row->id] = ['id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants];

                    $this->data['message'] = lang('product_added_to_list');
                }
            }

            if ($this->input->get('category')) {
                if ($products = $this->products_model->getCategoryProducts($this->input->get('category'))) {
                    foreach ($products as $row) {
                        $selected_variants = false;
                        if ($variants = $this->products_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = ['id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants];
                    }
                    $this->data['message'] = lang('products_added_to_list');
                } else {
                    $pr = [];
                    $this->session->set_flashdata('error', lang('no_product_found'));
                }
            }

            if ($this->input->get('subcategory')) {
                if ($products = $this->products_model->getSubCategoryProducts($this->input->get('subcategory'))) {
                    foreach ($products as $row) {
                        $selected_variants = false;
                        if ($variants = $this->products_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = ['id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants];
                    }
                    $this->data['message'] = lang('products_added_to_list');
                } else {
                    $pr = [];
                    $this->session->set_flashdata('error', lang('no_product_found'));
                }
            }
            
            $this->data['items'] = isset($pr) ? json_encode($pr) : false;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('print_barcodes')]];
            $meta                = ['page_title' => lang('print_barcodes'), 'bc' => $bc];
            $this->page_construct('products/print_barcodes', $meta, $this->data);
        }
    }

    public function export_counted($stoct_id = null)
    {
        $this->load->library('excel');
        $this->excel->setActiveSheetIndex(0);
        $this->excel->getActiveSheet()->setTitle(lang('purchase_request'));
        $this->excel->getActiveSheet()->SetCellValue('A1', lang('no'));
        $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_code'));
        $this->excel->getActiveSheet()->SetCellValue('C1', lang('product_name'));
        $this->excel->getActiveSheet()->SetCellValue('D1', lang('expected'));
        $this->excel->getActiveSheet()->SetCellValue('E1', lang('counted'));
        // $this->excel->getActiveSheet()->SetCellValue('F1', lang('cost'));

        $row = 2;
        $i = 1;

        if ($stoct_id) {
            $result = $this->site->getProductByStockItemId($stoct_id);
            
            foreach ($result as $res) {
                $this->excel->getActiveSheet()->SetCellValue('A' . $row, $i);
                $this->excel->getActiveSheet()->SetCellValue('B' . $row, $res->product_code);
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $res->product_name);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $res->expected);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $res->counted);
                // $this->excel->getActiveSheet()->SetCellValue('F' . $row, $res->cost);

                $row++;
                $i++;

            }

        }

        $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        // $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);

        $this->excel->getActiveSheet()->getStyle('A1:E1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->excel->getActiveSheet()->getStyle('A1:E1')->getFont()->setBold(true);

        $filename = 'stock_counted_' . date('Y_m_d_H_i_s');
        $this->load->helper('excel');
        create_excel($this->excel, $filename);
    }

    public function product_actions($wh = null)
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'sync_quantity') {
                    foreach ($_POST['val'] as $id) {
                        // $this->site->syncQuantity(null, null, null, $id);
                        $this->site->syncQuantity_13_05_21($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line('products_quantity_sync'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->products_model->deleteProduct($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line('products_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'labels') {
                    foreach ($_POST['val'] as $id) {
                        $row               = $this->products_model->getProductByID($id);
                        $selected_variants = false;
                        if ($variants = $this->products_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = ['id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants];
                    }

                    $this->data['items'] = isset($pr) ? json_encode($pr) : false;
                    $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                    $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('print_barcodes')]];
                    $meta                = ['page_title' => lang('print_barcodes'), 'bc' => $bc];
                    $this->page_construct('products/print_barcodes', $meta, $this->data);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('Products');
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
                    $this->excel->getActiveSheet()->SetCellValue('P1', lang('product_variants'));
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
                    $this->excel->getActiveSheet()->SetCellValue('AD1', lang('product_details'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $product   = $this->products_model->getProductDetail($id);
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
                        $variants         = $this->products_model->getProductOptions($id);
                        $product_variants = '';
                        if ($variants) {
                            $i = 1;
                            $v = count($variants);
                            foreach ($variants as $variant) {
                                $product_variants .= trim($variant->name) . ($i != $v ? '|' : '');
                                $i++;
                            }
                        }
                        if ($product->type == 'service' || $product->type == 'combo' || $product->type == 'bom') {
                            $quantity = 0;
                        } else {
                            $quantity = ($product->quantity ? $product->quantity : 0);
                            if ($wh) {
                                if ($wh_qty = $this->products_model->getProductQuantity($id, $wh)) {
                                    $quantity = (!empty($wh_qty['quantity']) ? $wh_qty['quantity'] : 0);
                                } else {
                                    $quantity = 0;
                                }
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
                        if ($this->Owner || $this->Admin || $this->GP['products-cost']) {
                            $this->excel->getActiveSheet()->SetCellValue('I' . $row, $product->cost);
                        }
                        if ($this->Owner || $this->Admin || $this->GP['products-price']) {
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
                    $filename = 'products_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line('no_product_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER'] ?? 'admin/products');
        }
    }


    public function asset_actions($wh = null)
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
                        $this->products_model->deleteProduct($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line('asset_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'labels') {
                    foreach ($_POST['val'] as $id) {
                        $row               = $this->products_model->getProductByID($id);
                        $selected_variants = false;
                        if ($variants = $this->products_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = ['id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants];
                    }

                    $this->data['items'] = isset($pr) ? json_encode($pr) : false;
                    $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                    $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products/assets'), 'page' => lang('Assets')], ['link' => '#', 'page' => lang('print_barcodes')]];
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
                        $product   = $this->products_model->getProductDetail($id);
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
                        $variants         = $this->products_model->getProductOptions($id);
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
                            if ($wh_qty = $this->products_model->getProductQuantity($id, $wh)) {
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
                        if ($this->Owner || $this->Admin || $this->GP['products-price']) {
                            $this->excel->getActiveSheet()->SetCellValue('I' . $row, $product->cost);
                        }
                        if ($this->Owner || $this->Admin || $this->GP['products-cost']) {
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
            redirect($_SERVER['HTTP_REFERER'] ?? 'admin/products/assets');
        }
    }

    public function qa_suggestions()
    {
        $term         = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed  = $this->bpas->analyze_term($term);
        $sr        = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $rows = $this->products_model->getQASuggestions($sr, $warehouse_id);
        if ($rows) {
            $r = 0;
            foreach ($rows as $row) {
                $option                = false;
                $row->product_type     = $row->type;
                $row->type             = 'addition';
                $row->item_tax_method  = $row->tax_method;
                $row->qty              = 1;
                $row->base_quantity    = 1;
                $row->quantity_balance = 0;
                $row->ordered_quantity = 0;
                $row->base_unit        = $row->unit;
                $row->base_unit_cost   = $row->cost;
                $row->unit             = $row->unit;
                $row->unit_name        = $this->site->getUnitByID($row->unit)->name;
                $row->discount         = '0';
                $row->expiry           = '';
                $row->serial           = '';
                $row->qoh = $this->bpas->convertQty($row->id, $row->quantity);
                $options = $this->site->getProductOptions($row->id, $warehouse_id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->site->getProductOptionByID($option_id) : $options[0];
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt       = json_decode('{}');
                    $opt->cost = 0;
                    $option_id = false;
                }
                $row->option = $option_id;
                $pis = $this->site->getStockMovement_ProductBalanceQuantity($row->id, $warehouse_id, $row->option);
                if ($pis) {
                    $row->quantity = $pis->quantity_balance;
                }
                if ($opt->cost != 0) {
                    $row->cost = $opt->cost;
                }
                $row->real_unit_cost = $row->cost;
                $units       = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate    = $this->site->getTaxRateByID($row->tax_rate);
                $stock_items = $this->site->getStockMovementByProductID($row->id, $warehouse_id, $row->option);
                $c           = sha1(uniqid(mt_rand(), true));
                if ($stock_items) {
                    foreach ($stock_items as $pi) {
                        $pr[] = [
                            'id'  => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')' . ($pi->expiry != null ? ' (' . $pi->expiry . ')' : ''),
                            'row' => $row, 'units' => $units, 'options' => $options, 'pitems' => $stock_items, 'expirys' => $stock_items, 'expiry' => $pi->expiry 
                        ];
                        $r++;
                    }
                } else {
                    $pr[] = [
                        'id'  => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                        'row' => $row, 'units' => $units, 'options' => $options, 'pitems' => $stock_items, 'expirys' => $stock_items, 'expiry' => null 
                    ];
                    $r++;
                }
            }
            if (isset($pr)) {
                $this->bpas->send_json($pr);
            } else {
                $this->bpas->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);    
            }
        } else {
            $this->bpas->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    public function quantity_adjustments($warehouse_id = null)
    {
        $this->bpas->checkPermissions('adjustments');
        $count = explode(',', $this->session->userdata('warehouse_id'));
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id ? $warehouse_id : null;
            $this->data['warehouse']  = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            if (count($count)>1) {
                $this->data['warehouses']   = $this->site->getAllWarehouses();
            } else {
                $this->data['warehouses']   = null;
            }
            $this->data['count_warehouses'] = $count;
            $this->data['user_warehouse']   = (isset($count) && count($count) == 1) ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
            $this->data['warehouse_id']     = $warehouse_id;
            $this->data['warehouse']        = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('quantity_adjustments')]];
        $meta = ['page_title' => lang('quantity_adjustments'), 'bc' => $bc];
        $this->page_construct('products/quantity_adjustments', $meta, $this->data);        
    }
     public function getadjustments($warehouse_id = null)
    {
        $this->bpas->checkPermissions('adjustments');
        if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
            $user         = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line('delete_adjustment') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('products/delete_adjustment/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a>";

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('adjustments')}.id as id, date, reference_no, warehouses.name as wh_name, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by, note, attachment")
            ->from('adjustments')
            ->join('warehouses', 'warehouses.id=adjustments.warehouse_id', 'left')
            ->join('users', 'users.id=adjustments.created_by', 'left')
            ->group_by('adjustments.id');
        if ($warehouse_id) {
            $this->datatables->where("FIND_IN_SET(bpas_adjustments.warehouse_id, '".$warehouse_id."')");
        }
        $this->datatables->add_column('Actions', "<div class='text-center'><a href='" . admin_url('products/edit_adjustment/$1') . "' class='tip' title='" . lang('edit_adjustment') . "'><i class='fa fa-edit'></i></a> " . $delete_link . '</div>', 'id');
        echo $this->datatables->generate();
    }

    public function set_rack($product_id = null, $warehouse_id = null)
    {
        $this->bpas->checkPermissions('edit', true);

        $this->form_validation->set_rules('rack', lang('rack_location'), 'trim|required');

        if ($this->form_validation->run() == true) {
            $data = [
                'rack_id'       => $this->input->post('rack'),
                'product_id'    => $this->input->post('product_id'),
                'warehouse_id'  => $this->input->post('warehouse'),
            ];
        } elseif ($this->input->post('set_rack')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('products/' . $warehouse_id);
        }

        if ($this->form_validation->run() == true && $this->products_model->setRack($data)) {
            $this->session->set_flashdata('message', lang('rack_set'));
            admin_redirect('products/' . $warehouse_id);
        } else {
            $this->data['error']        = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['product']      = $this->site->getProductByID($product_id);
            $wh_pr                      = $this->products_model->getProductQuantity($product_id, $warehouse_id);
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['racks']        = $this->products_model->getProductRacks();
            $this->data['rack']         = $wh_pr['rack_id'];
            $this->data['modal_js']     = $this->site->modal_js();
            $this->load->view($this->theme . 'products/set_rack', $this->data);
        }
    }

    public function stock_counts($warehouse_id = null)
    {
        $this->bpas->checkPermissions('stock_count');
        $count = explode(',', $this->session->userdata('warehouse_id'));
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id ? $warehouse_id : null;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            if (count($count)>1) {
                $this->data['warehouses']   = $this->site->getAllWarehouses();
            } else {
                $this->data['warehouses']   = null;
            }
            
            $this->data['count_warehouses'] = $count;
            $this->data['user_warehouse']   = (isset($count) && count($count) == 1) ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        }

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('stock_counts')]];
        $meta = ['page_title' => lang('stock_counts'), 'bc' => $bc];
        $this->page_construct('products/stock_counts', $meta, $this->data);
    }

    public function suggestions()
    {
        $term = $this->input->get('term', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }
        $rows = $this->products_model->getProductNames($term);
        if ($rows) {
            foreach ($rows as $row) {
                $pr[] = ['id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => 1];
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }
     public function using_suggestions($id = null)
    {
        $term = $this->input->get('term', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }
        $rows = $this->products_model->getProductNamesUsing($id, $term);
        if ($rows) {
            foreach ($rows as $row) {
                $pr[] = ['id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => 1];
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }


    /* ------------------------------------------------------------------ */

    public function update_price()
    {
        $this->bpas->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (DEMO) {
                $this->session->set_flashdata('message', lang('disabled_in_demo'));
                admin_redirect('welcome');
            }

            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('products');
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

                $keys = ['code', 'price'];

                $final = [];

                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv_pr) {
                    if (!$this->products_model->getProductByCode(trim($csv_pr['code']))) {
                        $this->session->set_flashdata('message', lang('check_product_code') . ' (' . $csv_pr['code'] . '). ' . lang('code_x_exist') . ' ' . lang('line_no') . ' ' . $rw);
                        admin_redirect('products');
                    }
                    $rw++;
                }
            }
        } elseif ($this->input->post('update_price')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/group_product_prices/' . $group_id);
        }

        if ($this->form_validation->run() == true && !empty($final)) {
            $this->products_model->updatePrice($final);
            $this->session->set_flashdata('message', lang('price_updated'));
            admin_redirect('products');
        } else {
            $this->data['userfile'] = ['name' => 'userfile',
                'id'                          => 'userfile',
                'type'                        => 'text',
                'value'                       => $this->form_validation->set_value('userfile'),
            ];
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'products/update_price', $this->data);
        }
    }

    public function view($id = null)
    {
        $this->bpas->checkPermissions('index');

        $pr_details = $this->products_model->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        $this->data['product']          = $pr_details;
        $this->data['unit']             = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand']            = $this->site->getBrandByID($pr_details->brand);
        $this->data['images']           = $this->products_model->getProductPhotos($id);
        $this->data['category']         = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory']      = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : null;
        $this->data['tax_rate']         = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : null;
        $this->data['popup_attributes'] = $this->popup_attributes;
        $this->data['warehouses']       = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options']          = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants']         = $this->products_model->getProductOptions($id);
        $this->data['sold']             = $this->products_model->getSoldQty($id);
        $this->data['purchased']        = $this->products_model->getPurchasedQty($id);
        $this->data['addon_items']      = $this->products_model->getProductAddOnItems($id);

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => $pr_details->name]];
        $meta = ['page_title' => $pr_details->name, 'bc' => $bc];
        $this->page_construct('products/view', $meta, $this->data);
    }

    
     public function asset_view($id = null)
    {
        $this->bpas->checkPermissions('index');

        $pr_details = $this->products_model->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        $this->data['product']          = $pr_details;
        $this->data['unit']             = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand']            = $this->site->getBrandByID($pr_details->brand);
        $this->data['images']           = $this->products_model->getProductPhotos($id);
        $this->data['category']         = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory']      = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : null;
        $this->data['tax_rate']         = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : null;
        $this->data['popup_attributes'] = $this->popup_attributes;
        $this->data['warehouses']       = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options']          = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants']         = $this->products_model->getProductOptions($id);
        $this->data['sold']             = $this->products_model->getSoldQty($id);
        $this->data['purchased']        = $this->products_model->getPurchasedQty($id);
        $this->data['addon_items']      = $this->products_model->getProductAddOnItems($id);

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products/assets'), 'page' => lang('Assets')], ['link' => '#', 'page' => $pr_details->name]];
        $meta = ['page_title' => $pr_details->name, 'bc' => $bc];
        $this->page_construct('products/asset_view', $meta, $this->data);
    }

    public function view_adjustment($id)
    {
        $this->bpas->checkPermissions('adjustments', true);

        $adjustment = $this->products_model->getAdjustmentByID($id);
        if (!$id || !$adjustment) {
            $this->session->set_flashdata('error', lang('adjustment_not_found'));
            $this->bpas->md();
        }

        $this->data['inv']        = $adjustment;
        $this->data['rows']       = $this->products_model->getAdjustmentItems($id);
        $this->data['created_by'] = $this->site->getUser($adjustment->created_by);
        $this->data['updated_by'] = $this->site->getUser($adjustment->updated_by);
        $this->data['warehouse']  = $this->site->getWarehouseByID($adjustment->warehouse_id);
        $this->load->view($this->theme . 'products/view_adjustment', $this->data);
    }

    public function view_count($id)
    {
        $this->bpas->checkPermissions('stock_count', true);
        $stock_count = $this->products_model->getStouckCountByID($id);

        if (!$stock_count->finalized) {
            $this->bpas->md('admin/products/finalize_count/' . $id);
            //$this->session->set_flashdata('error', lang('status_is_draft'));
            //$this->bpas->md('admin/products/stock_counts');
        }

        $this->data['id']                = $id;
        $this->data['stock_count']       = $stock_count;
        $this->data['stock_count_items'] = $this->products_model->getStockCountItems($id);
        $this->data['warehouse']         = $this->site->getWarehouseByID($stock_count->warehouse_id);
        $this->data['adjustment']        = $this->products_model->getAdjustmentByCountID($id);
        $this->load->view($this->theme . 'products/view_count', $this->data);
    }

    public function scan_count($stock_count_id = null)
    {
        $this->bpas->checkPermissions('barcode', true);
        $this->form_validation->set_rules('style', lang('style'), 'required');
        if ($this->form_validation->run() == true) {
            $style      = $this->input->post('style');
            $bci_size   = ($style == 10 || $style == 12 ? 50 : ($style == 14 || $style == 18 ? 30 : 20));
            $currencies = $this->site->getAllCurrencies();
            $s          = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
            if ($s < 1) {
                $this->session->set_flashdata('error', lang('no_product_selected'));
                admin_redirect('products/print_barcodes');
            }
            for ($m = 0; $m < $s; $m++) {
                $pid            = $_POST['product'][$m];
                $quantity       = $_POST['quantity'][$m];
                $product        = $this->products_model->getProductWithCategory($pid);
                $product->price = $this->input->post('check_promo') ? ($product->promotion ? $product->promo_price : $product->price) : $product->price;
                if ($variants = $this->products_model->getProductOptions($pid)) {
                    foreach ($variants as $option) {
                        if ($this->input->post('vt_' . $product->id . '_' . $option->id)) {
                            $barcodes[] = [
                                'site'    => $this->input->post('site_name') ? $this->Settings->site_name : false,
                                'name'    => $this->input->post('product_name') ? $product->name . ' - ' . $option->name : false,
                                'image'   => $this->input->post('product_image') ? $product->image : false,
                                'barcode' => $product->code . $this->Settings->barcode_separator . $option->id,
                                'bcs'     => 'code128',
                                'bcis'    => $bci_size,
                                // 'barcode' => $this->product_barcode($product->code . $this->Settings->barcode_separator . $option->id, 'code128', $bci_size),
                                'price'      => $this->input->post('price') ? $this->bpas->formatMoney($option->price != 0 ? ($product->price + $option->price) : $product->price, 'none') : false,
                                'rprice'     => $this->input->post('price') ? ($option->price != 0 ? ($product->price + $option->price) : $product->price) : false,
                                'unit'       => $this->input->post('unit') ? $product->unit : false,
                                'category'   => $this->input->post('category') ? $product->category : false,
                                'currencies' => $this->input->post('currencies'),
                                'variants'   => $this->input->post('variants') ? $variants : false,
                                'quantity'   => $quantity,
                            ];
                        }
                    }
                } else {
                    $barcodes[] = [
                        'site'  => $this->input->post('site_name') ? $this->Settings->site_name : false,
                        'name'  => $this->input->post('product_name') ? $product->name : false,
                        'image' => $this->input->post('product_image') ? $product->image : false,
                        // 'barcode' => $this->product_barcode($product->code, $product->barcode_symbology, $bci_size),
                        'barcode'    => $product->code,
                        'bcs'        => $product->barcode_symbology,
                        'bcis'       => $bci_size,
                        'price'      => $this->input->post('price') ? $this->bpas->formatMoney($product->price, 'none') : false,
                        'rprice'     => $this->input->post('price') ? $product->price : false,
                        'unit'       => $this->input->post('unit') ? $product->unit : false,
                        'category'   => $this->input->post('category') ? $product->category : false,
                        'currencies' => $this->input->post('currencies'),
                        'variants'   => false,
                        'quantity'   => $quantity,
                    ];
                }
            }
            $this->data['barcodes']   = $barcodes;
            $this->data['currencies'] = $currencies;
            $this->data['style']      = $style;
            $this->data['items']      = false;
            $bc                       = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('print_barcodes')]];
            $meta                     = ['page_title' => lang('print_barcodes'), 'bc' => $bc];
            $this->page_construct('products/print_barcodes', $meta, $this->data);
        } else {
            $check_count = $this->site->getStockById($stock_count_id);
            if ($check_count->status == 1) {
                $this->session->set_flashdata('error', lang('stock_count_finalized'));
                admin_redirect('products/stock_counts');
            }
            if ($stock_count_id) {
                $result = $this->site->getProductByStockItem($stock_count_id, 'scan');
                if ($result) {
                    foreach ($result as $row) {
                        $c = rand(100000, 9999999);
                        $selected_variants = false;
                        if ($variants = $this->products_model->getProductOptions($row->product_id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $ri = $this->Settings->item_addition ? $row->id : $c;
                        $pr[$ri] = ['id' => $c, 'product_id' => $row->product_id, 'label' => $row->product_name . ' (' . $row->product_code . ')' . ($row->expiry != null ? ' (' . $row->expiry . ')' : ''), 'code' => $row->product_code, 'name' => $row->product_name, 'variants' => $variants, 'expected' => $row->expected, 'qty' => $row->counted, 'cost' => $row->cost, 'selected_variants' => $selected_variants, 'expiry' => $row->expiry];
                    }
                }   
                //$this->data['message'] = lang('product_added_to_list');
            }
            $this->data['stock_count'] = $this->products_model->getStouckCountByID($stock_count_id);
            $this->data['stock_count_id'] = $stock_count_id;
            $this->data['items'] = isset($pr) ? json_encode($pr) : false;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('print_barcodes')]];
            $meta = ['page_title' => lang('scan_count'), 'bc' => $bc];
            $this->page_construct('products/scan_count', $meta, $this->data);
        }
    }
    
    function items_convert()
    {
        $this->bpas->checkPermissions('items_convert', NULL, 'products');
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required|is_unique[convert.reference_no]');

        $id_convert_item = 0;
        if ($this->form_validation->run() == true)
        {
            if ($this->Owner || $this->Admin || $this->Settings->allow_change_date == 1) {
                $date = $this->bpas->fld($_POST['sldate']);
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $warehouse_id        = $_POST['warehouse'];
            // list convert item from
            $cIterm_from_id     = $_POST['convert_from_items_id'];
            $cIterm_from_code   = $_POST['convert_from_items_code'];
            $cIterm_from_name   = $_POST['convert_from_items_name'];
            $cIterm_from_uom    = $_POST['convert_from_items_uom'];
            $cIterm_from_qty    = $_POST['convert_from_items_qty'];
            // list convert item to
            $iterm_to_id        = $_POST['convert_to_items_id'];
            $iterm_to_code      = $_POST['convert_to_items_code'];
            $iterm_to_name      = $_POST['convert_to_items_name'];
            $iterm_to_uom       = $_POST['convert_to_items_uom'];
            $iterm_to_qty       = $_POST['convert_to_items_qty'];
            $reference_no       = $_POST['reference_no']?$_POST['reference_no']:$this->site->getReference('con', $_POST['biller']);

            $data               = array(
                'reference_no'  => $reference_no,
                'date'          => $date,
                'warehouse_id'  => $_POST['warehouse'],
                'created_by'    => $this->session->userdata('user_id'),
                'noted'         => $_POST['note'],
                'bom_id'        => $_POST['bom_id'],
                'biller_id'     => $_POST['biller']
            );
            
            $idConvert          = $this->products_model->insertConvert($data);
            $id_convert_item    = $idConvert;
            
            $items              = array();
            $i                  = isset($_POST['convert_from_items_code']) ? sizeof($_POST['convert_from_items_code']) : 0;
            
            $qty_from           = '';
            $total_cost         = '';
            $cost_variant       = 0;
            $total_raw_cost     = 0;
            $total_fin_qty      = 0;
            $each_cost          = 0;
            
            for ($r = 0; $r < $i; $r++) {
                $qty_from       += $cIterm_from_qty[$r];
                $product_fr      = $this->site->getProductByID($cIterm_from_id[$r]);
                $total_cost     += ($cIterm_from_qty[$r] * $product_fr->cost);
                
                $ware_qty        = $this->products_model->getProductQuantity($cIterm_from_id[$r], $warehouse_id);
                
                //======================= Check Variant ===================//
                
                if(!empty($cIterm_from_uom[$r])){
                    $product_variant= $this->site->getProductVariantByOptionID($cIterm_from_uom[$r]);
                }
                
                $unit_qty     = ( !empty($product_variant->qty_unit) && $product_variant->qty_unit > 0 ? $product_variant->qty_unit : 1 );
                if($product_variant){
                    $cost_variant    = $product_fr->cost * $unit_qty;
                    $total_raw_cost += $cost_variant * $cIterm_from_qty[$r];
                } else {
                    $cost_variant    = $product_fr->cost;
                    $total_raw_cost += $cost_variant * $cIterm_from_qty[$r];
                }
                
                //============================= End =======================//
                //echo $ware_qty['quantity'] .'=='. $unit_qty  * $cIterm_from_qty[$r] .'<br/>';
                //====================== Check Quantity ===================//
                if($ware_qty['quantity'] < ($unit_qty  * $cIterm_from_qty[$r]) ){
                    $this->session->set_flashdata('error', $this->lang->line("quantity_is_valid"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                //=========================== End =========================//
        
                $qtytransfer = (-1) * ($unit_qty  * $cIterm_from_qty[$r]);

                $clause = array(
                    'purchase_id'   => NULL, 
                    'product_code'  => $cIterm_from_code[$r], 
                    'product_id'    => $cIterm_from_id[$r], 
                    'warehouse_id'  => $warehouse_id
                );
                
                $conItem = array(
                                'convert_id'    => $idConvert,
                                'product_id'    => $cIterm_from_id[$r],
                                'product_code'  => $cIterm_from_code[$r],
                                'product_name'  => $cIterm_from_name[$r],
                                'quantity'      => $cIterm_from_qty[$r],
                                'option_id'     => $cIterm_from_uom[$r],
                                'cost'          => $cost_variant,
                                'status'        => 'deduct'
                            );
                
                $this->db->insert('bpas_convert_items', $conItem);
                $convert_item_id = $this->db->insert_id();
                
                //================= Add Value For Stock =====================//
                
                $clause['quantity']         = $qtytransfer;
                $clause['item_tax']         = 0;
                $clause['date']             = date('Y-m-d');
                $clause['option_id']        = $cIterm_from_uom[$r];
                $clause['convert_id']       = $id_convert_item;
                $clause['product_name']     = $cIterm_from_name[$r];
                $clause['quantity_balance'] = $qtytransfer;
                $clause['cb_avg']           = $product_fr->cost;
                $clause['cb_qty']           = $product_fr->quantity;
                $clause['transaction_type'] = 'CONVERT';
                $clause['transaction_id']   = $convert_item_id;
                $clause['status']           = 'received';
                
                $this->db->insert('purchase_items', $clause);
                
                //========================= End ============================//
                            
                $this->site->syncQuantity(NULL, NULL, NULL, $cIterm_from_id[$r]);
                
            }
            
            $j = isset($_POST['convert_to_items_code']) ? sizeof($_POST['convert_to_items_code']) : 0;
            
            //========================= Get Finish Qty ======================//
            for ($r = 0; $r < $j; $r++) {
                $option     = $this->site->getProductVariantByOptionID($iterm_to_uom[$r]);
                if($option){
                    $total_fin_qty  += $iterm_to_qty[$r] * $option->qty_unit;
                }else{
                    $total_fin_qty  += $iterm_to_qty[$r];
                }
            }
            //=============================== End ===========================//
            
            for ($r = 0; $r < $j; $r++) {
                $products = $this->site->getProductByID($iterm_to_id[$r]);
                //======================== Check Variant ========================//
                if(!empty($iterm_to_uom[$r])){
                    $product_variant   = $this->site->getProductVariantByOptionID($iterm_to_uom[$r]);
                }
                
                $unit_qty = ( !empty($iterm_to_uom[$r]) ? $product_variant->qty_unit : 1 );
                
                //============================ End ==============================//
                
                //========================== AVG Cost ===========================//
                if(!empty($iterm_to_uom[$r])){
                    $qty_items  = $iterm_to_qty[$r] * $product_variant->qty_unit;
                }else{
                    $qty_items  = $iterm_to_qty[$r];
                }
                
                $each_cost      = $this->site->calculateCONAVCost($iterm_to_id[$r], $total_raw_cost, $total_fin_qty, $qty_items);
                //============================= End =============================//
                $qtytransfer    = ($unit_qty  * $iterm_to_qty[$r]);

                $clause         = array(
                    'purchase_id'   => NULL, 
                    'product_code'  => $iterm_to_code[$r], 
                    'product_id'    => $iterm_to_id[$r], 
                    'warehouse_id'  => $warehouse_id
                );
                
                $conItem        = array(
                    'convert_id'    => $idConvert,
                    'product_id'    => $iterm_to_id[$r],
                    'product_code'  => $iterm_to_code[$r],
                    'product_name'  => $iterm_to_name[$r],
                    'quantity'      => $iterm_to_qty[$r],
                    'option_id'     => $iterm_to_uom[$r],
                    'cost'          => $each_cost['cost'] / $iterm_to_qty[$r],
                    'status'        => 'add'
                );
                
                $this->db->insert('bpas_convert_items', $conItem);
                $convertitem_id = $this->db->insert_id();
                
                $clause['quantity']         = $qtytransfer;
                $clause['item_tax']         = 0;
                $clause['date']             = date('Y-m-d');
                $clause['option_id']        = $iterm_to_uom[$r];
                $clause['convert_id']       = $id_convert_item;
                $clause['product_name']     = $iterm_to_name[$r];
                $clause['quantity_balance'] = $qtytransfer;
                $clause['transaction_type'] = 'CONVERT';
                $clause['transaction_id']   = $convertitem_id;
                $clause['status']           = 'received';
                $clause['cb_avg']           = $products->cost;
                $clause['cb_qty']           = $products->quantity;
                $this->db->insert('purchase_items', $clause);
                
                $this->db->update('products', array('cost' => $each_cost['avg']), array('id' => $iterm_to_id[$r]));
                
                $this->site->syncQuantity(NULL, NULL, NULL, $iterm_to_id[$r]);
                
            }

            $this->session->set_flashdata('message', lang("convert_success"));
            admin_redirect('workorder');
        }else{
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            
            $reference = $this->site->updateReference('con');
            
            
            if ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) {
                $biller_id = $this->site->get_setting()->default_biller;
                $this->data['biller_id'] = $biller_id;
                $this->data['conumber'] = $this->site->getReference('con',$biller_id);
            } else {
                $biller_id = $this->session->userdata('biller_id');
                $this->data['biller_id'] = $biller_id;
                $this->data['conumber'] = $this->site->getReference('con',$biller_id);
            }
            
            //$this->site->updateReference('con'); 
            $warehouse_id = $this->session->userdata('warehouse_id');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouses_by_user'] = $this->products_model->getAllWarehousesByUser($warehouse_id);
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['bom'] = $this->products_model->getAllBoms();
            $this->data['billers'] = $this->site->getAllBiller();

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('products')));
            $meta = array('page_title' => lang('convert_product'), 'bc' => $bc);
            $this->page_construct('workorder/items_convert', $meta, $this->data);
        }
    }
    public function getDatabyBom_id()
    {
        $id             = $this->input->get('term', TRUE);
        $warehouse_id   = $this->input->get('warehouse_id', TRUE);
        $result = $this->products_model->getAllBom_id($id, $warehouse_id);
        if ($result) {
            $uom = array();
            foreach ($result as $row) {
                $options = $this->products_model->getProductOptions($row->product_id);
                
                $pr[] = array('row' => $row, 'variant' => $options );
            }
            //echo '<pre>';print_r($pr);echo '</pre>';
            echo json_encode($pr);
        };
        //echo json_encode($result);
    }
    public function getQtyFromOutData()
    {
        $id             = $this->input->get('term', TRUE);
        $qty_output     = $this->input->get('qty_output', TRUE);
        $warehouse_id   = $this->input->get('warehouse_id', TRUE);
        $result = $this->products_model->getAllBom_id($id, $warehouse_id);
        if ($result) {
            $uom = array();
            foreach ($result as $row) {
                $options = $this->products_model->getProductOptions($row->product_id);
                // if($row->status != "add"){
                    $row->quantity = $row->quantity * $qty_output; 
                // }
                $pr[] = array('row' => $row, 'variant' => $options );
            }
            //echo '<pre>';print_r($pr);echo '</pre>';
            echo json_encode($pr);
        };
        //echo json_encode($result);
    }

    public function using_stock()
    {
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
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('enter_using_stock')));
        $meta = array('page_title' => lang('enter_using_stock'), 'bc' => $bc);
        $this->page_construct('stock_using/index', $meta,$this->data);
    }

    public function get_using_stock()
    {
        $this->load->library('datatables');
        $fdate       = $this->input->get('start_date');
        $tdate       = $this->input->get('end_date');
        $referno     = $this->input->get('referno');
        $empno       = $this->input->get('empno');
        $plan        = $this->input->get('plan');
        $product     = $this->input->get('product');

        $start_date  = $this->bpas->fsd($fdate);
        $end_date    = $this->bpas->fsd($tdate);

        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_using_stock") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('products/delete_using_stock/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_using_stock') . "</a>";

        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">                                                               
                <li class="edit_using"><a href="'.admin_url('products/edit_using_stock/$1/$2').'" ><i class="fa fa-edit"></i>'.lang('edit_using_stock').'</a></li> 
                <li class="add_return" ><a href="'.admin_url('products/return_using_stock/$1/$2').'" ><i class="fa fa-reply"></i>'.lang('return_using_stock').'</a></li> 
                <li><a href="'.admin_url('products/print_using_stock_by_id/$1/$2').'" ><i class="fa fa-newspaper-o"></i>'.lang('print_using_stock').'</a></li>
                <li><a href="'.admin_url('products/print_sample_form_ppcp/$1/$2').'" ><i class="fa fa-newspaper-o"></i>'.lang('print_sample_form_ppcp').'</a></li>
                <li>' . $delete_link . '</li>
            </ul>
        </div>'; 

        $qi = "( SELECT using_stock_id, product_id, 
                    GROUP_CONCAT(CONCAT({$this->db->dbprefix('enter_using_stock_items')}.product_name, '__', {$this->db->dbprefix('enter_using_stock_items')}.qty_use) SEPARATOR '___') as item_nane from {$this->db->dbprefix('enter_using_stock_items')} ";
        if ($product) {
            $qi .= " WHERE {$this->db->dbprefix('enter_using_stock_items')}.product_id = {$product} ";
        }
        $qi .= " GROUP BY {$this->db->dbprefix('enter_using_stock_items')}.using_stock_id ) FQI";


        $this->datatables
            ->select("{$this->db->dbprefix('enter_using_stock')}.id as id,
                {$this->db->dbprefix('enter_using_stock')}.date,
                {$this->db->dbprefix('enter_using_stock')}.reference_no as refno,
                {$this->db->dbprefix('companies')}.company, 
                bpas_projects_plan.title as home_type, 
                {$this->db->dbprefix('warehouses')}.name as warehouse_name, 
                bpas_users.username, 
                {$this->db->dbprefix('enter_using_stock')}.note, 
                {$this->db->dbprefix('enter_using_stock')}.type as type", FALSE)
            
            ->from("enter_using_stock")
            ->join($qi, 'FQI.using_stock_id=enter_using_stock.id', 'left')
            ->join('companies', 'bpas_companies.id=enter_using_stock.biller_id', 'inner')
            ->join('warehouses', 'enter_using_stock.warehouse_id=bpas_warehouses.id', 'left')
            ->join('projects_plan', 'enter_using_stock.plan_id = bpas_projects_plan.id', 'left')
            ->join('products', 'enter_using_stock.address_id = bpas_products.id', 'left')
            ->join('users', 'bpas_users.id=enter_using_stock.employee_id', 'inner')
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
            if ($product) {
                $this->datatables->where('FQI.product_id', $product, false);
            }

            $this->datatables->add_column("Actions", $action_link, "id,type");
        echo $this->datatables->generate();
    }
    
    public function edit_enter_using_stock_by_id($id=NULL,$type=NULL)
    {
        $this->bpas->checkPermissions('using_stock');
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
        $getUsingStock=$this->products_model->getUsingStockById($id);
        $reference_no= $getUsingStock->reference_no;
        $wh_id=$getUsingStock->warehouse_id;
        $getUsingStockItem=$this->products_model->getUsingStockItemByRef($reference_no,$wh_id);
        $getQtyOnHandGroupByWh_ID=$this->products_model->getQtyOnHandGroupByWhID();
        $unit_of_measure_by_code=array();
        $i=0;
        foreach($getUsingStockItem as $Stock_I){
            $get_unit_of_measure = $this->products_model->getUnitOfMeasureByProductCode($Stock_I->product_code);
            $variant = $this->db->select("products.*, 
                                    '1' as measure_qty, 
                                    product_variants.name as description")
                                    ->from("products")
                                    ->where("products.code",$Stock_I->product_code)                                                                     
                                    ->join("product_variants","products.id=product_variants.product_id","left")
                                    ->get();
            if($variant->num_rows() > 0 && $variant->row()->description != null){
                $get_unit_of_measure = $variant->result();
            }
            foreach($get_unit_of_measure as $um){
                $product_code = $Stock_I->product_code;
                $u_description = $um->description;
                $u_measure_qty = $um->measure_qty;
                $unit_of_measure_by_code[$i]=array(
                                                    'product_code'=>$product_code,
                                                    'description'=>$u_description,
                                                    'measure_qty'=>$u_measure_qty
                                                );
                $i++;
            }
        }
        $this->data['getExpenses'] = $this->products_model->getAllExpenseCategory();
        $this->data['positions'] = $this->products_model->getAllPositionData();
        $this->data['reasons'] = $this->products_model->getAllreasons();
        $this->data['unit_of_measure_by_code'] =$unit_of_measure_by_code;
        $this->data['qqh'] =$getQtyOnHandGroupByWh_ID;
        $this->data['stock'] =$getUsingStock;
        $this->data['stock_item'] =$getUsingStockItem;
        $this->data['modal_js'] = $this->site->modal_js();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('edit_enter_using_stock')));
        $meta = array('page_title' => lang('edit_enter_using_stock'), 'bc' => $bc);
        $this->page_construct('products/edit_enter_using_stock', $meta, $this->data);
    } 

    public function update_enter_using_stock()
    {
            if ($this->Owner || $this->Admin || $this->Settings->allow_change_date == 1) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            
            $stock_id            = $this->input->post('stock_id');
            $date                = $date;
            $warehouse_id        = $this->input->post('from_location');
            $authorize_id        = $this->input->post('authorize_id');
            $employee_id         = $this->input->post('employee_id');
            $shop                = $this->input->post('shop');
            $account             = $this->input->post('account');
            $note                = $this->input->post('note');
            $cost                = $this->input->post('cost');
            $ref_prefix          = $this->input->post('ref_prefix');
            $stock_item_id_arr   = $this->input->post('stock_item_id');
            $item_code_arr       = $this->input->post('item_code');
            $description_arr     = $this->input->post('description');
            $reason_arr          = $this->input->post('reason');
            $qty_use_arr         = $this->input->post('qty_use');
            $last_qty_use_arr    = $this->input->post('last_qty_use');
            $unit_arr            = $this->input->post('unit');
            $qty_arr             = $this->input->post('qty_use');
            $reference_no        = $this->input->post('reference_no');
            $sotre_delete_id     = $this->input->post('sotre_delete_id');
            $product_id          = $this->input->post('product_id');
            $exp_cate_id         = $this->input->post('exp_catid');
            $delete_item         = (explode("-",$sotre_delete_id));
            $delete_product_id   = (explode("-",$product_id));
            $total_item_cost     = 0;
            $i=0;
            
            foreach($item_code_arr as $item_code)
            {
                $unit_of_measure=$this->products_model->getUnitOfMeasureByProductCode($item_code_arr[$i],$unit_arr[$i]);
                $variant = $this->db->select("products.*,
                                    qty_unit as measure_qty, 
                                    product_variants.name as description")
                                    ->from("products")
                                    ->where( 
                                        array(
                                            "products.code"=>$item_code_arr[$i],
                                            "product_variants.name" =>$unit_arr[$i]
                                            )
                                        )
                                    ->join("product_variants","products.id=product_variants.product_id","left")
                                    ->get();
                if($variant->num_rows() > 0 && $variant->row()->description != null){
                    $unit_of_measure = $variant->row();
                }
                
                $convert_qty = $qty_use_arr[$i]*$unit_of_measure->measure_qty;
                $total_cost  = $cost[$i] * $convert_qty;
                $total_item_cost+= $this->bpas->formatDecimal($total_cost);
                $i++;
            }
            $CurrentUser=$this->site->getUser();
            $data = array(
                'date'          => $date,
                'warehouse_id'  => $warehouse_id,
                'authorize_id'  => $authorize_id,
                'employee_id'   => $employee_id,
                'shop'          => $shop,
                'account'       => $account,
                'note'          => $note,
                'create_by'     => $CurrentUser->id,
                'type'          => 'use',
                'total_cost'    => $total_item_cost,
            );
            
            $insert_enter_using_stock = $this->products_model->update_enter_using_stock($data,$ref_prefix,$stock_id);
            
            $i = 0;
            $del_en_item              = $this->products_model->delete_enter_items_by_ref($reference_no);
            $del_pu_item              = $this->products_model->delete_purchase_items_by_ref($reference_no);
            $this->products_model->delete_inventory_valuation_details($stock_item_id_arr);
            
            foreach($item_code_arr as $item_code){
                $unit_of_measure = $this->products_model->getUnitOfMeasureByProductCode($item_code_arr[$i],$unit_arr[$i]);
                
                $variant = $this->db->select("products.*,
                                    qty_unit as measure_qty,
                                    product_variants.id as option_id,
                                    product_variants.name as description")
                                    ->from("products")
                                    ->where( 
                                        array(
                                            "products.code"=>$item_code_arr[$i],
                                            "product_variants.name" =>$unit_arr[$i]
                                            )
                                        )
                                    ->join("product_variants","products.id=product_variants.product_id","left")
                                    ->get();
                if($variant->num_rows() > 0 && $variant->row()->description != null){
                    $unit_of_measure = $variant->row();
                }   
                $option_id = $unit_of_measure->option_id;
                $convert_qty = $qty_use_arr[$i] * $unit_of_measure->measure_qty;
                $item_data = array(
                    'code'          => $item_code_arr[$i],
                    'description'   => $description_arr[$i],
                    'reason'        => $reason_arr[$i],
                    'qty_use'       => $convert_qty,
                    'unit'          => $unit_arr[$i],
                    'qty_by_unit'   => $qty_use_arr[$i],
                    'warehouse_id'  => $warehouse_id,
                    'cost'          => $cost[$i],
                    'reference_no'  => $reference_no,
                    'exp_cate_id'   => $exp_cate_id[$i],
                    'option_id'     => $option_id
                );
                $insert_enter_using_stock_item = $this->products_model->insert_enter_using_stock_item($item_data);
                if($insert_enter_using_stock_item){
                    $product        = $this->products_model->getProductQtyByCode($item_code_arr[$i]);
                    $product_id     = $product->id;
                    $product_code   = $product->code;
                    $product_name   = $product->name;
                    $net_unit_cost  = $product->price;
                    $pur_data = array(
                        'product_id'        => $product_id,
                        'product_code'      => $product_code,
                        'product_name'      => $product_name,
                        'net_unit_cost'     => $product->cost,
                        'option_id'         => $unit_of_measure->id,
                        'quantity'          => -1 * abs($convert_qty),
                        'reference'         => $reference_no,
                        'warehouse_id'      => $warehouse_id,
                        'subtotal'          => $pr_item->subtotal ? $pr_item->subtotal : 0,
                        'date'              => $date,
                        'status'            => 'received',
                        'net_unit_cost'     => $net_unit_cost,
                        'quantity_balance'  => -1 * abs($convert_qty),
                        'transaction_type'  => 'USING STOCK',
                        'transaction_id'    => $insert_enter_using_stock_item,
                    );
                    $this->db->insert('purchase_items', $pur_data);
                    $product_cost = $this->site->getProductByID($product_id);
                $this->db->update("inventory_valuation_details",array('cost'=>$product_cost->cost,'avg_cost'=>$product_cost->cost),array('field_id'=>$insert_enter_using_stock_item));
                        //$this->site->syncProductQty($product_id, $warehouse_id);
                    $this->site->syncQuantitys(null,null,null,$product_id);
                }
                $i++;
            }
            foreach($delete_item as $d_i){
                
                //$del = $this->products_model->delete_update_stock_item($d_i);
                
                if($delete_product_id){
                    foreach($delete_product_id as $product_id){
                        $this->site->syncQuantitys(null,null,null,$product_id);
                    }
                }
            }
            if($insert_enter_using_stock_item && $insert_enter_using_stock){
                $this->session->set_flashdata(lang('enter_using_stock_added.'));
                $r_r=str_replace("/","-",$this->input->post('reference_no'));
                // admin_redirect('products/print_enter_using_stock/'.$r_r);
                admin_redirect('products/using_stock');
            }else{
                $this->session->set_flashdata('error', $error);
                redirect($_SERVER["HTTP_REFERER"]);
            }
    }
    
    public function print_enter_using_stock_by_id($id, $type)
    {
        $this->bpas->checkPermissions('using_stock');
        if($type=="use"){
            $using_stock=$this->products_model->get_enter_using_stock_by_id($id);
            $ref_no=$using_stock->reference_no;
            $stock_item = $this->products_model->get_enter_using_stock_item_by_ref($ref_no);
             $this->data['using_stock'] = $using_stock; 
             $this->data['stock_item'] = $stock_item;
            $this->load->view($this->theme.'stock_using/print_enter_using_stock',$this->data);
        }
        if($type=="return"){
            $using_stock=$this->products_model->get_enter_using_stock_by_id($id);
            $ref_no=$using_stock->reference_no;
            $stock_item=$this->products_model->get_enter_using_stock_item_by_ref($ref_no);
             $this->data['using_stock'] = $using_stock; 
             $this->data['stock_item'] = $stock_item; 
            $this->load->view($this->theme.'stock_using/print_enter_using_stock_return',$this->data);
        }
    }
    
    public function print_using_stock_by_id($id, $type)
    {
        $this->bpas->checkPermissions('using_stock');
        if($type=="use"){
            $using_stock = $this->products_model->get_enter_using_stock_by_id($id);
            $ref_no      = $using_stock->reference_no;
            $stock_item  = $this->products_model->get_enter_using_stock_item_by_ref($ref_no);
            
            $this->data['using_stock']  = $using_stock;
            $this->data['stock_item']   = $stock_item; 
            $this->data['info']         = $this->products_model->get_enter_using_stock_info(); 
            $this->data['biller']       = $this->products_model->getUsingStockProject($id);
            $this->data['au_info']      = $this->products_model->getAuInfo($id);
            $this->load->view($this->theme.'stock_using/print_using_stock',$this->data);
        }
        if($type=="return"){
            $using_stock = $this->products_model->get_enter_using_stock_by_id($id);
            $ref_no=$using_stock->reference_no;
            $using_id=$using_stock->id;
            $stock_item=$this->products_model->get_enter_using_stock_item_by_using_id($using_id);
            $this->data['info']         = $this->products_model->get_enter_using_stock_info();
            $this->data['biller']       = $this->products_model->getUsingStockProject($id);
             $this->data['using_stock'] = $using_stock; 
             $this->data['stock_item'] = $stock_item; 
            $this->load->view($this->theme.'stock_using/print_enter_using_stock_return',$this->data);
        }
    }

    public function print_using_stock($ref)
    {
        $r_r            =   str_replace("-","/",$ref);
        $using_stock    =   $this->products_model->get_enter_using_stock_by_ref($r_r);
        $stock_item     =   $this->products_model->get_enter_using_stock_item_by_ref($r_r);
        $this->data['info'] = $this->products_model->get_enter_using_stock_info();
        $this->data['using_stock'] = $using_stock; 
        $this->data['stock_item'] = $stock_item;
        $this->data['biller'] =$this->products_model->getUsingStockProjectByRef($r_r);
        $this->data['au_info'] =$this->products_model->getAuInfoByref($r_r);

        $this->load->view($this->theme.'stock_using/print_using_stock',$this->data);
    }

    public function print_enter_using_stock_return($ref)
    {
        $r_r                        = str_replace("-","/",$ref);
        $using_stock                = $this->products_model->get_enter_using_stock_by_ref($r_r);
        $stock_item                 = $this->products_model->get_enter_using_stock_item_by_ref($r_r);
        $this->data['biller']       = $this->site->getCompanyByID($using_stock->biller_id); 
        $this->data['authorize']    = $this->site->getUser($using_stock->authorize_id); 
        $this->data['using_stock']  = $using_stock; 
        $this->data['stock_item']   = $stock_item; 
        $this->load->view($this->theme.'stock_using/print_enter_using_stock_return',$this->data);
    }

    public function add_using_stock($purchase_id = null, $id = NULL)
    {
        $this->bpas->checkPermissions('add_using_stock');
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required|is_unique[enter_using_stock.reference_no]');
        $this->form_validation->set_rules('from_location', lang("from_location"), 'required');
        // var_dump($this->input->post('add_using_stock'));
        // exit();
        if ($this->form_validation->run() == true) {
        
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld($this->input->post('date'));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $biller_id       = $this->input->post('biller');          
            $project_id      = $this->input->post('project');
            $authorize       = $this->input->post('authorize_id');
            $reference_no    = $this->input->post('reference_no');
            $employee_id     = $this->input->post('employee_id');
            $customer_id     = $this->input->post('customer');
            $plan            = $this->input->post('plan');
            $address         = $this->input->post('address');
            $warehouse_id    = $this->input->post('from_location');
            $biller_id       = $this->input->post('biller');
            $note            = $this->input->post('note');
            $total_item_cost = 0;
            $stockmoves      = null;
            //start using item
            $i = sizeof($_POST['product_id']);
            for ($r = 0; $r < $i; $r++) {
                $product_id   = $_POST['product_id'][$r];
                $product_code = $_POST['item_code'][$r];
                $product_name = $_POST['name'][$r];
                $product_cost = $_POST['cost'][$r];
                $description  = $_POST['description'][$r];
                $qty_use      = $_POST['qty_use'][$r];
                $unit_id      = $_POST['unit'][$r];
                $expiry       = isset($_POST['exp'][$r]) && !empty($_POST['exp'][$r]) && $_POST['exp'][$r] != 'false' && $_POST['exp'][$r] != 'undefined' && $_POST['exp'][$r] != 'null' && $_POST['exp'][$r] != 'NULL' && $_POST['exp'][$r] != '00/00/0000' && $_POST['exp'][$r] != '' ? $_POST['exp'][$r] : null; 
                $qty_balance  = $qty_use;
                $total_cost   = $product_cost * $qty_balance; 
                $option_id    = null;
                $variant      = $this->site->getProductVariantByID($product_id, $unit_id);
                if ($variant) {
                    $option_id = (is_numeric($variant->id) ? $variant->id : null);
                }
                if ($qty_balance == 0) {
                    $this->session->set_flashdata('error', $this->lang->line("unexpected_value") );
                    redirect($_SERVER["HTTP_REFERER"]);
                } 
                $product_details = $this->site->getProductByID($product_id);
                $unit            = $this->site->getProductUnit($product_id, $unit_id);
                $real_unit_cost  = $this->site->getAVGCost($product_details->id, $date);
                if ($this->Settings->accounting_method == '0') {
                    $costs = $this->site->getFifoCost($product_details->id, $qty_balance, $stockmoves);
                } else if ($this->Settings->accounting_method == '1') {
                    $costs = $this->site->getLifoCost($product_details->id, $qty_balance, $stockmoves);
                } else if ($this->Settings->accounting_method == '3') {
                    $costs = $this->site->getProductMethod($product_details->id, $qty_balance, $stockmoves);
                } else {
                    $costs = false;
                }
                if ($costs) {
                    $productAcc = $this->site->getProductAccByProductId($product_id);
                    foreach ($costs as $cost_item) { 
                        $stockmoves[] = array(
                            'transaction'    => 'UsingStock',
                            'product_id'     => $product_details->id,
                            'product_type'   => $product_details->type,
                            'product_code'   => $product_details->code,
                            'product_name'   => $product_details->name,
                            'option_id'      => $option_id,
                            'quantity'       => $cost_item['quantity'] * (-1),
                            'unit_quantity'  => $unit->unit_qty,
                            'unit_code'      => $unit->code,
                            'unit_id'        => $unit_id,
                            'warehouse_id'   => $warehouse_id,
                            'expiry'         => $expiry,
                            'date'           => $date,
                            'real_unit_cost' => $cost_item['cost'],
                            'serial_no'      => null,
                            'reference_no'   => $reference_no,
                            'user_id'        => $this->session->userdata('user_id'),
                        );
                        if ($this->Settings->module_account == 1) {        
                            $accTrans[] = array(
                                'tran_type'    => 'UsingStock',
                                'tran_date'    => $date,
                                'reference_no' => $reference_no,
                                'account_code' => $this->accounting_setting->default_stock,
                                'amount'       => -($cost_item['cost'] * abs($cost_item['quantity'])),
                                'narrative'    => 'Product Code: '.$product_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
                                'description'  => $note,
                                'biller_id'    => $biller_id,
                                'project_id'   => $project_id,
                                'customer_id'  => $customer_id,
                                'created_by'   => $this->session->userdata('user_id'),
                            );
                            $accTrans[] = array(
                                'tran_type'    => 'UsingStock',
                                'tran_date'    => $date,
                                'reference_no' => $reference_no,
                                'account_code' => $this->accounting_setting->default_stock_using,
                                'amount'       => ($cost_item['cost'] * abs($cost_item['quantity'])),
                                'narrative'    => 'Product Code: '.$product_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
                                'description'  => $note,
                                'biller_id'    => $biller_id,
                                'project_id'   => $project_id,
                                'customer_id'  => $customer_id,
                                'created_by'   => $this->session->userdata('user_id'),
                            );
                        }
                    }
                } else {
                    $stockmoves[] = array(
                        'transaction'    => 'UsingStock',
                        'product_id'     => $product_details->id,
                        'product_type'   => $product_details->type,
                        'product_code'   => $product_details->code,
                        'product_name'   => $product_details->name,
                        'option_id'      => $option_id,
                        'quantity'       => (-1) * $qty_balance,
                        'unit_quantity'  => $unit->unit_qty,
                        'unit_code'      => $unit->code,
                        'unit_id'        => $unit_id,
                        'warehouse_id'   => $warehouse_id,
                        'expiry'         => $expiry,
                        'date'           => $date,
                        'real_unit_cost' => $product_details->cost,
                        'serial_no'      => null,
                        'reference_no'   => $reference_no,
                        'user_id'        => $this->session->userdata('user_id'),
                    );
                    if ($this->Settings->module_account == 1) { 
                        $productAcc = $this->site->getProductAccByProductId($product_details->id);
                        $accTrans[] = array(
                            'tran_type'    => 'UsingStock',
                            'tran_date'    => $date,
                            'reference_no' => $reference_no,
                            'account_code' => $this->accounting_setting->default_stock,
                            'amount'       => -($product_details->cost * abs($qty_balance)),
                            'narrative'    => 'Product Code: '.$product_code.'#'.'Qty: '.$qty_balance.'#'.'Cost: '.$product_details->cost,
                            'description'  => $note,
                            'biller_id'    => $biller_id,
                            'project_id'   => $project_id,
                            'customer_id'  => $customer_id,
                            'created_by'   => $this->session->userdata('user_id'),
                        );
                        $accTrans[] = array(
                            'tran_type'    => 'UsingStock',
                            'tran_date'    => $date,
                            'reference_no' => $reference_no,
                            'account_code' => $this->accounting_setting->default_stock_using,
                            'amount'       => ($product_details->cost * abs($qty_balance)),
                            'narrative'    => 'Product Code: '.$product_code.'#'.'Qty: '.$qty_balance.'#'.'Cost: '.$product_details->cost,
                            'description'  => $note,
                            'biller_id'    => $biller_id,
                            'project_id'   => $project_id,
                            'customer_id'  => $customer_id,
                            'created_by'   => $this->session->userdata('user_id'),
                        );
                    }
                }
                $products[] = array(
                    'product_id'      => $product_id,
                    'code'            => $product_code,
                    'product_name'    => $product_name,
                    'description'     => $description,
                    'qty_use'         => $qty_balance,
                    'qty_by_unit'     => $qty_use,
                    'product_unit_id' => $unit_id,
                    'unit'            => $unit->name,
                    'expiry'          => $expiry,
                    'warehouse_id'    => $warehouse_id,
                    'cost'            => $product_cost,
                    'reference_no'    => $reference_no,
                    'option_id'       => is_numeric($option_id) ? $option_id : null
                );
                $total_item_cost += $total_cost;
            }

            if (empty($products)) {
                $this->session->set_flashdata('error', $this->lang->line("no_data_select") );
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                krsort($products);
            }
            //end using item
            

            //start finish data
            $j = sizeof($_POST['product_id_finish']);
            for ($v = 0; $v < $j; $v++) {
                $product_id_finish = $_POST['product_id_finish'][$v];
                $product_code_finish = $_POST['item_code_finish'][$v];
                $product_name_finish = $_POST['name_finish'][$v];
                $product_cost_finish = $_POST['cost_finish'][$v];
                $description_finish  = $_POST['description_finish'][$v];
                $qty_finish      = $_POST['qty_finish'][$v];
                $unit_id_finish      = $_POST['unit_finish'][$v];
                $expiry_finish       = isset($_POST['exp_finish'][$v]) && !empty($_POST['exp_finish'][$v]) && $_POST['exp_finish'][$v] != 'false' && $_POST['exp_finish'][$v] != 'undefined' && $_POST['exp_finish'][$v] != 'null' && $_POST['exp_finish'][$v] != 'NULL' && $_POST['exp_finish'][$v] != '00/00/0000' && $_POST['exp_finish'][$v] != '' ? $_POST['exp_finish'][$v] : null;
                $qty_balance_finish  = $qty_finish;
                $total_cost_finish   = $product_cost_finish * $qty_balance_finish;
                $option_id_finish    = null;
                $variant_finish      = $this->site->getProductVariantByID($product_id_finish, $unit_id_finish);
                if ($variant_finish) {
                    $option_id_finish = (is_numeric($variant_finish->id) ? $variant_finish->id : null);
                }
                // if ($qty_balance_finish == 0) {
                //     $this->session->set_flashdata('error', $this->lang->line("unexpected_value") );
                //     redirect($_SERVER["HTTP_REFERER"]);
                // }
                $product_details_finish = $this->site->getProductByID($product_id_finish);
                $unit_finish            = $this->site->getProductUnit($product_id_finish, $unit_id_finish);
                $real_unit_cost_finish  = $this->site->getAVGCost($product_details_finish->id, $date);
                // $product_id   = $_POST['product_id'][$r];
                // $product_code = $_POST['item_code'][$r];
                // $product_name = $_POST['name'][$r];
                // $product_cost = $_POST['cost'][$r];
                // $description  = $_POST['description'][$r];
                // $qty_use      = $_POST['qty_use'][$r];
                // $unit_id      = $_POST['unit'][$r];
                // $expiry       = isset($_POST['exp'][$r]) && !empty($_POST['exp'][$r]) && $_POST['exp'][$r] != 'false' && $_POST['exp'][$r] != 'undefined' && $_POST['exp'][$r] != 'null' && $_POST['exp'][$r] != 'NULL' && $_POST['exp'][$r] != '00/00/0000' && $_POST['exp'][$r] != '' ? $_POST['exp'][$r] : null; 
                // $qty_balance  = $qty_use;
                // $total_cost   = $product_cost * $qty_balance; 
                // $option_id    = null;
                // $variant      = $this->site->getProductVariantByID($product_id, $unit_id);
                // if ($variant) {
                //     $option_id = (is_numeric($variant->id) ? $variant->id : null);
                // }
                // if ($qty_balance == 0) {
                //     $this->session->set_flashdata('error', $this->lang->line("unexpected_value") );
                //     redirect($_SERVER["HTTP_REFERER"]);
                // } 
                // $product_details = $this->site->getProductByID($product_id);
                // $unit            = $this->site->getProductUnit($product_id, $unit_id);
                $real_unit_cost  = $this->site->getAVGCost($product_details->id, $date);
                // if ($this->Settings->accounting_method == '0') {
                //     $costs = $this->site->getFifoCost($product_details->id, $qty_balance, $stockmoves);
                // } else if ($this->Settings->accounting_method == '1') {
                //     $costs = $this->site->getLifoCost($product_details->id, $qty_balance, $stockmoves);
                // } else if ($this->Settings->accounting_method == '3') {
                //     $costs = $this->site->getProductMethod($product_details->id, $qty_balance, $stockmoves);
                // } else {
                //     $costs = false;
                // }
                // if ($costs) {
                //     $productAcc = $this->site->getProductAccByProductId($product_id);
                //     foreach ($costs as $cost_item) { 
                //         $stockmoves[] = array(
                //             'transaction'    => 'UsingStock',
                //             'product_id'     => $product_details->id,
                //             'product_type'   => $product_details->type,
                //             'product_code'   => $product_details->code,
                //             'product_name'   => $product_details->name,
                //             'option_id'      => $option_id,
                //             'quantity'       => $cost_item['quantity'] * (-1),
                //             'unit_quantity'  => $unit->unit_qty,
                //             'unit_code'      => $unit->code,
                //             'unit_id'        => $unit_id,
                //             'warehouse_id'   => $warehouse_id,
                //             'expiry'         => $expiry,
                //             'date'           => $date,
                //             'real_unit_cost' => $cost_item['cost'],
                //             'serial_no'      => null,
                //             'reference_no'   => $reference_no,
                //             'user_id'        => $this->session->userdata('user_id'),
                //         );
                //         if ($this->Settings->module_account == 1) {        
                //             $accTrans[] = array(
                //                 'tran_type'    => 'UsingStock',
                //                 'tran_date'    => $date,
                //                 'reference_no' => $reference_no,
                //                 'account_code' => $this->accounting_setting->default_stock,
                //                 'amount'       => -($cost_item['cost'] * abs($cost_item['quantity'])),
                //                 'narrative'    => 'Product Code: '.$product_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
                //                 'description'  => $note,
                //                 'biller_id'    => $biller_id,
                //                 'project_id'   => $project_id,
                //                 'customer_id'  => $customer_id,
                //                 'created_by'   => $this->session->userdata('user_id'),
                //             );
                //             $accTrans[] = array(
                //                 'tran_type'    => 'UsingStock',
                //                 'tran_date'    => $date,
                //                 'reference_no' => $reference_no,
                //                 'account_code' => $this->accounting_setting->default_stock_using,
                //                 'amount'       => ($cost_item['cost'] * abs($cost_item['quantity'])),
                //                 'narrative'    => 'Product Code: '.$product_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
                //                 'description'  => $note,
                //                 'biller_id'    => $biller_id,
                //                 'project_id'   => $project_id,
                //                 'customer_id'  => $customer_id,
                //                 'created_by'   => $this->session->userdata('user_id'),
                //             );
                //         }
                //     }
                // } else {
                //     $stockmoves[] = array(
                //         'transaction'    => 'UsingStock',
                //         'product_id'     => $product_details->id,
                //         'product_type'   => $product_details->type,
                //         'product_code'   => $product_details->code,
                //         'product_name'   => $product_details->name,
                //         'option_id'      => $option_id,
                //         'quantity'       => (-1) * $qty_balance,
                //         'unit_quantity'  => $unit->unit_qty,
                //         'unit_code'      => $unit->code,
                //         'unit_id'        => $unit_id,
                //         'warehouse_id'   => $warehouse_id,
                //         'expiry'         => $expiry,
                //         'date'           => $date,
                //         'real_unit_cost' => $product_details->cost,
                //         'serial_no'      => null,
                //         'reference_no'   => $reference_no,
                //         'user_id'        => $this->session->userdata('user_id'),
                //     );
                //     if ($this->Settings->module_account == 1) { 
                //         $productAcc = $this->site->getProductAccByProductId($product_details->id);
                //         $accTrans[] = array(
                //             'tran_type'    => 'UsingStock',
                //             'tran_date'    => $date,
                //             'reference_no' => $reference_no,
                //             'account_code' => $this->accounting_setting->default_stock,
                //             'amount'       => -($product_details->cost * abs($qty_balance)),
                //             'narrative'    => 'Product Code: '.$product_code.'#'.'Qty: '.$qty_balance.'#'.'Cost: '.$product_details->cost,
                //             'description'  => $note,
                //             'biller_id'    => $biller_id,
                //             'project_id'   => $project_id,
                //             'customer_id'  => $customer_id,
                //             'created_by'   => $this->session->userdata('user_id'),
                //         );
                //         $accTrans[] = array(
                //             'tran_type'    => 'UsingStock',
                //             'tran_date'    => $date,
                //             'reference_no' => $reference_no,
                //             'account_code' => $this->accounting_setting->default_stock_using,
                //             'amount'       => ($product_details->cost * abs($qty_balance)),
                //             'narrative'    => 'Product Code: '.$product_code.'#'.'Qty: '.$qty_balance.'#'.'Cost: '.$product_details->cost,
                //             'description'  => $note,
                //             'biller_id'    => $biller_id,
                //             'project_id'   => $project_id,
                //             'customer_id'  => $customer_id,
                //             'created_by'   => $this->session->userdata('user_id'),
                //         );
                //     }
                // }
                $products_finish[] = array(
                    'product_id'      => $product_id_finish,
                    'code'            => $product_code_finish,
                    'product_name'    => $product_name_finish,
                    'description'     => $description_finish,
                    'qty_use'         => $qty_balance_finish,
                    'qty_by_unit'     => $qty_finish,
                    'product_unit_id' => $unit_id_finish,
                    'unit'            => $unit_finish->name,
                    'expiry'          => $expiry_finish,
                    'warehouse_id'    => $warehouse_id,
                    'cost'            => $product_cost_finish,
                    'reference_no'    => $reference_no,
                    'option_id'       => is_numeric($option_id_finish) ? $option_id_finish : null
                );
                // var_dump($products_finish);
                // exit();
                // $products[] = array(
                //     'product_id'      => $product_id,
                //     'code'            => $product_code,
                //     'product_name'    => $product_name,
                //     'description'     => $description,
                //     'qty_use'         => $qty_balance,
                //     'qty_by_unit'     => $qty_use,
                //     'product_unit_id' => $unit_id,
                //     'unit'            => $unit->name,
                //     'expiry'          => $expiry,
                //     'warehouse_id'    => $warehouse_id,
                //     'cost'            => $product_cost,
                //     'reference_no'    => $reference_no,
                //     'option_id'       => is_numeric($option_id) ? $option_id : null
                // );
                // $total_item_cost += $total_cost;
            }
            if (empty($products_finish)) {
                $this->session->set_flashdata('error', $this->lang->line("no_data_select") );
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                krsort($products_finish);
            }

            //end finish data

            $data = array(
                'date'         => $date,
                'reference_no' => $reference_no,
                'warehouse_id' => $warehouse_id,
                'authorize_id' => $authorize,
                'employee_id'  => $employee_id,
                'customer_id'  => $customer_id,
                'biller_id'    => $biller_id,
                'note'         => $note,
                'create_by'    => $this->session->userdata('user_id'),
                'type'         => 'use',
                'total_cost'   => $total_item_cost,
                'plan_id'      => is_numeric($plan) ? $plan : null,
                'address_id'   => is_numeric($address) ? $address : null,
            ); 
        }else if ($this->input->post('add_using_stock')) {
            $error = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->session->set_flashdata('error', $error);
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() && $this->products_model->insert_enter_using_stock($data, $products, $products_finish, $stockmoves, $accTrans)) {
            $this->session->set_userdata('remove_usitem', 1);
            $this->session->set_flashdata('message', lang('enter_using_stock_added.'));
            admin_redirect('products/using_stock');
        } else {
            $data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['getExpense']   = $this->products_model->getAllExpenseCategory();
            $this->data['getGLChart']   = $this->products_model->getGLChart(); 
            $this->data['AllUsers']     = $this->site->getAllUsers();
            $this->data['CurrentUser']  = $this->site->getUser();
            $this->data['setting']      = $this->site->get_setting();
            $this->data['all_unit']     = $this->site->getUnits();
            $this->data['employees']    = $this->site->getAllEmployee();
            $this->data['product']      =  $this->products_model->getProductName_code();
            $this->data['productJSON']  = json_encode($this->data['product']); 
            // $this->data['reference']  = $this->site->getReference('es');
            if ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) {
                $biller_id = $this->site->get_setting()->default_biller;
            } else {
                $biller_id = $this->session->userdata('biller_id');    
            }
            $this->data['biller_id']    = $biller_id;
            $this->data['reference']    = $this->site->getReference('es', $biller_id);
            if ($purchase_id) {
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
            $this->data['plan']             = $this->products_model->getPlan();
            $this->data['modal_js']         = $this->site->modal_js();
            $this->data['positions']        = $this->products_model->getAllPositionData();
            
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('add_stock_using')));
            $meta = array('page_title' => lang('add_stock_using'), 'bc' => $bc);
            $this->page_construct('stock_using/add', $meta, $this->data);
        }
    }

    public function edit_using_stock($id = NULL, $type = NULL)
    {
        $this->bpas->checkPermissions('edit_using_stock');
        $this->form_validation->set_rules('from_location', lang("from_location"), 'required');
        if ($this->form_validation->run() == true){
            $stock_id = $this->input->post('stock_id');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld($this->input->post('date'));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $biller_id       = $this->input->post('biller');          
            $project_id      = $this->input->post('project');
            $authorize       = $this->input->post('authorize_id');
            $account         = $this->input->post('account');
            $reference_no    = $this->input->post('reference_no');
            $employee_id     = $this->input->post('employee_id');
            $customer_id     = $this->input->post('customer');
            $plan            = $this->input->post('plan');
            $address         = $this->input->post('address');
            $warehouse_id    = $this->input->post('from_location');
            $note            = $this->input->post('note');
            $total_item_cost = 0;
            $stockmoves      = null;
            $i = sizeof($_POST['product_id']);
            for ($r = 0; $r < $i; $r++) {
                $product_id   = $_POST['product_id'][$r];
                $product_code = $_POST['item_code'][$r];
                $product_name = $_POST['name'][$r];
                $product_cost = $_POST['cost'][$r];
                $description  = $_POST['description'][$r];
                $qty_use      = $_POST['qty_use'][$r];
                $qty_old      = $_POST['qty_old'][$r];
                $unit_id      = $_POST['unit'][$r];
                $expiry       = isset($_POST['exp'][$r]) && !empty($_POST['exp'][$r]) && $_POST['exp'][$r] != 'false' && $_POST['exp'][$r] != 'undefined' && $_POST['exp'][$r] != 'null' && $_POST['exp'][$r] != 'NULL' && $_POST['exp'][$r] != '00/00/0000' && $_POST['exp'][$r] != '' ? $_POST['exp'][$r] : null; 
                $qty_balance  = $qty_use;
                $total_cost   = $product_cost * $qty_balance; 
                $option_id    = null;
                $variant      = $this->site->getProductVariantByID($product_id, $unit_id);
                if ($variant) {
                    $option_id = (is_numeric($variant->id) ? $variant->id : null);
                }
                if ($qty_balance == 0) {
                    $this->session->set_flashdata('error', $this->lang->line("unexpected_value") );
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $product_details = $this->site->getProductByID($product_id);
                $unit            = $this->site->getProductUnit($product_id, $unit_id);
                $real_unit_cost  = $this->site->getAVGCost($product_details->id, $date, "UsingStock", $id);
				$product_details->cost = $real_unit_cost;
                if ($this->Settings->accounting_method == '0') {
                    $costs = $this->site->getFifoCost($product_details->id, $qty_balance, $stockmoves, 'UsingStock', $id);
                } else if ($this->Settings->accounting_method == '1') {
                    $costs = $this->site->getLifoCost($product_details->id, $qty_balance, $stockmoves, 'UsingStock', $id);
                } else if ($this->Settings->accounting_method == '3') {
                    $costs = $this->site->getProductMethod($product_details->id, $qty_balance, $stockmoves, 'UsingStock', $id);
                } else {
                    $costs = false;
                }
                if ($costs) {
                    $productAcc = $this->site->getProductAccByProductId($product_details->id);
                    foreach ($costs as $cost_item) {
                        $stockmoves[] = array(
                            'transaction'    => 'UsingStock',
                            'transaction_id' => $id,
                            'product_id'     => $product_details->id,
                            'product_type'   => $product_details->type,
                            'product_code'   => $product_details->code,
                            'product_name'   => $product_details->name,
                            'option_id'      => $option_id,
                            'quantity'       => $cost_item['quantity'] * (-1),
                            'unit_quantity'  => $unit->unit_qty,
                            'unit_code'      => $unit->code,
                            'unit_id'        => $unit_id,
                            'warehouse_id'   => $warehouse_id,
                            'expiry'         => $expiry,
                            'date'           => $date,
                            'real_unit_cost' => $cost_item['cost'],
                            'serial_no'      => null,
                            'reference_no'   => $reference_no,
                            'user_id'        => $this->session->userdata('user_id'),
                        );
                        if ($this->Settings->module_account == 1) { 
                            $accTrans[] = array(
                                'tran_type'    => 'UsingStock',
                                'tran_no'      => $id,
                                'tran_date'    => $date,
                                'reference_no' => $reference_no,
                                'account_code' => $this->accounting_setting->default_stock,
                                'amount'       => -($cost_item['cost'] * abs($cost_item['quantity'])),
                                'narrative'    => 'Product Code: '.$product_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
                                'description'  => $note,
                                'biller_id'    => $biller_id,
                                'project_id'   => $project_id,
                                'customer_id'  => $customer_id,
                                'created_by'   => $this->session->userdata('user_id'),
                            );
                            $accTrans[] = array(
                                'tran_type'    => 'UsingStock',
                                'tran_no'      => $id,
                                'tran_date'    => $date,
                                'reference_no' => $reference_no,
                                'account_code' => $this->accounting_setting->default_stock_using,
                                'amount'       => ($cost_item['cost'] * abs($cost_item['quantity'])),
                                'narrative'    => 'Product Code: '.$product_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
                                'description'  => $note,
                                'biller_id'    => $biller_id,
                                'project_id'   => $project_id,
                                'customer_id'  => $customer_id,
                                'created_by'   => $this->session->userdata('user_id'),
                            );
                        }
                    }
                } else {  
                    $stockmoves[] = array(
                        'transaction'    => 'UsingStock',
                        'transaction_id' => $id,
                        'product_id'     => $product_details->id,
                        'product_type'   => $product_details->type,
                        'product_code'   => $product_details->code,
                        'product_name'   => $product_details->name,
                        'option_id'      => $option_id,
                        'quantity'       => (-1)*$qty_balance,
                        'unit_quantity'  => $unit->unit_qty,
                        'unit_code'      => $unit->code,
                        'unit_id'        => $unit_id,
                        'warehouse_id'   => $warehouse_id,
                        'expiry'         => $expiry,
                        'date'           => $date,
                        'real_unit_cost' => $product_details->cost,
                        'serial_no'      => null,
                        'reference_no'   => $reference_no,
                        'user_id'        => $this->session->userdata('user_id'),
                    );
                    if ($this->Settings->module_account == 1) {	
                        $productAcc = $this->site->getProductAccByProductId($product_details->id);
                        $accTrans[] = array(
                            'tran_type'    => 'UsingStock',
                            'tran_no'      => $id,
                            'tran_date'    => $date,
                            'reference_no' => $reference_no,
                            'account_code' => $this->accounting_setting->default_stock,
                            'amount'       => -($product_details->cost * abs($qty_balance)),
                            'narrative'    => 'Product Code: '.$product_code.'#'.'Qty: '.$qty_balance.'#'.'Cost: '.$product_details->cost,
                            'description'  => $note,
                            'biller_id'    => $biller_id,
                            'project_id'   => $project_id,
                            'customer_id'  => $customer_id,
                            'created_by'   => $this->session->userdata('user_id'),
                        );
                        $accTrans[] = array(
                            'tran_type'    => 'UsingStock',
                            'tran_no'      => $id,
                            'tran_date'    => $date,
                            'reference_no' => $reference_no,
                            'account_code' => $this->accounting_setting->default_stock_using,
                            'amount'       => ($product_details->cost * abs($qty_balance)),
                            'narrative'    => 'Product Code: '.$product_code.'#'.'Qty: '.$qty_balance.'#'.'Cost: '.$product_details->cost,
                            'description'  => $note,
                            'biller_id'    => $biller_id,
                            'project_id'   => $project_id,
                            'customer_id'  => $customer_id,
                            'created_by'   => $this->session->userdata('user_id'),
                        );   
                    }
                }
                $products[] = array(
                    'using_stock_id'  => $stock_id,
                    'product_id'      => $product_id,
                    'code'            => $product_code,
                    'product_name'    => $product_name,
                    'description'     => $description,
                    'qty_use'         => $qty_balance,
                    'qty_by_unit'     => $qty_use,
                    'product_unit_id' => $unit_id,
                    'unit'            => $unit->name,
                    'expiry'          => $expiry,
                    'warehouse_id'    => $warehouse_id,
                    'cost'            => $product_cost,
                    'reference_no'    => $reference_no,
                    'option_id'       => is_numeric($option_id) ? $option_id : null
                );
                $total_item_cost += $total_cost;
            }
            if (empty($products)) {
                $this->session->set_flashdata('error', $this->lang->line("no_data_select") );
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                krsort($products);
            }
            $data = array(
                'date'          => $date,
                'reference_no'  => $reference_no,
                'warehouse_id'  => $warehouse_id,
                'authorize_id'  => $authorize,
                'employee_id'   => $employee_id,
                'customer_id'   => $customer_id,
                'biller_id'     => $biller_id,
                'account'       => $account,
                'note'          => $note,
                'create_by'     => $this->session->userdata('user_id'),
                'type'          => 'use',
                'total_cost'    => $total_item_cost,
                'plan_id'       => is_numeric($plan) ? $plan : null,
                'address_id'    => is_numeric($address) ? $address : null,
            ); 
        }
        if ($this->form_validation->run() && true && $this->products_model->update_enter_using_stock($stock_id, $data, $products, $stockmoves, $accTrans)) {
            $this->session->set_userdata('remove_usitem', 1);
            $this->session->set_flashdata(lang('enter_using_stock_updated.'));
            admin_redirect('products/using_stock');
        } else {
            $data['error']             = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $product                   = $this->products_model->getProductName_code();
            $this->data['warehouses']  = $this->site->getAllWarehouses();
            $this->data['getGLChart']  = $this->products_model->getGLChart();
            $this->data['AllUsers']    = $this->site->getAllUsers();
            $this->data['CurrentUser'] = $this->site->getUser();
            $this->data['setting']     = $this->site->get_setting();
            $this->data['biller']      = $this->site->getAllBiller();
            $this->data['all_unit']    = $this->site->getUnits();
            $this->data['employees']   = $this->site->getAllEmployee();
            $this->data['product']     = $product; 
            $this->data['productJSON'] = json_encode($product); 
            $getUsingStock             = $this->products_model->getUsingStockById($id);
            $reference_no              = $getUsingStock->reference_no;
            $date                      = $getUsingStock->date;
            $wh_id                     = $getUsingStock->warehouse_id;
            $getUsingStockItem         = $this->products_model->getUsingStockItemsByRef($reference_no);
            $c  = rand(100000, 9999999);
            $pr = [];
            foreach ($getUsingStockItem as $row) {
                $row->project_qty = 0;
                if ($getUsingStock->plan_id) {
                    $project_item = $this->products_model->getPlanUsing($getUsingStock->plan_id, $row->product_code, $getUsingStock->address_id);
                    if ($project_item) {
                        $row->project_qty = ($project_item->quantity_balance - $project_item->using_qty + $project_item->reutn_using_qty);
                    }
                }
                $row->qty_old   = $row->qty_use;
                $row->qty_use   = $row->qty_use; 
                $row->have_plan = isset($project_item) ? 1 : 0;
                $option_unit    = $this->products_model->getUnitAndVaraintByProductId($row->id);
                $expiry_date    = $this->site->getStockMovementByProductID($row->id, $wh_id);
                $ri = $this->Settings->item_addition ? $row->id : $c;
                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->product_code . ")", 'row' => $row, 'option_unit' => $option_unit, 'project_qty' => $row->project_qty, 'stock_item' => $row->e_id, 'expiry_date' => $expiry_date);
                $c++;
            }
            $this->data['items']       = json_encode($pr);
            $this->data['refer']       = $reference_no;
            $this->data['date']        = $this->bpas->hrsd($date);
            $this->data['where']       = $wh_id;
            $this->data['using_stock'] = $getUsingStock;
            $this->data['plan']        = $this->products_model->getPlan();
            $this->data['modal_js']    = $this->site->modal_js();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('edit_stock_using')));
            $meta = array('page_title' => lang('edit_stock_using'), 'bc' => $bc);
            $this->page_construct('stock_using/edit', $meta, $this->data);
        }
    }

    public function return_using_stock($id)
    {
        $this->bpas->checkPermissions('using_stock', null, 'products');
        $this->form_validation->set_rules('from_location', lang("from_location"), 'required');
        $this->form_validation->set_rules('return_reference_no', lang("return_reference_no"), 'required');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld($this->input->post('date'));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $project_id      = $this->input->post('project');
            $authorize       = $this->input->post('authorize_id');
            $account         = $this->input->post('account');
            $reference_no    = $this->input->post('reference_no');
            $return_ref      = $this->input->post('return_reference_no');
            $employee_id     = $this->input->post('employee_id');
            $customer_id     = $this->input->post('customer');
            $plan            = $this->input->post('plan');
            $address         = $this->input->post('address');
            $warehouse_id    = $this->input->post('from_location');
            $biller_id       = $this->input->post('biller');
            $note            = $this->input->post('note');
            $total_item_cost = 0;
            //start using stock data
            $i = sizeof($_POST['product_id']);
            for ($r = 0; $r < $i; $r++) {
                $product_id   = $_POST['product_id'][$r];
                $product_code = $_POST['item_code'][$r];
                $product_name = $_POST['name'][$r];
                $product_cost = $_POST['cost'][$r];
                $description  = $_POST['description'][$r];
                $qty_use      = $_POST['qty_use'][$r];
                $qty_old      = $_POST['qty_old'][$r];
                $unit_id      = $_POST['unit'][$r];
                $expiry       = isset($_POST['exp'][$r]) && !empty($_POST['exp'][$r]) && $_POST['exp'][$r] != 'false' && $_POST['exp'][$r] != 'undefined' && $_POST['exp'][$r] != 'null' && $_POST['exp'][$r] != 'NULL' && $_POST['exp'][$r] != '00/00/0000' && $_POST['exp'][$r] != '' ? $_POST['exp'][$r] : null; 
                $qty_balance  = $qty_use;
                $option_id    = null;
                $total_cost   = $product_cost * $qty_balance; 
                $variant      = $this->site->getProductVariantByID($product_id, $unit_id);
                if ($variant) {
                    $option_id = (is_numeric($variant->id) ? $variant->id : null);   
                }
                // if ($qty_balance == 0) {
                //     $this->session->set_flashdata('error', $this->lang->line("unexpected_value") );
                //     redirect($_SERVER["HTTP_REFERER"]);
                // } 
                $product_details = $this->site->getProductByID($product_id);
                $unit            = $this->site->getProductUnit($product_id, $unit_id);
                $stockmoves[] = array(
                    'transaction'    => 'UsingStock',
                    'product_id'     => $product_id,
                    'product_type'   => $product_details->type,
                    'product_code'   => $product_details->code,
                    'product_name'   => $product_details->name,
                    'option_id'      => $option_id,
                    'quantity'       => $qty_balance,
                    'unit_quantity'  => $unit->unit_qty,
                    'unit_code'      => $unit->code,
                    'unit_id'        => $unit_id,
                    'warehouse_id'   => $warehouse_id,
                    'expiry'         => $expiry,
                    'date'           => $date,
                    'real_unit_cost' => $product_cost,
                    'serial_no'      => null,
                    'reference_no'   => $return_ref,
                    'user_id'        => $this->session->userdata('user_id'),
                );
                $products[] = array(
                    'product_id'      => $product_id,
                    'code'            => $product_code,
                    'product_name'    => $product_name,
                    'description'     => $description,
                    'qty_use'         => $qty_balance,
                    'qty_by_unit'     => $qty_use,
                    'product_unit_id' => $unit_id,
                    'unit'            => $unit->name,
                    'expiry'          => $expiry,
                    'warehouse_id'    => $warehouse_id,
                    'cost'            => $product_cost,
                    'reference_no'    => $return_ref,
                    'option_id'       => is_numeric($option_id) ? $option_id : null
                );
                $total_item_cost += $total_cost;
                if ($this->Settings->module_account == 1) {
                    $accTrans[] = array(
                        'tran_type'     => 'UsingStock',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $this->accounting_setting->default_stock,
                        'amount'        => ($product_cost * abs($qty_use)),
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
                        'amount'        => -($product_cost * abs($qty_use)),
                        'narrative'     => 'Product Code: '.$product_code.'#'.'Qty: '.$qty_use.'#'.'Cost: '.$product_cost,
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                    );
                }
            }
            // if (empty($products)) {
            //     $this->session->set_flashdata('error', $this->lang->line("no_data_select") );
            //     redirect($_SERVER["HTTP_REFERER"]);
            // } else {
            //     krsort($products);
            // }
            //end using stock data

            //start finish data
            $j = sizeof($_POST['product_id_finish']);
            for ($v = 0; $v < $j; $v++) {
                $product_id_finish   = $_POST['product_id_finish'][$v];
                $product_code_finish = $_POST['item_code_finish'][$v];
                $product_name_finish = $_POST['name_finish'][$v];
                $product_cost_finish = $_POST['cost_finish'][$v];
                $description_finish  = $_POST['description_finish'][$v];
                $qty_use_finish      = $_POST['qty_finish'][$v];
                $qty_old_finish      = $_POST['qty_old_finish'][$v];
                $unit_id_finish      = $_POST['unit_finish'][$v];
                $expiry_finish       = isset($_POST['exp_finish'][$v]) && !empty($_POST['exp_finish'][$v]) && $_POST['exp_finish'][$v] != 'false' && $_POST['exp_finish'][$v] != 'undefined' && $_POST['exp_finish'][$v] != 'null' && $_POST['exp_finish'][$v] != 'NULL' && $_POST['exp_finish'][$v] != '00/00/0000' && $_POST['exp_finish'][$v] != '' ? $_POST['exp_finish'][$v] : null; 
                $qty_balance         = $qty_use_finish;
                $option_id_finish    = null;
                $total_cost          = $product_cost_finish * $qty_balance; 
                // if ($qty_balance == 0) {
                //     $this->session->set_flashdata('error', $this->lang->line("unexpected_value") );
                //     redirect($_SERVER["HTTP_REFERER"]);
                // } 
                $product_details     = $this->site->getProductByID($product_id_finish);
                $unit                = $this->site->getProductUnit($product_id_finish, $unit_id_finish);
                if ($this->Settings->accounting_method == '0') {
                    $costs = $this->site->getFifoCost($product_details->id, $qty_balance, null, 'FinishStock', null);
                } else if ($this->Settings->accounting_method == '1') {
                    $costs = $this->site->getLifoCost($product_details->id, $qty_balance, null, 'FinishStock', null);
                } else if ($this->Settings->accounting_method == '3') {
                    $costs = $this->site->getProductMethod($product_details->id, $qty_balance, null, 'FinishStock', null);
                } else {
                    $costs = false;
                }
                if ($costs) {
                    $productAcc = $this->site->getProductAccByProductId($product_details->id);
                    foreach ($costs as $cost_item) {
                        $stockmoves[] = array(
                            'transaction'    => 'FinishStock',
                            'product_id'     => $product_details->id,
                            'product_type'   => $product_details->type,
                            'product_code'   => $product_details->code,
                            'product_name'   => $product_details->name,
                            'option_id'      => $option_id_finish,
                            'quantity'       => $cost_item['quantity'],
                            'unit_quantity'  => $unit->unit_qty,
                            'unit_code'      => $unit->code,
                            'unit_id'        => $unit_id_finish,
                            'warehouse_id'   => $warehouse_id,
                            'expiry'         => $expiry_finish,
                            'date'           => $date,
                            'real_unit_cost' => $cost_item['cost'],
                            'serial_no'      => null,
                            'reference_no'   => $return_ref,
                            'user_id'        => $this->session->userdata('user_id'),
                        );
                        if ($this->Settings->module_account == 1) { 
                            $accTrans[] = array(
                                'tran_type'    => 'FinishStock',
                                'tran_date'    => $date,
                                'reference_no' => $return_ref,
                                'account_code' => $this->accounting_setting->default_stock,
                                'amount'       => ($cost_item['cost'] * abs($cost_item['quantity'])),
                                'narrative'    => 'Product Code: '.$product_code_finish.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
                                'description'  => $note,
                                'biller_id'    => $biller_id,
                                'project_id'   => $project_id,
                                'created_by'   => $this->session->userdata('user_id'),
                            );
                            $accTrans[] = array(
                                'tran_type'    => 'FinishStock',
                                'tran_date'    => $date,
                                'reference_no' => $return_ref,
                                'account_code' => $this->accounting_setting->default_stock_using,
                                'amount'       => -($cost_item['cost'] * abs($cost_item['quantity'])),
                                'narrative'    => 'Product Code: '.$product_code_finish.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
                                'description'  => $note,
                                'biller_id'    => $biller_id,
                                'project_id'   => $project_id,
                                'created_by'   => $this->session->userdata('user_id'),
                            );
                        }
                    }
                } else {
                    $stockmoves[] = array(
                        'transaction'    => 'FinishStock',
                        'product_id'     => $product_details->id,
                        'product_type'   => $product_details->type,
                        'product_code'   => $product_details->code,
                        'product_name'   => $product_details->name,
                        'option_id'      => $option_id_finish,
                        'quantity'       => $qty_balance,
                        'unit_quantity'  => $unit->unit_qty,
                        'unit_code'      => $unit->code,
                        'unit_id'        => $unit_id_finish,
                        'warehouse_id'   => $warehouse_id,
                        'expiry'         => $expiry_finish,
                        'date'           => $date,
                        'real_unit_cost' => $product_details->cost,
                        'serial_no'      => null,
                        'reference_no'   => $return_ref,
                        'user_id'        => $this->session->userdata('user_id'),
                    );
                    if ($this->Settings->module_account == 1) {	
                        $productAcc = $this->site->getProductAccByProductId($product_details->id);
                        $accTrans[] = array(
                            'tran_type'    => 'FinishStock',
                            'tran_date'    => $date,
                            'reference_no' => $return_ref,
                            'account_code' => $this->accounting_setting->default_stock,
                            'amount'       => ($product_details->cost * abs($qty_balance)),
                            'narrative'    => 'Product Code: '.$product_code_finish.'#'.'Qty: '.$qty_balance.'#'.'Cost: '.$product_details->cost,
                            'description'  => $note,
                            'biller_id'    => $biller_id,
                            'project_id'   => $project_id,
                            'created_by'   => $this->session->userdata('user_id'),
                        );
                        $accTrans[] = array(
                            'tran_type'    => 'FinishStock',
                            'tran_date'    => $date,
                            'reference_no' => $return_ref,
                            'account_code' => $this->accounting_setting->default_stock_using,
                            'amount'       => -($product_details->cost * abs($qty_balance)),
                            'narrative'    => 'Product Code: '.$product_code_finish.'#'.'Qty: '.$qty_balance.'#'.'Cost: '.$product_details->cost,
                            'description'  => $note,
                            'biller_id'    => $biller_id,
                            'project_id'   => $project_id,
                            'created_by'   => $this->session->userdata('user_id'),
                        ); 
                    }
                }
                $products[] = array(
                    'product_id'      => $product_id_finish,
                    'code'            => $product_code_finish,
                    'product_name'    => $product_name_finish,
                    'description'     => $description_finish,
                    'qty_use'         => $qty_balance,
                    'qty_by_unit'     => $qty_use_finish,
                    'product_unit_id' => $unit_id_finish,
                    'unit'            => $unit->name,
                    'expiry'          => $expiry_finish,
                    'warehouse_id'    => $warehouse_id,
                    'cost'            => $product_cost_finish,
                    'reference_no'    => $return_ref,
                    'option_id'       => is_numeric($option_id_finish) ? $option_id_finish : null
                );
                $total_item_cost += $total_cost;
            }
            if (empty($products)) {
                $this->session->set_flashdata('error', $this->lang->line("no_data_select") );
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                krsort($products);
            }
            // var_dump($products);
            // exit();
            
            //end finish data
            $data = array(
                'using_id'           => $id,
                'date'               => $date,
                'using_reference_no' => $reference_no,
                'reference_no'       => $return_ref,
                'warehouse_id'       => $warehouse_id,
                'authorize_id'       => $authorize,
                'employee_id'        => $employee_id,
                'customer_id'        => $customer_id,
                'biller_id'          => $biller_id,
                'account'            => $account,
                'note'               => $note,
                'create_by'          => $this->session->userdata('user_id'),
                'type'               => 'return',
                'total_cost'         => $total_item_cost,
                'plan_id'            => is_numeric($plan) ? $plan : null,
                'address_id'         => is_numeric($address) ? $address : null,   
            );
        }
        if ($this->form_validation->run() == true && $this->products_model->insert_enter_using_stock($data, $products, null, $stockmoves, $accTrans)) {
            $this->session->set_userdata('remove_usitem', 1);
            $this->session->set_flashdata(lang('enter_using_stock_return_added.'));
            admin_redirect('products/using_stock');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $getUsingStock             = $this->products_model->getUsingStockById($id);
            $this->data['using_stock'] = $getUsingStock;
            if ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) {
                $biller_id = $this->site->get_setting()->default_biller;
          
                $this->data['ref_return'] = $this->site->getReference('esr', $biller_id);
         
            } else {
                $biller_id = $this->session->userdata('biller_id');
                $this->data['ref_return'] = $this->site->getReference('esr', $biller_id);
            }
            $this->data['all_unit']     = $this->site->getUnits();
            $this->data['authorize_by'] = $this->site->getAllUsers();
            $this->data['accounting']   = $this->products_model->getGLChart();
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['plan']         = $this->products_model->getPlan();
            $this->data['employees']    = $this->site->getAllEmployee();
            $this->data['biller']       = $this->site->getAllBiller();
            $this->data['id']           = $id;
            $wh_id             = $getUsingStock->warehouse_id;
            $reference_no      = $getUsingStock->reference_no;

            // Get Using Stock Items
            $getUsingStockItem = $this->products_model->getUsingStockItemsByRef($reference_no);
            $total_qty_use = $this->products_model->getTotalQTYByUsingID($id);
            // var_dump($total_qty_use);exit();
            $c = str_replace(".", "", microtime(true));
            $r = 0; $pr = []; $t = 0;
            foreach ($getUsingStockItem as $row) {
                $row->project_qty = 0;
                if ($getUsingStock->plan_id) {
                    $project_item = $this->products_model->getPlanUsing($getUsingStock->plan_id, $row->product_code, $getUsingStock->address_id);
                    if ($project_item) {
                       $row->project_qty = ($project_item->quantity_balance - $project_item->using_qty);
                    }
                }
                $row->qty_old   = $row->qty_use;
                $row->qty_use   = $row->qty_use;
                $row->have_plan = isset($project_item) ? 1 : 0;
                $option_unit    = $this->products_model->getUnitAndVaraintByProductId($row->id);
                $expiry_date    = $this->site->getStockMovementByProductID($row->id, $wh_id);
                $ri = $this->Settings->item_addition ? $row->id : ($c + $r);      
                $pr[$ri] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->product_code . ")", 'row' => $row, 'option_unit' => $option_unit, 'project_qty' => $row->project_qty,'stock_item' => $row->e_id, 'expiry_date' => $expiry_date, 'type' => 'return');
                $use_product[] = array(
                    'row_id' => ($c + $t),
                    'id' => $row->id,
                    'name' => $row->name,
                    'code' => $row->product_code,
                    'price' => $row->cost,
                    'wax_setting_qty' => $row->qty_use,
                    'casting_qty' => $row->qty_use,
                    'filing_pre_polishing_qty' => $row->qty_use,
                    'stone_setting_qty' => $row->qty_use,
                    'total_stone_setting_qty' => $total_qty_use,
                    'final_polishing_qty' => $row->qty_use,
                    'quality_inspection_qty' => $row->qty_use,
                    'packaging_qty' => $row->qty_use,
                    'type' => 'use',
                );
                $r++;$t++;
            }
            $this->data['items'] = json_encode($pr);   
            // End Get Using Stock Items
          
            // Get Finished Using Stock
            $getUsingStockItem = $this->products_model->getFinishStockItemsByRef($reference_no);
            // var_dump($getUsingStockItem);exit();
            $c = str_replace(".", "", microtime(true));
            $r = 0; $pr = []; $t = 0;
            foreach ($getUsingStockItem as $pre_row) {
                $finish_product[] = array(
                    'row_id' => ($c + $t),
                    'id' => $pre_row->id,
                    'name' => $pre_row->name,
                    'code' => $pre_row->product_code,
                    'price' => $pre_row->cost,
                    'wax_setting_qty' => $pre_row->qty_use,
                    'casting_qty' => $pre_row->qty_use,
                    'filing_pre_polishing_qty' => $pre_row->qty_use,
                    'stone_setting_qty' => $pre_row->qty_use,
                    'total_stone_setting_qty' => $total_qty_use,
                    'final_polishing_qty' => $pre_row->qty_use,
                    'quality_inspection_qty' => $pre_row->qty_use,
                    'packaging_qty' => $pre_row->qty_use,
                    'type' => 'return',
                );
                $t++;
            }
            foreach ($getUsingStockItem as $row) {
                $row->project_qty = 0;
                if ($getUsingStock->plan_id) {
                    $project_item = $this->products_model->getPlanUsing($getUsingStock->plan_id, $row->product_code, $getUsingStock->address_id);
                    if ($project_item) {
                       $row->project_qty = ($project_item->quantity_balance - $project_item->using_qty);
                    }
                }
                $row->qty_old   = $row->qty_use;
                $row->qty_use   = $row->qty_use;
                $row->have_plan = isset($project_item) ? 1 : 0;
                $option_unit    = $this->products_model->getUnitAndVaraintByProductId($row->id);
                $expiry_date    = $this->site->getStockMovementByProductID($row->id, $wh_id);
                $ri = $this->Settings->item_addition ? $row->id : ($c + $r);      
                $fpr[$ri] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->product_code . ")", 'row' => $row, 'option_unit' => $option_unit, 'project_qty' => $row->project_qty, 'combo_items' => array_merge($use_product, $finish_product), 'stock_item' => $row->e_id, 'expiry_date' => $expiry_date, 'type' => 'return');
                $r++;
            }
            $this->data['items_finish'] = json_encode($fpr);   
            // var_dump($this->data['items_finish']);
            // exit();
            // End Get Finished Using Stock

            $this->data['modal_js'] = $this->site->modal_js();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('products')), array('link' => '#', 'page' => lang('return_using_stock')));
            $meta = array('page_title' => lang('return_using_stock'), 'bc' => $bc);
            $this->page_construct('stock_using/return_using_stock', $meta, $this->data);
        }
    }

    public function delete_using_stock($id = NULL)
    {
        $this->bpas->checkPermissions('using_stock-delete', true);
        $row = $this->products_model->getUsingStockByID($id);
        if ($this->products_model->deleteUsingStock($id)) {
            $this->session->set_flashdata('message', lang('using_stock_deleted')." - ". $row->reference_no);
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function suggestionsStock()
    {
        $addr         = $this->input->get('address',TRUE);
        $term         = $this->input->get('term', TRUE);
        $warehouse_id = $this->input->get('warehouse_id', TRUE);
        $plan         = $this->input->get('plan',TRUE);
        $address      = isset($addr) ? $addr : null;
        $rows = $this->products_model->getUsingStockProducts($term, $warehouse_id, $plan, $address);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            $project_qty = 0;
            foreach ($rows as $row) {
                if ($plan) {
                    $project_qty = $row->project_qty;
                } else {
                    $row->project_qty = 0;
                }
                $row->have_plan = 0;
                if ($row->project_qty && $row->in_plan) {
                    $row->have_plan = 1;
                }
                $row->qty_use = 1;
                $row->qty_old = 1;
                $row->expiry  = '';
                $option_unit  = $this->products_model->getUnitAndVaraintByProductId($row->id);
                $expiry_date  = $this->site->getStockMovementByProductID($row->id, $warehouse_id);
                $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",'row' => $row, 'option_unit' => $option_unit, 'project_qty' => $project_qty, 'expiry_date' => $expiry_date);
                $r++;
            }
            echo json_encode($pr);
        } else {
            echo json_encode(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    public function getAddress($plan = NULL)
    {
        if ($rows = $this->products_model->getAddressById($plan)) {
            $data = json_encode($rows);
        } else {
            $data = false;
        }
        echo $data;
    }

    public function print_sample_form_ppcp($id, $type)
    {
        $this->bpas->checkPermissions('using_stock');
        if($type=="use"){
            $using_stock = $this->products_model->get_enter_using_stock_by_id($id);
            $ref_no      = $using_stock->reference_no;
            $stock_item  = $this->products_model->get_enter_using_stock_item_by_ref($ref_no);
            
            $this->data['using_stock']  = $using_stock;
            $this->data['stock_item']   = $stock_item; 
            $this->data['info']         = $this->products_model->get_enter_using_stock_info();
            $this->data['biller']       = $this->products_model->getUsingStockProject($id);
            $this->data['customer']     = $this->products_model->getUsingStockByCustomerID($id);
            $this->data['au_info']      = $this->products_model->getAuInfo($id);
            $this->load->view($this->theme.'products/print_sample_form_ppcp',$this->data);
        }
        if($type=="return"){
            $using_stock = $this->products_model->get_enter_using_stock_by_id($id);
            $ref_no=$using_stock->reference_no;
            $stock_item=$this->products_model->get_enter_using_stock_item_by_ref($ref_no);
             $this->data['using_stock'] = $using_stock; 
             $this->data['stock_item'] = $stock_item; 
             $this->data['customer']    = $this->products_model->getUsingStockByCustomerID($id);
            $this->data['au_info']      = $this->products_model->getAuInfo($id);
            $this->load->view($this->theme.'products/print_sample_form_ppcp',$this->data);
        }
    }

    public function using_stock_action()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('using_stock-delete', true);
                    $using_stock_no = "";
                    foreach ($_POST['val'] as $id) {
                        $row = $this->products_model->getUsingStockByID($id);
                        $using_stock_no .= $row->reference_no.", ";
                        $this->products_model->deleteUsingStock($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("using_stock_deleted")." - ".$using_stock_no);
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('list_using_stock'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('project'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('plan'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('warehouse'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('employee'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('description'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('status'));
                    $this->excel->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold(true);
                    $this->excel->getActiveSheet()->getStyle('A1:H1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $i = 2;
                    foreach ($_POST['val'] as $id) {
                        $row = $this->products_model->get_all_enter_using_stock($id);
                        $this->excel->getActiveSheet()->SetCellValue('A'.$i, $row->date);
                        $this->excel->getActiveSheet()->SetCellValue('B'.$i, $row->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C'.$i, $row->company);
                        $this->excel->getActiveSheet()->SetCellValue('D'.$i, $row->plan);
                        $this->excel->getActiveSheet()->SetCellValue('E'.$i, $row->warehouse_name);
                        $this->excel->getActiveSheet()->SetCellValue('F'.$i, $row->username);
                        $this->excel->getActiveSheet()->SetCellValue('G'.$i, $this->bpas->decode_html(strip_tags($row->note)));
                        $this->excel->getActiveSheet()->SetCellValue('H'.$i, $row->type);
                        $this->excel->getActiveSheet()->getStyle('H'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $i++;       
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = lang('list_using_stock');
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
                $this->session->set_flashdata('error', $this->lang->line("No_selected. Please select at least one!"));
                redirect($_SERVER["HTTP_REFERER"]);
            }    
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function stock_received($warehouse_id = null)
    {
        $this->bpas->checkPermissions('index', null, 'stock_received');
        $count = explode(',', $this->session->userdata('warehouse_id'));
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            if (count($count) > 1) {
                $this->data['warehouses']   = $this->site->getAllWarehouses();
            } else {
                $this->data['warehouses']   = null;
            }
            
            $this->data['count_warehouses'] = $count;
            $this->data['user_warehouse']   = (isset($count) && count($count) == 1) ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
            $this->data['warehouse_id']     = $warehouse_id;
            $this->data['warehouse']        = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        }

        $this->data['alert_id'] = isset($_GET['alert_id']) ? $_GET['alert_id'] : null;
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('stock_received')]];
        $meta = ['page_title' => lang('stock_received'), 'bc' => $bc];
        $this->page_construct('products/stock_received', $meta, $this->data);
    }

    public function stock_received_actions()
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
                        $this->bpas->checkPermissions('export', true, 'stock_received');
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
                        $this->excel->getActiveSheet()->SetCellValue('H2', lang('warehouse'));
                        $this->excel->getActiveSheet()->SetCellValue('I2', lang('status'));
                        $styleArray = array(
                            'font'  => array(
                                'bold'  => true
                            )
                        );
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
                            $this->excel->getActiveSheet()->SetCellValue('H' . $row, $purchase->ware_name);
                            $this->excel->getActiveSheet()->SetCellValue('I' . $row, $purchase->status);
                            $row++;
                        }
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'Stock_Received_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line('no_stock_received_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function getStockReceived($warehouse_id = null)
    {
        $this->session->set_flashdata('error', $this->bpas->checkPermissions('index', null, 'stock_received'));
        if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
        $a                = $this->input->get('a') ? $this->input->get('a') : null;
        $detail_link      = anchor('admin/purchases/view_stock_received/$1', '<i class="fa fa-file-text-o"></i> ' . lang('stock_details'));
        $detail_link      = anchor('admin/purchases/view_stock_details/$1', '<i class="fa fa-money"></i> ' . lang('stock_details'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $email_link       = anchor('admin/purchases/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_purchase'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link        = anchor('admin/purchases/add_stock_received/$1', '<i class="fa fa-edit"></i> ' . lang('add_stock_received'));
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
            <li>' . $detail_link . '</li>
            <li class="add_stock">' . $edit_link . '</li>
        </ul>
        </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';
        $this->load->library('datatables');
        if(!$this->Settings->avc_costing){
        $this->datatables
            ->select("purchases.id, DATE_FORMAT({$this->db->dbprefix('purchases')}.date, '%Y-%m-%d %T') as date, 
                projects.project_name,reference_no, order_ref,request_ref,purchases.status, 
                attachment");
        } else {
            $this->datatables
            ->select("purchases.id, DATE_FORMAT({$this->db->dbprefix('purchases')}.date, '%Y-%m-%d %T') as date, 
                projects.project_name,reference_no, order_ref,request_ref,purchases.status, 
                attachment");
        }
        $this->datatables->from('purchases')->join('projects', 'purchases.project_id = projects.project_id', 'left');
        $this->datatables->where('purchases.is_asset !=',1);
        $this->datatables->where('purchases.status !=', 'returned');
        if ($warehouse_id) {
            $this->datatables->where('purchases.warehouse_id IN (' . $warehouse_id . ')');
        } elseif (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where_in("FIND_IN_SET(bpas_purchases.warehouse_id, '".$this->session->userdata('warehouse_id')."')");
            $this->datatables->where("FIND_IN_SET(bpas_purchases.created_by, '" . $this->session->userdata('user_id') . "')");
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('bpas_purchases.created_by', $this->session->userdata('user_id'));
        }
        if ($a) {
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

    public function import_products_cost_and_price_excel() 
    {
        $this->bpas->checkPermissions('update_cost_and_price');
        $this->load->helper('security');
        $this->form_validation->set_rules('type', lang('type'), 'required');
        if ($this->form_validation->run() == true) {
            $type         = $this->input->post('type');
            $categories   = $this->input->post('category') ? $this->input->post('category') : null;
            $brands       = $this->input->post('brand') ? $this->input->post('brand') : null;
            $this->load->helper('string');
            $name     = random_string('md5') . '.csv';
            $products = $this->products_model->getProductCost_Price_By_Unit($type, $categories, $brands);
            if (!empty($products)) {
                $this->load->helper('download');

                $data = file_get_contents('php://output'); 
                $filename = 'products_cost_price_' . date('d-m-Y').'.csv'; 
                header('Pragma: public');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Cache-Control: private',false);
                header('Content-Disposition: attachment; filename="'.basename($filename).'"');
                header('Content-Transfer-Encoding: binary');
                header('Connection: close');

                $csv_file = fopen('php://output', 'w');
                fprintf($csv_file, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($csv_file, [lang('product_code'), lang('product_name'), lang('unit_code'), lang('cost'), lang('price')]);
                foreach ($products as $product) {
                    $csv = [
                        'product_code' => $product->code,
                        'product_name' => $product->name,
                        'unit_code'    => $product->unit_code,
                        'cost'         => $product->cost,
                        'price'        => $product->price
                    ];
                    fputcsv($csv_file, $csv);
                }
                force_download($filename, $data);
                fclose($csv_file);
                exit();
            } else {
                $this->session->set_flashdata('error', lang('no_product_found'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['brands']     = $this->site->getAllBrands();
        $bc                       = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('import_products_cost_and_price_excel')]];
        $meta                     = ['page_title' => lang('import_products_cost_and_price_excel'), 'bc' => $bc];
        $this->page_construct('products/import_products_cost_price_excel', $meta, $this->data);
    }

    public function add_update_products_cost_price() 
    {
        $this->bpas->checkPermissions('update_cost_and_price');
        $this->load->helper('security');
        $products_cost_price = [];
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
                admin_redirect('products/import_products_cost_price_excel');
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
            $keys  = ['code', 'name', 'unit', 'cost', 'price'];
            $final = [];
            foreach ($arrResult as $key => $value) {
                $final[] = array_combine($keys, $value);
            }
            foreach ($final as $csv_pr) {
                if (isset($csv_pr['code']) && isset($csv_pr['unit']) && isset($csv_pr['cost']) && isset($csv_pr['price'])) {
                    $product = $this->site->getProductByCode($csv_pr['code']);
                    $unit    = $this->site->getUnitByCode($csv_pr['unit']);
                    $cost_price = [
                        'product_id' => $product->id,
                        'unit_id'    => $unit->id,
                        'cost'       => $csv_pr['cost'],
                        'price'      => $csv_pr['price']
                    ];
                    $products_cost_price[] = $cost_price;
                }
            }
        } else {
            $this->session->set_flashdata('error', 'Please select file!');
            admin_redirect('products/import_products_cost_and_price_excel');
        }
        if (!empty($products_cost_price) && $this->products_model->addProducts_Cost_Price($products_cost_price)) {
            $this->session->set_flashdata('message', sprintf(lang('products_cost/price_updated')));
            admin_redirect('products');
        }
    }

    public function getProducts_ajax()
    {
        $result = $this->site->getProducts();
        $this->bpas->send_json($result);
    }

    public function getStockCount($id = null)
    {
        $this->bpas->checkPermissions('index');
        $detail_link   = anchor('admin/transfers/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('transfer_details'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $email_link    = anchor('admin/transfers/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_transfer'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link     = anchor('admin/transfers/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_transfer'));
        $pdf_link      = anchor('admin/transfers/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $print_barcode = anchor('admin/products/print_barcodes/?transfer=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
        $delete_link   = "<a href='#' class='tip po' title='<b>" . lang('delete_transfer') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' id='a__$1' href='" . admin_url('transfers/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_transfer') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $print_barcode . '</li>
            <li>' . $delete_link . '</li>
        </ul>
        </div></div>';
        $this->load->library('datatables');
        $this->datatables
            ->select('id,CONCAT(product_code,'. ', product_name) AS product_code, expiry ,expected, counted, (counted-expected) as difference')
            ->from('stock_count_items');
            $this->datatables->order_by('product_id','ASC'); 
            $this->datatables->where('stock_count_id', $id);
            $this->datatables->where('counted >', "0");
            $this->datatables->where('status ', "0"); 
            // ->edit_column('fname', '$1 ($2)', 'fname, fcode')
            // ->edit_column('tname', '$1 ($2)', 'tname, tcode');
            //  $this->datatables->sortby('date');
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
        // $this->datatables->add_column('Actions', $action, 'id')
        //     ->unset_column('fcode')
        //     ->unset_column('tcode');
        echo $this->datatables->generate();
    }  

    public function reward_ring($count_id = null)
    {
        $this->bpas->checkPermissions('adjustments', true);
        $this->form_validation->set_rules('warehouse', lang('warehouse'), 'required');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld($this->input->post('date'));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('qa');
            $warehouse_id = $this->input->post('warehouse');
            $note         = $this->bpas->clear_tags($this->input->post('note'));
            $warehouse    = $this->site->getWarehouseByID($warehouse_id);
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id  = $_POST['product_id'][$r];
                $type        = $_POST['type'][$r];
                $quantity    = $_POST['quantity'][$r];
                $serial      = isset($_POST['serial'][$r]) && !empty($_POST['serial'][$r]) && $_POST['serial'][$r] != 'undefined' && $_POST['serial'][$r] != 'false' && $_POST['serial'][$r] != 'null' && $_POST['serial'][$r] != 'NULL' ? $_POST['serial'][$r] : null;
                $variant     = isset($_POST['variant'][$r]) && !empty($_POST['variant'][$r]) ? $_POST['variant'][$r] : null;
                $item_expiry = isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r]) && $_POST['expiry'][$r] != 'false' && $_POST['expiry'][$r] != 'undefined' && $_POST['expiry'][$r] != 'null' && $_POST['expiry'][$r] != 'NULL' && $_POST['expiry'][$r] != '00/00/0000' && $_POST['expiry'][$r] != '' ? $this->bpas->fsd($_POST['expiry'][$r]) : null; 
                if ((!$this->Settings->overselling || ($this->Settings->overselling && !$warehouse->overselling)) && $type == 'subtraction' && !$count_id) {
                    if ($variant) {
                        if ($op_wh_qty = $this->products_model->getProductWarehouseOptionQty($variant, $warehouse_id)) {
                            if ($op_wh_qty->quantity < $quantity) {
                                $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage'));
                                redirect($_SERVER['HTTP_REFERER']);
                            }
                        } else {
                            $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage'));
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                    }
                    if ($wh_qty = $this->products_model->getProductQuantity($product_id, $warehouse_id)) {
                        if ($wh_qty['quantity'] < $quantity) {
                            $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage'));
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                    } else {
                        $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                }
                $products[] = [
                    'product_id'   => $product_id,
                    // 'count_item_id'=>  $count_item_id,
                    'type'         => $type,
                    'quantity'     => $quantity,
                    'warehouse_id' => $warehouse_id,
                    'option_id'    => $variant,
                    'serial_no'    => $serial,
                    'expiry'       => $item_expiry,
                ];
                //----------account----
                if($this->Settings->module_account == 1){
                    $getproduct = $this->site->getProductByID($product_id);

                    if($type == 'subtraction'){
                        $amount_stock = -($getproduct->cost * $quantity);
                        $amount_cost = ($getproduct->cost * $quantity);
                    }else{
                        $amount_stock = ($getproduct->cost * $quantity);
                        $amount_cost = -($getproduct->cost * $quantity);
                    }

                    $accTrans[] = array(
                        'tran_type' => 'adjustment',
                        'tran_date' => $date,
                        'reference_no' => $reference_no,
                        'account_code' => $inventory_acc,
                        'amount' => $amount_stock,
                        'narrative' => $this->site->getAccountName($inventory_acc),
                        'description' => $note,
                        'biller_id' => $this->input->post('biller'),
                        'people_id' => $this->session->userdata('user_id'),
                        'created_by'  => $this->session->userdata('user_id'),
                    );

                    $accTrans[] = array(
                        'tran_type' => 'adjustment',
                        'tran_date' => $date,
                        'reference_no' => $reference_no,
                        'account_code' => $costing_acc,
                        'amount' => $amount_cost,
                        'narrative' => $this->site->getAccountName($costing_acc),
                        'description' => $note,
                        'biller_id' => $this->input->post('biller'),//$biller_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'created_by'  => $this->session->userdata('user_id'),
                    ); 
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('products'), 'required');
            } else {
                krsort($products);
            }
            $data = [
                'date'         => $date,
                'reference_no' => $reference_no,
                'warehouse_id' => $warehouse_id,
                'note'         => $note,
                'created_by'   => $this->session->userdata('user_id'),
                'biller_id'    => $this->input->post('biller'),
                'count_id'     => $this->input->post('count_id') ? $this->input->post('count_id') : null,
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
        }elseif ($this->input->post('add_adjustment')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('customers');
        }
        if ($this->form_validation->run() == true && $this->products_model->addAdjustment($data, $products, ((isset($accTrans) ? $accTrans : null)))) {
            $this->session->set_userdata('remove_qals', 1);
            $this->session->set_flashdata('message', lang('quantity_adjusted'));
            admin_redirect('products/quantity_adjustments');
        } else {
            if($this->Settings->auto_count){
                $variable = isset($_POST['val']) ? $_POST['val'] : 0;
                if ($count_id) {
                    $stock_count = $this->products_model->getStouckCountByID($count_id);
                    if($variable){
                        foreach ($variable as $value) {
                            // $items      = $this->products_model->getStockCountItems($count_id);
                            $items       = $this->products_model->getStockProductCountItems($count_id,$value);
                            foreach ($items as $item) {
                                $c = sha1(uniqid(mt_rand(), true));
                                if ($item->counted != $item->expected) {
                                    $product     = $this->site->getProductByID($item->product_id);
                                    $row         = json_decode('{}');
                                    $row->id     = $item->product_id;
                                    $row->code   = $product->code;
                                    $row->name   = $product->name;
                                    $row->qty    = $item->counted - $item->expected;
                                    $row->type   = $row->qty > 0 ? 'addition' : 'subtraction';
                                    $row->qty    = $row->qty > 0 ? $row->qty : (0 - $row->qty);
                                    $options     = $this->products_model->getProductOptions($product->id);
                                    $row->option = $item->product_variant_id ? $item->product_variant_id : 0;
                                    $row->serial = '';
                                    $ri          = $this->Settings->item_addition ? $product->id : $c;

                                    $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                                        'row'        => $row, 'options' => $options, ];
                                    $c++;
                                }
                            }
                        }
                    }else{
                        $pr =[];
                    }
                }
            } else {
                if ($count_id) {
                    $stock_count = $this->products_model->getStouckCountByID($count_id);
                    if (!empty($_POST['val'])) {
                        foreach ($_POST['val'] as $id) {
                            $items       = $this->products_model->getStockCountSomeItems($count_id, $id);
                            
                            foreach ($items as $item) {
                               
                                $c = sha1(uniqid(mt_rand(), true));
                                if ($item->counted != $item->expected) {
                                    $product     = $this->site->getProductByID($item->product_id);

                                    $row                    = json_decode('{}');
                                    $option                 = false;
                                    $row->id                = $item->product_id;
                                    $row->base_unit         = $product->unit;
                                    $row->base_unit_cost    = $product->cost;
                                    $row->unit              =  $product->unit;
                                    $row->unit_name         = $this->site->getUnitByID($product->unit)->name;
                                    $row->discount          = '0';
                                    $row->expiry            = $item->expiry;
                                    $row->code              = $product->code;
                                    $row->name              = $product->name;
                                    $row->qty               = $item->counted - $item->expected;
                                    $row->type              = $row->qty > 0 ? 'addition' : 'subtraction';
                                    $row->qty               = $row->qty > 0 ? $row->qty : (0 - $row->qty);
                                    $options                = $this->products_model->getProductOptions($product->id);
                                    $row->option            = $item->product_variant_id ? $item->product_variant_id : 0;
                                    $row->serial            = '';
                                    $ri                     = $this->Settings->item_addition ? $product->id : $c;

                                    $pr[$ri] = [
                                        'id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                                        'row'        => $row, 'options' => $options,
                                        'expiry' => $item->expiry,
                                    ];
                                    $c++;
                                }
                            }
                        }
                    } else {
                        $this->session->set_flashdata('error', $this->lang->line('no_record_selected'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                }
            }
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->data['sectionacc']       = $this->accounts_model->getAllChartAccount();
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->data['adjustment_items'] = $count_id ? json_encode($pr) : false;
            $this->data['warehouse_id']     = $count_id ? $stock_count->warehouse_id : false;
            $this->data['count_id']         = $count_id;
            $this->data['error']            = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses']       = $this->site->getAllWarehouses();
            $bc                             = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('reward_ring')]];
            $meta                           = ['page_title' => lang('reward_ring'), 'bc' => $bc];
            $this->page_construct('products/reward_ring', $meta, $this->data);
        }
    }
    
    public function reward_money($count_id = null)
    {
        $this->bpas->checkPermissions('adjustments', true);
        $this->form_validation->set_rules('warehouse', lang('warehouse'), 'required');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld($this->input->post('date'));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('qa');
            $warehouse_id = $this->input->post('warehouse');
            $note         = $this->bpas->clear_tags($this->input->post('note'));
            $warehouse    = $this->site->getWarehouseByID($warehouse_id);
           
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id  = $_POST['product_id'][$r];
                $type        = $_POST['type'][$r];
                $quantity    = $_POST['quantity'][$r];
                $serial      = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : null;
                $variant     = isset($_POST['variant'][$r]) && !empty($_POST['variant'][$r]) ? $_POST['variant'][$r] : null;
                $item_expiry = isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r]) && $_POST['expiry'][$r] != 'false' && $_POST['expiry'][$r] != 'undefined' && $_POST['expiry'][$r] != 'null' && $_POST['expiry'][$r] != 'NULL' && $_POST['expiry'][$r] != '00/00/0000' && $_POST['expiry'][$r] != '' ? $this->bpas->fsd($_POST['expiry'][$r]) : null; 
                if ((!$this->Settings->overselling || ($this->Settings->overselling && !$warehouse->overselling)) && $type == 'subtraction' && !$count_id) {
                    if ($variant) {
                        if ($op_wh_qty = $this->products_model->getProductWarehouseOptionQty($variant, $warehouse_id)) {
                            if ($op_wh_qty->quantity < $quantity) {
                                $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage'));
                                redirect($_SERVER['HTTP_REFERER']);
                            }
                        } else {
                            $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage'));
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                    }
                    if ($wh_qty = $this->products_model->getProductQuantity($product_id, $warehouse_id)) {
                        if ($wh_qty['quantity'] < $quantity) {
                            $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage'));
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                    } else {
                        $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                }
                $products[] = [
                    'product_id'   => $product_id,
                    // 'count_item_id'=>  $count_item_id,
                    'type'         => $type,
                    'quantity'     => $quantity,
                    'warehouse_id' => $warehouse_id,
                    'option_id'    => $variant,
                    'serial_no'    => $serial,
                    'expiry'       => $item_expiry,
                ];
                //----------account----
                if($this->Settings->module_account == 1){
                    $getproduct = $this->site->getProductByID($product_id);
                    if ($type == 'subtraction') {
                        $amount_stock = -($getproduct->cost * $quantity);
                        $amount_cost = ($getproduct->cost * $quantity);
                    } else {
                        $amount_stock = ($getproduct->cost * $quantity);
                        $amount_cost = -($getproduct->cost * $quantity);
                    }
                    $accTrans[] = array(
                        'tran_type' => 'adjustment',
                        'tran_date' => $date,
                        'reference_no' => $reference_no,
                        'account_code' => $inventory_acc,
                        'amount' => $amount_stock,
                        'narrative' => $this->site->getAccountName($inventory_acc),
                        'description' => $note,
                        'biller_id' => $this->input->post('biller'),
                        'people_id' => $this->session->userdata('user_id'),
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                    $accTrans[] = array(
                        'tran_type' => 'adjustment',
                        'tran_date' => $date,
                        'reference_no' => $reference_no,
                        'account_code' => $costing_acc,
                        'amount' => $amount_cost,
                        'narrative' => $this->site->getAccountName($costing_acc),
                        'description' => $note,
                        'biller_id' => $this->input->post('biller'),//$biller_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'created_by'  => $this->session->userdata('user_id'),
                    ); 
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('products'), 'required');
            } else {
                krsort($products);
            }
            $data = [
                'date'         => $date,
                'reference_no' => $reference_no,
                'warehouse_id' => $warehouse_id,
                'note'         => $note,
                'created_by'   => $this->session->userdata('user_id'),
                'biller_id'    => $this->input->post('biller'),
                'count_id'     => $this->input->post('count_id') ? $this->input->post('count_id') : null,
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
        }elseif ($this->input->post('add_adjustment')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('customers');
        }
        if ($this->form_validation->run() == true && $this->products_model->addAdjustment($data, $products, ((isset($accTrans) ? $accTrans : null)))) {
            $this->session->set_userdata('remove_qals', 1);
            $this->session->set_flashdata('message', lang('quantity_adjusted'));
            admin_redirect('products/quantity_adjustments');
        } else {
            if($this->Settings->auto_count){
                $variable = isset($_POST['val']) ? $_POST['val'] : 0;
                if ($count_id) {
                    $stock_count = $this->products_model->getStouckCountByID($count_id);
                    if($variable){
                        foreach ($variable as $value) {
                            // $items      = $this->products_model->getStockCountItems($count_id);
                            $items       = $this->products_model->getStockProductCountItems($count_id,$value);
                            foreach ($items as $item) {
                                $c = sha1(uniqid(mt_rand(), true));
                                if ($item->counted != $item->expected) {
                                    $product     = $this->site->getProductByID($item->product_id);
                                    $row         = json_decode('{}');
                                    $row->id     = $item->product_id;
                                    $row->code   = $product->code;
                                    $row->name   = $product->name;
                                    $row->qty    = $item->counted - $item->expected;
                                    $row->type   = $row->qty > 0 ? 'addition' : 'subtraction';
                                    $row->qty    = $row->qty > 0 ? $row->qty : (0 - $row->qty);
                                    $options     = $this->products_model->getProductOptions($product->id);
                                    $row->option = $item->product_variant_id ? $item->product_variant_id : 0;
                                    $row->serial = '';
                                    $ri          = $this->Settings->item_addition ? $product->id : $c;

                                    $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                                        'row'        => $row, 'options' => $options, ];
                                    $c++;
                                }
                            }
                        }
                    }else{
                        $pr =[];
                    }
                }
            } else {
                if ($count_id) {
                    $stock_count = $this->products_model->getStouckCountByID($count_id);
                    if (!empty($_POST['val'])) {
                        foreach ($_POST['val'] as $id) {
                            $items       = $this->products_model->getStockCountSomeItems($count_id, $id);
                            
                            foreach ($items as $item) {
                               
                                $c = sha1(uniqid(mt_rand(), true));
                                if ($item->counted != $item->expected) {
                                    $product     = $this->site->getProductByID($item->product_id);

                                    $row                    = json_decode('{}');
                                    $option                 = false;
                                    $row->id                = $item->product_id;
                                    $row->base_unit         = $product->unit;
                                    $row->base_unit_cost    = $product->cost;
                                    $row->unit              =  $product->unit;
                                    $row->unit_name         = $this->site->getUnitByID($product->unit)->name;
                                    $row->discount          = '0';
                                    $row->expiry            = $item->expiry;
                                    $row->code              = $product->code;
                                    $row->name              = $product->name;
                                    $row->qty               = $item->counted - $item->expected;
                                    $row->type              = $row->qty > 0 ? 'addition' : 'subtraction';
                                    $row->qty               = $row->qty > 0 ? $row->qty : (0 - $row->qty);
                                    $options                = $this->products_model->getProductOptions($product->id);
                                    $row->option            = $item->product_variant_id ? $item->product_variant_id : 0;
                                    $row->serial            = '';
                                    $ri                     = $this->Settings->item_addition ? $product->id : $c;

                                    $pr[$ri] = [
                                        'id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                                        'row'        => $row, 'options' => $options,
                                        'expiry' => $item->expiry,
                                    ];
                                    $c++;
                                }
                            }
                        }
                    } else {
                        $this->session->set_flashdata('error', $this->lang->line('no_record_selected'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                }
            }
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->data['sectionacc']       = $this->accounts_model->getAllChartAccount();
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->data['adjustment_items'] = $count_id ? json_encode($pr) : false;
            $this->data['warehouse_id']     = $count_id ? $stock_count->warehouse_id : false;
            $this->data['count_id']         = $count_id;
            $this->data['error']            = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses']       = $this->site->getAllWarehouses();
            $bc                             = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('reward_money')]];
            $meta                           = ['page_title' => lang('reward_money'), 'bc' => $bc];
            $this->page_construct('products/reward_money', $meta, $this->data);
        }
    }

    public function rewards_exchange($category, $type = null, $biller_id = null)
    {
        $this->bpas->checkPermissions('index', null, 'reward_exchange');
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
        $this->data['category']          = $category;
        $this->data['type']              = $type;
        $this->data['users']             = $this->site->getStaff();
        $this->data['users']             = $this->site->getStaff();
        $this->data['products']          = $this->site->getProducts();
        $this->data['warehouses']        = $this->site->getAllWarehouses();
        $this->data['count_warehouses']  = explode(',', $this->session->userdata('warehouse_id'));
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('rewards_exchange')]];
        $meta = ['page_title' => lang('rewards_exchange'), 'bc' => $bc];
        $this->page_construct('products/rewards_exchange', $meta, $this->data);
    }

    public function getRewardsExchange($category, $type = null, $biller_id = null)
    {
        $this->bpas->checkPermissions('index', null, 'reward_exchange');
        if ((!$this->Owner && !$this->Admin) && !$biller_id) {
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $biller_id = $user->multi_biller ? explode(',', $user->multi_biller) : null;         
            } else {
                $biller_id = $user->biller_id ? ((array) $user->biller_id) : null;
            }
        }
        $created_by     = $this->input->get('user') ? $this->input->get('user') : null;
        $customer       = $this->input->get('customer') ? $this->input->get('customer') : null;
        $biller         = $this->input->get('biller') ? $this->input->get('biller') : null;
        $reference_no   = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $warehouse      = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $payment_status = $this->input->get('payment_status') ? $this->input->get('payment_status') : NULL;
        $start_date     = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date       = $this->input->get('end_date') ? $this->input->get('end_date') : null;
        if ($start_date) {
            $start_date = $this->bpas->fld($start_date . ' 00:00:00');
            $end_date   = $this->bpas->fld($end_date . ' 23:59:00');
        }
        $add_stock_received = anchor('admin/products/add_reward_stock_received/$1', '<i class="fa fa-file-text-o"></i> ' . lang('add_stock_received'));
        $detail_link       = anchor('admin/products/modal_view_reward_exchange/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $payments_link     = anchor('admin/products/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_payment_link  = anchor('admin/products/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_delivery_link = anchor('admin/deliveries/add/0/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'));
        $edit_link         = anchor('admin/products/edit_reward_exchange/$3/$2/$1', '<i class="fa fa-edit"></i> ' . lang('edit_reward_exchange'));
        $delete_link       = "<a href='#' class='po' title='<b>" . lang('delete_reward_exchange') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('products/delete_reward_exchange/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_rewards_exchange') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">';
            $action .= '
                <li>' . $detail_link . '</li>';
            // if ($this->Settings->stock_received) {
                $action .= (($this->Owner || $this->Admin) ? '<li class="add_stock_received">'.$add_stock_received.'</li>' : ($this->GP['stock_received-add'] ? '<li class="add_stock_received">'.$add_stock_received.'</li>' : ''));
            // }
            $action .= '<li>' . $payments_link . '</li>
                <li class="add_payment">' . $add_payment_link . '</li>
                <li class="edit">' . $edit_link . '</li>
                <li class="delete">' . $delete_link . '</li>
            </ul>
        </div></div>';
        $this->load->library('datatables');
        $this->datatables->select("
                {$this->db->dbprefix('rewards_exchange')}.id as id, 
                {$this->db->dbprefix('rewards_exchange')}.category as category, 
                DATE_FORMAT({$this->db->dbprefix('rewards_exchange')}.date, '%Y-%m-%d %T') as date,
                {$this->db->dbprefix('rewards_exchange')}.reference_no,
                {$this->db->dbprefix('rewards_exchange')}.biller, 
                {$this->db->dbprefix('rewards_exchange')}.company, 
                {$this->db->dbprefix('rewards_exchange')}.type AS type, 
                {$this->db->dbprefix('rewards_exchange')}.status, 
                {$this->db->dbprefix('rewards_exchange')}.grand_total, 
                {$this->db->dbprefix('rewards_exchange')}.paid, 
                ({$this->db->dbprefix('rewards_exchange')}.grand_total - {$this->db->dbprefix('rewards_exchange')}.paid) as balance,
                {$this->db->dbprefix('rewards_exchange')}.payment_status")
            ->order_by('rewards_exchange.id', 'desc')
            ->from('rewards_exchange');
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("FIND_IN_SET({$this->db->dbprefix('rewards_exchange')}.created_by, '" . $this->session->userdata('user_id') . "')");
        }
        if ($biller_id) {
            $this->datatables->where_in('rewards_exchange.biller_id', $biller_id);
        }
        if ($biller) {
            $this->datatables->where('rewards_exchange.biller_id', $biller);
        }
        if ($warehouse) {
            $this->datatables->where('rewards_exchange.warehouse_id', $warehouse);
        }
        if ($created_by) {
            $this->datatables->where('rewards_exchange.created_by', $created_by);
        }
        if ($payment_status) {
            $get_status = explode('_', $payment_status);
            $this->datatables->where_in('rewards_exchange.payment_status', $get_status);
        }
        if ($reference_no) {
            $this->datatables->where('rewards_exchange.reference_no', $reference_no);
        }
        if ($customer) {
            $this->datatables->where('rewards_exchange.company_id', $customer);
        }
        if ($category) {
            $this->datatables->where('rewards_exchange.category', $category);
        }
        if ($type) {
            $this->datatables->where('rewards_exchange.type', $type);
        }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('rewards_exchange') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        $this->datatables->add_column('Actions', $action, 'id, type, category');
        $this->datatables->unset_column('category');
        echo $this->datatables->generate();
    }

    public function reward_exchange_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {   
                    $this->bpas->checkPermissions('delete', null, 'reward_exchange');
                    foreach ($_POST['val'] as $id) {
                        $inv = $this->products_model->getRewardExchangeByID($id);
                        // $this->Settings->stock_received && $inv->type != 'money'
                        if ($inv->type != 'money') {
                            if ($inv->status != 'pending') {
                                $this->session->set_flashdata('error', lang('reward_already_stock_received'));
                                admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');   
                            }
                        }
                        $this->products_model->deleteRewardExchange($id);
                    }
                    $this->session->set_flashdata('message', lang('rewards_exchange_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $rx = $this->products_model->getRewardExchangeByID($_POST['val'][0]);
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('rewards_exchange'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('no'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang($rx->category));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('type'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('status'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('grand_total'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('paid'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('balance'));
                    $this->excel->getActiveSheet()->SetCellValue('K1', lang('payment_status'));
                    $row = 2; 
                    foreach ($_POST['val'] as $id) {
                        $reward_exchange = $this->products_model->getRewardExchangeByID($id);
                        $biller  = $this->site->getCompanyByID($reward_exchange->biller_id);
                        $company = $this->site->getCompanyByID($reward_exchange->company_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, ($row -1));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrld($reward_exchange->date));
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $reward_exchange->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, ($biller->company != '-' ? $biller->company : $biller->name));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, ($company->company != '-' ? $company->company : $company->name));
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, lang($reward_exchange->type));
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, lang($reward_exchange->status));
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($reward_exchange->grand_total));
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($reward_exchange->paid));
                        $this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($reward_exchange->grand_total - $reward_exchange->paid));
                        $this->excel->getActiveSheet()->SetCellValue('K' . $row, lang($reward_exchange->payment_status));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'rewards_exchange_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_reward_exchange_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function add_reward_exchange($reward_category, $reward_type)
    {   
        $this->bpas->checkPermissions('add', null, 'reward_exchange');
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules($reward_category, lang($reward_category), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('status', lang('status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_invoiceNo']) {
                $reference = $this->site->CheckedSaleReference($this->input->post('reference_no'), $this->site->getReference('so'));
            } else {
                $reference = $this->site->getReference('so');
            }
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $project_id       = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project;
            $warehouse_id     = $this->input->post('warehouse');
            $company_id       = $this->input->post($reward_category);
            $biller_id        = $this->input->post('biller');
            $status           = $this->input->post('status');
            $payment_status   = $this->input->post('payment_status');
            $payment_term     = $this->input->post('payment_term');
            $company_details  = $this->site->getCompanyByID($company_id);
            $company          = !empty($company_details->company) && $company_details->company != '-' ? $company_details->company . '/' . $company_details->name : $company_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company . '/' . $biller_details->name : $biller_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note       = $this->bpas->clear_tags($this->input->post('staff_note'));
            $total            = 0;
            $digital          = false;
            $i                = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id                    = $_POST['product_id'][$r];
                $reward_id                  = $_POST['reward_id'][$r];
                $item_code                  = $_POST['product_code'][$r];
                $item_name                  = $_POST['product_name'][$r];
                $item_unit                  = $_POST['product_unit'][$r];
                $item_unit_quantity         = $_POST['quantity'][$r];
                $item_quantity              = $_POST['product_base_quantity'][$r];
                if ($reward_type == 'product') {
                    $receive_item_id            = $_POST['receive_product_id'][$r];
                    $receive_item_unit          = $_POST['receive_product_unit'][$r];
                    $receive_item_unit_quantity = $_POST['receive_quantity'][$r];
                    $receive_item_quantity      = $_POST['receive_base_quantity'][$r];
                } else {
                    $receive_item_id            = null;
                    $receive_item_unit          = null;
                    $receive_item_unit_quantity = null;
                    $receive_item_quantity      = null;
                }
                $item_interest      = isset($_POST['interest'][$r]) ? $_POST['interest'][$r] : null;
                $item_set_quantity  = $_POST['set_quantity'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_serial        = isset($_POST['serial'][$r]) && !empty($_POST['serial'][$r]) && $_POST['serial'][$r] != 'undefined' && $_POST['serial'][$r] != 'false' && $_POST['serial'][$r] != 'null' && $_POST['serial'][$r] != 'NULL' ? $_POST['serial'][$r] : null;
                $item_max_serial    = isset($_POST['max_serial'][$r]) ? $_POST['max_serial'][$r] : '';
                $item_detail        = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : '';
                $item_expiry        = null;
                $item_addition_type = null;
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details         = $this->products_model->getProductByID($item_id);
                    $product_receive_details = $this->products_model->getProductByID($receive_item_id);
                    $cost = $product_details->cost;
                    if ($item_type == 'digital') {
                        $digital = true;
                    }
                    $unit_price   = $this->bpas->formatDecimal($unit_price);
                    $subtotal     = ($unit_price * $item_set_quantity);
                    if ($reward_type == 'money') {
                        $interest = (($unit_price * $item_interest) / 100);
                        $subtotal = (($unit_price + ($interest ? $interest : 0)) * $item_set_quantity);
                    }
                    $unit         = $this->site->getUnitByID($item_unit);
                    $receive_unit = $this->site->getUnitByID($receive_item_unit);
                    $purchase_unit_cost = $product_details->cost;
                    $saleman = $this->site->getuser($this->input->post('saleman_by'));
                    if ($unit->id != $product_details->unit) {
                        $cost = $this->site->convertCostingToBase($purchase_unit_cost, $unit);
                    } else {
                        $cost = $cost;
                    }
                    $product = [
                        'warehouse_id'               => $warehouse_id,
                        'reward_id'                  => $reward_id,
                        'exchange_product_id'        => $item_id,
                        'exchange_product_code'      => $item_code,
                        'exchange_product_name'      => $item_name,
                        'exchange_product_unit_id'   => $unit ? $unit->id : null,
                        'exchange_product_unit_code' => $unit ? $unit->code : null,
                        'exchange_quantity'          => $item_quantity,
                        'exchange_unit_quantity'     => $item_unit_quantity,
                        'receive_product_id'         => $receive_item_id ? $receive_item_id : null,
                        'receive_product_code'       => $product_receive_details ? $product_receive_details->code : null,
                        'receive_product_name'       => $product_receive_details ? $product_receive_details->name : null,
                        'receive_product_unit_id'    => $receive_unit ? $receive_unit->id : null,
                        'receive_product_unit_code'  => $receive_unit ? $receive_unit->code : null,
                        'receive_quantity'           => $receive_item_quantity ? $receive_item_quantity : null,
                        'receive_unit_quantity'      => $receive_item_unit_quantity ? $receive_item_unit_quantity : null,
                        'set_quantity'               => $item_set_quantity,
                        'purchase_unit_cost'         => $purchase_unit_cost ? $purchase_unit_cost : NULL,
                        'unit_price'                 => $this->bpas->formatDecimal($unit_price),
                        'real_unit_price'            => $real_unit_price,
                        'subtotal'                   => $this->bpas->formatDecimal($subtotal),
                        'option_id'                  => $item_option,
                        'expiry'                     => $item_expiry,
                        'serial_no'                  => $item_serial,
                        'max_serial'                 => $item_max_serial,
                        'comment'                    => $item_detail,
                        'addition_type'              => $item_addition_type,
                        'interest'                   => $item_interest
                    ];
                    //========add accounting=========//
                    if($this->Settings->module_account == 1 && $item_type != 'manual' && ($status == 'completed')) {
                        $getproduct    = $this->site->getProductByID($item_id);
                        $default_sale  = $default_sale = ($item_type == 'standard') ? $this->accounting_setting->default_sale : $this->accounting_setting->other_income;
                        $inventory_acc = $this->accounting_setting->default_stock;
                        $costing_acc   = $this->accounting_setting->default_cost;
                        $accTrans[] = array(
                            'tran_type'     => 'Exchange',
                            'tran_date'     => $date,
                            'reference_no'  => $reference,
                            'account_code'  => $this->accounting_setting->default_stock,
                            'amount'        => -($cost * $item_unit_quantity),
                            'narrative'     => $this->site->getAccountName($this->accounting_setting->default_stock),
                            'description'   => $note,
                            'biller_id'     => $biller_id,
                            'project_id'    => $project_id,
                            'company_id'    => $company_id,
                            'created_by'    => $this->session->userdata('user_id'),
                        );
                        $accTrans[] = array(
                            'tran_type'     => 'Exchange',
                            'tran_date'     => $date,
                            'reference_no'  => $reference,
                            'account_code'  => $this->accounting_setting->default_cost,
                            'amount'        => ($cost * $item_unit_quantity),
                            'narrative'     => $this->site->getAccountName($this->accounting_setting->default_cost),
                            'description'   => $note,
                            'biller_id'     => $biller_id,
                            'project_id'    => $project_id,
                            'company_id'    => $company_id,
                            'created_by'    => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_cost)
                        );
                        $accTrans[] = array(
                            'tran_type'     => 'Exchange',
                            'tran_date'     => $date,
                            'reference_no'  => $reference,
                            'account_code'  => $default_sale,
                            'amount'        => -$subtotal,
                            'narrative'     => $this->site->getAccountName($default_sale),
                            'description'   => $note,
                            'biller_id'     => $biller_id,
                            'project_id'    => $project_id,
                            'company_id'    => $company_id,
                            'created_by'    => $this->session->userdata('user_id'),
                        );
                    }
                    //============end accounting=======//
                    $products[] = $product;
                    $total += $this->bpas->formatDecimal($subtotal, 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }
            $grand_total  = $this->bpas->formatDecimal($total, 4);
            $user         = $this->site->getUser($this->session->userdata('user_id'));
            $data         = [
                'date'                => $date,
                'project_id'          => $this->input->post('project'),
                'reference_no'        => $reference,
                'company_id'          => $company_id,
                'company'             => $company,
                'biller_id'           => $biller_id,
                'biller'              => $biller,
                'warehouse_id'        => $warehouse_id,
                'note'                => $note,
                'staff_note'          => $staff_note,
                'grand_total'         => $grand_total,
                'status'              => $status,
                'payment_status'      => $payment_status,
                'paid'                => 0, 
                'created_by'          => $this->session->userdata('user_id'),
                'category'            => $reward_category,
                'type'                => $reward_type,
            ];
            if ($payment_status == 'partial' || $payment_status == 'paid') {
                if ($this->input->post('paid_by') == 'deposit') {
                    if (!$this->site->check_customer_deposit($company_id, $this->input->post('amount-paid'))) {
                        $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                }
                if ($this->input->post('paid_by') == 'gift_card') {
                    $gc            = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
                    $amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                    $gc_balance    = $gc->balance - $amount_paying;
                    $payment       = [
                        'date'         => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'amount'       => $this->bpas->formatDecimal($amount_paying),
                        'paid_by'      => $this->input->post('paid_by'),
                        'cheque_no'    => $this->input->post('cheque_no'),
                        'cc_no'        => $this->input->post('gift_card_no'),
                        'cc_holder'    => $this->input->post('pcc_holder'),
                        'cc_month'     => $this->input->post('pcc_month'),
                        'cc_year'      => $this->input->post('pcc_year'),
                        'cc_type'      => $this->input->post('pcc_type'),
                        'created_by'   => $this->session->userdata('user_id'),
                        'note'         => $this->input->post('payment_note'),
                        'type'         => 'received',
                        'gc_balance'   => $gc_balance,
                    ];
                } else {
                    $payment = [
                        'date'         => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'amount'       => $this->bpas->formatDecimal($this->input->post('amount-paid')),
                        'paid_by'      => $this->input->post('paid_by'),
                        'cheque_no'    => $this->input->post('cheque_no'),
                        'cc_no'        => $this->input->post('pcc_no'),
                        'cc_holder'    => $this->input->post('pcc_holder'),
                        'cc_month'     => $this->input->post('pcc_month'),
                        'cc_year'      => $this->input->post('pcc_year'),
                        'cc_type'      => $this->input->post('pcc_type'),
                        'created_by'   => $this->session->userdata('user_id'),
                        'note'         => $this->input->post('payment_note'),
                        'type'         => 'received',
                    ];
                }
                $amount_paying = $this->bpas->formatDecimal($this->input->post('amount-paid'));
                //=====add accountig=====//
                if ($this->Settings->module_account == 1) {
                    if ($this->input->post('paid_by') == 'deposit') {
                        $payment['bank_account'] = $this->accounting_setting->default_sale_deposit;
                        $paying_to = $this->accounting_setting->default_sale_deposit;
                    } else {
                        $payment['bank_account'] = $this->input->post('bank_account');
                        $paying_to = $this->input->post('bank_account');
                    }
                    if($amount_paying < $grand_total){
                        $accTranPayments[] = array(
                            'tran_type'     => 'Payment',
                            'tran_date'     => $date,
                            'reference_no'  => $this->input->post('payment_reference_no'),
                            'account_code'  => $this->accounting_setting->default_receivable,
                            'amount'        => ($grand_total - $amount_paying),
                            'narrative'     => $this->site->getAccountName($this->accounting_setting->default_receivable),
                            'description'   => $this->input->post('payment_note'),
                            'biller_id'     => $biller_id,
                            'project_id'    => $project_id,
                            'company_id'    => $company_id,
                            'created_by'    => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                        );
                    }
                    $accTranPayments[] = array(
                        'tran_type'    => 'Payment',
                        'tran_date'    => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'account_code' => $paying_to,
                        'amount'       => $amount_paying,
                        'narrative'    => $this->site->getAccountName($paying_to),
                        'description'  => $this->input->post('payment_note'),
                        'biller_id'    => $biller_id,
                        'project_id'   => $project_id,
                        'company_id'   => $company_id,
                        'created_by'   => $this->session->userdata('user_id'),
                    );
                }
                //=====end accountig=====//
            } else {
                $accTranPayments = [];
                $payment         = [];
                $accTrans[] = array(
                    'tran_type'     => 'Exchange',
                    'tran_date'     => $date,
                    'reference_no'  => $reference,
                    'account_code'  => $this->accounting_setting->default_receivable,
                    'amount'        => $grand_total,
                    'narrative'     => $this->site->getAccountName($this->accounting_setting->default_receivable),
                    'biller_id'     => $biller_id,
                    'project_id'    => $project_id,
                    'company_id'    => $company_id,
                    'created_by'    => $this->session->userdata('user_id'),
                    'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                );
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
            // $this->bpas->print_arrays($data, $products, $payment);
        }
        if ($this->form_validation->run() == true && $this->products_model->addRewardExchange($data, $products, $payment, null, null, null, null)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang('reward_exchange_added'));
            admin_redirect('products/rewards_exchange/' . $reward_category);
        } else {
            $this->data['projects']      = $this->site->getAllProject();
            $this->data['error']         = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers']       = $this->site->getAllCompanies('biller');
            $this->data['data']          = $this->site->getBillerByUser($this->session->userdata('user_id'));
            $this->data['mbillers']      = $this->site->getAllCompaniesByBiller('biller', explode(',', $this->data['data']->multi_biller));
            $this->data['agencies']      = $this->site->getAllUsers();
            $this->data['payment_term']  = $this->site->getAllPaymentTerm();
            $this->data['warehouses']    = $this->site->getAllWarehouses();
            $this->data['tax_rates']     = $this->site->getAllTaxRates();
            $this->data['units']         = $this->site->getAllBaseUnits();
            $this->data['slnumber']      = $reward_category == 'customer' ? $this->site->getReference('crw') : $this->site->getReference('srw');
            $this->data['payment_ref']   = '';
            $this->data['category']      = $reward_category;
            $this->data['type']          = $reward_type;
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products/rewards_exchange/' . $reward_category), 'page' => lang('rewards_exchange')], ['link' => '#', 'page' => lang('add_reward_exchange')]];
            $meta = ['page_title' => lang('add_reward_exchange'), 'bc' => $bc];
            $this->page_construct('products/add_reward_exchange', $meta, $this->data);
        }
    }

    public function edit_reward_exchange($reward_category, $reward_type, $id = null)
    {
        $this->bpas->checkPermissions('edit', null, 'reward_exchange');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->products_model->getRewardExchangeByID($id);
        // $this->Settings->stock_received && $inv->type != 'money'
        if ($inv->type != 'money') {
            if ($inv->status != 'pending') {
                $this->session->set_flashdata('error', lang('reward_already_stock_received'));
                admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');   
            }
        }
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules($reward_category, lang($reward_category), 'required');
        $this->form_validation->set_rules('status', lang('status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $project_id             = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project;
            $warehouse_id           = $this->input->post('warehouse');
            $company_id             = $this->input->post($reward_category);
            $biller_id              = $this->input->post('biller');
            $total_items            = $this->input->post('total_items');
            $status                 = $this->input->post('status');
            $payment_status         = $this->input->post('payment_status');
            $payment_term           = $this->input->post('payment_term');
            $shipping               = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $company_details        = $this->site->getCompanyByID($company_id);
            $company                = !empty($company_details->company) && $company_details->company != '-' ? $company_details->company.'/'.$company_details->name : $company_details->name;
            $biller_details         = $this->site->getCompanyByID($biller_id);
            $biller                 = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company.'/'.$biller_details->name : $biller_details->name;
            $note                   = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note             = $this->bpas->clear_tags($this->input->post('staff_note'));
            $commission_product     = 0;
            $total                  = 0;
            $product_tax            = 0;
            $product_discount       = 0;
            $gst_data               = [];
            $total_cgst             = $total_sgst       = $total_igst       = 0;
            $i                      = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id                    = $_POST['product_id'][$r];
                $reward_id                  = $_POST['reward_id'][$r];
                $item_code                  = $_POST['product_code'][$r];
                $item_name                  = $_POST['product_name'][$r];
                $item_unit                  = $_POST['product_unit'][$r];
                $item_unit_quantity         = $_POST['quantity'][$r];
                $item_quantity              = $_POST['product_base_quantity'][$r];
                if ($reward_type == 'product') {
                    $receive_item_id            = $_POST['receive_product_id'][$r];
                    $receive_item_unit          = $_POST['receive_product_unit'][$r];
                    $receive_item_unit_quantity = $_POST['receive_quantity'][$r];
                    $receive_item_quantity      = $_POST['receive_base_quantity'][$r];
                } else {
                    $receive_item_id            = null;
                    $receive_item_unit          = null;
                    $receive_item_unit_quantity = null;
                    $receive_item_quantity      = null;
                }
                $item_interest      = isset($_POST['interest'][$r]) ? $_POST['interest'][$r] : null;
                $item_set_quantity  = $_POST['set_quantity'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_serial        = isset($_POST['serial'][$r]) && !empty($_POST['serial'][$r]) && $_POST['serial'][$r] != 'undefined' && $_POST['serial'][$r] != 'false' && $_POST['serial'][$r] != 'null' && $_POST['serial'][$r] != 'NULL' ? $_POST['serial'][$r] : null;
                $item_max_serial    = isset($_POST['max_serial'][$r]) ? $_POST['max_serial'][$r] : '';
                $item_detail        = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : '';
                $item_expiry        = null;
                $item_addition_type = null;
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details         = $this->products_model->getProductByID($item_id);
                    $product_receive_details = $this->products_model->getProductByID($receive_item_id);
                    $cost = $product_details->cost;
                    if ($item_type == 'digital') {
                        $digital = true;
                    } 
                    $unit_price   = $this->bpas->formatDecimal($unit_price);
                    $subtotal     = ($unit_price * $item_set_quantity);
                    if ($reward_type == 'money') {
                        $interest = (($unit_price * $item_interest) / 100);
                        $subtotal = (($unit_price + ($interest ? $interest : 0)) * $item_set_quantity);
                    }
                    $unit         = $this->site->getUnitByID($item_unit);
                    $receive_unit = $this->site->getUnitByID($receive_item_unit);
                    $purchase_unit_cost = $product_details->cost;
                    $saleman = $this->site->getuser($this->input->post('saleman_by'));
                    if ($unit->id != $product_details->unit) {
                        $cost =$this->site->convertCostingToBase($purchase_unit_cost, $unit);
                    } else {
                        $cost = $cost;
                    }
                    $product = [
                        'warehouse_id'               => $warehouse_id,
                        'reward_id'                  => $reward_id,
                        'exchange_product_id'        => $item_id,
                        'exchange_product_code'      => $item_code,
                        'exchange_product_name'      => $item_name,
                        'exchange_product_unit_id'   => $unit ? $unit->id : null,
                        'exchange_product_unit_code' => $unit ? $unit->code : null,
                        'exchange_quantity'          => $item_quantity,
                        'exchange_unit_quantity'     => $item_unit_quantity,
                        'receive_product_id'         => $receive_item_id ? $receive_item_id : null,
                        'receive_product_code'       => $product_receive_details ? $product_receive_details->code : null,
                        'receive_product_name'       => $product_receive_details ? $product_receive_details->name : null,
                        'receive_product_unit_id'    => $receive_unit ? $receive_unit->id : null,
                        'receive_product_unit_code'  => $receive_unit ? $receive_unit->code : null,
                        'receive_quantity'           => $receive_item_quantity ? $receive_item_quantity : null,
                        'receive_unit_quantity'      => $receive_item_unit_quantity ? $receive_item_unit_quantity : null,
                        'set_quantity'               => $item_set_quantity,
                        'purchase_unit_cost'         => $purchase_unit_cost ? $purchase_unit_cost : NULL,
                        'unit_price'                 => $this->bpas->formatDecimal($unit_price),
                        'real_unit_price'            => $real_unit_price,
                        'subtotal'                   => $this->bpas->formatDecimal($subtotal),
                        'option_id'                  => $item_option,
                        'expiry'                     => $item_expiry,
                        'serial_no'                  => $item_serial,
                        'max_serial'                 => $item_max_serial,
                        'comment'                    => $item_detail,
                        'addition_type'              => $item_addition_type,
                        'interest'                   => $item_interest
                    ];
                    //========add accounting=========//
                    if($this->Settings->module_account == 1 && $item_type != 'manual' && ($status == 'completed')) {
                        $getproduct    = $this->site->getProductByID($item_id);
                        $default_sale  = $default_sale = ($item_type == 'standard') ? $this->accounting_setting->default_sale : $this->accounting_setting->other_income;
                        $inventory_acc = $this->accounting_setting->default_stock;
                        $costing_acc   = $this->accounting_setting->default_cost;
                        $accTrans[] = array(
                            'tran_type'     => 'Exchange',
                            'tran_date'     => $date,
                            'reference_no'  => $reference,
                            'account_code'  => $this->accounting_setting->default_stock,
                            'amount'        => -($cost * $item_unit_quantity),
                            'narrative'     => $this->site->getAccountName($this->accounting_setting->default_stock),
                            'description'   => $note,
                            'biller_id'     => $biller_id,
                            'project_id'    => $project_id,
                            'company_id'    => $company_id,
                            'created_by'    => $this->session->userdata('user_id'),
                        );
                        $accTrans[] = array(
                            'tran_type'     => 'Exchange',
                            'tran_date'     => $date,
                            'reference_no'  => $reference,
                            'account_code'  => $this->accounting_setting->default_cost,
                            'amount'        => ($cost * $item_unit_quantity),
                            'narrative'     => $this->site->getAccountName($this->accounting_setting->default_cost),
                            'description'   => $note,
                            'biller_id'     => $biller_id,
                            'project_id'    => $project_id,
                            'company_id'    => $company_id,
                            'created_by'    => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_cost)
                        );
                        $accTrans[] = array(
                            'tran_type'     => 'Exchange',
                            'tran_date'     => $date,
                            'reference_no'  => $reference,
                            'account_code'  => $default_sale,
                            'amount'        => -$subtotal,
                            'narrative'     => $this->site->getAccountName($default_sale),
                            'description'   => $note,
                            'biller_id'     => $biller_id,
                            'project_id'    => $project_id,
                            'company_id'    => $company_id,
                            'created_by'    => $this->session->userdata('user_id'),
                        );
                    }
                    //============end accounting=======//
                    $products[] = $product;
                    $total += $this->bpas->formatDecimal($subtotal, 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }
            $grand_total  = $this->bpas->formatDecimal($total, 4);
            $user         = $this->site->getUser($this->session->userdata('user_id'));
            $data         = [
                'date'                => $date,
                'project_id'          => $this->input->post('project'),
                'reference_no'        => $reference,
                'company_id'          => $company_id,
                'company'             => $company,
                'biller_id'           => $biller_id,
                'biller'              => $biller,
                'warehouse_id'        => $warehouse_id,
                'note'                => $note,
                'staff_note'          => $staff_note,
                'grand_total'         => $grand_total,
                'status'              => $status,
                'payment_status'      => $payment_status,
                'paid'                => 0, 
                'created_by'          => $this->session->userdata('user_id'),
                'category'            => $reward_category,
                'type'                => $reward_type,
            ];
            if ($payment_status == 'partial' || $payment_status == 'paid') {
                if ($this->input->post('paid_by') == 'deposit') {
                    if (!$this->site->check_customer_deposit($company_id, $this->input->post('amount-paid'))) {
                        $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                }
                if ($this->input->post('paid_by') == 'gift_card') {
                    $gc            = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
                    $amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                    $gc_balance    = $gc->balance - $amount_paying;
                    $payment       = [
                        'date'         => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'amount'       => $this->bpas->formatDecimal($amount_paying),
                        'paid_by'      => $this->input->post('paid_by'),
                        'cheque_no'    => $this->input->post('cheque_no'),
                        'cc_no'        => $this->input->post('gift_card_no'),
                        'cc_holder'    => $this->input->post('pcc_holder'),
                        'cc_month'     => $this->input->post('pcc_month'),
                        'cc_year'      => $this->input->post('pcc_year'),
                        'cc_type'      => $this->input->post('pcc_type'),
                        'created_by'   => $this->session->userdata('user_id'),
                        'note'         => $this->input->post('payment_note'),
                        'type'         => 'received',
                        'gc_balance'   => $gc_balance,
                    ];
                } else {
                    $payment = [
                        'date'         => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'amount'       => $this->bpas->formatDecimal($this->input->post('amount-paid')),
                        'paid_by'      => $this->input->post('paid_by'),
                        'cheque_no'    => $this->input->post('cheque_no'),
                        'cc_no'        => $this->input->post('pcc_no'),
                        'cc_holder'    => $this->input->post('pcc_holder'),
                        'cc_month'     => $this->input->post('pcc_month'),
                        'cc_year'      => $this->input->post('pcc_year'),
                        'cc_type'      => $this->input->post('pcc_type'),
                        'created_by'   => $this->session->userdata('user_id'),
                        'note'         => $this->input->post('payment_note'),
                        'type'         => 'received',
                    ];
                }
                $amount_paying = $this->bpas->formatDecimal($this->input->post('amount-paid'));
                //=====add accountig=====//
                if ($this->Settings->module_account == 1) {
                    if ($this->input->post('paid_by') == 'deposit') {
                        $payment['bank_account'] = $this->accounting_setting->default_sale_deposit;
                        $paying_to = $this->accounting_setting->default_sale_deposit;
                    } else {
                        $payment['bank_account'] = $this->input->post('bank_account');
                        $paying_to = $this->input->post('bank_account');
                    }
                    if($amount_paying < $grand_total){
                        $accTranPayments[] = array(
                            'tran_type'     => 'Payment',
                            'tran_date'     => $date,
                            'reference_no'  => $this->input->post('payment_reference_no'),
                            'account_code'  => $this->accounting_setting->default_receivable,
                            'amount'        => ($grand_total - $amount_paying),
                            'narrative'     => $this->site->getAccountName($this->accounting_setting->default_receivable),
                            'description'   => $this->input->post('payment_note'),
                            'biller_id'     => $biller_id,
                            'project_id'    => $project_id,
                            'company_id'    => $company_id,
                            'created_by'    => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                        );
                    }
                    $accTranPayments[] = array(
                        'tran_type'    => 'Payment',
                        'tran_date'    => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'account_code' => $paying_to,
                        'amount'       => $amount_paying,
                        'narrative'    => $this->site->getAccountName($paying_to),
                        'description'  => $this->input->post('payment_note'),
                        'biller_id'    => $biller_id,
                        'project_id'   => $project_id,
                        'company_id'   => $company_id,
                        'created_by'   => $this->session->userdata('user_id'),
                    );
                }
            //=====end accountig=====//
            } else {
                $accTranPayments= [];
                $payment = [];
                $accTrans[] = array(
                    'tran_type'     => 'Exchange',
                    'tran_date'     => $date,
                    'reference_no'  => $reference,
                    'account_code'  => $this->accounting_setting->default_receivable,
                    'amount'        => $grand_total,
                    'narrative'     => $this->site->getAccountName($this->accounting_setting->default_receivable),
                    'biller_id'     => $biller_id,
                    'project_id'    => $project_id,
                    'company_id'    => $company_id,
                    'created_by'    => $this->session->userdata('user_id'),
                    'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                );
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
            // $this->bpas->print_arrays($id, $data, $products, $accTrans, $accTranPayments, $commission_product);
        }
        if ($this->form_validation->run() == true && $this->products_model->updateRewardExchange($id, $data, $products, null, null, null)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang('reward_exchange_updated'));
            admin_redirect('products/rewards_exchange/customer');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv']   = $inv;
            $this->bpas->deadlineDayEditing($this->data['inv']->date);
            $inv_items = $this->products_model->getAllRewardItems($id);
            $c = rand(100000, 9999999);
            $r = 0; $pr = array();
            foreach ($inv_items as $item) {
                $reward                 = $this->products_model->getRewardByID($item->reward_id);
                $exchange_product       = $this->products_model->getProductByID($item->exchange_product_id);
                $receive_product        = $this->products_model->getProductByID($item->receive_product_id);
                $cate_id                = $item->subcategory_id ? $item->subcategory_id : $item->category_id;
                $c                      = uniqid(mt_rand(), true);
                $row = $this->products_model->getWarehouseProduct($item->exchange_product_id, $item->warehouse_id);
                if (!$row) {
                    $row             = json_decode('{}');
                    $row->tax_method = 0;
                    $row->quantity   = 0;
                } else {
                    unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                }
                $option                 = false;
                $row->quantity          = 0;
                $row->item_tax_method   = $row->tax_method;
                $row->discount          = 0;          
                $row->serial            = '';
                $options                = $this->products_model->getProductOptions($row->id, $item->warehouse_id);
                $product_options        = $this->site->getAllProductOption($row->id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->products_model->getProductOptionByID($option_id) : $options[0];
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt        = json_decode('{}');
                    $opt->price = 0;
                    $option_id  = false;
                }
                $row->option = $option_id;
                $pis         = $this->site->getPurchasedItemstoSales($item->receive_product_id, $item->warehouse_id, $item->option_id);
                $set_price   = $this->site->getUnitByProId($row->id);
                if ($pis) {
                    $row->quantity = 0;
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        $pis = $this->site->getPurchasedItemstoSales($row->id, $warehouse_id, $row->option);
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
                $row->reward_id             = $item->reward_id;
                $row->qty                   = $item->exchange_unit_quantity;
                $row->base_quantity         = $item->exchange_quantity;
                $row->price                 = $reward->amount;
                $row->real_unit_price       = $reward->amount;
                $row->base_unit_price       = $reward->amount;
                $row->unit                  = $exchange_product->unit;
                $row->base_unit             = $exchange_product->unit;
                if ($reward_type == 'product') {
                    $row->receive_product_id    = $item->receive_product_id;
                    $row->receive_qty           = $item->receive_unit_quantity;
                    $row->receive_base_quantity = $item->receive_quantity;
                    $row->receive_unit          = $receive_product->unit;
                    $row->receive_base_unit     = $receive_product->unit;
                }
                $row->interest              = $item->interest;
                $row->set_quantity          = $item->set_quantity;
                $row->comment               = '';
                $categories                 = $this->site->getCategoryByID($cate_id);
                $fiber_type                 = false;
                $combo_items                = false;
                $fibers                     = false;
                $units                      = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate                   = $this->site->getTaxRateByID($row->tax_rate);
                $ri                         = $this->Settings->item_addition ? $row->id : $c;
                $pr[$ri] = [
                    'id'                => $c, 
                    'item_id'           => $row->id, 
                    'row'               => $row, 
                    'label'             => $row->name . ' (' . $row->code . ')', 
                    'category'          => $row->category_id, 
                    'tax_rate'          => $tax_rate, 
                    'units'             => $units,
                    'reward'            => $reward,
                    'exchange_product'  => $exchange_product,
                    'receive_product'   => $receive_product, 
                    'combo_items'       => false, 
                    'set_price'         => false, 
                    'options'           => false, 
                    'fiber'             => false, 
                    'expiry'            => null,
                ];
                $r++;
                if ($row->type == 'combo') {
                    $combo_items = json_decode($item->combo_product);
                }
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['projects']         = $this->site->getAllProject();
            $this->data['reward_items']     = json_encode($pr);
            $this->data['id']               = $id;
            $this->data['payment_term']     = $this->site->getAllPaymentTerm();
            $this->data['agencies']         = $this->site->getAllUsers();
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->data['units']            = $this->site->getAllBaseUnits();
            $this->data['tax_rates']        = $this->site->getAllTaxRates();
            $this->data['warehouses']       = $this->site->getAllWarehouses();
            $this->data['zones']            = $this->site->getAllZones();
            $this->data['type']             = $reward_type;
            $this->data['category']         = $reward_category;
            $Settings                       = $this->site->getSettings();
            $this->data['salemans']         = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('rewards_exchange'), 'page' => lang('products')], ['link' => '#', 'page' => lang('edit_reward_exchange')]];
            $meta = ['page_title' => lang('edit_reward_exchange'), 'bc' => $bc];
            $this->page_construct('products/edit_reward_exchange', $meta, $this->data);
        }
    }

    public function delete_reward_exchange($id)
    {
        $this->bpas->checkPermissions('delete', null, 'reward_exchange');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->products_model->getRewardExchangeByID($id);
        // $this->Settings->stock_received && $inv->type != 'money'
        if ($inv->type != 'money') {
            if ($inv->status != 'pending') {
                $this->session->set_flashdata('error', lang('reward_already_stock_received'));
                admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');   
            }
        }
        if ($this->products_model->deleteRewardExchange($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('reward_exchange_deleted')]);
            }
            $this->session->set_flashdata('message', lang('reward_exchange_deleted'));
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function payments($id = null)
    {
        $this->bpas->checkPermissions(false, true);
        $this->data['payments'] = $this->products_model->getRewardInvoicePayments($id);
        $this->data['inv']      = $this->products_model->getRewardExchangeByID($id);
        $this->load->view($this->theme . 'sales/reward_payments', $this->data);
    }

    public function add_payment($id = null, $down_payment_id = null)
    {
        $this->bpas->checkPermissions('payments', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->input->get('payment_term')) {
            $payment_term = $this->input->get('payment_term');
        } else {
            $payment_term = null;
        }
        $sale    = $this->products_model->getRewardExchangeByID($id);
        $balance = $sale->grand_total - $sale->paid;
        if ($sale->payment_status == 'paid' && $sale->grand_total == $sale->paid) {
            $this->session->set_flashdata('error', lang('reward_already_paid'));
            $this->bpas->md();
        }
        $this->form_validation->set_rules('paid_by', lang('paid_by'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $paid_by = $this->site->getCashAccountByCode($this->input->post('paid_by'));
            $paid_by_account = $paid_by->account_code;
            if($this->input->post('amount-paid') == '0') {
                $this->session->set_flashdata('error', lang('payment_not_be_zero'));
                $this->bpas->md();
            }
            $sale = $this->products_model->getRewardExchangeByID($this->input->post('reward_id'));
            if ($this->input->post('paid_by') == 'deposit') {
                $customer_id = $sale->company_id;
                if (!$this->site->check_customer_deposit($customer_id, $this->input->post('amount_paid_usd'), $this->input->post('amount_paid_khr'), $this->input->post('amount_paid_thb'))) {
                    $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            // $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay');
            $reference_no = $this->site->CheckedPaymentReference($this->input->post('reference_no'), $this->site->getReference('pay'));
            $currencies = array();
            $camounts = $this->input->post("c_amount");
            if(!empty($camounts)){
                foreach($camounts as $key => $camount){
                    $currency = $this->input->post("currency");
                    $rate = $this->input->post("rate");
                    $currencies[] = array(
                        "amount"   => $camounts[$key],
                        "currency" => $currency[$key],
                        "rate"     => $rate[$key],
                    );
                }
            }
            $payment = [
                'date'         => $date,
                'reward_exchange_id' => $this->input->post('reward_id'),
                'reference_no' => $reference_no,
                'amount'       => $this->input->post('amount-paid'),
                'discount'     => $this->input->post('discount'),
                'paid_by'      => $this->input->post('paid_by'),
                'currencies'   => json_encode($currencies),
                'cheque_no'    => $this->input->post('cheque_no'),
                'cc_no'        => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                'cc_holder'    => $this->input->post('pcc_holder'),
                'cc_month'     => $this->input->post('pcc_month'),
                'cc_year'      => $this->input->post('pcc_year'),
                'cc_type'      => $this->input->post('pcc_type'),
                'note'         => $this->input->post('note'),
                'created_by'   => $this->session->userdata('user_id'),
                'type'         => $sale->status == 'returned' ? 'returned' : 'received',
                'bank_account' => $paid_by_account,
                'payment_term' => $this->input->post('payment_term') ? $this->input->post('payment_term') : null,
                'write_off'    => $this->input->post('write_off') ? $this->input->post('write_off') : 0
            ];
            //=====add accounting=====//
            if($this->Settings->module_account == 1) {
                if($this->input->post('write_off')){
                    $accTranPayments[] = array(
                        'tran_no'   => $id,
                        'tran_type' => 'Payment',
                        'tran_date' => $date,
                        'reference_no' => $reference_no,
                        'account_code' => $this->accounting_setting->default_receivable,
                        'amount' => -($this->input->post('amount-paid')),
                        'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                        'description' => $this->input->post('note'),
                        'biller_id' => $sale->biller_id,
                        'project_id' => $sale->project_id,
                        'customer_id' => $sale->company_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                    );
                    $accTranPayments[] = array(
                        'tran_no'   => $id,
                        'tran_type' => 'Payment',
                        'tran_date' => $date,
                        'reference_no' => $reference_no,
                        'account_code' => $this->accounting_setting->default_write_off,
                        'amount' => $this->input->post('amount-paid'),
                        'narrative' => $this->site->getAccountName($this->accounting_setting->default_write_off),
                        'description' => $this->input->post('note'),
                        'biller_id' => $sale->biller_id,
                        'project_id' => $sale->project_id,
                        'customer_id' => $sale->company_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => 0, // 1= bussiness, 2 = investing, 3= financing activity
                    );
                } else {
                    if ($this->input->post('amount-paid') > $balance) {
                        $accTranPayments[] = array(
                            'tran_no'   => $id,
                            'tran_type' => 'Payment',
                            'tran_date' => $date,
                            'reference_no' => $reference_no,
                            'account_code' => $this->accounting_setting->default_receivable,
                            'amount' => -($balance+$this->input->post('discount')),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                            'description' => $this->input->post('note'),
                            'biller_id' => $sale->biller_id,
                            'project_id' => $sale->project_id,
                            'customer_id' => $sale->company_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                        );
                        $other_amount = $this->input->post('amount-paid') - $balance;
                        $accTranPayments[] = array(
                            'tran_no'   => $id,
                            'tran_type' => 'Payment',
                            'tran_date' => $date,
                            'reference_no' => $reference_no,
                            'account_code' => $this->accounting_setting->other_income,
                            'amount' => -($other_amount),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->other_income),
                            'description' => $this->input->post('note'),
                            'biller_id' => $sale->biller_id,
                            'project_id' => $sale->project_id,
                            'customer_id' => $sale->company_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => 1, // 1= bussiness, 2 = investing, 3= financing activity
                        );
                    } else {
                        $amount = $this->input->post('amount-paid');
                        $accTranPayments[] = array(
                            'tran_no'   => $id,
                            'tran_type' => 'Payment',
                            'tran_date' => $date,
                            'reference_no' => $reference_no,
                            'account_code' => $this->accounting_setting->default_receivable,
                            'amount' => -($this->input->post('amount-paid')+$this->input->post('discount')),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                            'description' => $this->input->post('note'),
                            'biller_id' => $sale->biller_id,
                            'project_id' => $sale->project_id,
                            'customer_id' => $sale->company_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                        );
                    }
                    if ($this->input->post('paid_by') == 'deposit') {
                        $paying_to = isset($this->accounting_setting->default_sale_deposit) ? $this->accounting_setting->default_sale_deposit : '';
                    } else {
                        $paying_to = isset($paid_by_account) ? $paid_by_account : $this->accounting_setting->default_cash ;
                    }
                    $accTranPayments[] = array(
                        'tran_no'   => $id,
                        'tran_type' => 'Payment',
                        'tran_date' => $date,
                        'reference_no' => $reference_no,
                        'account_code' => $paying_to,
                        'amount' => $this->input->post('amount-paid'),
                        'narrative' => $this->site->getAccountName($paying_to),
                        'description' => $this->input->post('note'),
                        'biller_id' => $sale->biller_id,
                        'project_id' => $sale->project_id,
                        'customer_id' => $sale->company_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => 1, // 1= bussiness, 2 = investing, 3= financing activity
                    );
                }
                if($this->input->post('discount') != 0){
                    $accTranPayments[] = array(
                        'tran_no'   => $id,
                        'tran_type'     => 'Payment',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $this->accounting_setting->default_sale_discount,
                        'amount'        => $this->input->post('discount'),
                        'narrative'     => 'Purchase Payment Discount '.$reference_no,
                        'description'   => $this->input->post('note'),
                        'biller_id'     => $sale->biller_id,
                        'project_id'    => $sale->project_id,
                        'customer_id'   => $sale->company_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'activity_type' => $this->site->get_activity($this->accounting_setting->default_sale_discount)
                    );
                }
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
        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->products_model->addRewardPayment($payment, $customer_id, $accTranPayments)) {
            if ($sale->shop) {
                $this->load->library('sms');
                $this->sms->paymentReceived($sale->id, $payment['reference_no'], $payment['amount']);
            }
            $this->session->set_flashdata('message', lang('payment_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if ($sale->status == 'returned' && $sale->paid == $sale->grand_total) {
                $this->session->set_flashdata('warning', lang('payment_was_returned'));
                $this->bpas->md();
            }
            $this->data['inv']             = $sale;
            $this->data['currencies']      = $this->site->getAllCurrencies();
            $this->data['payment_term']    = $payment_term;
            $this->data['payments']        = $this->products_model->getRewardInvoicePayments($sale->id);
            $this->data['deposit']         = $this->site->getCustomerDeposit($sale->company_id);
            $this->data['currency_dollar'] = $this->site->getCurrencyByCode('USD');
            $this->data['currency_riel']   = $this->site->getCurrencyByCode('KHR');
            $this->data['currency_baht']   = $this->site->getCurrencyByCode('THB');
            $this->data['payment_ref']     = $this->site->getReference('pay');
            $this->data['modal_js']        = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/add_reward_payment', $this->data);
        }
    }

    public function edit_payment($id = null)
    {
        $this->bpas->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->input->get('payment_term')) {
            $payment_term = $this->input->get('payment_term');
        } else {
            $payment_term = null;
        }
        $payment = $this->products_model->getPaymentByID($id);
        $sale    = $this->products_model->getRewardExchangeByID($payment->reward_exchange_id);
        if ($payment->paid_by == 'ppp' || $payment->paid_by == 'stripe' || $payment->paid_by == 'paypal' || $payment->paid_by == 'skrill') {
            $this->session->set_flashdata('error', lang('x_edit_payment'));
            $this->bpas->md();
        }
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('amount-paid', lang('amount'), 'required');
        $this->form_validation->set_rules('paid_by', lang('paid_by'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $paid_by = $this->site->getCashAccountByCode($this->input->post('paid_by'));
            $paid_by_account = $paid_by->account_code;
            $last_payment = $payment->amount;
            if ($this->input->post('paid_by') == 'deposit') {
                $customer_id = $sale->company_id;
                $amount_usd  = $this->input->post('amount_paid_usd') - $payment->amount_usd;
                $amount_khr  = $this->input->post('amount_paid_khr') - $payment->amount_khr;
                $amount_thb  = $this->input->post('amount_paid_thb') - $payment->amount_thb;
                if (!$this->site->check_customer_deposit($customer_id, $amount_usd, $amount_khr, $amount_thb)) {
                    $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $payment->date;
            }
            $currencies = array();
            $camounts = $this->input->post("c_amount");
            if(!empty($camounts)){
                foreach($camounts as $key => $camount){
                    $currency = $this->input->post("currency");
                    $rate = $this->input->post("rate");
                    $currencies[] = array(
                        "amount"   => $camounts[$key],
                        "currency" => $currency[$key],
                        "rate"     => $rate[$key],
                    );
                }
            }
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay');
            $payment = [
                'date'         => $date,
                'reward_exchange_id' => $this->input->post('reward_id'),
                'reference_no' => $this->input->post('reference_no'),
                'amount'       => $this->input->post('amount-paid'),
                'discount'     => $this->input->post('discount'),
                'currencies'   => json_encode($currencies),
                'paid_by'      => $this->input->post('paid_by'),
                'cheque_no'    => $this->input->post('cheque_no'),
                'cc_no'        => $this->input->post('pcc_no'),
                'cc_holder'    => $this->input->post('pcc_holder'),
                'cc_month'     => $this->input->post('pcc_month'),
                'cc_year'      => $this->input->post('pcc_year'),
                'cc_type'      => $this->input->post('pcc_type'),
                'note'         => $this->input->post('note'),
                'created_by'   => $this->session->userdata('user_id'),
                'bank_account' => $paid_by_account,
                'write_off'    => $this->input->post('write_off')?$this->input->post('write_off'):0
            ];
            //=====add accounting=====//
            if ($this->Settings->module_account == 1) {
                $balance = $sale->grand_total - ($sale->paid - $last_payment);
                if ($this->input->post('write_off')) {
                    $accTranPayments[] = array(
                        'tran_no' => $this->input->post('reward_id'),
                        'payment_id' => $id,
                        'tran_type' => 'Payment',
                        'tran_date' => $date,
                        'reference_no' => $reference_no,
                        'account_code' => $this->accounting_setting->default_receivable,
                        'amount' => -($this->input->post('amount-paid')+$this->input->post('discount')),
                        'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                        'description' => $this->input->post('note'),
                        'biller_id' => $sale->biller_id,
                        'project_id' => $sale->project_id,
                        'customer_id' => $sale->company_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                    );
                    $accTranPayments[] = array(
                        'tran_no'   => $this->input->post('reward_id'),
                        'payment_id' => $id,
                        'tran_type' => 'Payment',
                        'tran_date' => $date,
                        'reference_no' => $reference_no,
                        'account_code' => $this->accounting_setting->default_write_off,
                        'amount' => $this->input->post('amount-paid'),
                        'narrative' => $this->site->getAccountName($this->accounting_setting->default_write_off),
                        'description' => $this->input->post('note'),
                        'biller_id' => $sale->biller_id,
                        'project_id' => $sale->project_id,
                        'customer_id' => $sale->company_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => 0, // 1= bussiness, 2 = investing, 3= financing activity
                    );
                } else {
                        $amount = $this->input->post('amount-paid');
                        $accTranPayments[] = array(
                            'tran_no' => $this->input->post('reward_id'),
                            'payment_id' => $id,
                            'tran_type' => 'Payment',
                            'tran_date' => $date,
                            'reference_no' => $reference_no,
                            'account_code' => $this->accounting_setting->default_receivable,
                            'amount' => -($this->input->post('amount-paid')+$this->input->post('discount')),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                            'description' => $this->input->post('note'),
                            'biller_id' => $sale->biller_id,
                            'project_id' => $sale->project_id,
                            'customer_id' => $sale->company_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                        );
                        if ($this->input->post('paid_by') == 'deposit') {
                            $paying_to = $this->accounting_setting->default_sale_deposit;
                        } else {
                            $paying_to = isset($paid_by_account) ? $paid_by_account : $this->accounting_setting->default_cash ;
                        }
                        $accTranPayments[] = array(
                            'tran_no' => $this->input->post('reward_id'),
                            'payment_id' => $id,
                            'tran_type' => 'Payment',
                            'tran_date' => $date,
                            'reference_no' => $reference_no,
                            'account_code' => $paying_to,
                            'amount' => $this->input->post('amount-paid'),
                            'narrative' => $this->site->getAccountName($paying_to),
                            'description' => $this->input->post('note'),
                            'biller_id' => $sale->biller_id,
                            'project_id' => $sale->project_id,
                            'customer_id' => $sale->company_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => 1 // 1= bussiness, 2 = investing, 3= financing activity
                        );
                    }
                    if ($this->input->post('discount') != 0) {
                        $accTranPayments[] = array(
                            'tran_no'       => $this->input->post('reward_id'),
                            'payment_id'    => $id,
                            'tran_type'     => 'Payment',
                            'tran_date'     => $date,
                            'reference_no'  => $reference_no,
                            'account_code'  => $this->accounting_setting->default_sale_discount,
                            'amount'        => $this->input->post('discount'),
                            'narrative'     => 'Sale Payment Discount '.$reference_no,
                            'description'   => $this->input->post('note'),
                            'biller_id'     => $sale->biller_id,
                            'project_id'    => $sale->project_id,
                            'customer_id'   => $sale->company_id,
                            'created_by'    => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_sale_discount)
                        );
                    }
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
            } elseif ($this->input->post('edit_payment')) {
                $this->session->set_flashdata('error', validation_errors());
                redirect($_SERVER['HTTP_REFERER']);
            }
        if ($this->form_validation->run() == true && $this->products_model->updateRewardPayment($id, $payment, $customer_id, $accTranPayments)) {
            $this->session->set_flashdata('message', lang('payment_updated'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv']             = $sale;
            $this->data['payment']         = $payment;
            $this->data['payment_term']    = $payment_term;
            $this->data['currencies']      = $this->site->getAllCurrencies();
            $this->data['deposit']         = (isset($sale->company_id) ? $this->site->getCustomerDeposit($sale->company_id) : null);
            $this->data['modal_js']        = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/edit_reward_payment', $this->data);
        }
    }

    public function delete_reward_payment($id = null)
    {
        $this->bpas->checkPermissions('delete');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->products_model->deleteRewardPayment($id)) {
            $this->session->set_flashdata('message', lang('payment_deleted'));
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function reward_suggestions($reward_category, $reward_type)
    {
        $term         = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);
        $customer_id  = $this->input->get('customer_id', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed       = $this->bpas->analyze_term($term);
        $sr             = $analyzed['term'];
        $option_id      = $analyzed['option_id'];
        $warehouse      = $this->site->getWarehouseByID($warehouse_id);
        $rows           = $this->products_model->getProductRewardNames($sr, $warehouse_id, $reward_category, $reward_type);
        if ($rows) {
            $r = 0; $pr = array();
            foreach ($rows as $row) {
                $reward                 = $this->products_model->getRewardByID($row->reward_id);
                $exchange_product       = $this->products_model->getProductByID($reward->exchange_product_id);
                $receive_product        = $reward->receive_product_id ? $this->products_model->getProductByID($reward->receive_product_id) : null;
                $cate_id                = $row->subcategory_id ? $row->subcategory_id : $row->category_id;
                $c                      = uniqid(mt_rand(), true);
                unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $option                 = false;
                $row->quantity          = 0;
                $row->item_tax_method   = $row->tax_method;
                $row->discount          = 0;          
                $row->serial            = '';
                $options                = $this->products_model->getProductOptions($row->id, $warehouse_id);
                $product_options        = $this->site->getAllProductOption($row->id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->products_model->getProductOptionByID($option_id) : $options[0];
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt        = json_decode('{}');
                    $opt->price = 0;
                    $option_id  = false;
                }
                $row->option = $option_id;
                $pis         = $this->site->getPurchasedItemstoSales($row->id, $warehouse_id, $row->option);
                $set_price   = $this->site->getUnitByProId($row->id);
                if ($pis) {
                    $row->quantity = 0;
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        $pis = $this->site->getPurchasedItemstoSales($row->id, $warehouse_id, $row->option);
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
                $row->qty                   = $reward->exchange_quantity;
                $row->base_quantity         = $reward->exchange_quantity;
                $row->price                 = $reward->amount;
                $row->real_unit_price       = $reward->amount;
                $row->base_unit_price       = $reward->amount;
                $row->interest              = $reward->interest;
                $row->unit                  = $exchange_product->unit;
                $row->base_unit             = $exchange_product->unit;
                if ($reward_type == 'product') {
                    $row->receive_product_id    = $reward->receive_product_id;
                    $row->receive_qty           = $reward->receive_quantity;
                    $row->receive_base_quantity = $reward->receive_quantity;
                    $row->receive_unit          = $receive_product->unit;
                    $row->receive_base_unit     = $receive_product->unit;
                }
                $row->set_quantity          = 1;
                $row->comment               = '';
                $categories                 = $this->site->getCategoryByID($cate_id);
                $fiber_type                 = false;
                $combo_items                = false;
                $fibers                     = false;
                $units                      = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate                   = $this->site->getTaxRateByID($row->tax_rate);
                $pr[] = [
                    'id'                => sha1($c . $r), 
                    'item_id'           => $row->id, 
                    'row'               => $row, 
                    'label'             => $row->name . ' (' . $row->code . ')', 
                    'category'          => $row->category_id, 
                    'tax_rate'          => $tax_rate, 
                    'units'             => $units,
                    'reward'            => $reward, 
                    'exchange_product'  => $exchange_product,
                    'receive_product'   => $receive_product, 
                    'combo_items'       => false, 
                    'set_price'         => false, 
                    'options'           => false, 
                    'fiber'             => false, 
                    'expiry'            => null
                ];
                $r++;
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    public function modal_view_reward_exchange($id)
    {
        $this->bpas->checkPermissions('index', null, 'reward_exchange');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->products_model->getRewardExchangeByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by, true);
        }
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
        $this->data['company']     = $this->site->getCompanyByID($inv->company_id);
        $this->data['created_by']  = $this->site->getUser($inv->created_by);
        $this->data['updated_by']  = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']         = $inv;
        $this->data['currency']    = $this->site->getCurrencyByCode($inv->currency);
        $this->data['rows']        = $this->products_model->getAllRewardExchangeItems($id);
        $this->load->view($this->theme . 'products/modal_view_reward_exchange', $this->data);
    }

    public function add_reward_stock_received($id = null)
    {
        $this->bpas->checkPermissions('add', null, 'stock_received');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->products_model->getRewardExchangeByID($id);
        if (!empty($inv) && $inv->status == 'completed') {
            $this->session->set_flashdata('error', 'stock_already_received!');
            redirect($_SERVER['HTTP_REFERER']);
        } elseif ($inv->type == 'money') {
            $this->session->set_flashdata('error', 'reward_exchange_type_is_money!');
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('total_items', $this->lang->line('order_items'), 'required');
        $this->form_validation->set_rules('reference_no', $this->lang->line('ref_no'), 'required');
        $this->form_validation->set_rules('warehouse', $this->lang->line('warehouse'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('project', $this->lang->line('project'), '');
        $this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $project_id   = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project;
            $warehouse_id = $this->input->post('warehouse');
            $status       = $this->input->post('status') ? $this->input->post('status') : 'received';
            $note         = $this->bpas->clear_tags($this->input->post('note'));
            $i            = sizeof($_POST['product']);
            for ($r = 0; $r < $i; $r++) {
                $item_id                 = $_POST['product'][$r];
                $item_quantity           = $_POST['quantity'][$r];
                $item_option             = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $item_expiry             = isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r]) && $_POST['expiry'][$r] != 'false' && $_POST['expiry'][$r] != 'undefined' && $_POST['expiry'][$r] != 'null' && $_POST['expiry'][$r] != 'NULL' && $_POST['expiry'][$r] != '00/00/0000' && $_POST['expiry'][$r] != '' ? $this->bpas->fsd($_POST['expiry'][$r]) : null; 
                $reward_exchange_item_id = $_POST['reward_exchange_item_id'][$r];
                $item_addition_type      = null;
                if (isset($item_id) && isset($item_quantity)) {
                    $product_details = $this->purchases_model->getProductByID($item_id);
                    $product = [
                        'reward_exchange_item_id' => $reward_exchange_item_id,
                        'product_id'              => $product_details->id,
                        'quantity'                => $item_quantity,
                        'option_id'               => $item_option,
                        'addition_type'           => $item_addition_type,
                        'expiry'                  => $item_expiry,
                    ];
                    $products[] = $product;
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }
            $data = [
                'date'               => $date,
                'reward_exchange_id' => $id,
                'reference_no'       => ($this->input->post('received_reference_no') ? $this->input->post('received_reference_no') : $this->site->getReference('str')),
                'created_by'         => $this->session->userdata('user_id'),
                'total_quantity'     => $this->input->post('total_items'),
                'warehouse_id'       => $warehouse_id,
                'note'               => $note,
            ];
            // $this->bpas->print_arrays($id, $data, $products);
        }
        if ($this->form_validation->run() == true && $this->products_model->addRewardStockReceived($id, $data, $products)) {
            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line('stock_received_added'));
            admin_redirect('products/rewards_stock_received');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['id']           = $id;
            $this->data['inv']          = $inv;
            $this->data['inv_items']    = $this->products_model->getAllRewardItems_x_Balance($id);
            $this->data['reference_no'] = $this->site->getReference('str');
            $this->data['company']      = $this->site->getAllCompanies('supplier');
            $this->data['categories']   = $this->site->getAllCategories();
            $this->data['tax_rates']    = $this->site->getAllTaxRates();
            $this->data['warehouse']    = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['projects']     = $this->site->getAllProject();
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->session->set_userdata('remove_pols', 1);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('add_reward_stock_received')]];
            $meta = ['page_title' => lang('add_reward_stock_received'), 'bc' => $bc];
            $this->page_construct('products/add_reward_stock_received', $meta, $this->data);
        }
    }

    public function edit_reward_stock_received($id = null) 
    {
        $this->bpas->checkPermissions('edit', null, 'stock_received');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $stock_received  = $this->products_model->getRewardStockReceivedByID($id);
        $reward_exchange = $this->products_model->getRewardExchangeByID($stock_received->reward_exchange_id);
        if (!$this->session->userdata('edit_right')) {
            $this->bpas->view_rights($stock_received->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('total_items', $this->lang->line('order_items'), 'required');
        $this->form_validation->set_rules('reference_no', $this->lang->line('ref_no'), 'required');
        $this->form_validation->set_rules('warehouse', $this->lang->line('warehouse'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('project', $this->lang->line('project'), '');
        $this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $stock_received->date;
            }
            $project_id   = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project;
            $warehouse_id = $this->input->post('warehouse');
            $status       = $this->input->post('status') ? $this->input->post('status') : 'received';
            $note         = strip_tags(html_entity_decode($this->input->post('note')));
            $i            = sizeof($_POST['product']);
            for ($r = 0; $r < $i; $r++) {
                $item_id                 = $_POST['product'][$r];
                $item_quantity           = $_POST['quantity'][$r];
                $item_option             = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $item_expiry             = isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r]) && $_POST['expiry'][$r] != 'false' && $_POST['expiry'][$r] != 'undefined' && $_POST['expiry'][$r] != 'null' && $_POST['expiry'][$r] != 'NULL' && $_POST['expiry'][$r] != '00/00/0000' && $_POST['expiry'][$r] != '' ? $this->bpas->fsd($_POST['expiry'][$r]) : null; 
                $reward_exchange_item_id = $_POST['reward_exchange_item_id'][$r];
                $item_addition_type      = null;
                if (isset($item_id) && isset($item_quantity)) {
                    $product_details = $this->site->getProductByID($item_id);
                    $product = [
                        'reward_exchange_item_id' => $reward_exchange_item_id,
                        'product_id'              => $product_details->id,
                        'quantity'                => $item_quantity,
                        'option_id'               => $item_option,
                        'addition_type'           => $item_addition_type,
                        'expiry'                  => $item_expiry,
                    ];
                    $products[] = $product;
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }
            $data = [
                'date'               => $date,
                'reward_exchange_id' => $reward_exchange->id,
                'reference_no'       => ($this->input->post('received_reference_no') ? $this->input->post('received_reference_no') : $this->site->getReference('str')),
                'total_quantity'     => $this->input->post('total_items'),
                'warehouse_id'       => $warehouse_id,
                'note'               => $note,
                'updated_by'         => $this->session->userdata('user_id'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ];
            // $this->bpas->print_arrays($id, $data, $products);
        }
        if ($this->form_validation->run() == true && $this->products_model->updateRewardStockReceived($reward_exchange->id, $id, $data, $products)) {
            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line('stock_received_updated'));
            admin_redirect('products/rewards_stock_received');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['id']           = $id;
            $this->data['inv']          = $reward_exchange;
            $this->data['inv_items']    = $this->products_model->getAllRewardItems_x_Balance($reward_exchange->id);
            $this->data['stock_received']       = $stock_received;
            $this->data['stock_received_items'] = $this->products_model->getRewardStockReceivedItems($id);
            $this->data['reference_no'] = $this->site->getReference('str');
            $this->data['company']      = $this->site->getAllCompanies('supplier');
            $this->data['categories']   = $this->site->getAllCategories();
            $this->data['warehouse']    = $this->site->getWarehouseByID($stock_received->warehouse_id);
            $this->data['projects']     = $this->site->getAllProject();
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->session->set_userdata('remove_pols', 1);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('edit_reward_stock_received')]];
            $meta = ['page_title' => lang('edit_reward_stock_received'), 'bc' => $bc];
            $this->page_construct('products/edit_reward_stock_received', $meta, $this->data);
        }
    }

    public function delete_reward_stock_received($id)
    {
        $this->bpas->checkPermissions('delete', null, 'stock_received');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->products_model->deleteRewardStockReceived($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('stock_received_deleted')]);
            }
            $this->session->set_flashdata('message', lang('stock_received_deleted'));
            admin_redirect('products/stock_received');
        }
    }

    public function get_product_stock_balance_ajax()
    {
        $product_id   = $this->input->get('product_id');
        $expiry       = ((!empty($this->input->get('expiry')) && $this->input->get('expiry') != '' && $this->input->get('expiry') != 'null') ? $this->bpas->fsd($this->input->get('expiry')) : null);
        $warehouse_id = $this->input->get('warehouse_id');
        if($data = $this->products_model->getProductStockBalance($product_id, null, $expiry, $warehouse_id)) {
            $this->bpas->send_json($data);
        } else {
            $this->bpas->send_json(false);    
        }       
    }

    public function rewards_stock_received($warehouse_id = null)
    {
        $this->bpas->checkPermissions('index', null, 'stock_received');
        $count = explode(',', $this->session->userdata('warehouse_id'));
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            if (count($count) > 1) {
                $this->data['warehouses']   = $this->site->getAllWarehouses();
            } else {
                $this->data['warehouses']   = null;
            }
            $this->data['count_warehouses'] = $count;
            $this->data['user_warehouse']   = (isset($count) && count($count) == 1) ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
            $this->data['warehouse_id']     = $warehouse_id;
            $this->data['warehouse']        = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        }
        $this->data['alert_id'] = isset($_GET['alert_id']) ? $_GET['alert_id'] : null;
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('rewards_stock_received')]];
        $meta = ['page_title' => lang('rewards_stock_received'), 'bc' => $bc];
        $this->page_construct('products/rewards_stock_received', $meta, $this->data);
    }

    public function getRewardsStockReceived($warehouse_id = null)
    {
        $this->session->set_flashdata('error', $this->bpas->checkPermissions('index', null, 'stock_received'));
        if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
        $detail_link = anchor('admin/products/modal_view_reward_stock_received/$1', '<i class="fa fa-money"></i> ' . lang('stock_details'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link   = anchor('admin/products/edit_reward_stock_received/$1', '<i class="fa fa-edit"></i> ' . lang('edit_stock_received'));
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line('delete_stock_received') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('products/delete_reward_stock_received/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_stock_received') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li class="view_stock">' . $detail_link . '</li>
            <li class="edit_stock">' . $edit_link . '</li>
            <li class="delete_stock">' . $delete_link . '</li>
        </ul>
        </div></div>';
        $this->load->library('datatables');
        $this->datatables->select("
            stock_received.id AS id, 
            DATE_FORMAT({$this->db->dbprefix('stock_received')}.date, '%Y-%m-%d %T') as date,
            rewards_exchange.reference_no as reward_reference_no, stock_received.reference_no, warehouses.name as warehouse, 
            IF({$this->db->dbprefix('rewards_exchange')}.category = 'customer', {$this->db->dbprefix('companies')}.name, '') AS customer,
            IF({$this->db->dbprefix('rewards_exchange')}.category = 'supplier', {$this->db->dbprefix('companies')}.name, '') AS supplier,
            stock_received.note");
        $this->datatables->from('stock_received');
        $this->datatables->join('rewards_exchange', 'rewards_exchange.id = stock_received.reward_exchange_id', 'inner');
        $this->datatables->join('warehouses', 'warehouses.id = stock_received.warehouse_id', 'left');
        $this->datatables->join('companies', 'companies.id = rewards_exchange.company_id', 'left');
        if ($warehouse_id) {
            $this->datatables->where('stock_received.warehouse_id IN (' . $warehouse_id . ')');
        } elseif (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where_in("FIND_IN_SET(bpas_stock_received.warehouse_id, '".$this->session->userdata('warehouse_id')."')");
            $this->datatables->where("FIND_IN_SET(bpas_stock_received.created_by, '" . $this->session->userdata('user_id') . "')");
        } 
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('bpas_stock_received.created_by', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    public function modal_view_reward_stock_received($id = null)
    {
        $this->bpas->checkPermissions('index', null, 'stock_received');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->products_model->getRewardStockReceivedByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by, true);
        }
        $reward_exchange = $this->products_model->getRewardExchangeByID($inv->reward_exchange_id);
        $this->data['reward_exchange'] = $reward_exchange;
        $this->data['rows']       = $this->products_model->getRewardStockReceivedItems($id);
        $this->data['company']    = $this->site->getCompanyByID($reward_exchange->company_id);
        $this->data['warehouse']  = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']        = $inv;
        $this->data['currencys']  = $this->site->getAllCurrencies();
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $this->site->getUser($inv->updated_by);
        $this->data['biller']     = $this->site->getCompanyByID($reward_exchange->biller_id);
        $this->load->view($this->theme . 'products/modal_view_reward_stock_received', $this->data);
    }

    public function rewards_stock_received_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete', null, 'stock_received');
                    foreach ($_POST['val'] as $id) {
                        $this->purchases_model->deletePurchase($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line('stock_received_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'combine') {
                    $html = $this->combine_pdf($_POST['val']);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('rewards_stock_received'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('rewards_stock_received'));
                    $this->excel->getActiveSheet()->getStyle('E1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $this->excel->getActiveSheet()->getStyle("E1")->getFont()->setSize(13);
                    $this->excel->getActiveSheet()->SetCellValue('A2', lang('date'));   
                    $this->excel->getActiveSheet()->SetCellValue('B2', (lang('reward') . ' ' . lang('reference_no')));
                    $this->excel->getActiveSheet()->SetCellValue('C2', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('D2', lang('warehouse'));
                    $this->excel->getActiveSheet()->SetCellValue('E2', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('F2', lang('supplier'));
                    $this->excel->getActiveSheet()->SetCellValue('G2', lang('product_code'));
                    $this->excel->getActiveSheet()->SetCellValue('H2', lang('product_name'));
                    $this->excel->getActiveSheet()->SetCellValue('I2', lang('quantity'));
                    $this->excel->getActiveSheet()->SetCellValue('J2', lang('unit'));
                    $this->excel->getActiveSheet()->SetCellValue('K2', lang('note'));
                    $styleArray = array('font'  => array('bold'  => true));
                    $this->excel->getActiveSheet()->getStyle('A1:K1')->applyFromArray($styleArray);
                    $row = 3;
                    foreach ($_POST['val'] as $id) {
                        $stock_received       = $this->products_model->getRewardStockReceivedByID($id);
                        $reward_exchange      = $this->products_model->getRewardExchangeByID($stock_received->reward_exchange_id);
                        $stock_received_items = $this->products_model->getRewardStockReceivedItems($id);
                        $warehouse            = $this->site->getWarehouseByID($stock_received->warehouse_id);
                        $company              = $this->site->getCompanyByID($reward_exchange->company_id);
                        $count = (count($stock_received_items) -1);
                        $this->excel->getActiveSheet()->mergeCells('A' . $row . ':A' . ($row + $count))->SetCellValue('A' . $row, $this->bpas->hrld($stock_received->date));
                        $this->excel->getActiveSheet()->mergeCells('B' . $row . ':B' . ($row + $count))->SetCellValue('B' . $row, $reward_exchange->reference_no);
                        $this->excel->getActiveSheet()->mergeCells('C' . $row . ':C' . ($row + $count))->SetCellValue('C' . $row, $stock_received->reference_no);
                        $this->excel->getActiveSheet()->mergeCells('D' . $row . ':D' . ($row + $count))->SetCellValue('D' . $row, $warehouse->name);
                        $this->excel->getActiveSheet()->mergeCells('E' . $row . ':E' . ($row + $count))->SetCellValue('E' . $row, ($reward_exchange->category == 'customer' ? $company->name : ''));
                        $this->excel->getActiveSheet()->mergeCells('F' . $row . ':F' . ($row + $count))->SetCellValue('F' . $row, ($reward_exchange->category == 'supplier' ? $company->name : ''));
                        $this->excel->getActiveSheet()->mergeCells('K' . $row . ':K' . ($row + $count))->SetCellValue('K' . $row, strip_tags(html_entity_decode($stock_received->note)));
                        foreach ($stock_received_items as $item) {  
                            $product = $this->products_model->getProductByID($item->product_id);
                            $unit    = $this->site->getUnitByID($product->unit);
                            $this->excel->getActiveSheet()->SetCellValue('G' . $row, $product->code);
                            $this->excel->getActiveSheet()->SetCellValue('H' . $row, $product->name);
                            $this->excel->getActiveSheet()->SetCellValue('I' . $row, $item->quantity);
                            $this->excel->getActiveSheet()->SetCellValue('J' . $row, $unit->name);
                            $row++;
                        }
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'rewards_stock_received_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line('no_stock_received_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function e_menu($warehouse_id = null)
    {
        $this->bpas->checkPermissions();
        $count = explode(',', $this->session->userdata('warehouse_id'));
        $products   = $this->site->getAllProducts();
        $warehouses = $this->site->getAllWarehouses();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id ? $warehouse_id : null;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            if (count($count)>1) {
                $this->data['warehouses']   = $this->site->getAllWarehouses();
            } else {
                $this->data['warehouses']   = null;
            }
            $this->data['count_warehouses'] = $count;
            $this->data['user_warehouse']   = (isset($count) && count($count) == 1) ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
            $this->data['warehouse_id']     = $warehouse_id;
            $this->data['warehouse']        = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        }
        $this->data['products']   = $this->site->getProducts();
        $this->data['categories'] = $this->site->getAllCategories();
        $bc                     = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('products')]];
        $meta                   = ['page_title' => lang('products'), 'bc' => $bc];
        $this->page_construct('products/e-menu', $meta, $this->data);
    }

    public function consignments($warehouse_id = null, $biller_id = NULL)
    {
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['warehouses'] = $this->site->getWarehouses();
        $this->data['warehouse']  = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        $this->data['billers']    = $this->site->getBillers();
        $this->data['biller']     = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('inventory')), array('link' => '#', 'page' => lang('consignments')));
        $meta = array('page_title' => lang('consignments'), 'bc' => $bc);
        $this->page_construct('products/consignments', $meta, $this->data);

    }

    public function getConsignments($warehouse_id = null, $biller_id = NULL)
    {
        $this->bpas->checkPermissions('consignments');
        $create_sale = '';
        if(($this->Admin || $this->Owner) || $this->GP['sales-add']){
            $create_sale = anchor('admin/sales/add/?consignment_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('create_sale'), ' class="consignment-create_sale" ​');
        }
        $edit_link = anchor('admin/products/edit_consignment/$1', '<i class="fa fa-edit"></i> ' . lang('edit_consignment'), ' class="edit_consignment" ');
        $view_return_link = anchor('admin/products/view_consignment_return/$1', '<i class="fa fa-file-text-o"></i>' . lang('view_consignment_return'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $return_link = anchor('admin/products/return_consignment/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_consignment'));
        $delete_link = "<a href='#' class='po delete_consignment' title='<b>" . $this->lang->line("delete_consignment") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('products/delete_consignment/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_consignment') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $view_return_link . '</li>
                        <li>' . $return_link . '</li>
                        <li>' . $create_sale . '</li>
                        <li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
       
        $this->load->library('datatables');
        $this->datatables
            ->select("id, date, reference_no, customer, grand_total, status, attachment")
            ->where("consignments.status !=",'returned')
            ->from("consignments");
        if ($warehouse_id) {
            $this->datatables->where('consignments.warehouse_id', $warehouse_id);
        }
        if ($biller_id) {
            $this->datatables->where('consignments.biller_id', $biller_id);
        }   
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
            $this->datatables->where('consignments.biller_id', $this->session->userdata('biller_id'));
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) { 
            $this->datatables->where_in('consignments.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    public function add_consignment()
    {
        $this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
        if ($this->form_validation->run() == true) {
            $biller_id  = $this->input->post('biller');
            $project_id = $this->input->post('project');
            $reference  = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('csm', $biller_id);
            if ($this->Owner || $this->Admin || $this->bpas->GP['change_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $valid_day        = $this->input->post('valid_day');
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $status           = 'pending';
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $total            = 0;
            $stockmoves       = null;
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            $consignmentAcc = $this->site->getAccountSettingByBiller($biller_id);
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_comment       = $_POST['product_comment'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $item_expiry        = isset($_POST['expired_data'][$r]) && !empty($_POST['expired_data'][$r]) && $_POST['expired_data'][$r] != 'false' && $_POST['expired_data'][$r] != 'undefined' && $_POST['expired_data'][$r] != 'null' && $_POST['expired_data'][$r] != 'NULL' && $_POST['expired_data'][$r] != '00/00/0000' && $_POST['expired_data'][$r] != '' ? $_POST['expired_data'][$r] : null; 
                $real_unit_price    = $this->bpas->formatDecimalRaw($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimalRaw($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_serial        = isset($_POST['serial'][$r]) && !empty($_POST['serial'][$r]) && $_POST['serial'][$r] != 'undefined' && $_POST['serial'][$r] != 'false' && $_POST['serial'][$r] != 'null' && $_POST['serial'][$r] != 'NULL' ? $_POST['serial'][$r] : null;
                if (isset($item_code) && isset($item_quantity)) {
                    $product_details  = $item_type != 'manual' ? $this->products_model->getProductByCode($item_code) : null;
                    $unit_price       = $this->bpas->formatDecimalRaw($unit_price);
                    $item_net_price   = $unit_price;
                    $pr_item_discount = $this->bpas->formatDecimalRaw($item_unit_quantity);
                    $subtotal = ($item_net_price * $item_unit_quantity);
                    $unit = $this->site->getProductUnit($product_details->id, $item_unit);
                    if ($this->Settings->accounting_method == '0') {
                        $costs = $this->site->getFifoCost($product_details->id, $item_quantity, $stockmoves);
                    } else if ($this->Settings->accounting_method == '1') {
                        $costs = $this->site->getLifoCost($product_details->id, $item_quantity, $stockmoves);
                    } else if ($this->Settings->accounting_method == '3') {
                        $costs = $this->site->getProductMethod($product_details->id, $item_quantity, $stockmoves);
                    }
                    if (isset($costs) && $costs) {
                        $productAcc = $this->site->getProductAccByProductId($item_id);
                        foreach ($costs as $cost_item) {
                            $stockmoves[] = array(
                                'transaction'    => 'Consignment',
                                'product_id'     => $product_details->id,
                                'product_type'   => $item_type,
                                'product_code'   => $item_code,
                                'product_name'   => $item_name,
                                'option_id'      => $item_option,
                                'quantity'       => $cost_item['quantity'] * (-1),
                                'unit_quantity'  => $unit->unit_qty,
                                'unit_code'      => $unit->code,
                                'unit_id'        => $item_unit,
                                'warehouse_id'   => $warehouse_id,
                                'date'           => $date,
                                'expiry'         => $item_expiry,
                                'real_unit_cost' => $cost_item['cost'],
                                'reference_no'   => $reference,
                                'user_id'        => $this->session->userdata('user_id'),
                            );
                            if ($this->Settings->module_account == 1) {
                                $accTrans[] = array(
                                    'tran_type'     => 'Consignment',
                                    'tran_date'     => $date,
                                    'reference_no'  => $reference,
                                    'account_code'  => $this->accounting_setting->default_stock,
                                    'amount'        => -($cost_item['cost'] * abs($cost_item['quantity'])),
                                    'narrative'     => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
                                    'description'   => $note,
                                    'biller_id'     => $biller_id,
                                    'project_id'    => $project_id,
                                    'created_by'    => $this->session->userdata('user_id'),
                                    'customer_id'   => $customer_id,
                                );
                                $accTrans[] = array(
                                    'tran_type'     => 'Consignment',
                                    'tran_date'     => $date,
                                    'reference_no'  => $reference,
                                    'account_code'  => $consignmentAcc->consignment_acc,
                                    'amount'        => ($cost_item['cost'] * abs($cost_item['quantity'])),
                                    'narrative'     => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
                                    'description'   => $note,
                                    'biller_id'     => $biller_id,
                                    'project_id'    => $project_id,
                                    'created_by'    => $this->session->userdata('user_id'),
                                    'customer_id'   => $customer_id,
                                );
                            }
                        }
                    } else {
                        $stockmoves[] = array(
                            'transaction'    => 'Consignment',
                            'product_id'     => $product_details->id,
                            'product_type'   => $item_type,
                            'product_code'   => $item_code,
                            'product_name'   => $item_name,
                            'option_id'      => $item_option,
                            'quantity'       => (-1) * $item_quantity,
                            'unit_quantity'  => $unit->unit_qty,
                            'unit_code'      => $unit->code,
                            'unit_id'        => $item_unit,
                            'warehouse_id'   => $warehouse_id,
                            'date'           => $date,
                            'expiry'         => $item_expiry,
                            'serial_no'      => $item_serial,
                            'real_unit_cost' => $product_details->cost,
                            'reference_no'   => $reference,
                            'user_id'        => $this->session->userdata('user_id'),
                        );
                        if ($this->Settings->module_account == 1) {       
                            $productAcc = $this->site->getProductAccByProductId($product_details->id);
                            $accTrans[] = array(
                                'tran_type'    => 'Consignment',
                                'tran_date'    => $date,
                                'reference_no' => $reference,
                                'account_code' => $this->accounting_setting->default_stock,
                                'amount'       => -($product_details->cost * abs($item_quantity)),
                                'narrative'    => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
                                'description'  => $note,
                                'biller_id'    => $biller_id,
                                'project_id'   => $project_id,
                                'created_by'   => $this->session->userdata('user_id'),
                                'customer_id'  => $customer_id,
                            );
                            $accTrans[] = array(
                                'tran_type'    => 'Consignment',
                                'tran_date'    => $date,
                                'reference_no' => $reference,
                                'account_code' => $consignmentAcc->consignment_acc,
                                'amount'       => ($product_details->cost * abs($item_quantity)),
                                'narrative'    => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
                                'description'  => $note,
                                'biller_id'    => $biller_id,
                                'project_id'   => $project_id,
                                'created_by'   => $this->session->userdata('user_id'),
                                'customer_id'  => $customer_id,
                            );
                        }
                    }
                    $products[] = array(
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $this->bpas->formatDecimalRaw($item_net_price),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $warehouse_id,
                        'subtotal'          => $this->bpas->formatDecimalRaw($subtotal),
                        'real_unit_price'   => $real_unit_price,
                        'expiry'            => $item_expiry,
                        'serial_no'         => $item_serial,
                        'comment'           => $item_comment
                    );
                    $total += $this->bpas->formatDecimalRaw($item_net_price * $item_unit_quantity);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }
            $grand_total = $this->bpas->formatDecimalRaw($total);
            $data = array(
                'date'         => $date,
                'reference_no' => $reference,
                'customer_id'  => $customer_id,
                'customer'     => $customer,
                'biller_id'    => $biller_id,
                'biller'       => $biller,
                'project_id'   => $project_id,
                'warehouse_id' => $warehouse_id,
                'note'         => $note,
                'total'        => $total,
                'grand_total'  => $grand_total,
                'valid_day'    => $valid_day,
                'status'       => $status,
                'created_by'   => $this->session->userdata('user_id'),
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
            // $this->bpas->print_arrays($products, $stockmoves);
        }
        if ($this->form_validation->run() == true && $this->products_model->addConsignment($data, $products, $stockmoves, $accTrans)) {
            $this->session->set_userdata('remove_csmls', 1);
            $this->session->set_flashdata('message', $this->lang->line("consignment_added") . " " . $reference);
            if ($this->input->post('add_consignment_next')) {
                admin_redirect('products/add_consignment');
            } else {
                admin_redirect('products/consignments');
            }
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers']    =  $this->site->getBillers();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('inventory')), array('link' => admin_url('products/consignments'), 'page' => lang('consignments')), array('link' => '#', 'page' => lang('add_consignment')));
            $meta = array('page_title' => lang('add_consignment'), 'bc' => $bc);
            $this->page_construct('products/add_consignment', $meta, $this->data);
        }
    }

    public function edit_consignment($id = false)
    {
        $this->bpas->checkPermissions();
        $consignment = $this->products_model->getConsignmentByID($id);
        if ($consignment->status == 'partial') {
            $this->session->set_flashdata('error', lang("consignment_is_in_process"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else if ($consignment->status == 'completed') {
            $this->session->set_flashdata('error', lang("consignment_is_already_completed"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
        if ($this->form_validation->run() == true) {
            $biller_id  = $this->input->post('biller');
            $project_id = $this->input->post('project');
            $reference  = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('csm',$biller_id);
            if ($this->Owner || $this->Admin || $this->bpas->GP['change_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $valid_day        = $this->input->post('valid_day');
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $total            = 0;
            $stockmoves       = null;
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            $consignmentAcc = $this->site->getAccountSettingByBiller($biller_id);
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_comment       = $_POST['product_comment'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $item_expiry        = isset($_POST['expired_data'][$r]) && !empty($_POST['expired_data'][$r]) && $_POST['expired_data'][$r] != 'false' && $_POST['expired_data'][$r] != 'undefined' && $_POST['expired_data'][$r] != 'null' && $_POST['expired_data'][$r] != 'NULL' && $_POST['expired_data'][$r] != '00/00/0000' && $_POST['expired_data'][$r] != '' ? $_POST['expired_data'][$r] : null; 
                $real_unit_price    = $this->bpas->formatDecimalRaw($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimalRaw($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_serial        = isset($_POST['serial'][$r]) && !empty($_POST['serial'][$r]) && $_POST['serial'][$r] != 'undefined' && $_POST['serial'][$r] != 'false' && $_POST['serial'][$r] != 'null' && $_POST['serial'][$r] != 'NULL' ? $_POST['serial'][$r] : null;
                if (isset($item_code) && isset($item_quantity)) {
                    $product_details  = $item_type != 'manual' ? $this->products_model->getProductByCode($item_code) : null;
                    $unit_price       = $this->bpas->formatDecimalRaw($unit_price);
                    $item_net_price   = $unit_price;
                    $pr_item_discount = $this->bpas->formatDecimalRaw($item_unit_quantity);
                    $subtotal = ($item_net_price * $item_unit_quantity);
                    $unit = $this->site->getProductUnit($product_details->id, $item_unit);
                    if ($this->Settings->accounting_method == '0') {
                        $costs = $this->site->getFifoCost($product_details->id, $item_quantity, $stockmoves,'Consignment', $id);
                    } else if ($this->Settings->accounting_method == '1') {
                        $costs = $this->site->getLifoCost($product_details->id, $item_quantity, $stockmoves,'Consignment', $id);
                    } else if ($this->Settings->accounting_method == '3') {
                        $costs = $this->site->getProductMethod($product_details->id, $item_quantity, $stockmoves,'Consignment', $id);
                    }
                    if (isset($costs) && $costs) {
                        $productAcc = $this->site->getProductAccByProductId($item_id);
                        foreach ($costs as $cost_item) {
                            $stockmoves[] = array(
                                'transaction_id' => $id,
                                'transaction'    => 'Consignment',
                                'product_id'     => $product_details->id,
                                'product_type'   => $item_type,
                                'product_code'   => $item_code,
                                'product_name'   => $item_name,
                                'option_id'      => $item_option,
                                'quantity'       => $cost_item['quantity'] * (-1),
                                'unit_quantity'  => $unit->unit_qty,
                                'unit_code'      => $unit->code,
                                'unit_id'        => $item_unit,
                                'warehouse_id'   => $warehouse_id,
                                'date'           => $date,
                                'expiry'         => $item_expiry,
                                'real_unit_cost' => $cost_item['cost'],
                                'reference_no'   => $reference,
                                'user_id'        => $this->session->userdata('user_id'),
                            );
                            if ($this->Settings->module_account == 1) {       
                                $accTrans[] = array(
                                    'tran_no'      => $id,
                                    'tran_type'    => 'Consignment',
                                    'tran_date'    => $date,
                                    'reference_no' => $reference,
                                    'account_code' => $this->accounting_setting->default_stock,
                                    'amount'       => -($cost_item['cost'] * abs($cost_item['quantity'])),
                                    'narrative'    => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
                                    'description'  => $note,
                                    'biller_id'    => $biller_id,
                                    'project_id'   => $project_id,
                                    'customer_id'  => $customer_id,
                                    'created_by'   => $this->session->userdata('user_id'),
                                );
                                $accTrans[] = array(
                                    'tran_no'      => $id,
                                    'tran_type'    => 'Consignment',
                                    'tran_date'    => $date,
                                    'reference_no' => $reference,
                                    'account_code' => $consignmentAcc->consignment_acc,
                                    'amount'       => ($cost_item['cost'] * abs($cost_item['quantity'])),
                                    'narrative'    => 'Product Code: '.$item_code.'#'.'Qty: '.$cost_item['quantity'].'#'.'Cost: '.$cost_item['cost'],
                                    'description'  => $note,
                                    'biller_id'    => $biller_id,
                                    'project_id'   => $project_id,
                                    'customer_id'  => $customer_id,
                                    'created_by'   => $this->session->userdata('user_id'),
                                );
                            }
                        }
                    } else {
                        $stockmoves[] = array(
                            'transaction_id' => $id,
                            'transaction'    => 'Consignment',
                            'product_id'     => $product_details->id,
                            'product_type'   => $item_type,
                            'product_code'   => $item_code,
                            'product_name'   => $item_name,
                            'option_id'      => $item_option,
                            'quantity'       => (-1) * $item_quantity,
                            'unit_quantity'  => $unit->unit_qty,
                            'unit_code'      => $unit->code,
                            'unit_id'        => $item_unit,
                            'warehouse_id'   => $warehouse_id,
                            'date'           => $date,
                            'expiry'         => $item_expiry,
                            'serial_no'      => $item_serial,
                            'real_unit_cost' => $product_details->cost,
                            'reference_no'   => $reference,
                            'user_id'        => $this->session->userdata('user_id'),
                        );
                        if ($this->Settings->module_account == 1) {    
                            $productAcc = $this->site->getProductAccByProductId($product_details->id);
                            $accTrans[] = array(
                                'tran_no'      => $id,
                                'tran_type'    => 'Consignment',
                                'tran_date'    => $date,
                                'reference_no' => $reference,
                                'account_code' => $this->accounting_setting->default_stock,
                                'amount'       => -($product_details->cost * abs($item_quantity)),
                                'narrative'    => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
                                'description'  => $note,
                                'biller_id'    => $biller_id,
                                'project_id'   => $project_id,
                                'created_by'   => $this->session->userdata('user_id'),
                                'customer_id'  => $customer_id,
                            );
                            $accTrans[] = array(
                                'tran_no'      => $id,
                                'tran_type'    => 'Consignment',
                                'tran_date'    => $date,
                                'reference_no' => $reference,
                                'account_code' => $consignmentAcc->consignment_acc,
                                'amount'       => ($product_details->cost * abs($item_quantity)),
                                'narrative'    => 'Product Code: '.$item_code.'#'.'Qty: '.$item_quantity.'#'.'Cost: '.$product_details->cost,
                                'description'  => $note,
                                'biller_id'    => $biller_id,
                                'project_id'   => $project_id,
                                'created_by'   => $this->session->userdata('user_id'),
                                'customer_id'  => $customer_id,
                            );
                        }
                    }
                    $products[] = array(
                        'consignment_id'    => $id,
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $this->bpas->formatDecimalRaw($item_net_price),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $warehouse_id,
                        'subtotal'          => $this->bpas->formatDecimalRaw($subtotal),
                        'real_unit_price'   => $real_unit_price,
                        'comment'           => $item_comment,
                        'expiry'            => $item_expiry,
                        'serial_no'         => $item_serial,
                    );
                    $total += $this->bpas->formatDecimalRaw($item_net_price * $item_unit_quantity);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }
            $grand_total = $this->bpas->formatDecimalRaw($total);
            $data = array(
                'date'         => $date,
                'reference_no' => $reference,
                'customer_id'  => $customer_id,
                'customer'     => $customer,
                'biller_id'    => $biller_id,
                'biller'       => $biller,
                'project_id'   => $project_id,
                'warehouse_id' => $warehouse_id,
                'note'         => $note,
                'total'        => $total,
                'grand_total'  => $grand_total,
                'valid_day'    => $valid_day,
                'updated_by'   => $this->session->userdata('user_id'),
                'updated_at'   => date('Y-m-d H:i:s'),
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
        }
        if ($this->form_validation->run() == true && $this->products_model->updateConsignment($id, $data, $products, $stockmoves, $accTrans)) {
            $this->session->set_userdata('remove_csmls', 1);
            $this->session->set_flashdata('message', $this->lang->line("consignment_updated") ." ". $reference);
            admin_redirect('products/consignments');
        } else {
            $consingment_items = $this->products_model->getConsigmentItems($id);
            krsort($consingment_items);
            $c = rand(100000, 9999999);
            foreach ($consingment_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $row = json_decode('{}');
                }
                $row->quantity = 0;
                $pis = $this->site->getStockMovement_ProductBalanceQuantity($item->product_id, $item->warehouse_id, $item->option_id, 'Consignment', $id);
                if ($pis) {
                    $row->quantity = $pis->quantity_balance;
                }
                $product_serials = $this->products_model->getActiveProductSerialID($item->product_id, $consignment->warehouse_id, $item->serial_no);
                $row->fup = 1;
                $row->id = $item->product_id;
                $row->code = $item->product_code;
                $row->name = $item->product_name;
                $row->type = $item->product_type;
                $row->base_quantity = $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_price = $row->price ? $row->price : $item->real_unit_price;
                $row->unit = $item->product_unit_id;
                $row->qty = $item->unit_quantity;
                $row->price = $this->bpas->formatDecimalRaw($item->net_unit_price);
                $row->unit_price = $item->unit_price ;
                $row->real_unit_price = $item->real_unit_price;
                $row->option = $item->option_id;
                $row->comment = $item->comment;
                $row->serial = $item->serial_no;
                $row->expired = $item->expiry;
                $options = $this->products_model->getProductOptions($row->id, $item->warehouse_id);
                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->products_model->getProductComboItems($row->id, $item->warehouse_id);
                    foreach ($combo_items as $combo_item) {
                        $combo_item->quantity = $combo_item->qty * $item->quantity;
                    }
                }
                $units = $this->site->getUnitbyProduct($row->id, $row->base_unit);
                $stock_items = $this->site->getStockMovementByProductID($item->product_id, $item->warehouse_id, $item->option_id, null, 'Consignment', $id);
                $ri = $this->Settings->item_addition ? $row->id : $c;
                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'combo_items' => $combo_items, 'units' => $units, 'options' => $options, 'pitems' => $stock_items, 'product_serials' => $product_serials);
                $c++;
            }
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['consignment'] = $consignment;
            $this->data['consingment_items'] = json_encode($pr);
            $this->data['billers']    =  $this->site->getBillers();
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->session->set_userdata('remove_csmls', 1);
            $bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('inventory')), array('link' => admin_url('products/consignments'), 'page' => lang('consignments')), array('link' => '#', 'page' => lang('edit_consignment')));
            $meta = array('page_title' => lang('edit_consignment'), 'bc' => $bc);
            $this->page_construct('products/edit_consignment', $meta, $this->data);
        }
    }

    public function delete_consignment($id = null)
    {
        $this->bpas->checkPermissions(NULL, true);
        $consignment = $this->products_model->getConsignmentByID($id);
        if ($consignment->status == 'partial') {
            $this->session->set_flashdata('error', lang("consignment_is_in_process"));
            $this->bpas->md();
        } else if ($consignment->status == 'completed') {
            $this->session->set_flashdata('error', lang("consignment_is_already_completed"));
            $this->bpas->md();
        } else { 
            if ($this->input->get('id')) {
                $id = $this->input->get('id');
            }
            if ($this->products_model->deleteConsignment($id)) {
                if ($this->input->is_ajax_request()) {
                    echo lang("consignment_deleted");
                    die();
                }
                $this->session->set_flashdata('message', lang('consignment_deleted'));
                admin_redirect('products/consignments');
            }
        }   
    }

    public function return_consignment($consignment_id = false)
    {
        $this->bpas->checkPermissions('add_consignment');
        $consignment = $this->products_model->getConsignmentByID($consignment_id);
        if ($consignment->status == 'completed') {
            $this->session->set_flashdata('error', lang("consignment_is_already_completed"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('consignment', $this->lang->line("consignment"), 'required');
        if ($this->form_validation->run() == true) {
            $i = ($this->input->post('product_id')) ? sizeof($this->input->post('product_id')) : 0;
            if ($this->Owner || $this->Admin || $this->bpas->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('rcsm',$consignment->biller_id);
            $note = $this->bpas->clear_tags($this->input->post('note'));
            $consignmentAcc = $this->site->getAccountSettingByBiller($consignment->biller_id);
            $products = false;
            $grand_total = 0;
            for ($r = 0; $r < $i; $r++) {
                $consignment_item_id = $this->input->post('consignment_item_id')[$r];
                $product_id      = $this->input->post('product_id')[$r];
                $product_code    = $this->input->post('product_code')[$r];
                $product_name    = $this->input->post('product_name')[$r];
                $product_expiry  = $this->input->post('product_expiry')[$r];
                $product_serial  = $this->input->post('product_serial')[$r];
                $product_cost    = $this->input->post('product_cost')[$r];
                $product_type    = $this->input->post('product_type')[$r];
                $option_id       = $this->input->post('option_id')[$r];
                $product_unit_id = $_POST['unit'][$r];
                $return_quantity = $this->input->post('return_quantity')[$r] * (-1);
                $quantity        = $return_quantity;
                $real_unit_price = $this->input->post('real_unit_price')[$r];
                if ($return_quantity < 0) {
                    $unit = $this->site->getProductUnit($product_id, $product_unit_id);
                    if ($unit->unit_qty > 1) {
                        $quantity = $quantity * $unit->unit_qty;
                    }
                    $unit_price = $real_unit_price *  $unit->unit_qty;
                    $stockmoves[] = array(
                        'transaction'    => 'Consignment',
                        'product_id'     => $product_id,
                        'product_type'   => $product_type,
                        'product_code'   => $product_code,
                        'product_name'   => $product_name,
                        'option_id'      => $option_id,
                        'quantity'       => (-1)*$quantity,
                        'unit_quantity'  => $unit->unit_qty,
                        'unit_code'      => $unit->code,
                        'unit_id'        => $product_unit_id,
                        'warehouse_id'   => $consignment->warehouse_id,
                        'expiry'         => $product_expiry,
                        'date'           => $date,
                        'real_unit_cost' => $product_cost,
                        'serial_no'      => $product_serial,
                        'reference_no'   => $reference_no,
                        'user_id'        => $this->session->userdata('user_id'),
                    );
                    if ($this->Settings->module_account == 1) { 
                        $productAcc = $this->site->getProductAccByProductId($product_id);
                        $accTrans[] = array(
                            'tran_type'    => 'Consignment',
                            'tran_date'    => $date,
                            'account_code' => $this->accounting_setting->default_stock,
                            'amount'       => ($product_cost * abs($quantity)),
                            'narrative'    => 'Product Code: '.$product_code.'#'.'Qty: '.abs($quantity).'#'.'Cost: '.$product_cost,
                            'description'  => $note,
                            'biller_id'    => $consignment->biller_id,
                            'project_id'   => $consignment->project_id,
                            'customer_id'  => $consignment->customer_id,
                            'reference_no' => $reference_no,
                            'created_by'   => $this->session->userdata('user_id'),
                        );
                        $accTrans[] = array(
                            'tran_type'    => 'Consignment',
                            'tran_date'    => $date,
                            'account_code' => $consignmentAcc->consignment_acc,
                            'amount'       => -($product_cost * abs($quantity)),
                            'narrative'    => 'Product Code: '.$product_code.'#'.'Qty: '.abs($quantity).'#'.'Cost: '.$product_cost,
                            'description'  => $note,
                            'biller_id'    => $consignment->biller_id,
                            'project_id'   => $consignment->project_id,
                            'customer_id'  => $consignment->customer_id,
                            'reference_no' => $reference_no,
                            'created_by'   => $this->session->userdata('user_id'),
                        );
                    }
                    $products[] = array(
                        'consignment_item_id' => $consignment_item_id,
                        'product_id'          => $product_id,
                        'product_code'        => $product_code,
                        'product_name'        => $product_name,
                        'product_type'        => $product_type,
                        'option_id'           => $option_id,
                        'expiry'              => $product_expiry,
                        'serial_no'           => $product_serial,
                        'product_unit_id'     => $product_unit_id,
                        'product_unit_code'   => $unit->code,
                        'warehouse_id'        => $consignment->warehouse_id,
                        'quantity'            => $quantity,
                        'unit_quantity'       => $return_quantity,
                        'real_unit_price'     => $real_unit_price,
                        'unit_price'          => $unit_price,
                        'net_unit_price'      => $unit_price,
                        'subtotal'            => $real_unit_price * $quantity,
                    );
                    $grand_total += $real_unit_price * $quantity;       
                }
            }
            if ($products) {
                $data = array(
                    'consignment_id' => $consignment_id,
                    'reference_no'   => $reference_no,
                    'date'           => $date,
                    'warehouse_id'   => $consignment->warehouse_id,
                    'biller_id'      => $consignment->biller_id,
                    'biller'         => $consignment->biller,
                    'customer'       => $consignment->customer,
                    'customer_id'    => $consignment->customer_id,
                    'project_id'     => $consignment->project_id,
                    'grand_total'    => $grand_total,  
                    'total'          => $grand_total,    
                    'status'         => 'returned', 
                    'note'           => $note,
                    'created_by'     => $this->session->userdata('user_id')
                );
                if ($_FILES['document']['size'] > 0) {
                    $this->load->library('upload');
                    $config['upload_path'] = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['overwrite'] = FALSE;
                    $config['encrypt_name'] = TRUE;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('document')) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
                    $photo = $this->upload->file_name;
                    $data['attachment'] = $photo;
                }
                // $this->bpas->print_arrays($stockmoves);
                if ($this->products_model->addConsignment($data, $products, $stockmoves, $accTrans)) {
                    $this->session->set_flashdata('message', lang("consignment_returned")." - ".$data['reference_no']);
                    admin_redirect("products/consignments");
                }
            } else {
                $this->session->set_flashdata('error', lang("product_return_qty_is_required"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $consignment_items = $this->products_model->getConsigmentItems($consignment_id);
            $this->data['consignment'] = $consignment;
            $this->data['consignment_items'] = $consignment_items;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('inventory')), array('link' => admin_url('products/consignments'), 'page' => lang('consignments')), array('link' => '#', 'page' => lang('return_consignment')));
            $meta = array('page_title' => lang('return_consignment'), 'bc' => $bc);
            $this->page_construct('products/return_consignment', $meta, $this->data);
        }
    }

    public function modal_view_consignment($id = null)
    {
        $this->bpas->checkPermissions('consignments', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $consignment = $this->products_model->getConsignmentByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($consignment->created_by, true);
        }
        $this->data['rows']        = $this->products_model->getConsigmentItems($id);
        $this->data['customer']    = $this->site->getCompanyByID($consignment->customer_id);
        $this->data['biller']      = $this->site->getCompanyByID($consignment->biller_id);
        $this->data['created_by']  = $this->site->getUser($consignment->created_by);
        $this->data['updated_by']  = $consignment->updated_by ? $this->site->getUser($consignment->updated_by) : null;
        $this->data['warehouse']   = $this->site->getWarehouseByID($consignment->warehouse_id);
        $this->data['consignment'] = $consignment;
        $this->data['project']     = $this->site->getProjectByID($consignment->project_id);
        if ($this->Owner || $this->Admin || $this->bpas->GP['unlimited-print']) {
            $this->data['print'] = 0;
        } else {
            if ($this->Settings->limit_print=='1' && $this->site->checkPrint('Consignment',$consignment->id)) {
                $this->data['print'] = 1;
            } else if ($this->Settings->limit_print=='2' && $this->site->checkPrint('Consignment',$consignment->id)) {
                $this->data['print'] = 2;
            } else {
                $this->data['print'] = 0;
            }
        }
        $this->load->view($this->theme . 'products/modal_view_consignment', $this->data);
    }

    public function view_consignment_return($id = null)
    {
        $this->data['consignment'] = $this->products_model->getConsignmentByID($id);
        $this->data['returns'] = $this->products_model->getConsignmentByConsignID($id);
        $this->load->view($this->theme . 'products/view_consignment_return', $this->data);
    }

    public function product_suggestions()
    {
        $term = $this->input->get('term', TRUE);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }
        $rows = $this->products_model->getBomProductNames($term);
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

    public function get_raw_suggestions()
    {
        $term = $this->input->get('term', TRUE);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }
        $rows = $this->products_model->getRawProductNames($term);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            foreach ($rows as $row) {
                // $options = $this->products_model->getUnitbyProduct($row->id,$row->unit);
                $options = $this->site->getUnitByProId($row->id);
                $pr[] = array('row_id' => $c, 'id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => 1, 'cost' => $row->cost, 'options' => $options,'bom_type'=>'' );
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    public function getWarehouseByID_Ajax() 
    {
        $warehouse_id = $this->input->get('warehouse_id');
        $warehouse    = $this->site->getWarehouseByID($warehouse_id);
        $this->bpas->send_json($warehouse);
    } 


    public function saleman_stock($warehouse_id = null, $biller_id = NULL)
    {
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['warehouses'] = $this->site->getWarehouses();
        $this->data['warehouse']  = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        $this->data['billers']    = $this->site->getBillers();
        $this->data['biller']     = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('inventory')), array('link' => '#', 'page' => lang('consignments')));
        $meta = array('page_title' => lang('consignments'), 'bc' => $bc);
        $this->page_construct('products/consignments', $meta, $this->data);

    }

    public function getSalemanStock($warehouse_id = null, $biller_id = NULL)
    {
        $this->bpas->checkPermissions('consignments');
        $create_sale = '';
        if(($this->Admin || $this->Owner) || $this->GP['sales-add']){
            $create_sale = anchor('admin/sales/add/?consignment_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('create_sale'), ' class="consignment-create_sale" ​');
        }
        $edit_link = anchor('admin/products/edit_consignment/$1', '<i class="fa fa-edit"></i> ' . lang('edit_consignment'), ' class="edit_consignment" ');
        $view_return_link = anchor('admin/products/view_consignment_return/$1', '<i class="fa fa-file-text-o"></i>' . lang('view_consignment_return'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $return_link = anchor('admin/products/return_consignment/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_consignment'));
        $delete_link = "<a href='#' class='po delete_consignment' title='<b>" . $this->lang->line("delete_consignment") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('products/delete_consignment/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_consignment') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $view_return_link . '</li>
                        <li>' . $return_link . '</li>
                        <li>' . $create_sale . '</li>
                        <li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
       
        $this->load->library('datatables');
        $this->datatables
            ->select("id, date, reference_no, customer, grand_total, status, attachment")
            ->where("consignments.status !=",'returned')
            ->from("consignments");
        if ($warehouse_id) {
            $this->datatables->where('consignments.warehouse_id', $warehouse_id);
        }
        if ($biller_id) {
            $this->datatables->where('consignments.biller_id', $biller_id);
        }   
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
            $this->datatables->where('consignments.biller_id', $this->session->userdata('biller_id'));
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) { 
            $this->datatables->where_in('consignments.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
    public function add_count_ring($reward_category=NULL, $reward_type=NULL)
    {   
        $this->bpas->checkPermissions('add', null, 'reward_exchange');
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules($reward_category, lang($reward_category), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('status', lang('status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_invoiceNo']) {
                $reference = $this->site->CheckedSaleReference($this->input->post('reference_no'), $this->site->getReference('so'));
            } else {
                $reference = $this->site->getReference('so');
            }
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $project_id       = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project;
            $warehouse_id     = $this->input->post('warehouse');
            $company_id       = $this->input->post($reward_category);
            $biller_id        = $this->input->post('biller');
            $status           = $this->input->post('status');
            $payment_status   = $this->input->post('payment_status');
            $payment_term     = $this->input->post('payment_term');
            $company_details  = $this->site->getCompanyByID($company_id);
            $company          = !empty($company_details->company) && $company_details->company != '-' ? $company_details->company . '/' . $company_details->name : $company_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company . '/' . $biller_details->name : $biller_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note       = $this->bpas->clear_tags($this->input->post('staff_note'));
            $total            = 0;
            $digital          = false;
            $i                = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id                    = $_POST['product_id'][$r];
                $reward_id                  = $_POST['reward_id'][$r];
                $item_code                  = $_POST['product_code'][$r];
                $item_name                  = $_POST['product_name'][$r];
                $item_unit                  = $_POST['product_unit'][$r];
                $item_unit_quantity         = $_POST['quantity'][$r];
                $item_quantity              = $_POST['product_base_quantity'][$r];
                if ($reward_type == 'product') {
                    $receive_item_id            = $_POST['receive_product_id'][$r];
                    $receive_item_unit          = $_POST['receive_product_unit'][$r];
                    $receive_item_unit_quantity = $_POST['receive_quantity'][$r];
                    $receive_item_quantity      = $_POST['receive_base_quantity'][$r];
                } else {
                    $receive_item_id            = null;
                    $receive_item_unit          = null;
                    $receive_item_unit_quantity = null;
                    $receive_item_quantity      = null;
                }
                $item_interest      = isset($_POST['interest'][$r]) ? $_POST['interest'][$r] : null;
                $item_set_quantity  = $_POST['set_quantity'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_serial        = isset($_POST['serial'][$r]) && !empty($_POST['serial'][$r]) && $_POST['serial'][$r] != 'undefined' && $_POST['serial'][$r] != 'false' && $_POST['serial'][$r] != 'null' && $_POST['serial'][$r] != 'NULL' ? $_POST['serial'][$r] : null;
                $item_max_serial    = isset($_POST['max_serial'][$r]) ? $_POST['max_serial'][$r] : '';
                $item_detail        = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : '';
                $item_expiry        = null;
                $item_addition_type = null;
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details         = $this->products_model->getProductByID($item_id);
                    $product_receive_details = $this->products_model->getProductByID($receive_item_id);
                    $cost = $product_details->cost;
                    if ($item_type == 'digital') {
                        $digital = true;
                    }
                    $unit_price   = $this->bpas->formatDecimal($unit_price);
                    $subtotal     = ($unit_price * $item_set_quantity);
                    if ($reward_type == 'money') {
                        $interest = (($unit_price * $item_interest) / 100);
                        $subtotal = (($unit_price + ($interest ? $interest : 0)) * $item_set_quantity);
                    }
                    $unit         = $this->site->getUnitByID($item_unit);
                    $receive_unit = $this->site->getUnitByID($receive_item_unit);
                    $purchase_unit_cost = $product_details->cost;
                    $saleman = $this->site->getuser($this->input->post('saleman_by'));
                    if ($unit->id != $product_details->unit) {
                        $cost = $this->site->convertCostingToBase($purchase_unit_cost, $unit);
                    } else {
                        $cost = $cost;
                    }
                    $product = [
                        'warehouse_id'               => $warehouse_id,
                        'reward_id'                  => $reward_id,
                        'exchange_product_id'        => $item_id,
                        'exchange_product_code'      => $item_code,
                        'exchange_product_name'      => $item_name,
                        'exchange_product_unit_id'   => $unit ? $unit->id : null,
                        'exchange_product_unit_code' => $unit ? $unit->code : null,
                        'exchange_quantity'          => $item_quantity,
                        'exchange_unit_quantity'     => $item_unit_quantity,
                        'receive_product_id'         => $receive_item_id ? $receive_item_id : null,
                        'receive_product_code'       => $product_receive_details ? $product_receive_details->code : null,
                        'receive_product_name'       => $product_receive_details ? $product_receive_details->name : null,
                        'receive_product_unit_id'    => $receive_unit ? $receive_unit->id : null,
                        'receive_product_unit_code'  => $receive_unit ? $receive_unit->code : null,
                        'receive_quantity'           => $receive_item_quantity ? $receive_item_quantity : null,
                        'receive_unit_quantity'      => $receive_item_unit_quantity ? $receive_item_unit_quantity : null,
                        'set_quantity'               => $item_set_quantity,
                        'purchase_unit_cost'         => $purchase_unit_cost ? $purchase_unit_cost : NULL,
                        'unit_price'                 => $this->bpas->formatDecimal($unit_price),
                        'real_unit_price'            => $real_unit_price,
                        'subtotal'                   => $this->bpas->formatDecimal($subtotal),
                        'option_id'                  => $item_option,
                        'expiry'                     => $item_expiry,
                        'serial_no'                  => $item_serial,
                        'max_serial'                 => $item_max_serial,
                        'comment'                    => $item_detail,
                        'addition_type'              => $item_addition_type,
                        'interest'                   => $item_interest
                    ];
                    //========add accounting=========//
                    if($this->Settings->module_account == 1 && $item_type != 'manual' && ($status == 'completed')) {
                        $getproduct    = $this->site->getProductByID($item_id);
                        $default_sale  = $default_sale = ($item_type == 'standard') ? $this->accounting_setting->default_sale : $this->accounting_setting->other_income;
                        $inventory_acc = $this->accounting_setting->default_stock;
                        $costing_acc   = $this->accounting_setting->default_cost;
                        $accTrans[] = array(
                            'tran_type'     => 'Exchange',
                            'tran_date'     => $date,
                            'reference_no'  => $reference,
                            'account_code'  => $this->accounting_setting->default_stock,
                            'amount'        => -($cost * $item_unit_quantity),
                            'narrative'     => $this->site->getAccountName($this->accounting_setting->default_stock),
                            'description'   => $note,
                            'biller_id'     => $biller_id,
                            'project_id'    => $project_id,
                            'company_id'    => $company_id,
                            'created_by'    => $this->session->userdata('user_id'),
                        );
                        $accTrans[] = array(
                            'tran_type'     => 'Exchange',
                            'tran_date'     => $date,
                            'reference_no'  => $reference,
                            'account_code'  => $this->accounting_setting->default_cost,
                            'amount'        => ($cost * $item_unit_quantity),
                            'narrative'     => $this->site->getAccountName($this->accounting_setting->default_cost),
                            'description'   => $note,
                            'biller_id'     => $biller_id,
                            'project_id'    => $project_id,
                            'company_id'    => $company_id,
                            'created_by'    => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_cost)
                        );
                        $accTrans[] = array(
                            'tran_type'     => 'Exchange',
                            'tran_date'     => $date,
                            'reference_no'  => $reference,
                            'account_code'  => $default_sale,
                            'amount'        => -$subtotal,
                            'narrative'     => $this->site->getAccountName($default_sale),
                            'description'   => $note,
                            'biller_id'     => $biller_id,
                            'project_id'    => $project_id,
                            'company_id'    => $company_id,
                            'created_by'    => $this->session->userdata('user_id'),
                        );
                    }
                    //============end accounting=======//
                    $products[] = $product;
                    $total += $this->bpas->formatDecimal($subtotal, 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }
            $grand_total  = $this->bpas->formatDecimal($total, 4);
            $user         = $this->site->getUser($this->session->userdata('user_id'));
            $data         = [
                'date'                => $date,
                'project_id'          => $this->input->post('project'),
                'reference_no'        => $reference,
                'company_id'          => $company_id,
                'company'             => $company,
                'biller_id'           => $biller_id,
                'biller'              => $biller,
                'warehouse_id'        => $warehouse_id,
                'note'                => $note,
                'staff_note'          => $staff_note,
                'grand_total'         => $grand_total,
                'status'              => $status,
                'payment_status'      => $payment_status,
                'paid'                => 0, 
                'created_by'          => $this->session->userdata('user_id'),
                'category'            => $reward_category,
                'type'                => $reward_type,
            ];
            if ($payment_status == 'partial' || $payment_status == 'paid') {
                if ($this->input->post('paid_by') == 'deposit') {
                    if (!$this->site->check_customer_deposit($company_id, $this->input->post('amount-paid'))) {
                        $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                }
                if ($this->input->post('paid_by') == 'gift_card') {
                    $gc            = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
                    $amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                    $gc_balance    = $gc->balance - $amount_paying;
                    $payment       = [
                        'date'         => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'amount'       => $this->bpas->formatDecimal($amount_paying),
                        'paid_by'      => $this->input->post('paid_by'),
                        'cheque_no'    => $this->input->post('cheque_no'),
                        'cc_no'        => $this->input->post('gift_card_no'),
                        'cc_holder'    => $this->input->post('pcc_holder'),
                        'cc_month'     => $this->input->post('pcc_month'),
                        'cc_year'      => $this->input->post('pcc_year'),
                        'cc_type'      => $this->input->post('pcc_type'),
                        'created_by'   => $this->session->userdata('user_id'),
                        'note'         => $this->input->post('payment_note'),
                        'type'         => 'received',
                        'gc_balance'   => $gc_balance,
                    ];
                } else {
                    $payment = [
                        'date'         => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'amount'       => $this->bpas->formatDecimal($this->input->post('amount-paid')),
                        'paid_by'      => $this->input->post('paid_by'),
                        'cheque_no'    => $this->input->post('cheque_no'),
                        'cc_no'        => $this->input->post('pcc_no'),
                        'cc_holder'    => $this->input->post('pcc_holder'),
                        'cc_month'     => $this->input->post('pcc_month'),
                        'cc_year'      => $this->input->post('pcc_year'),
                        'cc_type'      => $this->input->post('pcc_type'),
                        'created_by'   => $this->session->userdata('user_id'),
                        'note'         => $this->input->post('payment_note'),
                        'type'         => 'received',
                    ];
                }
                $amount_paying = $this->bpas->formatDecimal($this->input->post('amount-paid'));
                //=====add accountig=====//
                if ($this->Settings->module_account == 1) {
                    if ($this->input->post('paid_by') == 'deposit') {
                        $payment['bank_account'] = $this->accounting_setting->default_sale_deposit;
                        $paying_to = $this->accounting_setting->default_sale_deposit;
                    } else {
                        $payment['bank_account'] = $this->input->post('bank_account');
                        $paying_to = $this->input->post('bank_account');
                    }
                    if($amount_paying < $grand_total){
                        $accTranPayments[] = array(
                            'tran_type'     => 'Payment',
                            'tran_date'     => $date,
                            'reference_no'  => $this->input->post('payment_reference_no'),
                            'account_code'  => $this->accounting_setting->default_receivable,
                            'amount'        => ($grand_total - $amount_paying),
                            'narrative'     => $this->site->getAccountName($this->accounting_setting->default_receivable),
                            'description'   => $this->input->post('payment_note'),
                            'biller_id'     => $biller_id,
                            'project_id'    => $project_id,
                            'company_id'    => $company_id,
                            'created_by'    => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                        );
                    }
                    $accTranPayments[] = array(
                        'tran_type'    => 'Payment',
                        'tran_date'    => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'account_code' => $paying_to,
                        'amount'       => $amount_paying,
                        'narrative'    => $this->site->getAccountName($paying_to),
                        'description'  => $this->input->post('payment_note'),
                        'biller_id'    => $biller_id,
                        'project_id'   => $project_id,
                        'company_id'   => $company_id,
                        'created_by'   => $this->session->userdata('user_id'),
                    );
                }
                //=====end accountig=====//
            } else {
                $accTranPayments = [];
                $payment         = [];
                $accTrans[] = array(
                    'tran_type'     => 'Exchange',
                    'tran_date'     => $date,
                    'reference_no'  => $reference,
                    'account_code'  => $this->accounting_setting->default_receivable,
                    'amount'        => $grand_total,
                    'narrative'     => $this->site->getAccountName($this->accounting_setting->default_receivable),
                    'biller_id'     => $biller_id,
                    'project_id'    => $project_id,
                    'company_id'    => $company_id,
                    'created_by'    => $this->session->userdata('user_id'),
                    'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                );
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
            // $this->bpas->print_arrays($data, $products, $payment);
        }
        if ($this->form_validation->run() == true && $this->products_model->addRewardExchange($data, $products, $payment, null, null, null, null)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang('reward_exchange_added'));
            admin_redirect('products/rewards_exchange/' . $reward_category);
        } else {
            $this->data['projects']      = $this->site->getAllProject();
            $this->data['error']         = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers']       = $this->site->getAllCompanies('biller');
            $this->data['data']          = $this->site->getBillerByUser($this->session->userdata('user_id'));
            $this->data['mbillers']      = $this->site->getAllCompaniesByBiller('biller', explode(',', $this->data['data']->multi_biller));
            $this->data['agencies']      = $this->site->getAllUsers();
            $this->data['payment_term']  = $this->site->getAllPaymentTerm();
            $this->data['warehouses']    = $this->site->getAllWarehouses();
            $this->data['tax_rates']     = $this->site->getAllTaxRates();
            $this->data['units']         = $this->site->getAllBaseUnits();
            $this->data['slnumber']      = $reward_category == 'customer' ? $this->site->getReference('crw') : $this->site->getReference('srw');
            $this->data['payment_ref']   = '';
            $this->data['category']      = $reward_category;
            $this->data['type']          = $reward_type;
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products/rewards_exchange/' . $reward_category), 'page' => lang('rewards_exchange')], ['link' => '#', 'page' => lang('add_reward_exchange')]];
            $meta = ['page_title' => lang('add_count_ring'), 'bc' => $bc];
            $this->page_construct('products/add_count_ring', $meta, $this->data);
        }
    }
    public function count_ring_suggestions($reward_category=NULL, $reward_type=NULL)
    {
        $term         = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);
        $customer_id  = $this->input->get('customer_id', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed       = $this->bpas->analyze_term($term);
        $sr             = $analyzed['term'];
        $option_id      = $analyzed['option_id'];
        $warehouse      = $this->site->getWarehouseByID($warehouse_id);
        $rows           = $this->products_model->getProductRingNames($sr, $warehouse_id);
        if ($rows) {
            $r = 0; $pr = array();
            foreach ($rows as $row) {

                $reward                 = $this->products_model->getProductByID($row->id);
                $receive_product        = $reward->id ? $this->products_model->getProductByID($reward->id) : null;

                $cate_id                = $row->subcategory_id ? $row->subcategory_id : $row->category_id;

                $c                      = uniqid(mt_rand(), true);
                unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $option                 = false;
                $row->quantity          = 0;
                $row->item_tax_method   = $row->tax_method;
                $row->discount          = 0;          
                $row->serial            = '';
                $set_price   = $this->site->getUnitByProId($row->id);
       
                $row->quantity = 1;
       

                $row->qty                   = 0;
                $row->base_quantity         = 0;
                $row->price                 = 0;
                $row->real_unit_price       = 0;
                $row->base_unit_price       = 0;
                $row->interest              = 0;
                $row->unit                  = $row->unit;
                $row->base_unit             = $row->unit;

                if ($reward_type == 'product') {
                    $row->receive_product_id    = $reward->receive_product_id;
                    $row->receive_qty           = $reward->receive_quantity;
                    $row->receive_base_quantity = $reward->receive_quantity;
                    $row->receive_unit          = $row->unit;
                    $row->receive_base_unit     = $row->unit;
                }
                $row->set_quantity          = 1;
                $row->comment               = '';
                $categories                 = $this->site->getCategoryByID($cate_id);
                $fiber_type                 = false;
                $combo_items                = false;
                $fibers                     = false;
                $units                      = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate                   = $this->site->getTaxRateByID($row->tax_rate);
                $pr[] = [
                    'id'                => sha1($c . $r), 
                    'item_id'           => $row->id, 
                    'row'               => $row, 
                    'label'             => $row->name . ' (' . $row->code . ')', 
                    'category'          => $row->category_id, 
                    'tax_rate'          => $tax_rate, 
                    'units'             => $units,
                    'reward'            => $reward, 
                    'exchange_product'  => $reward,
                    'receive_product'   => $receive_product, 
                    'combo_items'       => false, 
                    'set_price'         => false, 
                    'options'           => false, 
                    'fiber'             => false, 
                    'expiry'            => null
                ];
                $r++;
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }
}