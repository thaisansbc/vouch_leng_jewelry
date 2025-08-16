<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (!empty($variants)) {
    foreach ($variants as $variant) {
        $vars[] = addslashes($variant->name);
    }
} else {
    $vars = [];
} ?>
<script type="text/javascript">
    $(document).ready(function() {
        $('.gen_slug').change(function(e) {
            getSlug($(this).val(), 'products');
        });
        $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
            placeholder: "<?= lang('select_category_to_load') ?>",
            minimumResultsForSearch: 7,
            data: [{
                id: '',
                text: '<?= lang('select_category_to_load') ?>'
            }]
        });
        $('#category_______').change(function() {
            var v = $(this).val();
            $('#modal-loading').show();
            if (v) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= admin_url('products/getSubCategories') ?>/" + v,
                    dataType: "json",
                    success: function(scdata) {
                        if (scdata != null) {
                            scdata.push({
                                id: '',
                                text: '<?= lang('select_subcategory') ?>'
                            });
                            $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                                placeholder: "<?= lang('select_category_to_load') ?>",
                                minimumResultsForSearch: 7,
                                data: scdata
                            });
                        } else {
                            $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('no_subcategory') ?>").select2({
                                placeholder: "<?= lang('no_subcategory') ?>",
                                minimumResultsForSearch: 7,
                                data: [{
                                    id: '',
                                    text: '<?= lang('no_subcategory') ?>'
                                }]
                            });
                        }
                    },
                    error: function() {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                        $('#modal-loading').hide();
                    }
                });
            } else {
                $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
                    placeholder: "<?= lang('select_category_to_load') ?>",
                    minimumResultsForSearch: 7,
                    data: [{
                        id: '',
                        text: '<?= lang('select_category_to_load') ?>'
                    }]
                });
            }
            $('#modal-loading').hide();
        });
        $('#code').bind('keypress', function(e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                return false;
            }
        });
        $('#code').bind('keyup', function(e) {
            var unit_id = $("#unit").val();
            $("#unit_id_"+unit_id+"").val($(this).val());
        });
    });
</script>
<body>
    <div class="breadcrumb-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_product');?></h2>
    </div>
    <div class="box">
        <div class="box-content">
            <div class="row">
                <div class="col-lg-12">
                    <?php
                    $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                    echo admin_form_open_multipart('products/add', $attrib)
                    ?>
                    <ul id="myTab" class="nav nav-tabs">
                        <li class="bold"><a href="#required" class="tab-grey"><?= lang('required') ?></a></li>
                        <li class="bold"><a href="#optional" class="tab-grey"><?= lang('details') ?></a></li>
                        <?php if ($Settings->module_account) { ?><li class="bold"><a href="#accounting" class="tab-grey"><?= lang('accounting') ?></a></li><?php } ?>
                        <li class="bold"><a href="#variants1" class="tab-grey"><?= lang('variants') ?></a></li>
                        <!-- <li class="bold"><a href="#warehouse1" class="tab-grey"><?= lang('warehouse') ?></a></li> -->
                        <li class="bold"><a href="#tab_supplier" class="tab-grey"><?= lang('supplier') ?></a></li>
                        <li class="bold"><a href="#tab_promotion" class="tab-grey"><?= lang('promotion') ?></a></li>
                        <?php if (SHOP) { ?>
                        <li class="bold"><a href="#tab_shop" class="tab-grey"><?= lang('shop') ?></a></li>
                        <?php } ?>
                        <li class="bold"><a href="#tab_add_on" class="tab-grey"><?= lang('add_on') ?></a></li>
                        <li class="bold"><a href="#tab_racks" class="tab-grey"><?= lang('racks') ?></a></li>
                    </ul>
                    <div class="tab-content">
                        <div id="required" class="tab-pane fade in">
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang('product_type', 'type') ?>
                                            <?php
                                            $product_types = $this->config->item("product_types");
                                            foreach($product_types as $product_type){
                                                $opts[$product_type] = lang($product_type);
                                            }
                                            echo form_dropdown('type', $opts, (isset($_POST['type']) ? $_POST['type'] : ($product ? $product->type : '')), 'class="form-control" id="type" required="required"');
                                            ?>
                                        </div>
                                    </div>
                                    <?php if($this->Settings->module_concrete){ ?>
                                    <div style="display:none;" class="col-md-6 bom">
                                        <div class="form-group">
                                            <?= lang("stregth", "stregth") ?>
                                            <?php
                                                $str_opt[0] = lang("no");
                                                $str_opt[1] = lang("yes");
                                                echo form_dropdown('stregth', $str_opt, (isset($_POST['stregth']) ? $_POST['stregth'] : ($product ? $product->stregth : '')), 'class="form-control" id="stregth"');
                                            ?>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <div class="col-md-6">
                                        <div class="form-group all">
                                            <?= lang('barcode', 'code') ?>
                                            <div class="input-group">
                                                <?= form_input('code', (isset($_POST['code']) ? $_POST['code'] : ($product ? $product->code :'')), 'class="form-control" id="code" placeholder="' . lang('generate') . ' ' . lang('code') . '" required="required"') ?>
                                                <span class="input-group-addon pointer" id="random_num" style="padding: 1px 10px;">
                                                    <i class="fa fa-random"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group all">
                                            <?= lang('product_name', 'name') ?>
                                            <?= form_input('name', (isset($_POST['name']) ? $_POST['name'] : ($product ? $product->name : '')), 'class="form-control gen_slug" placeholder="' . lang('input') . ' ' . lang('name') . '" id="name" required="required"'); ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group all">
                                            <?= lang('category', 'category') ?>
                                            <div class="input-group" style="width: 100%">
                                                <?php 
                                                $form_category = null;
                                                function formMultiLevelCategory($data, $n, $str = '')
                                                {
                                                    $form_category = ($n ? '<select id="category" name="category" class="form-control select" style="width: 100%" placeholder="' . lang('select') . ' ' . lang('category') . '" required="required"><option value="" selected>' . lang('select') . ' ' . lang('category') . '</option>' : '');
                                                    foreach ($data as $key => $categories) {
                                                        if (!empty($categories->children)) {
                                                            $form_category .= '<option disabled>' . $str . $categories->name . '</option>';
                                                            $form_category .= formMultiLevelCategory($categories->children, 0, ($str.'&emsp;&emsp;'));
                                                        } else {
                                                            $form_category .= ('<option value="' . $categories->id . '">' . $str . $categories->name . '</option>');
                                                        }
                                                    }

                                                    $form_category .= ($n ? '</select>' : '');
                                                    return $form_category;
                                                }
                                                // echo htmlentities(formMultiLevelCategory($nest_categories, 1));
                                                echo formMultiLevelCategory($nest_categories, 1); ?>
                                                <?php if ($Owner || $Admin) {?>
                                                    <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                                        <a href="<?php echo admin_url('system_settings/add_category'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                            <i class="fa fa-plus-circle" id="addIcon" style="font-size: 1.2em;"></i>
                                                        </a>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                             
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang('product_unit', 'unit'); ?>
                                            <div class="input-group" style="width:100%">
                                                <?php
                                                $pu[''] = lang('select') . ' ' . lang('unit');
                                                foreach ($base_units as $bu) {
                                                    $pu[$bu->id] = $bu->name . ' (' . $bu->code . ')';
                                                } ?>
                                                <?= form_dropdown(
                                                    'unit',
                                                    $pu,
                                                    set_value('unit', ($product ? $product->unit : '')),
                                                    'class="form-control tip" id="unit" required="required" style="width:100%;"'
                                                ); ?>
                                                <?php if ($Owner || $Admin) { ?>
                                                    <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                                        <a href="<?php echo admin_url('system_settings/add_unit'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                            <i class="fa fa-plus-circle" id="addIcon" style="font-size: 1.2em;"></i>
                                                        </a>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group standard">
                                            <?= lang('default_sale_unit', 'default_sale_unit'); ?>
                                            <?php $uopts[''] = lang('select_unit_first'); ?>
                                            <?= form_dropdown('default_sale_unit', $uopts, ($product ? $product->sale_unit : ''), 'class="form-control" id="default_sale_unit" style="width:100%;"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group standard">
                                            <?= lang('default_purchase_unit', 'default_purchase_unit'); ?>
                                            <?= form_dropdown('default_purchase_unit', $uopts, ($product ? $product->purchase_unit : ''), 'class="form-control" id="default_purchase_unit" style="width:100%;"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label" for="currency"><?= lang("default_currency"); ?></label>
                                            <div class="controls">
                                                <?php
                                                    foreach ($currencies as $currency) {
                                                        $cu[$currency->code] = $currency->name;
                                                    }
                                                    echo form_dropdown('currency', $cu, (isset($_POST['currency']) ? $_POST['currency'] : $Settings->default_currency), 'class="form-control tip" id="currency" style="width:100%;"');
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($this->Settings->multiple_code_unit == 1) { ?>
                                        <div class="col-md-4">
                                            <div id="pr_code"></div>
                                        </div>
                                    <?php } ?>
                                    <?php $style = ($this->Settings->multiple_code_unit == 1) ? "col-md-4" : "col-md-6"; ?>
                                    <div class="<?= $style?>" >
                                        <div class="form-group all">
                                            <div class="cost1">
                                                <?= lang('product_price', 'price') ?>($)
                                                <?= form_input('price', (isset($_POST['price']) ? $_POST['price'] : ($product ? $this->bpas->formatDecimal($product->price) : 0)), 'class="form-control tip price" id="price" ') ?>
                                            </div>
                                            <div id="units_div"></div>
                                            <div id="input_p"></div>
                                        </div>
                                    </div>
                                    <div class="<?= $style?>" >
                                            <div class="form-group">
                                                <div class="cost1"> 
                                                    <?= lang('product_cost', 'cost') ?>($)
                                                    <?= form_input('cost', (isset($_POST['cost']) ? $_POST['cost'] : ($product ? $this->bpas->formatDecimal($product->cost) : 0)), 'class="form-control tip" id="cost" required="required"') ?>
                                                </div>
                                                <div id="input" ></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 hide">
                                            <div class="form-group">
                                                <?= lang('other_cost', 'other_cost') ?>
                                                <?= form_input('other_cost', (isset($_POST['other_cost']) ? $_POST['other_cost'] : ($product ? $this->bpas->formatDecimal($product->other_cost) : 0)), 'class="form-control tip" id="other_cost" ') ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6 hide">
                                            <div class="form-group all">
                                                <?= lang('other_price', 'other_price') ?>
                                                <?= form_input('other_price', (isset($_POST['other_price']) ? $_POST['other_price'] : ($product ? $this->bpas->formatDecimal($product->other_price) : 0)), 'class="form-control tip" id="other_price" ') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="bom" style="display:none;">
                                            <div class="form-group">
                                                <?= lang("add_product", "add_item") . ' (' . lang('not_with_variants') . ')'; ?>
                                                <?php echo form_input('add_item', '', 'class="form-control ttip" id="add_item_bom" data-placement="top" data-trigger="focus" data-bv-notEmpty-message="' . lang('please_add_items_below') . '" placeholder="' . $this->lang->line("add_item") . '"'); ?>
                                            </div>
                                            <div class="control-group table-group">
                                                <label class="table-label" for="combo"><?= lang("bom_products"); ?></label>
                                                <div class="controls table-controls">
                                                    <table id="bomTable" class="table items table-striped table-bordered table-condensed table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>
                                                                <th style="display: none !important;"><?= lang("type"); ?></th>
                                                                <?php if ($this->config->item("concretes")) { ?>
                                                                    <th style="display: none !important;"><?= lang("biller"); ?></th>
                                                                <?php } ?>
                                                                <th><?= lang("quantity"); ?></th>
                                                                <th><?= lang("unit"); ?></th>
                                                                <th class="col-md-1 col-sm-1 col-xs-1 text-center" style="text-align: center !important;">
                                                                    <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="combo" style="display: none;">
                                            <div class="form-group">
                                                <?= lang('add_product', 'add_item') . ' (' . lang('not_with_variants') . ')'; ?>
                                                <?php echo form_input('add_item', '', 'class="form-control ttip" id="add_item" data-placement="top" data-trigger="focus" data-bv-notEmpty-message="' . lang('please_add_items_below') . '" placeholder="' . $this->lang->line('add_item') . '"'); ?>
                                            </div>
                                            <div class="control-group table-group">
                                                <label class="table-label" for="combo"><?= lang('combo_products'); ?></label>
                                                <div class="controls table-controls">
                                                    <table id="prTable" class="table items table-striped table-bordered table-condensed table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th class="col-md-5 col-sm-5 col-xs-5"><?= lang('product') . ' (' . lang('code') . ' - ' . lang('name') . ')'; ?></th>
                                                                <th class="col-md-2 col-sm-2 col-xs-2"><?= lang('quantity'); ?></th>
                                                                <th class="col-md-3 col-sm-3 col-xs-3"><?= lang('unit_price'); ?></th>
                                                                <th class="col-md-1 col-sm-1 col-xs-1 text-center">
                                                                    <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="digital" style="display:none;">
                                            <div class="form-group digital">
                                                <?= lang('digital_file', 'digital_file') ?>
                                                <input id="digital_file" type="file" data-browse-label="<?= lang('browse'); ?>" name="digital_file" data-show-upload="false" data-show-preview="false" class="form-control file">
                                            </div>
                                            <div class="form-group">
                                                <?= lang('file_link', 'file_link'); ?>
                                                <?= form_input('file_link', set_value('file_link'), 'class="form-control" id="file_link"'); ?>
                                            </div>
                                        </div>
                                        <div class="form-group all">
                                            <?= lang('product_image', 'product_image') ?>
                                            <input id="product_image" type="file" data-browse-label="<?= lang('browse'); ?>" name="product_image" data-show-upload="false" data-show-preview="true" accept="image/*" class="form-control file">
                                        </div>
                                        <div class="form-group all">
                                            <?= lang('product_gallery_images', 'images') ?>
                                            <input id="images" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile[]" multiple="true" data-show-upload="false" data-show-preview="true" class="form-control file" accept="image/*">
                                        </div>
                                        <div id="img-details"></div>
                                    </div>
                                </div>
                            </div>
                        <div id="optional" class="tab-pane fade">
                            <div class="row">
                                <div class="col-md-12">
                                    <?php /*
                                    if($Settings->seperate_product_by_biller){
                                        if ($Owner || $Admin) { ?>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <?= lang("biller", "biller"); ?>
                                                    <?php
                                                    $bl[""] = "";
                                                    foreach ($billers as $biller) {
                                                        $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company . '/' . $biller->name : $biller->name;
                                                    }
                                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                                    ?>
                                                </div>
                                            </div>
                                        <?php } else {
                                            $biller_input = array(
                                                'type'  => 'hidden',
                                                'name'  => 'biller',
                                                'id'    => 'slbiller',
                                                'value' => $user_billers[0],
                                            );
                                            echo form_input($biller_input);
                                        }
                                    } */?>
                                    <div class="col-md-4">
                                        <div class="form-group all">
                                            <?= lang('item_code', 'item_code') ?>
                                            <?= form_input('item_code', (isset($_POST['item_code']) ? $_POST['item_code'] : ($product ? $product->item_code : '')), 'class="form-control" id="item_code" ') ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group all">
                                            <?= lang('serial', 'serial') ?>
                                            <?= form_input('serial', (isset($_POST['serial']) ? $_POST['serial'] : ($product ? $product->serial : '')), 'class="form-control" id="serial" ') ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group all">
                                            <?= lang('max_serial', 'max_serial') ?>
                                            <?= form_input('max_serial', (isset($_POST['max_serial']) ? $_POST['max_serial'] : ($product ? $product->max_serial : '')), 'class="form-control" id="max_serial" ') ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group all">
                                            <?= lang('batch_numer', 'batch_numer') ?>
                                            <?= form_input('batch_numer', (isset($_POST['batch_numer']) ? $_POST['batch_numer'] : ($product ? $product->batch_numer : '')), 'class="form-control" id="batch_numer" ') ?>
                                        </div>
                                    </div>
                                    <?php if (SHOP) { ?>
                                        <div class="col-md-4">
                                            <div class="form-group all">
                                                <?= lang('slug', 'slug'); ?>
                                                <?= form_input('slug', set_value('slug'), 'class="form-control tip" id="slug" required="required"'); ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <div class="col-md-4">
                                        <div class="form-group all">
                                            <?= lang('second_name', 'second_name'); ?>
                                            <?= form_input('second_name', set_value('second_name'), 'class="form-control tip" id="second_name"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group standard_combo">
                                            <?= lang('weight', 'weight'); ?>
                                            <?= form_input('weight', set_value('weight'), 'class="form-control tip" id="weight"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group all">
                                            <?= lang('barcode_symbology', 'barcode_symbology') ?>
                                            <?php
                                            $bs = ['code25' => 'Code25', 'code39' => 'Code39', 'code128' => 'Code128', 'ean8' => 'EAN8', 'ean13' => 'EAN13', 'upca' => 'UPC-A', 'upce' => 'UPC-E'];
                                            echo form_dropdown('barcode_symbology', $bs, (isset($_POST['barcode_symbology']) ? $_POST['barcode_symbology'] : ($product ? $product->barcode_symbology : 'code128')), 'class="form-control select" id="barcode_symbology" required="required" style="width:100%;"');
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group all">
                                            <?= lang('brand', 'brand') ?>
                                            <div class="input-group" style="width:100%">
                                                <?php
                                                $br[''] = '';
                                                if (!empty($brands)) {
                                                    foreach ($brands as $brand) {
                                                        $br[$brand->id] = $brand->name;
                                                    }
                                                }
                                                echo form_dropdown('brand', $br, (isset($_POST['brand']) ? $_POST['brand'] : ($product ? $product->brand : '')), 'class="form-control select" id="brand" placeholder="' . lang('select') . ' ' . lang('brand') . '" style="width:100%"') ?>
                                                <?php if ($Owner || $Admin) { ?>
                                                    <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                                        <a href="<?php echo admin_url('system_settings/add_brand'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                            <i class="fa fa-plus-circle" id="addIcon" style="font-size: 1.2em;"></i>
                                                        </a>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($Settings->invoice_view == 2) { ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang('hsn_code', 'hsn_code'); ?>
                                                <?= form_input('hsn_code', set_value('hsn_code', ($product ? $product->hsn_code : '')), 'class="form-control" id="hsn_code"'); ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <?php if ($Settings->tax1) { ?>
                                        <div class="col-md-4">
                                            <div class="form-group all">
                                                <?= lang('product_tax', 'tax_rate') ?>
                                                <div class="input-group" style="width:100%">
                                                    <?php
                                                    $tr[''] = '';
                                                    foreach ($tax_rates as $tax) {
                                                        $tr[$tax->id] = $tax->name;
                                                    }
                                                    echo form_dropdown('tax_rate', $tr, (isset($_POST['tax_rate']) ? $_POST['tax_rate'] : ($product ? $product->tax_rate : $Settings->default_tax_rate)), 'class="form-control select" id="tax_rate" placeholder="' . lang('select') . ' ' . lang('product_tax') . '" style="width:100%"') ?>
                                                    <?php if ($Owner || $Admin) { ?>
                                                        <div class="input-group-addon no-print" style="padding: 2px 8px;">

                                                            <a href="<?php echo admin_url('system_settings/add_tax_rate'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                                <i class="fa fa-plus-circle" id="addIcon" style="font-size: 1.2em;"></i>
                                                            </a>

                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <!-- <php
                                                    $tr[''] = '';
                                                    foreach ($tax_rates as $tax) {
                                                        $tr[$tax->id] = $tax->name;
                                                    }
                                                    echo form_dropdown('tax_rate', $tr, (isset($_POST['tax_rate']) ? $_POST['tax_rate'] : ($product ? $product->tax_rate : $Settings->default_tax_rate)), 'class="form-control select" id="tax_rate" placeholder="' . lang('select') . ' ' . lang('product_tax') . '" style="width:100%"')
                                                    ?> -->
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group all">
                                                <?= lang('tax_method', 'tax_method') ?>
                                                <?php
                                                $tm = ['1' => lang('exclusive'), '0' => lang('inclusive')];
                                                echo form_dropdown('tax_method', $tm, (isset($_POST['tax_method']) ? $_POST['tax_method'] : ($product ? $product->tax_method : '')), 'class="form-control select" id="tax_method" placeholder="' . lang('select') . ' ' . lang('tax_method') . '" style="width:100%"'); ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <div class="col-md-4">
                                        <div class="form-group standard">
                                            <?= lang('alert_quantity', 'alert_quantity') ?>
                                            <div class="input-group"> <?= form_input('alert_quantity', (isset($_POST['alert_quantity']) ? $_POST['alert_quantity'] : ($product ? $this->bpas->formatQuantityDecimal($product->alert_quantity) : '')), 'class="form-control tip" id="alert_quantity"') ?>
                                                <span class="input-group-addon">
                                                    <input type="checkbox" name="track_quantity" id="track_quantity" value="1" <?= ($product ? (isset($product->track_quantity) ? 'checked="checked"' : '') : 'checked="checked"') ?>>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group all">
                                            <?= lang('expiry_alert_days', 'expiry_alert_days'); ?>
                                            <?= form_input('expiry_alert_days', set_value('expiry_alert_days'), 'class="form-control tip" id="expiry_alert_days"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group all">
                                            <?= lang('stock_type', 'stock_type') ?>
                                            <div class="input-group" style="width:100%">
                                                <?php
                                                // $st[''] = '';
                                                // $stock_type_selected = explode(',',$product->stock_type);
                                                foreach ($stock_types as $stock_type) {
                                                    $st[$stock_type->id] = $stock_type->name;
                                                }
                                                echo form_dropdown('stock_type[]', $st, (isset($_POST['stock_type']) ? $_POST['stock_type'] :''), 'class="form-control select" multiple="multiple" id="stock_type" placeholder="' . lang('select') . ' ' . lang('stock_type') . '" style="width:100%"')
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">                                        
                                        <div class="form-group">
                                            <?= lang('multi_option', 'multi_option'); ?>
                                            <?php
                                            $opt = [];
                                            if(!empty($options)) {
                                                foreach ($options as $option) {
                                                    $opt[$option->id] = $option->name;
                                                }
                                            }
                                            echo form_dropdown('product_option[]', $opt, (isset($_POST['product_option']) ? $_POST['product_option'] : ''), 'id="product_option" class="form-control select" data-placeholder="' . lang('select') . ' ' . lang('option') . '" style="width:100%;" multiple="multiple"');
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4"> 
                                        <div class="form-group">
                                            <?= lang('active_status', 'status'); ?>
                                            <?php $status = ['1' => 'Yes', '0' => 'No']; ?>
                                            <?= form_dropdown('status', $status, (isset($_POST['status']) ? $_POST['status'] : ''), 'class="form-control tip" id="status" required="required"'); ?>
                                        </div>
                                    </div>
                                </div>
                                <?php if($Settings->cbm == 1){ ?>
                                <div class="col-md-12">
                                    <div class="row">
                                        <strong><?= lang("cbm") ?></strong><br>
                                        <table class="table table-bordered table-condensed table-striped" style=" margin-bottom: 0; margin-top: 10px;">
                                            <thead>
                                                <tr>
                                                    <th><?= lang('length') ?> (cm)</th>
                                                    <th><?= lang('width') ?> (cm)</th>
                                                    <th><?= lang('height') ?> (cm)</th>
                                                    <th><?= lang('weight') ?> (kg)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><input type="text" name="p_length" class="form-control text-right" id="p_length"/></td>
                                                    <td><input type="text" name="p_width" class="form-control text-right" id="p_width"/></td>
                                                    <td><input type="text" name="p_height" class="form-control text-right" id="p_height"/></td>
                                                    <td><input type="text" name="p_weight" class="form-control text-right" id="p_weight"/></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                                <?php } ?>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <input name="cf" type="checkbox" class="checkbox" id="extras" value="" <?= isset($_POST['cf']) ? 'checked="checked"' : '' ?> />
                                        <label for="extras" class="padding05"><?= lang('custom_fields') ?></label>
                                    </div>
                                    <div class="row" id="extras-con" style="display: none;">
                                        <div class="col-md-4">
                                            <div class="form-group all">
                                                <?= lang('pcf1', 'cf1') ?>
                                                <?= form_input('cf1', (isset($_POST['cf1']) ? $_POST['cf1'] : ($product ? $product->cf1 : '')), 'class="form-control tip" id="cf1"') ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group all">
                                                <?= lang('pcf2', 'cf2') ?>
                                                <?= form_input('cf2', (isset($_POST['cf2']) ? $_POST['cf2'] : ($product ? $product->cf2 : '')), 'class="form-control tip" id="cf2"') ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group all">
                                                <?= lang('pcf3', 'cf3') ?>
                                                <?= form_input('cf3', (isset($_POST['cf3']) ? $_POST['cf3'] : ($product ? $product->cf3 : '')), 'class="form-control tip" id="cf3"') ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group all">
                                                <?= lang('pcf4', 'cf4') ?>
                                                <?= form_input('cf4', (isset($_POST['cf4']) ? $_POST['cf4'] : ($product ? $product->cf4 : '')), 'class="form-control tip" id="cf4"') ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group all">
                                                <?= lang('pcf5', 'cf5') ?>
                                                <?= form_input('cf5', (isset($_POST['cf5']) ? $_POST['cf5'] : ($product ? $product->cf5 : '')), 'class="form-control tip" id="cf5"') ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group all">
                                                <?= lang('pcf6', 'cf6') ?>
                                                <?= form_input('cf6', (isset($_POST['cf6']) ? $_POST['cf6'] : ($product ? $product->cf6 : '')), 'class="form-control tip" id="cf6"') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group all">
                                        <?= lang('product_details', 'product_details') ?>
                                        <?= form_textarea('product_details', (isset($_POST['product_details']) ? $_POST['product_details'] : ($product ? $product->product_details : '')), 'class="form-control" id="details"'); ?>
                                    </div>
                                    <div class="form-group all">
                                        <?= lang('product_details_for_invoice', 'details') ?>
                                        <?= form_textarea('details', (isset($_POST['details']) ? $_POST['details'] : ($product ? $product->details : '')), 'class="form-control" id="details"'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="tab_promotion" class="tab-pane fade">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <input type="checkbox" class="checkbox" value="1" name="promotion" id="promotion" <?= $this->input->post('promotion') ? 'checked="checked"' : ''; ?>>
                                        <label for="promotion" class="padding05">
                                            <?= lang('promotion'); ?>
                                        </label>
                                    </div>
                                    <div id="promo" style="display:none;">
                                        <div class="well well-sm">
                                            <div class="form-group">
                                                <?= lang('promo_price', 'promo_price'); ?>
                                                <?= form_input('promo_price', set_value('promo_price'), 'class="form-control tip" id="promo_price"'); ?>
                                            </div>
                                            <div class="form-group">
                                                <?= lang('start_date', 'start_date'); ?>
                                                <?= form_input('start_date', set_value('start_date'), 'class="form-control tip date" id="start_date"'); ?>
                                            </div>
                                            <div class="form-group">
                                                <?= lang('end_date', 'end_date'); ?>
                                                <?= form_input('end_date', set_value('end_date'), 'class="form-control tip date" id="end_date"'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="variants1" class="tab-pane fade">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="standard">
                                        <div id="attrs"></div>
                                        <div class="form-group">
                                            <input type="checkbox" class="checkbox" name="attributes" id="attributes" <?= $this->input->post('attributes') || $product_options ? 'checked="checked"' : ''; ?>><label for="attributes" class="padding05"><?= lang('product_has_attributes'); ?></label> <?= lang('eg_sizes_colors'); ?>
                                        </div>
                                        <div class="well well-sm" id="attr-con" style="<?= $this->input->post('attributes') || $product_options ? '' : 'display:none;'; ?>">
                                            <div class="form-group" id="ui" style="margin-bottom: 0;">
                                                <div class="input-group">
                                                    <?php echo form_input('attributesInput', '', 'class="form-control select-tags" id="attributesInput" placeholder="' . $this->lang->line('enter_attributes') . '"'); ?>
                                                    <div class="input-group-addon" style="padding: 2px 5px;">
                                                        <a href="#" id="addAttributes">
                                                            <i class="fa fa-2x fa-plus-circle" id="addIcon"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div style="clear:both;"></div>
                                            </div>
                                            <div class="table-responsive">
                                                <table id="attrTable" class="table table-bordered table-condensed table-striped" style="<?= $this->input->post('attributes') || $product_options ? '' : 'display:none;'; ?>margin-bottom: 0; margin-top: 10px;">
                                                    <thead>
                                                        <tr class="active">
                                                            <th><?= lang('name') ?></th>
                                                            <th><?= lang('warehouse') ?></th>
                                                            <!-- <th><?= lang('quantity') ?></th> -->
                                                            <th><?= lang('price_addition') ?></th>
                                                            <th><i class="fa fa-times attr-remove-all"></i></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody><?php
                                                            if ($this->input->post('attributes')) {
                                                                $a = sizeof($_POST['attr_name']);
                                                                for ($r = 0; $r <= $a; $r++) {
                                                                    if (isset($_POST['attr_name'][$r]) && (isset($_POST['attr_warehouse'][$r]) || isset($_POST['attr_quantity'][$r]))) {
                                                                        echo '<tr class="attr"><td><input type="hidden" name="attr_name[]" value="' . $_POST['attr_name'][$r] . '"><span>' . $_POST['attr_name'][$r] . '</span></td><td class="code text-center"><input type="hidden" name="attr_warehouse[]" value="' . $_POST['attr_warehouse'][$r] . '"><input type="hidden" name="attr_wh_name[]" value="' . $_POST['attr_wh_name'][$r] . '"><span>' . $_POST['attr_wh_name'][$r] . '</span></td><td class="price text-right"><input type="hidden" name="attr_price[]" value="' . $_POST['attr_price'][$r] . '"><span>' . $_POST['attr_price'][$r] . '</span></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>';
                                                                        // echo '<tr class="attr"><td><input type="hidden" name="attr_name[]" value="' . $_POST['attr_name'][$r] . '"><span>' . $_POST['attr_name'][$r] . '</span></td><td class="code text-center"><input type="hidden" name="attr_warehouse[]" value="' . $_POST['attr_warehouse'][$r] . '"><input type="hidden" name="attr_wh_name[]" value="' . $_POST['attr_wh_name'][$r] . '"><span>' . $_POST['attr_wh_name'][$r] . '</span></td><td class="quantity text-center"><input type="hidden" name="attr_quantity[]" value="' . $this->bpas->formatQuantityDecimal($_POST['attr_quantity'][$r]) . '"><span>' . $this->bpas->formatQuantity($_POST['attr_quantity'][$r]) . '</span></td><td class="price text-right"><input type="hidden" name="attr_price[]" value="' . $_POST['attr_price'][$r] . '"><span>' . $_POST['attr_price'][$r] . '</span></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>';
                                                                    }
                                                                }
                                                            } elseif ($product_options) {
                                                                foreach ($product_options as $option) {
                                                                    echo '<tr class="attr"><td><input type="hidden" name="attr_name[]" value="' . $option->name . '"><span>' . $option->name . '</span></td><td class="code text-center"><input type="hidden" name="attr_warehouse[]" value="' . $option->warehouse_id . '"><input type="hidden" name="attr_wh_name[]" value="' . $option->wh_name . '"><span>' . $option->wh_name . '</span></td><td class="quantity text-center"><input type="hidden" name="attr_quantity[]" value="' . $this->bpas->formatQuantityDecimal($option->wh_qty) . '"><span>' . $this->bpas->formatQuantity($option->wh_qty) . '</span></td><td class="price text-right"><input type="hidden" name="attr_price[]" value="' . $this->bpas->formatMoney($option->price) . '"><span>' . $this->bpas->formatMoney($option->price) . '</span></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>';
                                                                }
                                                            }
                                                            ?></tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                        <div id="warehouse1" class="tab-pane fade">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="<?= $product ? 'text-warning' : '' ?>">
                                    <strong><?= lang('warehouse_quantity') ?></strong><br>
                                    <?php
                                    if (($Admin || $Owner) || !$this->session->userdata('warehouse_id')) {
                                        if (!empty($warehouses)) {
                                            if ($product) {
                                                echo '<div class="row"><div class="col-md-12"><div class="well"><div id="show_wh_edit">';
                                                if (!empty($warehouses_products)) {
                                                    echo '<div style="display: none;">';
                                                    foreach ($warehouses_products as $wh_pr) {
                                                        echo '<span class="bold text-info">' . $wh_pr->name . ': <span class="padding05" id="rwh_qty_' . $wh_pr->id . '">' . $this->bpas->formatQuantity($wh_pr->quantity) . '</span>' . ($wh_pr->rack ? ' (<span class="padding05" id="rrack_' . $wh_pr->id . '">' . $wh_pr->rack . '</span>)' : '') . '</span><br>';
                                                    }
                                                    echo '</div>';
                                                }
                                                foreach ($warehouses as $warehouse) {
                                                    //$whs[$warehouse->id] = $warehouse->name;
                                                    echo '<div class="col-md-6 col-sm-6 col-xs-6" style="padding-bottom:15px;">' . $warehouse->name . '<br><div class="form-group">' . form_hidden('wh_' . $warehouse->id, $warehouse->id) . form_input('wh_qty_' . $warehouse->id, (isset($_POST['wh_qty_' . $warehouse->id]) ? $_POST['wh_qty_' . $warehouse->id] : (isset($warehouse->quantity) ? $warehouse->quantity : '')), 'class="form-control wh" id="wh_qty_' . $warehouse->id . '" placeholder="' . lang('quantity') . '"') . '</div>';
                                                    if ($Settings->racks) {
                                                        echo '<div class="form-group">' . form_input('rack_' . $warehouse->id, (isset($_POST['rack_' . $warehouse->id]) ? $_POST['rack_' . $warehouse->id] : (isset($warehouse->rack) ? $warehouse->rack : '')), 'class="form-control wh" id="rack_' . $warehouse->id . '" placeholder="' . lang('rack') . '"') . '</div>';
                                                    }
                                                    echo '</div>';
                                                }
                                                echo '</div><div class="clearfix"></div></div></div></div>';
                                            } else {
                                                echo '<div class="row"><div class="col-md-12"><div class="well">';
                                                foreach ($warehouses as $warehouse) {
                                                    //$whs[$warehouse->id] = $warehouse->name;
                                                    echo '<div class="col-md-6 col-sm-6 col-xs-6" style="padding-bottom:15px;">' . $warehouse->name . '<br>
                                                                <div class="form-group">' . 
                                                                    form_hidden('wh_' . $warehouse->id, $warehouse->id) . 
                                                                    form_input('wh_qty_' . $warehouse->id, (isset($_POST['wh_qty_' . $warehouse->id]) ? $_POST['wh_qty_' . $warehouse->id] : ''), 'class="form-control" id="wh_qty_' . $warehouse->id . '" placeholder="' . lang('quantity') . '"') . 
                                                                '</div>';
                                                    if ($Settings->racks) {
                                                        echo '<div class="form-group">' . 
                                                                form_input('rack_' . $warehouse->id, (isset($_POST['rack_' . $warehouse->id]) ? $_POST['rack_' . $warehouse->id] : ''), 'class="form-control" id="rack_' . $warehouse->id . '" placeholder="' . lang('rack') . '"') . 
                                                            '</div>';
                                                    }
                                                    echo '</div>';
                                                }
                                                echo '<div class="clearfix"></div></div></div></div>';
                                            }
                                        }
                                    } else {
                                        $user_warehouses = explode(',', $this->session->userdata('warehouse_id'));
                                        if (!empty($warehouses)) {
                                            if ($product) {
                                                echo '<div class="row"><div class="col-md-12"><div class="well"><div id="show_wh_edit">';
                                                if (!empty($warehouses_products)) {
                                                    echo '<div style="display: none;">';
                                                    foreach ($warehouses_products as $wh_pr) {
                                                        foreach ($user_warehouses as $value) {
                                                            if ($wh_pr->id == $value) {
                                                                echo '<span class="bold text-info">' . $wh_pr->name . ': <span class="padding05" id="rwh_qty_' . $wh_pr->id . '">' . $this->bpas->formatQuantity($wh_pr->quantity) . '</span>' . ($wh_pr->rack ? ' (<span class="padding05" id="rrack_' . $wh_pr->id . '">' . $wh_pr->rack . '</span>)' : '') . '</span><br>';
                                                            }
                                                        }
                                                    }
                                                    echo '</div>';
                                                }
                                                foreach ($warehouses as $warehouse) {
                                                    foreach ($user_warehouses as $value) {
                                                        if ($warehouse->id == $value) {
                                                            echo '<div class="col-md-6 col-sm-6 col-xs-6" style="padding-bottom:15px;">' . $warehouse->name . '<br><div class="form-group">' . form_hidden('wh_' . $warehouse->id, $warehouse->id) . form_input('wh_qty_' . $warehouse->id, (isset($_POST['wh_qty_' . $warehouse->id]) ? $_POST['wh_qty_' . $warehouse->id] : (isset($warehouse->quantity) ? $warehouse->quantity : '')), 'class="form-control wh" id="wh_qty_' . $warehouse->id . '" placeholder="' . lang('quantity') . '"') . '</div>';
                                                            if ($Settings->racks) {
                                                                echo '<div class="form-group">' . form_input('rack_' . $warehouse->id, (isset($_POST['rack_' . $warehouse->id]) ? $_POST['rack_' . $warehouse->id] : (isset($warehouse->rack) ? $warehouse->rack : '')), 'class="form-control wh" id="rack_' . $warehouse->id . '" placeholder="' . lang('rack') . '"') . '</div>';
                                                            }
                                                            echo '</div>';
                                                        }
                                                    }
                                                }
                                                echo '</div><div class="clearfix"></div></div></div></div>';
                                            } else {
                                                echo '<div class="row"><div class="col-md-12"><div class="well">';
                                                foreach ($warehouses as $warehouse) {
                                                    foreach ($user_warehouses as $value) {
                                                        if ($warehouse->id == $value) {
                                                            echo '<div class="col-md-6 col-sm-6 col-xs-6" style="padding-bottom:15px;">' . $warehouse->name . '<br>
                                                                        <div class="form-group">' . 
                                                                            form_hidden('wh_' . $warehouse->id, $warehouse->id) . 
                                                                            form_input('wh_qty_' . $warehouse->id, (isset($_POST['wh_qty_' . $warehouse->id]) ? $_POST['wh_qty_' . $warehouse->id] : ''), 'class="form-control" id="wh_qty_' . $warehouse->id . '" placeholder="' . lang('quantity') . '"') . 
                                                                        '</div>';
                                                            if ($Settings->racks) {
                                                                echo '<div class="form-group">' . 
                                                                        form_input('rack_' . $warehouse->id, (isset($_POST['rack_' . $warehouse->id]) ? $_POST['rack_' . $warehouse->id] : ''), 'class="form-control" id="rack_' . $warehouse->id . '" placeholder="' . lang('rack') . '"') . 
                                                                    '</div>';
                                                            }
                                                            echo '</div>';
                                                        }
                                                    }
                                                }
                                                echo '<div class="clearfix"></div></div></div></div>';
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>
                        <div id="tab_supplier" class="tab-pane fade">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group standard">
                                        <div class="form-group">
                                            <?= lang('supplier', 'supplier') ?>
                                            <button type="button" class="btn btn-primary btn-xs" id="addSupplier"><i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                        <div class="row" id="supplier-con">
                                            <div class="col-xs-12">
                                                <div class="form-group">
                                                    <?php
                                                    echo form_input('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : ''), 'class="form-control ' . ($product ? '' : 'suppliers') . '" id="' . ($product && !empty($product->supplier1) ? 'supplier1' : 'supplier') . '" placeholder="' . lang('select') . ' ' . lang('supplier') . '" style="width:100%;"');
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="col-xs-6">
                                                <div class="form-group">
                                                    <?= form_input('supplier_part_no', (isset($_POST['supplier_part_no']) ? $_POST['supplier_part_no'] : ''), 'class="form-control tip" id="supplier_part_no" placeholder="' . lang('supplier_part_no') . '"'); ?>
                                                </div>
                                            </div>
                                            <div class="col-xs-6">
                                                <div class="form-group">
                                                    <?= form_input('supplier_price', (isset($_POST['supplier_price']) ? $_POST['supplier_price'] : ''), 'class="form-control tip" id="supplier_price" placeholder="' . lang('supplier_price') . '"'); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="ex-suppliers"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="tab_shop" class="tab-pane fade">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <input name="featured" type="checkbox" class="checkbox" id="featured" value="1" <?= isset($_POST['featured']) ? 'checked="checked"' : '' ?> />
                                        <label for="featured" class="padding05"><?= lang('featured') ?></label>
                                    </div>
                                    <div class="form-group">
                                        <input name="hide_pos" type="checkbox" class="checkbox" id="hide_pos" value="1" <?= isset($_POST['hide_pos']) ? 'checked="checked"' : '' ?> />
                                        <label for="hide_pos" class="padding05"><?= lang('hide_in_pos') ?></label>
                                    </div>
                                    <div class="form-group">
                                        <input name="hide" type="checkbox" class="checkbox" id="hide" value="1" <?= isset($_POST['hide']) ? 'checked="checked"' : '' ?> />
                                        <label for="hide" class="padding05"><?= lang('hide_in_shop') ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="tab_add_on" class="tab-pane fade">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <?= lang('add_product', 'add_item') . ' (' . lang('not_with_addon') . ')'; ?>
                                        <?php echo form_input('add_on_item', '', 'class="form-control ttip" id="add_on_item" data-placement="top" data-trigger="focus" data-bv-notEmpty-message="' . lang('please_add_items_below') . '" placeholder="' . $this->lang->line('add_item') . '"'); ?>
                                    </div>
                                    <div class="control-group table-group">
                                        <label class="table-label"><?= lang('addon_products'); ?></label>
                                        <div class="controls table-controls">
                                            <table id="prTable_addon" class="table items table-striped table-bordered table-condensed table-hover">
                                                <thead>
                                                    <tr>
                                                        <th class="col-md-5 col-sm-5 col-xs-5"><?= lang('product') . ' (' . lang('code') . ' - ' . lang('name') . ')'; ?></th>
                                                        <th class="col-md-3 col-sm-3 col-xs-3"><?= lang('price'); ?></th>
                                                        <th class="col-md-3 col-sm-3 col-xs-3"><?= lang('description'); ?></th>
                                                        <th class="col-md-1 col-sm-1 col-xs-1 text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="tab_racks" class="tab-pane fade">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="control-group table-group">
                                        <label class="table-label"><?= lang('warehouse') . ' ' . lang('racks'); ?></label>
                                        <div class="controls table-controls">
                                            <table id="prTable_addon" class="table items table-striped table-bordered table-condensed table-hover">
                                                <thead>
                                                    <tr>
                                                        <th class="col-md-5 col-sm-5 col-xs-5"><?= lang('warehouse') . ' ' . lang('name') . ' (' . lang('code') . ')'; ?></th>
                                                        <th class="col-md-5 col-sm-5 col-xs-5"><?= lang('rack'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($warehouses)) { ?>
                                                        <?php foreach ($warehouses as $warehouse) { ?>
                                                        <tr>
                                                            <td class="col-md-5 col-sm-5 col-xs-5">
                                                                <input type="hidden" name="wh_rack_<?= $warehouse->id; ?>" value="<?= $warehouse->id; ?>">
                                                                <?= $warehouse->name . ' (' . $warehouse->code . ')' ?>
                                                            </td>
                                                            <td class="col-md-5 col-sm-5 col-xs-5">
                                                                <?php
                                                                $p_racks[''] = lang('select');
                                                                if (!empty($product_racks)) {
                                                                    foreach ($product_racks as $product_rack) {
                                                                        $p_racks[$product_rack->id] = $product_rack->name;
                                                                    }
                                                                }
                                                                echo form_dropdown('wh_product_rack_id_' . $warehouse->id, $p_racks, '', 'id="wh_product_rack_id_' .  $warehouse->id . '" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('rack') . '" style="width: 100%; !important;" '); ?>
                                                            </td>
                                                        </tr>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="tab_add_on" class="tab-pane fade">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <?= lang('add_product', 'add_item') . ' (' . lang('not_with_addon') . ')'; ?>
                                        <?php echo form_input('add_on_item', '', 'class="form-control ttip" id="add_on_item" data-placement="top" data-trigger="focus" data-bv-notEmpty-message="' . lang('please_add_items_below') . '" placeholder="' . $this->lang->line('add_item') . '"'); ?>
                                    </div>
                                    <div class="control-group table-group">
                                        <label class="table-label"><?= lang('addon_products'); ?></label>
                                        <div class="controls table-controls">
                                            <table id="prTable_addon" class="table items table-striped table-bordered table-condensed table-hover">
                                                <thead>
                                                    <tr>
                                                        <th class="col-md-5 col-sm-5 col-xs-5"><?= lang('product') . ' (' . lang('code') . ' - ' . lang('name') . ')'; ?></th>
                                                        <th class="col-md-3 col-sm-3 col-xs-3"><?= lang('price'); ?></th>
                                                        <th class="col-md-3 col-sm-3 col-xs-3"><?= lang('description'); ?></th>
                                                        <th class="col-md-1 col-sm-1 col-xs-1 text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="accounting" class="tab-pane fade">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group standard">
                                        <div class="form-group">
                                            <?= lang("revenue_account", "revenue_account") ?>
                                            <?php 
                                            $acc_section = array(""=>lang('select_sale_account'));
                                                foreach($chart_accounts as $section){
                                                    $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                                }
                                                echo form_dropdown('revenue_account', $acc_section,(isset($_POST['revenue_account']) ? $_POST['revenue_account'] : ''),'id="revenue_account" class="form-control" style="width:100%;" ');
                                            ?>
                                        </div>
                                        <div class="form-group">
                                            <?= lang("stock_account", "stock_account") ?>
                                            <?php
                                                $acc_section = array( ""=> lang('select_stock_account') );
                                                foreach($chart_accounts as $section){
                                                    $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                                }
                                                echo form_dropdown('stock_account', $acc_section,(isset($_POST['stock_account']) ? $_POST['stock_account'] : ''),'id="stock_account" class="form-control"  style="width:100%;" ');
                                            ?>
                                        </div>
                                        <div class="form-group standard">
                                            <?= lang("cost_of_sale_account", "cost_of_sale_account") ?>
                                            <?php
                                            $acc_section = array(""=>lang('cost_of_sale_account'));
                                       
                                            foreach($chart_accounts as $section){
                                                $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                            }
                                            echo form_dropdown('pro_cost_account', $acc_section,(isset($_POST['pro_cost_account']) ? $_POST['pro_cost_account'] : ''),'id="pro_cost_account" class="form-control" style="width:100%;" ');
                                        ?>
                                        </div>
                                        <div class="form-group">
                                            <?= lang("adjustment_account", "adjustment_account") ?>
                                            <?php
                                                $acc_section = array(""=>lang('select_adjustment_account'));
                                                foreach($chart_accounts as $section){
                                                    $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                                }
                                                echo form_dropdown('adjustment_account', $acc_section,(isset($_POST['adjustment_account']) ? $_POST['adjustment_account'] : ''),'id="adjustment_account" class="form-control"  style="width:100%;" ');
                                            ?>
                                        </div>
                                        <div class="form-group">
                                            <?= lang("stock_using_account", "stock_using_account") ?>
                                            <?php
                                                $acc_section = array(""=>lang('stock_using_account'));
                                                foreach($chart_accounts as $section) {
                                                    $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                                }
                                            echo form_dropdown('stock_using_account', $acc_section,(isset($_POST['stock_using_account']) ? $_POST['stock_using_account'] : ''),'id="stock_using" class="form-control" tyle="width:100%;" ');
                                            ?>
                                        </div>
                                        <?php if($Settings->module_manufacturing){?>
                                        <div class="form-group">
                                            <?= lang("convert_account", "convert_account") ?>
                                            <?php
                                                $acc_section = array(""=>lang('select_convert_account'));
                                                foreach($chart_accounts as $section) {
                                                    $acc_section[$section->accountcode] = $section->accountcode.' | '.$section->accountname;
                                                }
                                            echo form_dropdown('convert_account', $acc_section,(isset($_POST['convert_account']) ? $_POST['convert_account'] : ''),'id="convert_account" class="form-control" tyle="width:100%;" ');
                                            ?>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div><br>
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php echo form_submit('add_product', $this->lang->line('add_product'), 'class="btn btn-primary"'); ?>
                        </div>
                    </div>
                    <?= form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</body>
<script type="text/javascript">
    $(document).ready(function() {
        $('#currency').change(function() {
            $("#cost").val(0);
            $("#other_cost").val(0);
        });
        $('#cost').change(function() {
            var cost = $("#cost").val();
            var currency_code = $('#currency').val();
            $.ajax({
                type: "get",
                url: "<?= admin_url('products/getcost') ?>/" + currency_code,
                data: {
                    cost : cost,
                    currency_code: currency_code
                },
                success: function(data) {
                    $("#other_cost").val(data);
                }
            });
            return false;
        });
        $('#price').change(function() {
            var price = $("#price").val();
            var currency_code = $('#currency').val();
            $.ajax({
                type: "get",
                url: "<?= admin_url('products/getprice') ?>/" + currency_code,
                data: {
                    price : price,
                    currency_code: currency_code
                },
                success: function(data) {
                    $("#other_price").val(data);
                }
            });
            return false;
        });
        $('#other_cost').change(function() {
            var cost = $("#other_cost").val();
            var currency_code = $('#currency').val();
            $.ajax({
                type: "get",
                url: "<?= admin_url('products/getother_cost') ?>/" + currency_code,
                data: {
                    cost : cost,
                    currency_code: currency_code
                },
                success: function(data) {
                    $("#cost").val(data);
                }
            });
            return false;
        });
        $('#other_price').change(function() {
            var price = $("#other_price").val();
            var currency_code = $('#currency').val();
            $.ajax({
                type: "get",
                url: "<?= admin_url('products/getother_price') ?>/" + currency_code,
                data: {
                    price : price,
                    currency_code: currency_code
                },
                success: function(data) {
                    $("#price").val(data);
                }
            });
            return false;
        });

        $('form[data-toggle="validator"]').bootstrapValidator({
            excluded: [':disabled']
        });
        var audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3');
        var audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
        var items = {};
        var addOn_items = {};
        <?php
        if ($combo_items) {
            foreach ($combo_items as $item) {
                //echo 'ietms['.$item->id.'] = '.$item.';';
                if ($item->code) {
                    echo 'add_product_item(' . json_encode($item) . ');';
                }
            }
        }
        if ($addon_items) {
            foreach ($addon_items as $item) {
                if ($item->code) {
                    echo 'add_product_addon_item(' . json_encode($item) . ');';
                }
            }
        }
        ?>
        <?= isset($_POST['cf']) ? '$("#extras").iCheck("check");' : '' ?>
        $('#extras').on('ifChecked', function() {
            $('#extras-con').slideDown();
        });
        $('#extras').on('ifUnchecked', function() {
            $('#extras-con').slideUp();
        });

        <?= isset($_POST['promotion']) ? '$("#promotion").iCheck("check");' : '' ?>
        $('#promotion').on('ifChecked', function(e) {
            $('#promo').slideDown();
        });
        $('#promotion').on('ifUnchecked', function(e) {
            $('#promo').slideUp();
        });

        $('.attributes').on('ifChecked', function(event) {
            $('#options_' + $(this).attr('id')).slideDown();
        });
        $('.attributes').on('ifUnchecked', function(event) {
            $('#options_' + $(this).attr('id')).slideUp();
        });
        //$('#cost').removeAttr('required');
        $('#digital_file').change(function() {
            if ($(this).val()) {
                $('#file_link').removeAttr('required');
                $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'file_link');
            } else {
                $('#file_link').attr('required', 'required');
                $('form[data-toggle="validator"]').bootstrapValidator('addField', 'file_link');
            }
        });
        $('#type').change(function() {
            var t = $(this).val();
            if (t !== 'standard') {
                $('.standard').slideUp();
              //  $('#unit').attr('disabled', true);
               // $('#cost').attr('disabled', true);
                $('#track_quantity').iCheck('uncheck');
            } else {
                $('.standard').slideDown();
                $('#track_quantity').iCheck('check');
              //  $('#unit').attr('disabled', false);
              //  $('#cost').attr('disabled', false);
            }
            if (t !== 'digital') {
                $('.digital').slideUp();
                $('#file_link').removeAttr('required');
                $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'file_link');
            } else {
                $('.digital').slideDown();
                $('#file_link').attr('required', 'required');
                $('form[data-toggle="validator"]').bootstrapValidator('addField', 'file_link');
            }
            if (t !== 'combo') {
                $('.combo').slideUp();
            } else {
                $('.combo').slideDown();
            }
            if (t == 'standard' || t == 'combo') {
                $('.standard_combo').slideDown();
            } else {
                $('.standard_combo').slideUp();
            }
            if (t == 'bom') {
                $('.bom').show();
            }else{
                $('.bom').hide();
            }
        });

        var t = $('#type').val();
        if (t !== 'standard') {
            $('.standard').slideUp();
            $('#unit').attr('disabled', true);
            $('#cost').attr('disabled', true);
            $('#track_quantity').iCheck('uncheck');
        } else {
            $('.standard').slideDown();
            $('#track_quantity').iCheck('check');
            $('#unit').attr('disabled', false);
            $('#cost').attr('disabled', false);
        }
        if (t !== 'digital') {
            $('.digital').slideUp();
            $('#file_link').removeAttr('required');
            $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'file_link');
        } else {
            $('.digital').slideDown();
            $('#file_link').attr('required', 'required');
            $('form[data-toggle="validator"]').bootstrapValidator('addField', 'file_link');
        }
        if (t !== 'combo') {
            $('.combo').slideUp();
        } else {
            $('.combo').slideDown();
        }
        if (t == 'standard' || t == 'combo') {
            $('.standard_combo').slideDown();
        } else {
            $('.standard_combo').slideUp();
        }

        $("#add_item_bom").autocomplete({
            source: '<?= admin_url('products/get_raw_suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');

                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_bom_product_item(ui.item);
                    if (row) {
                        $(this).val('');
                    }
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>');
                }
            }
        });
        function add_bom_product_item(item) {
            if (item == null) {
                return false;
            }
            item_id = item.row_id;
            if (items[item_id]) {
                items[item_id].qty = (parseFloat(items[item_id].qty) + 1).toFixed(2);
            } else {
                items[item_id] = item;
            }
            $("#bomTable tbody").empty();           
            $.each(items, function () {
                var bom_unit = this.bom_unit;
                var opt = '<p>n/a</p>';
                if(this.options !== false) {
                    opt = "<select name=\"bom_unit_id[]\" class=\"form-control bom_unit select\">";
                    $.each(this.options, function () {
                        opt += "<option "+(this.id == bom_unit ? 'selected' : '')+" value="+this.id+">"+this.name+"</option>";
                    });
                    opt += "</select>";
                }
                var bom_biller = this.bom_biller;
                var sel_biller = "";
                <?php if ($this->Settings->module_concrete) { ?>
                    var billers = <?= $billers ?>;  
                    sel_biller += "<select name=\"bom_item_biller[]\" class=\"form-control bom_biller select\">";
                    sel_biller += "<option value=''><?= lang('select').' '.lang('biller') ?></option>";
                    $.each(billers, function () {
                        sel_biller += "<option "+(this.id == bom_biller ? 'selected' : '')+" value="+this.id+">"+this.company+"</option>";
                    });
                    sel_biller += "</select>";
                <?php } ?>
                var row_no = this.row_id;
                var newTr = $('<tr id="row_' + row_no + '" class="item_' + this.id + '" data-item-id="' + row_no + '"></tr>');
                tr_html = '<td><input name="bom_item_id[]" type="hidden" value="' + this.id + '"><input name="bom_item_name[]" type="hidden" value="' + this.name + '"><input name="bom_item_code[]" type="hidden" value="' + this.code + '"><span id="name_' + row_no + '">' + this.code + ' - ' + this.name + '</span></td>';
                tr_html += '<td class="text-center" style="display: none !important;"><input class="form-control text-center bom_type" type="text" value="' + this.bom_type + '" name="bom_type[]"/></td>';
                <?php if ($this->Settings->module_concrete) { ?>
                    tr_html += '<td class="text-center" style="display: none !important;">' + sel_biller + '</td>'; 
                <?php } ?>
                tr_html += '<td><input class="form-control text-center bom_item_quantity" name="bom_item_quantity[]" type="text" value="' + formatDecimal(this.qty) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
                tr_html += '<td class="text-center">' + opt + '</td>'; 
                tr_html += '<td class="text-center"><i class="fa fa-times tip del" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
                newTr.html(tr_html);
                newTr.prependTo("#bomTable");               
            });
            $('.item_' + item_id).addClass('warning');
            return true;
        }
        $("#add_item").autocomplete({
            source: '<?= admin_url('products/suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function(event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_product_found') ?>', function() {
                        $('#add_item').focus();
                    });
                    $(this).val('');
                } else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                } else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_product_found') ?>', function() {
                        $('#add_item').focus();
                    });
                    $(this).val('');
                }
            },
            select: function(event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_product_item(ui.item);
                    if (row) {
                        $(this).val('');
                    }
                } else {
                    bootbox.alert('<?= lang('no_product_found') ?>');
                }
            }
        });
        $("#add_on_item").autocomplete({
            source: '<?= admin_url('products/suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function(event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_product_found') ?>', function() {
                        $('#add_on_item').focus();
                    });
                    $(this).val('');
                } else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                } else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_product_found') ?>', function() {
                        $('#add_on_item').focus();
                    });
                    $(this).val('');
                }
            },
            select: function(event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_product_addon_item(ui.item);
                    if (row) {
                        $(this).val('');
                    }
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>');
                }
            }
        });
        <?php if ($this->input->post('type') == 'combo') {
            $c = isset($_POST['combo_item_code']) ? sizeof($_POST['combo_item_code']) : 0;
            for ($r = 0; $r <= $c; $r++) {
                if (isset($_POST['combo_item_code'][$r]) && isset($_POST['combo_item_quantity'][$r]) && isset($_POST['combo_item_price'][$r])) {
                    $items[] = ['id' => $_POST['combo_item_id'][$r], 'name' => $_POST['combo_item_name'][$r], 'code' => $_POST['combo_item_code'][$r], 'qty' => $_POST['combo_item_quantity'][$r], 'price' => $_POST['combo_item_price'][$r]];
                }
            }
            echo '
                var ci = ' . (isset($items) ? json_encode($items) : "''") . ';
                $.each(ci, function() { add_product_item(this); });';
        } ?>

        function add_product_item(item) {
            if (item == null) {
                return false;
            }
            item_id = item.id;
            if (items[item_id]) {
                items[item_id].qty = (parseFloat(items[item_id].qty) + 1).toFixed(2);
            } else {
                items[item_id] = item;
            }
            var pp = 0;
            $("#prTable tbody").empty();
            $.each(items, function() {
                var row_no = this.id;
                var newTr = $('<tr id="row_' + row_no + '" class="item_' + this.id + '" data-item-id="' + row_no + '"></tr>');
                tr_html = '<td><input name="combo_item_id[]" type="hidden" value="' + this.id + '"><input name="combo_item_name[]" type="hidden" value="' + this.name + '"><input name="combo_item_code[]" type="hidden" value="' + this.code + '"><span id="name_' + row_no + '">' + this.code + ' - ' + this.name + '</span></td>';
                tr_html += '<td><input class="form-control text-center rquantity" name="combo_item_quantity[]" type="text" value="' + formatDecimal(this.qty) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
                tr_html += '<td><input class="form-control text-center rprice" name="combo_item_price[]" type="text" value="' + formatDecimal(this.price) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="combo_item_price_' + row_no + '" onClick="this.select();"></td>';
                tr_html += '<td class="text-center"><i class="fa fa-times tip del" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
                newTr.html(tr_html);
                newTr.prependTo("#prTable");
                pp += formatDecimal(parseFloat(this.price) * parseFloat(this.qty));
            });
            $('.item_' + item_id).addClass('warning');
            $('#price').val(pp);
            return true;
        }
         $("#add_on_item").autocomplete({
            source: '<?= admin_url('products/suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function(event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>', function() {
                        $('#add_on_item').focus();
                    });
                    $(this).val('');
                } else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                } else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>', function() {
                        $('#add_on_item').focus();
                    });
                    $(this).val('');
                }
            },
            select: function(event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_product_addon_item(ui.item);
                    if (row) {
                        $(this).val('');
                    }
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>');
                }
            }
        });
        <?php
        $c = isset($_POST['addOn_item_code']) ? sizeof($_POST['addOn_item_code']) : 0;
        for ($r = 0; $r <= $c; $r++) {
            if (isset($_POST['addOn_item_code'][$r])) {
                $rows[] = ['id' => $_POST['addOn_item_id'][$r], 'name' => $_POST['addOn_item_name'][$r], 'code' => $_POST['addOn_item_code'][$r], 'price' => $_POST['addOn_item_price'][$r],'description' => $_POST['addOn_item_description'][$r]];
            }
        }
        echo 'var addOn_item_ = ' . (isset($rows) ? json_encode($rows) : "''") . '; $.each(addOn_item_, function() { add_product_addon_item(this); });'; ?>
        function add_product_addon_item(item) {
            if (item == null) {
                return false;
            }
            item_id = item.id;
            addOn_items[item_id] = item;
            $("#prTable_addon tbody").empty();
            $.each(addOn_items, function() {
                var row_no = this.id;
                var des = "";
                if(typeof(this.description) != "undefined"){
                    des = this.description;
                }
                var newTr = $('<tr id="row_' + row_no + '" class="item_' + this.id + '" data-item-id="' + row_no + '"></tr>');
                tr_html = '<td><input name="addOn_item_id[]" type="hidden" value="' + this.id + '"><input name="addOn_item_name[]" type="hidden" value="' + this.name + '"><input name="addOn_item_code[]" type="hidden" value="' + this.code + '"><span id="name_' + row_no + '">' + this.code + ' - ' + this.name + '</span></td>';

                 tr_html += '<td><input class="form-control text-center" name="addOn_item_price[]" type="text" value="' + this.price + '" data-id="' + row_no + '" data-item="' + this.id + '" id="addOn_item_price' + row_no + '" onClick="this.select();"></td>';

                tr_html += '<td><input class="form-control text-center" name="addOn_item_description[]" type="text" value="'+ des +'" data-id="' + row_no + '" data-item="' + this.id + '" id="addOn_item_description' + row_no + '" onClick="this.select();"></td>';

                tr_html += '<td class="text-center"><i class="fa fa-times tip addOn_del" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
                newTr.html(tr_html);
                newTr.prependTo("#prTable_addon");
            });
            $('.item_' + item_id).addClass('warning');
            return true;
        }

        function calculate_price() {
            var rows = $('#prTable').children('tbody').children('tr');
            var pp = 0;
            $.each(rows, function() {
                pp += formatDecimal(parseFloat($(this).find('.rprice').val()) * parseFloat($(this).find('.rquantity').val()));
            });
            $('.price').val(pp);
            return true;
        }
        $(document).on('change', '.rquantity, .rprice', function() {
            calculate_price();
        });
        $(document).on('click', '.del', function() {
            var id = $(this).attr('id');
            delete items[id];
            $(this).closest('#row_' + id).remove();
            calculate_price();
        });
        $(document).on('click', '.addOn_del', function() {
            var id = $(this).attr('id');
            delete addOn_items[id];
            $(this).closest('#row_' + id).remove();
        });
        var su = 2;
        $('#addSupplier').click(function() {
            if (su <= 5) {
                $('#supplier_1').select2('destroy');
                var html = '<div style="clear:both;height:5px;"></div><div class="row"><div class="col-xs-12"><div class="form-group"><input type="hidden" name="supplier_' + su + '", class="form-control" id="supplier_' + su + '" placeholder="<?= lang('select') . ' ' . lang('supplier') ?>" style="width:100%;display: block !important;" /></div></div><div class="col-xs-6"><div class="form-group"><input type="text" name="supplier_' + su + '_part_no" class="form-control tip" id="supplier_' + su + '_part_no" placeholder="<?= lang('supplier_part_no') ?>" /></div></div><div class="col-xs-6"><div class="form-group"><input type="text" name="supplier_' + su + '_price" class="form-control tip" id="supplier_' + su + '_price" placeholder="<?= lang('supplier_price') ?>" /></div></div></div>';
                $('#ex-suppliers').append(html);
                var sup = $('#supplier_' + su);
                suppliers(sup);
                su++;
            } else {
                bootbox.alert('<?= lang('max_reached') ?>');
                return false;
            }
        });

         // The option of input only number
        $(document).on('keypress','.cost',function(event) {
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
        });
        $(document).on('keypress','.price',function(event) {
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
        });
        $('#cost').keypress(function(event) {
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
        });
        $('#price').keypress(function(event) {
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
        });

        // $(document).on('change', '.cost', function(){
        //     var cost = $(this).attr('data');
        //     var slsh = $(this).val() ? $(this).val() : 0;
        //     if (!is_numeric(slsh)){
        //         $(this).val(cost);
        //         bootbox.alert(lang.unexpected_value);
        //         return;
        //     }
        //     $(this).attr('data', $(this).val());
        //     return false;
        // });

        // $(document).on('change', '.price', function(){
        //     var price = $(this).attr('data');
        //     var slsh = $(this).val() ? $(this).val() : 0;
        //     if (!is_numeric(slsh)) {
        //         $(this).val(price);
        //         bootbox.alert(lang.unexpected_value);
        //         return;
        //     }
        //     $(this).attr('data', $(this).val());
        //     return false;
        // });

        // End the option of input only number
        var _URL = window.URL || window.webkitURL;
        $("input#images").on('change.bs.fileinput', function() {
            var ele = document.getElementById($(this).attr('id'));
            var result = ele.files;
            $('#img-details').empty();
            for (var x = 0; x < result.length; x++) {
                var fle = result[x];
                for (var i = 0; i <= result.length; i++) {
                    var img = new Image();
                    img.onload = (function(value) {
                        return function() {
                            ctx[value].drawImage(result[value], 0, 0);
                        }
                    })(i);
                    img.src = 'images/' + result[i];
                }
            }
        });
        
        var variants = <?= json_encode($vars); ?>;
        $(".select-tags").select2({
            tags: variants,
            tokenSeparators: [","],
            multiple: true
        });
        $(document).on('ifChecked', '#attributes', function(e) {
            $('#attr-con').slideDown();
        });
        $(document).on('ifUnchecked', '#attributes', function(e) {
            $(".select-tags").select2("val", "");
            $('.attr-remove-all').trigger('click');
            $('#attr-con').slideUp();
        });
        $('#addAttributes').click(function(e) {
            e.preventDefault();
            var attrs_val = $('#attributesInput').val(),
                attrs;
            attrs = attrs_val.split(',');
            for (var i in attrs) {
                if (attrs[i] !== '') {
                    <?php if (!empty($warehouses)) {
                        foreach ($warehouses as $warehouse) {
                            echo '$(\'#attrTable\').show().append(\'<tr class="attr"><td><input type="hidden" name="attr_name[]" value="\' + attrs[i] + \'"><span>\' + attrs[i] + \'</span></td><td class="code text-center"><input type="hidden" name="attr_warehouse[]" value="' . $warehouse->id . '"><span>' . $warehouse->name . '</span></td><td class="price text-right"><input type="hidden" name="attr_price[]" value="0"><span>0</span></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>\');';
                            // echo '$(\'#attrTable\').show().append(\'<tr class="attr"><td><input type="hidden" name="attr_name[]" value="\' + attrs[i] + \'"><span>\' + attrs[i] + \'</span></td><td class="code text-center"><input type="hidden" name="attr_warehouse[]" value="' . $warehouse->id . '"><span>' . $warehouse->name . '</span></td><td class="quantity text-center"><input type="hidden" name="attr_quantity[]" value="0"><span>0</span></td><td class="price text-right"><input type="hidden" name="attr_price[]" value="0"><span>0</span></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>\');';
                        }
                    } else {
                    ?>
                        $('#attrTable').show().append('<tr class="attr"><td><input type="hidden" name="attr_name[]" value="' + attrs[i] + '"><span>' + attrs[i] + '</span></td><td class="code text-center"><input type="hidden" name="attr_warehouse[]" value=""><span></span></td><td class="price text-right"><input type="hidden" name="attr_price[]" value="0"><span>0</span></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>');
                        // $('#attrTable').show().append('<tr class="attr"><td><input type="hidden" name="attr_name[]" value="' + attrs[i] + '"><span>' + attrs[i] + '</span></td><td class="code text-center"><input type="hidden" name="attr_warehouse[]" value=""><span></span></td><td class="quantity text-center"><input type="hidden" name="attr_quantity[]" value="0"><span></span></td><td class="price text-right"><input type="hidden" name="attr_price[]" value="0"><span>0</span></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>');
                    <?php
                    } ?>
                }
            }
        });
        //$('#attributesInput').on('select2-blur', function(){
        //    $('#addAttributes').click();
        //});
        $(document).on('click', '.delAttr', function() {
            $(this).closest("tr").remove();
        });
        $(document).on('click', '.attr-remove-all', function() {
            $('#attrTable tbody').empty();
            $('#attrTable').hide();
        });
        var row, warehouses = <?= json_encode($warehouses); ?>;
        $(document).on('click', '.attr td:not(:last-child)', function() {
            row = $(this).closest("tr");
            $('#aModalLabel').text(row.children().eq(0).find('span').text());
            $('#awarehouse').select2("val", (row.children().eq(1).find('input').val()));
            // $('#aquantity').val(row.children().eq(2).find('input').val());
            $('#aprice').val(row.children().eq(3).find('span').text());
            $('#aModal').appendTo('body').modal('show');
        });

        // $('#aModal').on('shown.bs.modal', function () {
        //     $('#aquantity').focus();
        //     $(this).keypress(function( e ) {
        //         if ( e.which == 13 ) {
        //             $('#updateAttr').click();
        //         }
        //     });
        // });
        $(document).on('click', '#updateAttr', function() {
            var wh = $('#awarehouse').val(),
                wh_name;
            $.each(warehouses, function() {
                if (this.id == wh) {
                    wh_name = this.name;
                }
            });
            row.children().eq(1).html('<input type="hidden" name="attr_warehouse[]" value="' + wh + '"><input type="hidden" name="attr_wh_name[]" value="' + wh_name + '"><span>' + wh_name + '</span>');
            // row.children().eq(2).html('<input type="hidden" name="attr_quantity[]" value="' + ($('#aquantity').val() ? $('#aquantity').val() : 0) + '"><span>' + decimalFormat($('#aquantity').val()) + '</span>');
            // row.children().eq(3).html('<input type="hidden" name="attr_price[]" value="' + $('#aprice').val() + '"><span>' + currencyFormat($('#aprice').val()) + '</span>');
            row.children().eq(2).html('<input type="hidden" name="attr_price[]" value="' + $('#aprice').val() + '"><span>' + currencyFormat($('#aprice').val()) + '</span>');
            $('#aModal').modal('hide');
        });
    });

    <?php if ($product) {
    ?>
        $(document).ready(function() {
            var t = "<?= $product->type ?>";
            if (t !== 'standard') {
                $('.standard').slideUp();
                $('#cost').attr('required', 'required');
                $('#track_quantity').iCheck('uncheck');
                $('form[data-toggle="validator"]').bootstrapValidator('addField', 'cost');
            } else {
                $('.standard').slideDown();
                $('#track_quantity').iCheck('check');
                $('#cost').removeAttr('required');
                $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'cost');
            }
            if (t !== 'digital') {
                $('.digital').slideUp();
                $('#file_link').removeAttr('required');
                $('form[data-toggle="validator"]').bootstrapValidator('removeField', 'file_link');
            } else {
                $('.digital').slideDown();
                $('#file_link').attr('required', 'required');
                $('form[data-toggle="validator"]').bootstrapValidator('addField', 'file_link');
            }
            if (t !== 'combo') {
                $('.combo').slideUp();
                //$('#add_item').removeAttr('required');
                //$('form[data-toggle="validator"]').bootstrapValidator('removeField', 'add_item');
            } else {
                $('.combo').slideDown();
                //$('#add_item').attr('required', 'required');
                //$('form[data-toggle="validator"]').bootstrapValidator('addField', 'add_item');
            }
            $("#code").parent('.form-group').addClass("has-error");
            $("#code").focus();
            $("#product_image").parent('.form-group').addClass("text-warning");
            $("#images").parent('.form-group').addClass("text-warning");
            $.ajax({
                type: "get",
                async: false,
                url: "<?= admin_url('products/getSubCategories') ?>/" + <?= $product->category_id ?>,
                dataType: "json",
                success: function(scdata) {
                    if (scdata != null) {
                        $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                            placeholder: "<?= lang('select_category_to_load') ?>",
                            data: scdata
                        });
                    } else {
                        $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('no_subcategory') ?>").select2({
                            placeholder: "<?= lang('no_subcategory') ?>",
                            data: [{
                                id: '',
                                text: '<?= lang('no_subcategory') ?>'
                            }]
                        });
                    }
                }
            });
            <?php if ($product->supplier1) { ?>
                select_supplier('supplier1', "<?= $product->supplier1; ?>");
                $('#supplier_price').val("<?= $product->supplier1price == 0 ? '' : $this->bpas->formatDecimal($product->supplier1price); ?>");
            <?php } ?>
            <?php if ($product->supplier2) { ?>
                $('#addSupplier').click();
                select_supplier('supplier_2', "<?= $product->supplier2; ?>");
                $('#supplier_2_price').val("<?= $product->supplier2price == 0 ? '' : $this->bpas->formatDecimal($product->supplier2price); ?>");
            <?php } ?>
            <?php if ($product->supplier3) { ?>
                $('#addSupplier').click();
                select_supplier('supplier_3', "<?= $product->supplier3; ?>");
                $('#supplier_3_price').val("<?= $product->supplier3price == 0 ? '' : $this->bpas->formatDecimal($product->supplier3price); ?>");
            <?php } ?>
            <?php if ($product->supplier4) { ?>
                $('#addSupplier').click();
                select_supplier('supplier_4', "<?= $product->supplier4; ?>");
                $('#supplier_4_price').val("<?= $product->supplier4price == 0 ? '' : $this->bpas->formatDecimal($product->supplier4price); ?>");
            <?php } ?>
            <?php if ($product->supplier5) { ?>
                $('#addSupplier').click();
                select_supplier('supplier_5', "<?= $product->supplier5; ?>");
                $('#supplier_5_price').val("<?= $product->supplier5price == 0 ? '' : $this->bpas->formatDecimal($product->supplier5price); ?>");
            <?php } ?>
            function select_supplier(id, v) {
                $('#' + id).val(v).select2({
                    minimumInputLength: 1,
                    data: [],
                    initSelection: function(element, callback) {
                        $.ajax({
                            type: "get",
                            async: false,
                            url: "<?= admin_url('suppliers/getSupplier') ?>/" + $(element).val(),
                            dataType: "json",
                            success: function(data) {
                                callback(data[0]);
                            }
                        });
                    },
                    ajax: {
                        url: site.base_url + "suppliers/suggestions",
                        dataType: 'json',
                        quietMillis: 15,
                        data: function(term, page) {
                            return {
                                term: term,
                                limit: 10
                            };
                        },
                        results: function(data, page) {
                            if (data.results != null) {
                                return {
                                    results: data.results
                                };
                            } else {
                                return {
                                    results: [{
                                        id: '',
                                        text: 'No Match Found'
                                    }]
                                };
                            }
                        }
                    }
                }); //.select2("val", "<?= $product->supplier1; ?>");
            }
            var whs = $('.wh');
            $.each(whs, function() {
                $(this).val($('#r' + $(this).attr('id')).text());
            });
        });
    <?php } ?>
$(document).ready(function() {   
    $('#unit').change(function(e) {
        var code        = $("#code").val(); 
        var v = $(this).val();
                if(v){
                    $.ajax({
                        type: "get",
                        async: false,
                        url: "<?= admin_url('products/getSubUnits') ?>/" + v,
                        dataType: "json",
                        success: function(data) {
                    
                            $('#input').empty();
                            $('#units_div').empty();
                            $('#input_p').empty();
                            $('#pr_code').empty();
                            $('#default_sale_unit').select2("destroy").empty().select2({
                                minimumResultsForSearch: 7
                            });
                            $('#default_purchase_unit').select2("destroy").empty().select2({
                                minimumResultsForSearch: 7
                            });
                            $.each(data, function() {
                                var code_     = (this.id == v ? code : '');
                                var attr      = (this.id == v ? 'readonly' : '');
                                var style     = (this.id == v ? 'width:100%;float:left;' : 'width:80%;float:left;');
                                var button    = (this.id != v ? "<span class='input-group-addon pointer' id='random_num' style='width:20%;float:left;padding:9px 0'><i class='fa fa-random'></i></span>" : "");
                                product_input = $(" <div class='input-group'>"
                                                    +"<label style='float:left;'> Product Code ("+this.name+") </label>"
                                                    +"<input style='"+ style +"' type='text' id='unit_id_"+this.id+"' data='0' name='" + this.code + "_code' value='" + code_ + "' class='form-control code_by_unit' " + attr + ">"
                                                    + button+ "</div>");
                                units_div     = $("<input type='hidden' name='units_div[]' value='"+this.code+"' class='form-control'>");
                                input         = $(" <label>Product cost ("+this.code+") </label> <input type='text' id='value_c' data='0' name='"+this.code+"_cost' value='0' class='form-control cost' re>");
                                input_p       = $("<label>Product Price ("+this.code+")</label><input type='text' id='value_p' data='0' name='"+this.code+"_price' value='0' class='form-control price' class='form-control price'>");
                            
                                $("<option />", {
                                    value: this.id,
                                    text: this.name + ' (' + this.code + ')'
                                }).appendTo($('#default_sale_unit'));

                                $("<option />", {
                                    value: this.id,
                                    text: this.name + ' (' + this.code + ')'
                                }).appendTo($('#default_purchase_unit'));

                                product_input.appendTo("#pr_code");
                                units_div.appendTo("#units_div");
                                input.appendTo("#input");
                                input_p.appendTo("#input_p");
                                $('.cost1').hide(); 
                                $('#input').show();
                            });
                            $('#default_sale_unit').select2('val', v);
                            $('#default_purchase_unit').select2('val', v);
                        },
                        error: function() {
                            bootbox.alert('<?= lang('ajax_error') ?>');
                        }
                    });

                } else if (v != 8) {
                    $.ajax({
                        type: "get",
                        async: false,
                        url: "<?= admin_url('products/getSubUnits') ?>/" + v,
                        dataType: "json",
                        success: function(data) {
                            $('#default_sale_unit').select2("destroy").empty().select2({
                                minimumResultsForSearch: 7
                            });
                            $('#default_purchase_unit').select2("destroy").empty().select2({
                                minimumResultsForSearch: 7
                            });
                            $.each(data, function() {
                                $("<option />", {
                                    value: this.id,
                                    text: this.name + ' (' + this.code + ')'
                                }).appendTo($('#default_sale_unit'));
                                $("<option />", {
                                    value: this.id,
                                    text: this.name + ' (' + this.code + ')'
                                }).appendTo($('#default_purchase_unit'));
                                $("#input").empty();
                                $("#input_p").empty();
                                $('.cost1').show();
                            });
                            $('#default_sale_unit').select2('val', v);
                            $('#default_purchase_unit').select2('val', v);
                        },
                        error: function() {
                            bootbox.alert('<?= lang('ajax_error') ?>');
                        }
                    });
                } else {
                    $('#default_sale_unit').select2("destroy").empty();
                    $('#default_purchase_unit').select2("destroy").empty();
                    $("<option />", {
                        value: '',
                        text: '<?= lang('select_unit_first') ?>'
                    }).appendTo($('#default_sale_unit'));
                    $("<option />", {
                        value: '',
                        text: '<?= lang('select_unit_first') ?>'
                    }).appendTo($('#default_purchase_unit'));
                    $('#default_sale_unit').select2({
                        minimumResultsForSearch: 7
                    }).select2('val', '');
                    $('#default_purchase_unit').select2({
                        minimumResultsForSearch: 7
                    }).select2('val', '');
                }
    });
});
</script>  
<div class="modal" id="aModal" tabindex="-1" role="dialog" aria-labelledby="aModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                        <i class="fa fa-2x">&times;</i>
                    </span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="aModalLabel"><?= lang('add_product_manually') ?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="awarehouse" class="col-sm-4 control-label"><?= lang('warehouse') ?></label>
                        <div class="col-sm-8">
                            <?php
                            $wh[''] = '';
                            foreach ($warehouses as $warehouse) {
                                $wh[$warehouse->id] = $warehouse->name;
                            }
                            echo form_dropdown('warehouse', $wh, '', 'id="awarehouse" class="form-control"');
                            ?>
                        </div>
                    </div>
                    <!-- <div class="form-group">
                    <label for="aquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="aquantity">
                    </div>
                </div> -->
                    <input type="hidden" id="aquantity" value="0">
                    <div class="form-group">
                        <label for="aprice" class="col-sm-4 control-label"><?= lang('price_addition') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="aprice">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="updateAttr"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var arr_code = [];
        $(document).on('focusin', '.code_by_unit', function () {
            arr_code = [];
            $('.code_by_unit').each(function(index, element) {
                arr_code.push($(element).val());
            });
        }).on('change', '.code_by_unit', function(){
            if (arr_code.includes($(this).val()) == true) {
                alert('Your product code is a  Duplicate');
                $(this).val('');
                $(this).focus();
            }
        }); 
    });
</script>