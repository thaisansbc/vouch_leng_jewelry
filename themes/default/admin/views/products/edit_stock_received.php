<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    ul.ui-autocomplete { max-height: 200px !important; overflow-y: auto !important; overflow-x: hidden; }
</style>
<script type="text/javascript">
    var count = 1,
        an = 1,
        po_edit = true,
        product_variant = 0,
        DT = <?= $Settings->default_tax_rate ?>,
        DC = '<?= $default_currency->code ?>',
        shipping = 0,
        product_tax = 0,
        invoice_tax = 0,
        total_discount = 0,
        total = 0,
        tax_rates = <?php echo json_encode($tax_rates); ?>,
        poitems = {},
        audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3'),
        audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
    $(window).bind("load", function() {
        $(".rec_con").show();
    });
    $(document).ready(function() {
        $(".rec_con").show();
        $('#postatus').change(function() {
            var st = $(this).val();
            if (st == 'received' || st == 'partial') {
                $(".rec_con").show();
            } else {
                $(".rec_con").hide();
            }
        });
        <?php if ($inv) { ?>
            localStorage.setItem('podate', '<?= date($dateFormats['php_ldate'], strtotime($stock_received->date)) ?>');
            localStorage.setItem('posupplier', '<?= $inv->supplier_id ?>');
            localStorage.setItem('poref', '<?= $inv->reference_no ?>');
            localStorage.setItem('powarehouse', '<?= $inv->warehouse_id ?>');
            localStorage.setItem('postatus', '<?= $inv->status ?>');
            localStorage.setItem('ponote', '<?= str_replace(["\r", "\n"], '', $this->bpas->decode_html($stock_received->note)); ?>');
            localStorage.setItem('podiscount', '<?= $inv->order_discount_id ?>');
            localStorage.setItem('potax2', '<?= $inv->order_tax_id ?>');
            localStorage.setItem('poshipping', '<?= $inv->shipping ?>');
            localStorage.setItem('popayment_term', '<?= $inv->payment_term ?>');
            if (parseFloat(localStorage.getItem('potax2')) >= 1 || localStorage.getItem('podiscount').length >= 1 || parseFloat(localStorage.getItem('poshipping')) >= 1) {
                localStorage.setItem('poextras', '1');
            }
            localStorage.setItem('poitems', JSON.stringify(<?= $inv_items; ?>));
        <?php } ?>

        <?php if ($Owner || $Admin || $GP['change_date']) { ?>
            if (!localStorage.getItem('podate')) {
                $("#podate").datetimepicker({
                    format: site.dateFormats.js_ldate,
                    fontAwesome: true,
                    language: 'sma',
                    weekStart: 1,
                    todayBtn: 1,
                    autoclose: 1,
                    todayHighlight: 1,
                    startView: 2,
                    forceParse: 0
                }).datetimepicker('update', new Date());
            }
            $(document).on('change', '#podate', function(e) {
                localStorage.setItem('podate', $(this).val());
            });
            if (podate = localStorage.getItem('podate')) {
                $('#podate').val(podate);
            }
        <?php } ?>
        ItemnTotals();
        $("#add_item").autocomplete({
            source: '<?= admin_url('purchases/suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function(event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function() {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                } else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                } else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function() {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
            },
            select: function(event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_purchase_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });

        $(document).on('click', '#addItemManually', function(e) {
            if (!$('#mcode').val()) {
                $('#mError').text('<?= lang('product_code_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mname').val()) {
                $('#mError').text('<?= lang('product_name_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mcategory').val()) {
                $('#mError').text('<?= lang('product_category_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#munit').val()) {
                $('#mError').text('<?= lang('product_unit_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mcost').val()) {
                $('#mError').text('<?= lang('product_cost_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mprice').val()) {
                $('#mError').text('<?= lang('product_price_is_required') ?>');
                $('#mError-con').show();
                return false;
            }

            var msg, row = null,
                product = {
                    type: 'standard',
                    code: $('#mcode').val(),
                    name: $('#mname').val(),
                    tax_rate: $('#mtax').val(),
                    tax_method: $('#mtax_method').val(),
                    category_id: $('#mcategory').val(),
                    unit: $('#munit').val(),
                    cost: $('#mcost').val(),
                    price: $('#mprice').val()
                };

            $.ajax({
                type: "get",
                async: false,
                url: site.base_url + "products/addByAjax",
                data: {
                    token: "<?= $csrf; ?>",
                    product: product
                },
                dataType: "json",
                success: function(data) {
                    if (data.msg == 'success') {
                        row = add_purchase_item(data.result);
                    } else {
                        msg = data.msg;
                    }
                }
            });
            if (row) {
                $('#mModal').modal('hide');
            } else {
                $('#mError').text(msg);
                $('#mError-con').show();
            }
            return false;
        });
        $(window).bind('beforeunload', function(e) {
            $.get('<?= admin_url('welcome/set_data/remove_pols/1'); ?>');
            if (count > 1) {
                var message = "You will loss data!";
                return message;
            }
        });
        $('#reset').click(function(e) {
            $(window).unbind('beforeunload');
        });
        $('#edit_pruchase').click(function() {
            $(window).unbind('beforeunload');
            $('form.edit-po-form').submit();
        });
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-edit"></i><?= lang('edit_stock_received'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'class' => 'edit-po-form'];
                    echo admin_form_open_multipart('purchases/edit_stock_received/' . $stock_received->id . '/' . $inv->id, $attrib)
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['change_date']) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('date', 'podate'); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ''), 'class="form-control input-tip datetime" id="podate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('reference_no', 'received_poref'); ?>
                                <?php echo form_input('received_reference_no', (isset($_POST['received_reference_no']) ? $_POST['received_reference_no'] : $reference_no), 'class="form-control input-tip" id="received_poref" required="required"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('purchase_reference_no', 'poref'); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $purchase->reference_no), 'class="form-control input-tip" id="poref" required="required" readonly'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('warehouse', 'powarehouse'); ?>
                                <?php
                                $wh[''] = '';
                                foreach ($warehouses as $warehouse) {
                                    if($warehouse->id == $purchase->warehouse_id) {
                                        $wh[$warehouse->id] = $warehouse->name;
                                    }
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $purchase->warehouse_id), 'id="powarehouse" class="form-control input-tip select" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('warehouse') . '" required="required" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <?php if ($this->Settings->project) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("project", "poproject"); ?>
                                <div class="input-group" style="width:100%">
                                    <?php
                                    if (isset($purchase)) {
                                        $project_id =  $purchase->project_id;
                                    } else {
                                        $project_id =  "";
                                    }
                                    $bl[""] = "";
                                    foreach ($projects as $project) {
                                        $bl[$project->project_id] = $project->project_name;
                                    }
                                    echo form_dropdown('project', $bl, (isset($_POST['project']) ? $_POST['project'] : $project_id), 'id="poproject" data-placeholder="' . lang("select") . ' ' . lang("project") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                    <input type="hidden" name="project_id" value="" id="project_id" class="form-control">
                                    <?php if ($Owner) { ?>
                                        <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                            <a href="<?php echo admin_url('projects/add'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                <i class="fa fa-plus-circle" id="addIcon" style="font-size: 1.2em;"></i>
                                            </a>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                        <!-- <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('status', 'postatus'); ?>
                                <?php
                                $post = ['received' => lang('received'), 'partial' => lang('partial')];
                                echo form_dropdown('status', $post, (isset($_POST['status']) ? $_POST['status'] : $purchase->status), 'id="postatus" class="form-control input-tip select" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('status') . '" required="required" style="width:100%;" ');
                                ?>
                            </div>
                        </div> -->
                        <!-- <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('document', 'document') ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
                            </div>
                        </div> -->
                        <div class="col-md-12 hide">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                                <div class="panel-body" style="padding: 5px;">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang('supplier', 'posupplier'); ?>
                                            <div class="input-group">
                                                <input type="hidden" name="supplier" value="" id="posupplier" class="form-control" style="width:100%;" placeholder="<?= lang('select') . ' ' . lang('supplier') ?>">
                                                <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                                    <a href="#" id="removeReadonly">
                                                        <i class="fa fa-unlock" id="unLock"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <input type="hidden" name="supplier_id" value="" id="supplier_id" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="col-md-12" id="sticker" style="display: none;"> 
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a>
                                        </div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . $this->lang->line('add_product_to_order') . '" disabled'); ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang('order_items'); ?></label>
                                <div class="controls table-controls">
                                    <table id="poTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
                                            <tr>
                                                <th class="col-md-6"><?= lang('product') . ' (' . lang('code') . ' - ' . lang('name') . ')'; ?></th>
                                                <th class="col-md-2"><?= lang('purchase_quantity'); ?></th>
                                                <th class="col-md-2"><?= lang('received_quantity'); ?></th>
                                                <th class="col-md-2"><?= lang('quantity'); ?></th>
                                                <th style="width: 30px !important; text-align: center;">
                                                    <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <input type="hidden" name="total_items" value="" id="total_items" required="required" />
                        <div class="col-md-12">
                            <div class="row" id="extras-con" style="display: none;">
                                <?php if ($Settings->tax2 || $Settings->tax1) { ?>
                                    <div class="col-md-4 hide">
                                        <div class="form-group">
                                            <?= lang('order_tax', 'potax2') ?>
                                            <?php
                                            $tr[''] = '';
                                            foreach ($tax_rates as $tax) {
                                                $tr[$tax->id] = $tax->name;
                                            }
                                            echo form_dropdown('order_tax', $tr, $inv->order_tax_id, 'id="potax2" class="form-control input-tip select" style="width:100%;"'); ?>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="col-md-4 hide">
                                    <div class="form-group">
                                        <?= lang('discount_label', 'podiscount'); ?>
                                        <?php echo form_input('discount', '', 'class="form-control input-tip" id="podiscount"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4 hide">
                                    <div class="form-group">
                                        <?= lang('shipping', 'poshipping'); ?>
                                        <?php echo form_input('shipping', '', 'class="form-control input-tip" id="poshipping"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4 hide">
                                    <div class="form-group">
                                        <?= lang('adjust_paid', 'adjust_paid'); ?>
                                        <?php echo form_input('adjust_paid', $purchase->adjust_paid, 'class="form-control input-tip" id="adjust_paid"'); ?>
                                    </div>
                                </div>
                                <div class="col-sm-4 hide">
                                    <div class="form-group">
                                        <?= lang('payment_term', 'popayment_term'); ?>
                                        <?php 
                                            $pt[''] = '';
                                            foreach($payment_term as $pterm){
                                                $pt[$pterm->id] = $pterm->description;
                                            }
                                            echo form_input('payment_term', (isset($_POST['payment_term']) ? $_POST['payment_term'] : $purchase->payment_term), 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('payment_term_tip') . '" id="popayment_term"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group">
                                <?= lang('note', 'ponote'); ?>
                                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ''), 'class="form-control" id="ponote" style="margin-top: 10px; height: 100px;"'); ?>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="from-group"><?php echo form_submit('edit_pruchase', $this->lang->line('submit'), 'id="edit_pruchase" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                    <table class="table table-condensed totals" style="margin-bottom:0;">
                        <tr class="warning">
                            <td><?= lang('items') ?> <span class="totals_val pull-right" id="titems">0</span></td>
                            <td style="display: none;"><?= lang('total') ?> <span class="totals_val pull-right" id="total">0.00</span></td>
                            <td style="display: none;"><?= lang('order_discount') ?> <span class="totals_val pull-right" id="tds">0.00</span></td>
                            <?php if ($Settings->tax2) { ?>
                                <td style="display: none;"><?= lang('order_tax') ?> <span class="totals_val pull-right" id="ttax2">0.00</span></td>
                            <?php } ?>
                            <td style="display: none;"><?= lang('shipping') ?> <span class="totals_val pull-right" id="tship">0.00</span></td>
                            <td style="display: none;"><?= lang('grand_total') ?> <span class="totals_val pull-right" id="gtotal">0.00</span></td>
                        </tr>
                    </table>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span></button>
                <h4 class="modal-title" id="prModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <?php if ($Settings->tax1) {
                    ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?= lang('product_tax') ?></label>
                            <div class="col-sm-8">
                                <?php
                                $tr[''] = '';
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('ptax', $tr, '', 'id="ptax" class="form-control pos-input-tip" style="width:100%;"'); ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pquantity">
                        </div>
                    </div>
                    <?php if ($Settings->product_expiry) { ?>
                        <div class="form-group">
                            <label for="pexpiry" class="col-sm-4 control-label"><?= lang('product_expiry') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="pexpiry">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="punit" class="col-sm-4 control-label"><?= lang('product_unit') ?></label>
                        <div class="col-sm-8">
                            <div id="punits-div"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>
                        <div class="col-sm-8">
                            <div id="poptions-div"></div>
                        </div>
                    </div>
                    <?php if ($Settings->product_discount) { ?>
                        <div class="form-group">
                            <label for="pdiscount" class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pdiscount">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pcost" class="col-sm-4 control-label"><?= lang('unit_cost') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pcost">
                        </div>
                    </div>
                    <table class="table table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_cost'); ?></th>
                            <th style="width:25%;"><span id="net_cost"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="pro_tax"></span></th>
                        </tr>
                    </table>
                    <div class="panel panel-default">
                        <div class="panel-heading"><?= lang('calculate_unit_cost'); ?></div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="pcost" class="col-sm-4 control-label"><?= lang('subtotal') ?></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="psubtotal">
                                        <div class="input-group-addon" style="padding: 2px 8px;">
                                            <a href="#" id="calculate_unit_price" class="tip" title="<?= lang('calculate_unit_cost'); ?>">
                                                <i class="fa fa-calculator"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="punit_cost" value="" />
                    <input type="hidden" id="old_tax" value="" />
                    <input type="hidden" id="old_qty" value="" />
                    <input type="hidden" id="old_cost" value="" />
                    <input type="hidden" id="row_id" value="" />
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span></button>
                <h4 class="modal-title" id="mModalLabel"><?= lang('add_standard_product') ?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <div class="alert alert-danger" id="mError-con" style="display: none;">
                    <!--<button data-dismiss="alert" class="close" type="button">×</button>-->
                    <span id="mError"></span>
                </div>
                <div class="row">
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('product_code', 'mcode') ?> *
                            <input type="text" class="form-control" id="mcode">
                        </div>
                        <div class="form-group">
                            <?= lang('product_name', 'mname') ?> *
                            <input type="text" class="form-control" id="mname">
                        </div>
                        <div class="form-group">
                            <?= lang('category', 'mcategory') ?> *
                            <?php
                            $cat[''] = '';
                            foreach ($categories as $category) {
                                $cat[$category->id] = $category->name;
                            }
                            echo form_dropdown('category', $cat, '', 'class="form-control select" id="mcategory" placeholder="' . lang('select') . ' ' . lang('category') . '" style="width:100%"')
                            ?>
                        </div>
                        <div class="form-group">
                            <?= lang('unit', 'munit') ?> *
                            <input type="text" class="form-control" id="munit">
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('cost', 'mcost') ?> *
                            <input type="text" class="form-control" id="mcost">
                        </div>
                        <div class="form-group">
                            <?= lang('price', 'mprice') ?> *
                            <input type="text" class="form-control" id="mprice">
                        </div>
                        <?php if ($Settings->tax1) {
                        ?>
                            <div class="form-group">
                                <?= lang('product_tax', 'mtax') ?>
                                <?php
                                $tr[''] = '';
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('mtax', $tr, '', 'id="mtax" class="form-control input-tip select" style="width:100%;"'); ?>
                            </div>
                            <div class="form-group all">
                                <?= lang('tax_method', 'mtax_method') ?>
                                <?php
                                $tm = ['0' => lang('inclusive'), '1' => lang('exclusive')];
                                echo form_dropdown('tax_method', $tm, '', 'class="form-control select" id="mtax_method" placeholder="' . lang('select') . ' ' . lang('tax_method') . '" style="width:100%"')
                                ?>
                            </div>
                        <?php
                        } ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="addItemManually"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('body a, body button').attr('tabindex', -1);
        check_add_item_val();
        if (site.settings.set_focus != 1) {
            $('#add_item').focus();
        }
        // Order level shipping and discoutn localStorage
        if (podiscount = localStorage.getItem('podiscount')) {
            $('#podiscount').val(podiscount);
        }
        $('#potax2').change(function (e) {
            localStorage.setItem('potax2', $(this).val());
        });
        if (potax2 = localStorage.getItem('potax2')) {
            $('#potax2').select2("val", potax2);
        }
        $('#postatus').change(function (e) {
            localStorage.setItem('postatus', $(this).val());
        });
        if (postatus = localStorage.getItem('postatus')) {
            $('#postatus').select2("val", postatus);
        }
        var old_shipping;
        $('#poshipping').focus(function () {
            old_shipping = $(this).val();
        }).change(function () {
            var posh = $(this).val() ? $(this).val() : 0;
            if (!is_numeric(posh)) {
                $(this).val(old_shipping);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            shipping = parseFloat(posh);
            localStorage.setItem('poshipping', shipping);
            var gtotal = ((total + invoice_tax) - order_discount) + shipping;
            $('#gtotal').text(formatMoney(gtotal));
            $('#tship').text(formatMoney(shipping));
        });
        if (poshipping = localStorage.getItem('poshipping')) {
            shipping = parseFloat(poshipping);
            $('#poshipping').val(shipping);
        }

        $('#popayment_term').change(function (e) {
            localStorage.setItem('popayment_term', $(this).val());
        });
        if (popayment_term = localStorage.getItem('popayment_term')) {
            $('#popayment_term').val(popayment_term);
        }
        if (localStorage.getItem('poitems')) {
            loadItems();
        }

        // clear localStorage and reload
        $('#reset').click(function (e) {
            bootbox.confirm(lang.r_u_sure, function (result) {
                if (result) {
                    if (localStorage.getItem('poitems')) {
                        localStorage.removeItem('poitems');
                    }
                    if (localStorage.getItem('podiscount')) {
                        localStorage.removeItem('podiscount');
                    }
                    if (localStorage.getItem('potax2')) {
                        localStorage.removeItem('potax2');
                    }
                    if (localStorage.getItem('poshipping')) {
                        localStorage.removeItem('poshipping');
                    }
                    if (localStorage.getItem('poref')) {
                        localStorage.removeItem('poref');
                    }
                    if (localStorage.getItem('powarehouse')) {
                        localStorage.removeItem('powarehouse');
                    }
                    if (localStorage.getItem('ponote')) {
                        localStorage.removeItem('ponote');
                    }
                    if (localStorage.getItem('posupplier')) {
                        localStorage.removeItem('posupplier');
                    }
                    if (localStorage.getItem('pocurrency')) {
                        localStorage.removeItem('pocurrency');
                    }
                    if (localStorage.getItem('poextras')) {
                        localStorage.removeItem('poextras');
                    }
                    if (localStorage.getItem('podate')) {
                        localStorage.removeItem('podate');
                    }
                    if (localStorage.getItem('postatus')) {
                        localStorage.removeItem('postatus');
                    }
                    if (localStorage.getItem('popayment_term')) {
                        localStorage.removeItem('popayment_term');
                    }

                    $('#modal-loading').show();
                    location.reload();
                }
            });
        });

        var $supplier = $('#posupplier'), $currency = $('#pocurrency');
        $('#poref').change(function (e) {
            localStorage.setItem('poref', $(this).val());
        });
        if (poref = localStorage.getItem('poref')) {
            $('#poref').val(poref);
        }
        $('#powarehouse').change(function (e) {
            localStorage.setItem('powarehouse', $(this).val());
        });
        if (powarehouse = localStorage.getItem('powarehouse')) {
            $('#powarehouse').select2("val", powarehouse);
        }

            // $('#ponote').redactor('destroy');
            $('#ponote').redactor({
                buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
                formattingTags: ['p', 'pre', 'h3', 'h4'],
                minHeight: 100,
                changeCallback: function (e) {
                    var v = this.get();
                    localStorage.setItem('ponote', v);
                }
            });
            if (ponote = localStorage.getItem('ponote')) {
                $('#ponote').redactor('set', ponote);
            }
            $supplier.change(function (e) {
                localStorage.setItem('posupplier', $(this).val());
                $('#supplier_id').val($(this).val());
            });
            if (posupplier = localStorage.getItem('posupplier')) {
                $supplier.val(posupplier).select2({
                    minimumInputLength: 1,
                    data: [],
                    initSelection: function (element, callback) {
                        $.ajax({
                            type: "get", async: false,
                            url: site.base_url+"suppliers/getSupplier/" + $(element).val(),
                            dataType: "json",
                            success: function (data) {
                                callback(data[0]);
                            }
                        });
                    },
                    ajax: {
                        url: site.base_url + "suppliers/suggestions",
                        dataType: 'json',
                        quietMillis: 15,
                        data: function (term, page) {
                            return {
                                term: term,
                                limit: 10
                            };
                        },
                        results: function (data, page) {
                            if (data.results != null) {
                                return {results: data.results};
                            } else {
                                return {results: [{id: '', text: 'No Match Found'}]};
                            }
                        }
                    }
                });
            } else {
                nsSupplier();
            }

    if (localStorage.getItem('poextras')) {
        $('#extras').iCheck('check');
        $('#extras-con').show();
    }
    $('#extras').on('ifChecked', function () {
        localStorage.setItem('poextras', 1);
        $('#extras-con').slideDown();
    });
    $('#extras').on('ifUnchecked', function () {
        localStorage.removeItem("poextras");
        $('#extras-con').slideUp();
    });
    $(document).on('change', '.rexpiry', function () {
        var item_id = $(this).closest('tr').attr('data-item-id');
        poitems[item_id].row.expiry = $(this).val();
        localStorage.setItem('poitems', JSON.stringify(poitems));
    });

    // prevent default action upon enter
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });

    // Order tax calcuation
    if (site.settings.tax2 != 0) {
        $('#potax2').change(function () {
            localStorage.setItem('potax2', $(this).val());
            loadItems();
            return;
        });
    }

    // Order discount calcuation
    var old_podiscount;
    $('#podiscount').focus(function () {
        old_podiscount = $(this).val();
    }).change(function () {
        var pod = $(this).val() ? $(this).val() : 0;
        if (is_valid_discount(pod)) {
            localStorage.removeItem('podiscount');
            localStorage.setItem('podiscount', pod);
            loadItems();
            return;
        } else {
            $(this).val(old_podiscount);
            bootbox.alert(lang.unexpected_value);
            return;
        }
    });

        /* ----------------------
         * Delete Row Method
         * ---------------------- */

         $(document).on('click', '.podel', function () {
            var row = $(this).closest('tr');
            var item_id = row.attr('data-item-id');
            delete poitems[item_id];
            row.remove();
            if(poitems.hasOwnProperty(item_id)) { } else {
                localStorage.setItem('poitems', JSON.stringify(poitems));
                loadItems();
                return;
            }
        });
        $('#prModal').on('shown.bs.modal', function (e) {
            if($('#poption').select2('val') != '') {
                $('#poption').select2('val', product_variant);
                product_variant = 0;
            }
        });
        /* --------------------------
         * Edit Row Quantity Method
         -------------------------- */
        var old_row_qty;
        $(document).on("focus", '.rquantity', function () {
            old_row_qty = $(this).val();
        }).on("change", '.rquantity', function () {
            var row = $(this).closest('tr');
            if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
                $(this).val(old_row_qty);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            var new_qty = parseFloat($(this).val()),
            item_id = row.attr('data-item-id');
            poitems[item_id].row.base_quantity = new_qty;
            if(poitems[item_id].row.unit != poitems[item_id].row.base_unit) {
                $.each(poitems[item_id].units, function(){
                    if (this.id == poitems[item_id].row.unit) {
                        poitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                    }
                });
            }
            
            poitems[item_id].row.qty = new_qty;
            poitems[item_id].row.received = new_qty;
            localStorage.setItem('poitems', JSON.stringify(poitems));
            loadItems();
        });

        $(document).on("focus", '.addition_type', function () {
            old_row_qty = $(this).val();
        }).on("change", '.addition_type', function () {
            var row = $(this).closest('tr');
            var new_text = $(this).val(),
            item_id = row.attr('data-item-id');
            
            poitems[item_id].row.addition_type = new_text;
            localStorage.setItem('poitems', JSON.stringify(poitems));
            loadItems();
        });

        var old_received;
        $(document).on("focus", '.received', function () {
            old_received = $(this).val();
        }).on("change", '.received', function () {
            var row = $(this).closest('tr');
            item_id = row.attr('data-item-id');
            new_received = $(this).val() ? $(this).val() : 0;
            unit = formatDecimal(row.children().children('.runit').val());
            var x_received      = poitems[item_id].row.x_received;
            var added_stock_qty = poitems[item_id].row.added_stock_qty;
            $.each(poitems[item_id].units, function(){
                if (this.id == unit) {
                    x_received      = formatDecimal(baseToUnitQty(x_received, this), 4);
                    added_stock_qty = formatDecimal(baseToUnitQty(added_stock_qty, this), 4);
                }
            });
            if (!is_numeric(new_received)) {
                $(this).val(old_received);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            var new_received = parseFloat($(this).val());
            if (parseFloat(new_received) <= 0) {
                $(this).val(old_received);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            if (parseFloat(new_received) > (parseFloat(poitems[item_id].row.qty) - parseFloat(x_received) + parseFloat(added_stock_qty))) {
                $(this).val(old_received);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            $.each(poitems[item_id].units, function(){
                if (this.id == unit) {
                    qty_received = formatDecimal(unitToBaseQty(new_received, this), 4);
                }
            });
            poitems[item_id].row.unit_received = new_received;
            poitems[item_id].row.received = qty_received;
            poitems[item_id].row.on_first_open = 0;
            localStorage.setItem('poitems', JSON.stringify(poitems));
            loadItems();
        });

        /* --------------------------
         * Edit Row Cost Method
         -------------------------- */
        var old_cost;
        $(document).on("focus", '.rcost', function () {
            old_cost = $(this).val();
        }).on("change", '.rcost', function () {
            var row = $(this).closest('tr');
            if (!is_numeric($(this).val())) {
                $(this).val(old_cost);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            var new_cost = parseFloat($(this).val()),
            item_id = row.attr('data-item-id');
            poitems[item_id].row.cost = new_cost;
            localStorage.setItem('poitems', JSON.stringify(poitems));
            loadItems();
        });

        $(document).on("click", '#removeReadonly', function () {
            $('#posupplier').select2('readonly', false);
            return false;
        });
        if (po_edit) {
            $('#posupplier').select2("readonly", true);
        }
    });
    /* -----------------------
     * Misc Actions
     ----------------------- */

    // hellper function for supplier if no localStorage value
    function nsSupplier() {
        $('#posupplier').select2({
            minimumInputLength: 1,
            ajax: {
                url: site.base_url + "suppliers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });
    }

    function loadItems() {
        if (localStorage.getItem('poitems')) {
            total = 0;
            count = 1; count_qty_received = 1; count_qty = 1;
            an = 1;
            product_tax = 0;
            invoice_tax = 0;
            product_discount = 0;
            order_discount = 0;
            total_discount = 0;
            base_pqty = 0;
            $("#poTable tbody").empty();
            poitems = JSON.parse(localStorage.getItem('poitems'));
            sortedItems = (site.settings.item_addition == 1) ? _.sortBy(poitems, function(o){return [parseInt(o.order)];}) : poitems;

            var add_col = 0, t_qty=0;
            var order_no = new Date().getTime();
            var punit_code = '', item_punit = {};
            $.each(sortedItems, function () {
                var item = this;
                var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
                item.order = item.order ? item.order : order_no++;
                var product_id = item.row.id, item_type = item.row.type, combo_items = item.combo_items, item_cost = item.row.cost, 
                    item_oqty = item.row.oqty, item_qty = item.row.qty, item_bqty = item.row.quantity_balance, item_x_received = item.row.x_received,
                    qty_received = ((item.row.received >= 0) ? item.row.received : item.row.qty),
                    added_stock_qty = item.row.added_stock_qty;

                var item_expiry = item.row.expiry, item_tax_method = item.row.tax_method, item_ds = item.row.discount, item_discount = 0, item_option = item.row.option, item_code = item.row.code, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
                var item_supplier_part_no = item.row.supplier_part_no ? item.row.supplier_part_no : '';
                if (item.row.new_entry == 1) { item_bqty = item_qty; item_oqty = item_qty; }
                var unit_cost = item.row.real_unit_cost;
                var product_unit = item.row.unit, base_quantity = item.row.base_quantity;
                var supplier = localStorage.getItem('posupplier'), belong = false;
                item_punit = item.units, punit_code = item.row.purchase_unit;
                var purchase_item_id = item.row.purchase_item_id;
                var addition_type = item.row.addition_type?item.row.addition_type:'';

                    if (supplier == item.row.supplier1) {
                        belong = true;
                    } else if (supplier == item.row.supplier2) {
                        belong = true;
                    } else if (supplier == item.row.supplier3) {
                        belong = true;
                    } else if (supplier == item.row.supplier4) {
                        belong = true;
                    } else if (supplier == item.row.supplier5) {
                        belong = true;
                    }
                    var unit_qty_received = qty_received;
                    if(item.row.fup != 1 && product_unit != item.row.base_unit) {
                        $.each(item.units, function(){
                            if (this.id == product_unit) {
                                base_quantity = formatDecimal(unitToBaseQty(item.row.qty, this), 4);
                                unit_qty_received = item.row.unit_received ? item.row.unit_received : formatDecimal(baseToUnitQty(qty_received, this), 4);
                                item_x_received   = formatDecimal(baseToUnitQty(item_x_received, this), 4);
                                added_stock_qty   = formatDecimal(baseToUnitQty(added_stock_qty, this), 4);
                                unit_cost = formatDecimal((parseFloat(item.row.base_unit_cost)*(unitToBaseQty(1, this))), 4);
                            }
                        });
                    }

                    var ds = item_ds ? item_ds : '0';
                    item_discount = calculateDiscount(ds, unit_cost);
                    product_discount += parseFloat(item_discount * item_qty);
                    unit_cost = formatDecimal(unit_cost-item_discount);
                    var pr_tax = item.tax_rate;
                    var pr_tax_val = pr_tax_rate = 0;
                    if (site.settings.tax1 == 1 && (ptax = calculateTax(pr_tax, unit_cost, item_tax_method))) {
                        pr_tax_val = ptax[0];
                        pr_tax_rate = ptax[1];
                        product_tax += pr_tax_val * item_qty;
                    }
                    item_cost = item_tax_method == 0 ? formatDecimal(unit_cost-pr_tax_val, 4) : formatDecimal(unit_cost);
                    unit_cost = formatDecimal(unit_cost+item_discount, 4);
                    var sel_opt = '';
                    $.each(item.options, function () {
                        if(this.id == item_option) {
                            sel_opt = this.name;
                        }
                    });

                var product_unit_code = '';
                $.each(item.units, function(index, val_item){
                    if (product_unit == val_item.id) {
                        product_unit_code = val_item.name;
                    }
                });

                var row_no = item.id;
                var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
                tr_html = '<td><input name="purchase_item_id[]" type="hidden" value="' + purchase_item_id + '"><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><input name="product[]" type="hidden" class="rcode" value="' + item_code + '"><input name="product_name[]" type="hidden" class="rname" value="' + item_name + '"><input name="product_option[]" type="hidden" class="roption" value="' + item_option + '"><input name="part_no[]" type="hidden" class="rpart_no" value="' + item_supplier_part_no + '"><span class="sname" id="name_' + row_no + '">' + item_code +' - '+ item_name +(sel_opt != '' ? ' ('+sel_opt+')' : '')+' <span class="label label-default">'+item_supplier_part_no+'</span></span><span id="product_unit_code" class="text-right"> ('+product_unit_code+') </span></td>';
                if (site.settings.product_expiry == 1) {
                    tr_html += '<td style="display: none;"><input class="form-control date rexpiry" name="expiry[]" type="text" value="' + item_expiry + '" data-id="' + row_no + '" data-item="' + item_id + '" id="expiry_' + row_no + '"></td>';
                }
                if (item.fiber.name == 'Fiber') {
                    tr_html += '<td class="text-center" style="display: none;"><input id="" class="form-control addition_type" type="text" name="addition_type[]" value="'+addition_type+'" required="required"></td>';
                }
                tr_html += '<td class="text-right" style="display: none;"><input class="form-control input-sm text-right rcost" name="net_cost[]" type="hidden" id="cost_' + row_no + '" value="' + item_cost + '">'+
                        '<input class="rucost" name="unit_cost[]" type="hidden" value="' + unit_cost + '">'+
                        '<input class="realucost" name="real_unit_cost[]" type="hidden" value="' + item.row.real_unit_cost + '">' +
                        '<span class="text-right scost" id="scost_' + row_no + '">' + formatMoney(item_cost) + '</span></td>';

                tr_html += '<td><input name="quantity_balance[]" type="hidden" class="rbqty" value="' + item_bqty + '">' + 
                                '<input class="form-control text-center rquantity" name="quantity[]" type="text" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" value="' + formatQuantity2(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();" readonly>' + 
                                '<input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '"><input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' + base_quantity + '"></td>';

                tr_html += '<td><input class="form-control text-center" type="text" value="' + formatDecimal(item_x_received) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="received_' + row_no + '" onClick="this.select();" readonly></td>';

                tr_html += '<td><input name="ordered_quantity[]" type="hidden" class="oqty" value="' + item_oqty + '">' + 
                               '<input class="form-control text-center received" name="received[]" type="text" value="' + (item.row.on_first_open == 1 ? formatDecimal(added_stock_qty) : formatDecimal(unit_qty_received)) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="received_' + row_no + '" onClick="this.select();">' + 
                               '<input name="received_base_quantity[]" type="hidden" class="rrbase_quantity" value="' + qty_received + '"></td>';

                if (site.settings.product_discount == 1) {
                    tr_html += '<td class="text-right" style="display: none;"><input class="form-control input-sm rdiscount" name="product_discount[]" type="hidden" id="discount_' + row_no + '" value="' + item_ds + '"><span class="text-right sdiscount text-danger" id="sdiscount_' + row_no + '">' + formatMoney(0 - (item_discount * item_qty)) + '</span></td>';
                }
                if (site.settings.tax1 == 1) {
                    tr_html += '<td class="text-right" style="display: none;"><input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + pr_tax.id + '"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' + (pr_tax_rate ? '(' + pr_tax_rate + ')' : '') + ' ' + formatMoney(pr_tax_val * item_qty) + '</span></td>';
                }
                tr_html += '<td class="text-right" style="display: none;"><span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + formatMoney(((parseFloat(item_cost) + parseFloat(pr_tax_val)) * parseFloat(item_qty))) + '</span></td>';
                tr_html += '<td class="text-center"><i class="fa fa-times tip podel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
                newTr.html(tr_html);
                newTr.prependTo("#poTable");
                total += formatDecimal(((parseFloat(item_cost) + parseFloat(pr_tax_val)) * parseFloat(item_qty)), 4);
                count += parseFloat(item_qty);
                count_qty_received += (item.row.on_first_open == 1 ? formatDecimal(added_stock_qty) : formatDecimal(unit_qty_received));
                count_qty += parseFloat(unit_qty_received);

                an++;
                if(!belong) $('#row_' + row_no).addClass('warning');

                base_pqty = item.row.order_qty;
                var purchase_order_qty = 0;
                var total_qty = 0;
                if(product_unit != item.row.base_unit){
                    $.each(item.units, function(index, qitem){
                        if (product_unit == qitem.id) {
                            total_qty = unitToBaseQty(parseFloat(item_qty), this);
                        }
                        if (punit_code == qitem.id) {
                            purchase_order_qty = unitToBaseQty(parseFloat(base_pqty), this);
                        }
                    });
                }else{
                    $.each(item.units, function(index, qitem){
                        total_qty = parseFloat(item_qty);
                        if (punit_code == qitem.id) {
                            purchase_order_qty = unitToBaseQty(parseFloat(base_pqty), this);
                        }
                    });
                }
                t_qty += total_qty;
            });

            var tPurQty = 0;
            $.each(sortedItems, function(){
                var item = this;
                tPurQty += parseFloat(item.row.total_purchase_qty);
                if (item.fiber.name == 'Fiber') {
                    if(tPurQty !== false){     
                        if (t_qty > tPurQty) {
                            if(site.settings.overselling != 1) { $('#add_pruchase, #edit_pruchase').attr('disabled', true); }
                            $('td').addClass('danger');
                        }else if(t_qty <= tPurQty){
                            if(site.settings.overselling != 1) { $('#add_pruchase, #edit_pruchase').attr('disabled', false); }
                        }
                    }
                }
            });

            var col = 0 + add_col;
            if (site.settings.product_expiry == 1) { col++; }
            var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th>';
                tfoot += '<th class="text-center">' + formatQty(parseFloat(count) - 1) + '</th>';
                tfoot += '<th class="text-center">' + formatQty(parseFloat(count_qty_received) - 1) + '</th>';
                tfoot += '<th class="text-center"><input name="total_qty" type="hidden" value="' + (parseFloat(count_qty) - 1) + '">' + formatQty(parseFloat(count_qty) - 1) + '</th>';
            
            if (site.settings.product_discount == 1) {
                tfoot += '<th class="text-right" style="display: none">'+formatMoney(product_discount)+'</th>';
            }
            if (site.settings.tax1 == 1) {
                tfoot += '<th class="text-right" style="display: none">'+formatMoney(product_tax)+'</th>';
            }
            tfoot += '<th class="text-right" style="display: none">'+formatMoney(total)+'</th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
            $('#poTable tfoot').html(tfoot);

            // Order level discount calculations
            if (podiscount = localStorage.getItem('podiscount')) {
                var ds = podiscount;
                if (ds.indexOf("%") !== -1) {
                    var pds = ds.split("%");
                    if (!isNaN(pds[0])) {
                        order_discount = formatDecimal(((total * parseFloat(pds[0])) / 100), 4);
                    } else {
                        order_discount = formatDecimal(ds);
                    }
                } else {
                    order_discount = formatDecimal(ds);
                }
            }

            // Order level tax calculations
            if (site.settings.tax2 != 0) {
                if (potax2 = localStorage.getItem('potax2')) {
                    $.each(tax_rates, function () {
                        if (this.id == potax2) {
                            if (this.type == 2) {
                                invoice_tax = formatDecimal(this.rate);
                            }
                            if (this.type == 1) {
                                invoice_tax = formatDecimal((((total - order_discount) * this.rate) / 100), 4);
                            }
                        }
                    });
                }
            }
            total_discount = parseFloat(order_discount + product_discount);
            // Totals calculations after item addition
            var gtotal = ((total + invoice_tax) - order_discount) + shipping;
            $('#total').text(formatMoney(total));
            $('#titems').text((an-1)+' ('+(formatQty(parseFloat(count) - 1))+')');
            $('#tds').text(formatMoney(order_discount));
            if (site.settings.tax1) {
                $('#ttax1').text(formatMoney(product_tax));
            }
            if (site.settings.tax2 != 0) {
                $('#ttax2').text(formatMoney(invoice_tax));
            }
            $('#gtotal').text(formatMoney(gtotal));
            if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
                $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
                $(window).scrollTop($(window).scrollTop() + 1);
            }
            set_page_focus();
        }
    }

    if (typeof (Storage) === "undefined") {
        $(window).bind('beforeunload', function (e) {
            if (count > 1) {
                var message = "You will loss data!";
                return message;
            }
        });
    }
</script>