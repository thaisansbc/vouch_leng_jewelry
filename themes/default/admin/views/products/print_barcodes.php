<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (isset($style) && ($style == 84 || $style == 'shelf' || $style == '1' || $style == '4025')) { ?>
    <style type="text/css">
        @media print {
            @page {
                <?php if (isset($style) && $style == 'shelf') { ?>
                    size: A4 portrait;
                <?php } ?>
                margin: 0;
                padding: 0;
            }
        }
    </style>
<?php } ?>
<style type="text/css">
    .barcodejewellery{
        width:90mm;
        margin: 0 auto;
    }
    .stylejewellery{
        /* border: 1px solid #ccc; */
        page-break-after :auto;
    }
</style>
<div class="breadcrumb-header no-print">
    <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('print_barcode_label'); ?></h2>
    <div class="box-icon">
        <ul class="btn-tasks">
            <li class="dropdown">
                <a href="#" onclick="window.print();return false;" id="print-icon" class="tip" title="<?= lang('print') ?>">
                    <i class="icon fa fa-print"></i>
                </a>
            </li>
        </ul>
    </div>
</div>
<div class="box">
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext no-print"><?php echo sprintf(
                lang('print_barcode_heading'),
                anchor('admin/system_settings/categories', lang('categories')),
                anchor('admin/system_settings/subcategories', lang('subcategories')),
                anchor('admin/purchases', lang('purchases')),
                anchor('admin/transfers', lang('transfers'))
                ); ?></p>
                <div class="well well-sm no-print">
                    <div class="form-group">
                        <?= lang('add_product', 'add_item'); ?>
                        <?php echo form_input('add_item', '', 'class="form-control" id="add_item" placeholder="' . $this->lang->line('add_item') . '"'); ?>
                    </div>
                    <?= admin_form_open('products/print_barcodes', 'id="barcode-print-form" data-toggle="validator"'); ?>
                    <div class="controls table-controls">
                        <table id="bcTable" class="table items table-striped table-bordered table-condensed table-hover">
                            <thead>
                            <tr>
                                <th class="col-xs-4"><?= lang('product_name') . ' (' . $this->lang->line('product_code') . ')'; ?></th>
                                <th class="col-xs-1"><?= lang('quantity'); ?></th>
                                <th class="col-xs-7"><?= lang('variants'); ?></th>
                                <th class="text-center" style="width:30px;">
                                    <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                </th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                        <div class="form-group">
                            <?= lang('style', 'style'); ?>
                            <?php 
                                $opts = [
                                    '' => lang('select') . ' ' . lang('style'), 
                                    1 => lang('1_per_sheet') . ' ' . '(50 x 30mm)', 
                                    4025 => lang('1_per_sheet') . ' ' . '(40 x 25mm)', 
                                    84 => lang('84_per_sheet') . ' ' . '(C7 x R12) (16 x 21mm 1260pcs) Label Size A4', 
                                    40 => lang('40_per_sheet'), 
                                    30 => lang('30_per_sheet'), 
                                    24 => lang('24_per_sheet'), 
                                    20 => lang('20_per_sheet'), 
                                    18 => lang('18_per_sheet'), 
                                    14 => lang('14_per_sheet'), 
                                    12 => lang('12_per_sheet'), 
                                    10 => lang('10_per_sheet'), 
                                    2  => lang('2_per_sheet'),
                                    'jewellery' => lang('jewellery_label'),
                                    'shelf' => lang('Shelf_Label'),
                                    50 => lang('continuous_feed')
                                ]; ?>
                            <?= form_dropdown('style', $opts, set_value('style', 24), 'class="form-control tip" id="style" required="required"'); ?>
                            <div class="row cf-con" style="margin-top: 10px; display: none;">
                                <div class="col-xs-4">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <?= form_input('cf_width', '', 'class="form-control" id="cf_width" placeholder="' . lang('width') . '"'); ?>
                                            <span class="input-group-addon" style="padding-left:10px;padding-right:10px;"><?= lang('inches'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-4">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <?= form_input('cf_height', '', 'class="form-control" id="cf_height" placeholder="' . lang('height') . '"'); ?>
                                            <span class="input-group-addon" style="padding-left:10px;padding-right:10px;"><?= lang('inches'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-4">
                                    <div class="form-group">
                                    <?php $oopts = [0 => lang('portrait'), 1 => lang('landscape')]; ?>
                                        <?= form_dropdown('cf_orientation', $oopts, '', 'class="form-control" id="cf_orientation" placeholder="' . lang('orientation') . '"'); ?>
                                    </div>
                                </div>
                            </div>
                            <span class="help-block"><?= lang('barcode_tip'); ?></span>
                            <div class="clearfix"></div>
                        </div>
                        <div class="form-group">
                            <span style="font-weight: bold; margin-right: 15px;"><?= lang('print'); ?>:</span>
                            <input name="site_name" type="checkbox" id="site_name" value="1" checked="checked" style="display:inline-block;" />
                            <label for="site_name" class="padding05"><?= lang('site_name'); ?></label>
                            <input name="product_name" type="checkbox" id="product_name" value="1" checked="checked" style="display:inline-block;" />
                            <label for="product_name" class="padding05"><?= lang('product_name'); ?></label>
                            <input name="price" type="checkbox" id="price" value="1" checked="checked" style="display:inline-block;" />
                            <label for="price" class="padding05"><?= lang('price'); ?></label>
                            <input name="currencies" type="checkbox" id="currencies" value="1" style="display:inline-block;" />
                            <label for="currencies" class="padding05"><?= lang('currencies'); ?></label>
                            <input name="unit" type="checkbox" id="unit" value="1" style="display:inline-block;" />
                            <label for="unit" class="padding05"><?= lang('unit'); ?></label>
                            <input name="category" type="checkbox" id="category" value="1" style="display:inline-block;" />
                            <label for="category" class="padding05"><?= lang('category'); ?></label>
                            <input name="variants" type="checkbox" id="variants" value="1" style="display:inline-block;" />
                            <label for="variants" class="padding05"><?= lang('variants'); ?></label>
                            <br>
                            <input name="product_image" type="checkbox" id="product_image" value="1" style="display:inline-block;" />
                            <label for="product_image" class="padding05"><?= lang('product_image'); ?></label>

                            <input name="product_weight" type="checkbox" id="product_weight" value="1" style="display:inline-block;" />
                            <label for="product_weight" class="padding05"><?= lang('product_weight'); ?></label>

                            <input name="check_promo" type="checkbox" id="check_promo" value="1" checked="checked" style="display:inline-block;" />
                            <label for="check_promo" class="padding05"><?= lang('check_promo'); ?></label>
                        </div>
                        <div class="form-group">
                            <?php echo form_submit('print', lang('update'), 'class="btn btn-primary"'); ?>
                            <button type="button" id="reset" class="btn btn-danger"><?= lang('reset'); ?></button>
                        </div>
                    <?= form_close(); ?>
                    <div class="clearfix"></div>
                </div>
                <div id="barcode-con">
                    <?php
                        if ($this->input->post('print')) {
                            if (!empty($barcodes)) {
                                echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" title="' . lang('print') . '"><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                                $c = 1;
                                if ($style == 12 || $style == 18 || $style == 24 || $style == 40) {
                                    echo '<div class="barcodea4">';
                                } elseif ($style == 84) {
                                    echo '<div class="barcode84">';
                                } elseif($style == 1){
                                    echo '<div class="barcode1_50x30">';
                                }elseif($style == 4025){
                                    echo '<div class="barcode1_40x25">';
                                }elseif ($style == 'shelf') { 
                                    echo '<div class="barcodeshelf">';
                                }elseif ($style == 'jewellery') { 
                                    echo '<div class="barcodejewellery">';
                                } elseif ($style != 50) {
                                    echo '<div class="barcode">';
                                }
                                foreach ($barcodes as $item) {
                                    
                                    if($style == 4025){
                                        echo '<div class="barcode1_40x25">';
                                    }elseif($style == 1){
                                        echo '<div class="barcode1_50x30">';
                                    }elseif ($style == 'shelftop') { 
                                        echo '<div class="barcodeshelftop">';
                                    }elseif ($style == 'shelf') { 
                                        echo '<div class="barcodeshelf">';
                                    }

                                    for ($r = 1; $r <= $item['quantity']; $r++) {
                                        echo '<div class="item style' . $style . '" ' .
                                        ($style == 50 && $this->input->post('cf_width') && $this->input->post('cf_height') ? 'style="width:' . $this->input->post('cf_width') . 'in; height:' . $this->input->post('cf_height') . 'in;border:0;"' : '') . '>';
                                        if ($style == 50) {
                                            if ($this->input->post('cf_orientation')) {
                                                $ty        = (($this->input->post('cf_height') / $this->input->post('cf_width')) * 100) . '%';
                                                $landscape = '
                                                -webkit-transform-origin: 0 0;
                                                -moz-transform-origin:    0 0;
                                                -ms-transform-origin:     0 0;
                                                transform-origin:         0 0;
                                                -webkit-transform: translateY(' . $ty . ') rotate(-90deg);
                                                -moz-transform:    translateY(' . $ty . ') rotate(-90deg);
                                                -ms-transform:     translateY(' . $ty . ') rotate(-90deg);
                                                transform:         translateY(' . $ty . ') rotate(-90deg);
                                                ';
                                                echo '<div class="div50" style="width:' . $this->input->post('cf_height') . 'in;height:' . $this->input->post('cf_width') . 'in;border: 1px dotted #CCC;' . $landscape . '">';
                                            } else {
                                                echo '<div class="div50" style="width:' . $this->input->post('cf_width') . 'in;height:' . $this->input->post('cf_height') . 'in;border: 1px dotted #CCC;padding-top:0.025in;">';
                                            }
                                        }
                                        if ($style == 'shelf') {
                                            if ($item['image']) {
                                                echo '<span class="product_image"><img src="' . base_url('assets/uploads/thumbs/' . $item['image']) . '" alt="" /></span>';
                                            }
                                            if ($item['site']) {
                                                echo '<span class="barcode_site">' . $item['site'] . '</span>';
                                            }
                                            if ($item['name']) {
                                                echo '<span style="font-size: 15px; margin-left: 15px;" class="barcode_name">' . character_limiter($item['name'], 25) . '</span>';
                                            }
                                            if ($item['price']) {
                                                echo '<span class="barcode_price" style="display: block; font-size: 22px; font-weight: bold; text-align: right; margin-top: 7%; margin-bottom: 5%; margin-right: 20px;">';
                                                if ($item['currencies']) {
                                                    foreach ($currencies as $currency) {
                                                        echo  $currency->code . ': ' . $this->bpas->formatMoney($item['rprice'] * $currency->rate, 'none') . ', ';
                                                    }
                                                } else {
                                                    echo "$".$item['price'];
                                                }
                                                echo '</span> ';
                                            }
                                            if ($item['unit']) {
                                                echo '<span class="barcode_unit">' . lang('unit') . ': ' . $item['unit'] . '</span>, ';
                                            }
                                            if ($item['category']) {
                                                echo '<span class="barcode_category">' . lang('category') . ': ' . $item['category'] . '</span> ';
                                            }
                                            if ($item['variants']) {
                                                echo '<span class="variants">' . lang('variants') . ': ';
                                                foreach ($item['variants'] as $variant) {
                                                    echo $variant->name . ', ';
                                                }
                                                echo '</span> ';
                                            }
                                            echo '<span class="barcode_image"><img src="' . admin_url('products/barcode/' . $item['barcode'] . '/' . $item['bcs'] . '/' . $item['bcis']) . '" alt="' . $item['barcode'] . '" class="bcimg" style="width: auto !important; height: 50px !important;"/></span>';
                                        }else if ($style == 'jewellery') {
                                            echo '
                                            <table width="100%">
                                                <tr>
                                                    <td width="33%" style="padding-top: 5px;font-weight: bold;padding-left:15px;">';
                                                        if ($item['name']) {
                                                            echo '<span style="font-size: 9px;" class="barcode_name">' . character_limiter($item['name'], 25) . '</span>';
                                                        }
                                                        // if ($item['weight']) {
                                                        //     echo '<span style="font-size: 9px;" class="barcode_name">' . $this->bpas->formatDecimal($item['weight']) . '</span>';
                                                        // }
                                                        if ($item['price']) {
                                                            echo '<span class="barcode_price" style="display: block; font-size: 9px; font-weight: bold; text-align: left; margin-bottom: 5%; margin-right: 20px;">';
                                                            if ($item['currencies']) {
                                                                foreach ($currencies as $currency) {
                                                                    echo  $currency->code . ': ' . $this->bpas->formatMoney($item['rprice'] * $currency->rate, 'none') . ', ';
                                                                }
                                                            } else {
                                                                echo "$".$item['price'];
                                                            }
                                                            echo '</span> ';
                                                        }
                                            echo    '</td>';
                                            echo    '<td width="33%">';
                                                        echo '<span style="font-size: 9px; margin-top: 5px;font-weight: bold;" class="barcode_name">' . 'Vouch Leng Jewellery'. '</span>';
                                                        echo '<span class="barcode_image"><img src="' . admin_url('products/barcode/' . $item['barcode'] . '/' . $item['bcs'] . '/' . $item['bcis']) . '" alt="' . $item['barcode'] . '" class="bcimg" style="width: auto !important; height: 25px !important;"/></span>';
                                            echo   '</td>';
                                            echo ' <td width="35%"></td>';

                                        echo    '</tr>';
                                        echo'</table>';
                                      
                                            
                                            if ($item['unit']) {
                                                echo '<span class="barcode_unit">' . lang('unit') . ': ' . $item['unit'] . '</span>, ';
                                            }
                                            if ($item['category']) {
                                                echo '<span class="barcode_category">' . lang('category') . ': ' . $item['category'] . '</span> ';
                                            }
                                            if ($item['variants']) {
                                                echo '<span class="variants">' . lang('variants') . ': ';
                                                foreach ($item['variants'] as $variant) {
                                                    echo $variant->name . ', ';
                                                }
                                                echo '</span> ';
                                            }
                                            
                                        } else {
                                            if ($item['image']) {
                                                echo '<span class="product_image"><img src="' . base_url('assets/uploads/thumbs/' . $item['image']) . '" alt="" /></span>';
                                            }
                                            if ($item['site']) {
                                                echo '<span class="barcode_site">' . $item['site'] . '</span>';
                                            }
                                            if ($item['price']) {
                                                echo '<span class="barcode_price">';
                                                if ($item['currencies']) {
                                                    foreach ($currencies as $currency) {
                                                        echo  $currency->code . ': ' . $this->bpas->formatMoney($item['rprice'] * $currency->rate, 'none') . ', ';
                                                    }
                                                } else {
                                                    echo "$".$item['price'];
                                                }
                                                echo '</span> ';
                                            }
                                            if ($item['name']) {
                                                echo '<span style="font-size:9.3px;" class="barcode_name">' . character_limiter($item['name'], 25) . '</span>';
                                            }
                                            if ($item['unit']) {
                                                echo '<span class="barcode_unit">' . lang('unit') . ': ' . $item['unit'] . '</span>, ';
                                            }
                                            if ($item['category']) {
                                                echo '<span class="barcode_category">' . lang('category') . ': ' . $item['category'] . '</span> ';
                                            }
                                            if ($item['variants']) {
                                                echo '<span class="variants">' . lang('variants') . ': ';
                                                foreach ($item['variants'] as $variant) {
                                                    echo $variant->name . ', ';
                                                }
                                                echo '</span> ';
                                            }
                                            echo '<span class="barcode_image"><img src="' . admin_url('products/barcode/' . $item['barcode'] . '/' . $item['bcs'] . '/' . $item['bcis']) . '" alt="' . $item['barcode'] . '" class="bcimg" /></span>';
                                        }
                                        if ($style == 50) {
                                            echo '</div>';
                                        }

                                        echo '</div>';
                                        
                                        if ($style == 40) {
                                            if ($c % 40 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            }
                                        } elseif ($style == 30) {
                                            if ($c % 30 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcode">';
                                            }
                                        } elseif ($style == 24) {
                                            if ($c % 24 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            }
                                        } elseif ($style == 20) {
                                            if ($c % 20 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcode">';
                                            }
                                        } elseif ($style == 18) {
                                            if ($c % 18 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            }
                                        } elseif ($style == 14) {
                                            if ($c % 14 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcode">';
                                            }
                                        } elseif ($style == 12) {
                                            if ($c % 12 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            }
                                        } elseif ($style == 10) {
                                            if ($c % 10 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcode">';
                                            }
                                        } elseif ($style == 2) {
                                            if ($c % 2 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcode">';
                                            }
                                        } elseif ($style == 84) {
                                            if ($r == $item['quantity']) {
                                                if ($c % 84 == 0) {
                                                    echo '</div><div class="clearfix"></div>';
                                                }    
                                            } elseif ($r != $item['quantity'] && $c % 84 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcode84">';
                                            }
                                        } elseif ($style == 'shelf') {
                                            if ($r == $item['quantity']) {
                                                if ($c % 14 == 0) {
                                                    echo '</div><div class="clearfix"></div>';
                                                }    
                                            } elseif ($r != $item['quantity'] && $c % 14 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodeshelf">';
                                            }
                                        } elseif($style == 1) {
                                            if ($r == $item['quantity']) {
                                                if ($c % 1 == 0) {
                                                    echo '</div><div class="clearfix"></div>';
                                                }    
                                            } elseif ($r != $item['quantity'] && $c % 1 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcode1_50x30">';
                                            }
                                        }elseif($style == 4025){
                                            if ($r == $item['quantity']) {
                                                if ($c % 1 == 0) {
                                                    echo '</div><div class="clearfix"></div>';
                                                }    
                                            } elseif ($r != $item['quantity'] && $c % 1 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcode1_40x25">';
                                            }
                                        }
                                        $c++;
                                    }
                                }
                                
                                if ($style != 50) {
                                    echo '</div>';
                                }
                            
                                echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" title="' . lang('print') . '"><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                            } else {
                                echo '<h3>' . lang('no_product_selected') . '</h3>';
                            }
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var ac = false; bcitems = {};
    if (localStorage.getItem('bcitems')) {
        bcitems = JSON.parse(localStorage.getItem('bcitems'));
    }
    <?php if ($items) { ?>
        localStorage.setItem('bcitems', JSON.stringify(<?= $items; ?>));
    <?php } ?>
    $(document).ready(function() {
        <?php if ($this->input->post('print')) { ?>
            $( window ).load(function() {
                $('html, body').animate({
                    scrollTop: ($("#barcode-con").offset().top)-15
                }, 1000);
            });
        <?php } ?>
        if (localStorage.getItem('bcitems')) {
            loadItems();
        }
        $("#add_item").autocomplete({
            source: '<?= admin_url('products/get_suggestions'); ?>',
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
                    var row = add_product_item(ui.item);
                    if (row) {
                        $(this).val('');
                    }
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>');
                }
            }
        });
        check_add_item_val();
        $('#style').change(function (e) {
            localStorage.setItem('bcstyle', $(this).val());
            if ($(this).val() == 50) {
                $('.cf-con').slideDown();
            } else {
                $('.cf-con').slideUp();
            }
        });
        if (style = localStorage.getItem('bcstyle')) {
            $('#style').val(style);
            $('#style').select2("val", style);
            if (style == 50) {
                $('.cf-con').slideDown();
            } else {
                $('.cf-con').slideUp();
            }
        }
        $('#cf_width').change(function (e) {
            localStorage.setItem('cf_width', $(this).val());
        });
        if (cf_width = localStorage.getItem('cf_width')) {
            $('#cf_width').val(cf_width);
        }
        $('#cf_height').change(function (e) {
            localStorage.setItem('cf_height', $(this).val());
        });
        if (cf_height = localStorage.getItem('cf_height')) {
            $('#cf_height').val(cf_height);
        }
        $('#cf_orientation').change(function (e) {
            localStorage.setItem('cf_orientation', $(this).val());
        });
        if (cf_orientation = localStorage.getItem('cf_orientation')) {
            $('#cf_orientation').val(cf_orientation);
        }
        $(document).on('ifChecked', '#site_name', function(event) {
            localStorage.setItem('bcsite_name', 1);
        });
        $(document).on('ifUnchecked', '#site_name', function(event) {
            localStorage.setItem('bcsite_name', 0);
        });
        if (site_name = localStorage.getItem('bcsite_name')) {
            if (site_name == 1)
                $('#site_name').iCheck('check');
            else
                $('#site_name').iCheck('uncheck');
        }
        $(document).on('ifChecked', '#product_name', function(event) {
            localStorage.setItem('bcproduct_name', 1);
        });
        $(document).on('ifUnchecked', '#product_name', function(event) {
            localStorage.setItem('bcproduct_name', 0);
        });
        if (product_name = localStorage.getItem('bcproduct_name')) {
            if (product_name == 1)
                $('#product_name').iCheck('check');
            else
                $('#product_name').iCheck('uncheck');
        }
        $(document).on('ifChecked', '#price', function(event) {
            localStorage.setItem('bcprice', 1);
        });
        $(document).on('ifUnchecked', '#price', function(event) {
            localStorage.setItem('bcprice', 0);
            $('#currencies').iCheck('uncheck');
        });
        if (price = localStorage.getItem('bcprice')) {
            if (price == 1)
                $('#price').iCheck('check');
            else
                $('#price').iCheck('uncheck');
        }
        $(document).on('ifChecked', '#currencies', function(event) {
            localStorage.setItem('bccurrencies', 1);
        });
        $(document).on('ifUnchecked', '#currencies', function(event) {
            localStorage.setItem('bccurrencies', 0);
        });
        if (currencies = localStorage.getItem('bccurrencies')) {
            if (currencies == 1)
                $('#currencies').iCheck('check');
            else
                $('#currencies').iCheck('uncheck');
        }
        $(document).on('ifChecked', '#unit', function(event) {
            localStorage.setItem('bcunit', 1);
        });
        $(document).on('ifUnchecked', '#unit', function(event) {
            localStorage.setItem('bcunit', 0);
        });
        if (unit = localStorage.getItem('bcunit')) {
            if (unit == 1)
                $('#unit').iCheck('check');
            else
                $('#unit').iCheck('uncheck');
        }
        $(document).on('ifChecked', '#category', function(event) {
            localStorage.setItem('bccategory', 1);
        });
        $(document).on('ifUnchecked', '#category', function(event) {
            localStorage.setItem('bccategory', 0);
        });
        if (category = localStorage.getItem('bccategory')) {
            if (category == 1)
                $('#category').iCheck('check');
            else
                $('#category').iCheck('uncheck');
        }
        $(document).on('ifChecked', '#check_promo', function(event) {
            localStorage.setItem('bccheck_promo', 1);
        });
        $(document).on('ifUnchecked', '#check_promo', function(event) {
            localStorage.setItem('bccheck_promo', 0);
        });
        if (check_promo = localStorage.getItem('bccheck_promo')) {
            if (check_promo == 1)
                $('#check_promo').iCheck('check');
            else
                $('#check_promo').iCheck('uncheck');
        }


        $(document).on('ifChecked', '#product_image', function(event) {
            localStorage.setItem('bcproduct_image', 1);
        });
        $(document).on('ifUnchecked', '#product_image', function(event) {
            localStorage.setItem('bcproduct_image', 0);
        });
        if (product_image = localStorage.getItem('bcproduct_image')) {
            if (product_image == 1)
                $('#product_image').iCheck('check');
            else
                $('#product_image').iCheck('uncheck');
        }


        $(document).on('ifChecked', '#variants', function(event) {
            localStorage.setItem('bcvariants', 1);
        });
        $(document).on('ifUnchecked', '#variants', function(event) {
            localStorage.setItem('bcvariants', 0);
        });
        if (variants = localStorage.getItem('bcvariants')) {
            if (variants == 1)
                $('#variants').iCheck('check');
            else
                $('#variants').iCheck('uncheck');
        }
        $(document).on('ifChecked', '.checkbox', function(event) {
            var item_id = $(this).attr('data-item-id');
            var vt_id = $(this).attr('id');
            bcitems[item_id]['selected_variants'][vt_id] = 1;
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
        });
        $(document).on('ifUnchecked', '.checkbox', function(event) {
            var item_id = $(this).attr('data-item-id');
            var vt_id = $(this).attr('id');
            bcitems[item_id]['selected_variants'][vt_id] = 0;
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
        });
        $(document).on('click', '.del', function () {
            var id = $(this).attr('id');
            delete bcitems[id];
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
            $(this).closest('#row_' + id).remove();
        });
        $('#reset').click(function (e) {
            bootbox.confirm(lang.r_u_sure, function (result) {
                if (result) {
                    if (localStorage.getItem('bcitems')) {
                        localStorage.removeItem('bcitems');
                    }
                    if (localStorage.getItem('bcstyle')) {
                        localStorage.removeItem('bcstyle');
                    }
                    if (localStorage.getItem('bcsite_name')) {
                        localStorage.removeItem('bcsite_name');
                    }
                    if (localStorage.getItem('bcproduct_name')) {
                        localStorage.removeItem('bcproduct_name');
                    }
                    if (localStorage.getItem('bcprice')) {
                        localStorage.removeItem('bcprice');
                    }
                    if (localStorage.getItem('bccurrencies')) {
                        localStorage.removeItem('bccurrencies');
                    }
                    if (localStorage.getItem('bcunit')) {
                        localStorage.removeItem('bcunit');
                    }
                    if (localStorage.getItem('bccategory')) {
                        localStorage.removeItem('bccategory');
                    }
                    // if (localStorage.getItem('cf_width')) {
                    //     localStorage.removeItem('cf_width');
                    // }
                    // if (localStorage.getItem('cf_height')) {
                    //     localStorage.removeItem('cf_height');
                    // }
                    // if (localStorage.getItem('cf_orientation')) {
                    //     localStorage.removeItem('cf_orientation');
                    // }
                    $('#modal-loading').show();
                    window.location.replace("<?= admin_url('products/print_barcodes'); ?>");
                }
            });
        });
        var old_row_qty;
        $(document).on("focus", '.quantity', function () {
            old_row_qty = $(this).val();
        }).on("change", '.quantity', function () {
            var row = $(this).closest('tr');
            if (!is_numeric($(this).val())) {
                $(this).val(old_row_qty);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            var new_qty = parseFloat($(this).val()),
            item_id = row.attr('data-item-id');
            bcitems[item_id].qty = new_qty;
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
        });
    });
    function add_product_item(item) {
        ac = true;
        if (item == null) {
            return false;
        }
        item_id = item.id;
        if (bcitems[item_id]) {
            bcitems[item_id].qty = parseFloat(bcitems[item_id].qty) + 1;
        } else {
            bcitems[item_id] = item;
            bcitems[item_id]['selected_variants'] = {};
            $.each(item.variants, function () {
                bcitems[item_id]['selected_variants'][this.id] = 1;
            });
        }
        localStorage.setItem('bcitems', JSON.stringify(bcitems));
        loadItems();
        return true;
    }
    function loadItems () {
        if (localStorage.getItem('bcitems')) {
            $("#bcTable tbody").empty();
            bcitems = JSON.parse(localStorage.getItem('bcitems'));
            $.each(bcitems, function () {
                var item = this;
                var row_no = item.id;
                var vd = '';
                var newTr = $('<tr id="row_' + row_no + '" class="row_' + item.id + '" data-item-id="' + item.id + '"></tr>');
                tr_html = '<td><input name="product[]" type="hidden" value="' + item.id + '"><span id="name_' + row_no + '">' + item.name + ' (' + item.code + ')</span></td>';
                tr_html += '<td><input class="form-control quantity text-center" name="quantity[]" type="text" value="' + formatDecimal(item.qty) + '" data-id="' + row_no + '" data-item="' + item.id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
                if(item.variants) {
                    $.each(item.variants, function () {
                        vd += '<input name="vt_'+ item.id +'_'+ this.id +'" type="checkbox" class="checkbox" id="'+this.id+'" data-item-id="'+item.id+'" value="'+this.id+'" '+( item.selected_variants[this.id] == 1 ? 'checked="checked"' : '')+' style="display:inline-block;" /><label for="'+this.id+'" class="padding05">'+this.name+'</label>';
                    });
                }
                tr_html += '<td>'+vd+'</td>';
                tr_html += '<td class="text-center"><i class="fa fa-times tip del" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
                newTr.html(tr_html);
                newTr.appendTo("#bcTable");
            });
            $('input[type="checkbox"],[type="radio"]').not('.skip').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%'
            });
            return true;
        }
    }
</script>

<script type="text/javascript">
    $(document).ready(function(){
        var barcodes  = <?php echo json_encode($barcodes); ?>;
        var total_qty = 0;
        $.each(barcodes, function (key, barcode) {
            total_qty += parseInt(barcode.quantity);
        });
        <?php if ($style != 84 && $style != 1 && $style != 4025) { ?>
            if (barcodes) {
                var rows = parseInt(total_qty / 3);
                var i = 1, mg = 5.5;
                for (let row = 1; row <= rows; row++) {
                    for (let col = i; col <= row * 3; col++) {
                        i++;
                        $('#barcode-con > div > div:nth-child('+col+')').css('margin-bottom', mg + 'px');
                    }
                    mg +=0.1 ;
                }
            }
        <?php } elseif ($style == 84) { ?>
            if (barcodes) {
                var rows = parseInt(total_qty / 7);
                var i = 1, tmp_i = 1, mg = 7;
                for (let row = 1; row <= rows; row++) {
                    for (let col = i; col <= row * 7; col++) {
                        i++;
                        $('#barcode-con > div > div:nth-child(' + col + ')').css('margin-bottom', (tmp_i == 6 ? (mg + 8) : mg) + 'px');
                    }
                    if (tmp_i == 6) {
                        tmp_i = 0;
                    }
                    // mg += 0.2;
                    tmp_i++;
                }
            }
        <?php } ?>
    });
</script>