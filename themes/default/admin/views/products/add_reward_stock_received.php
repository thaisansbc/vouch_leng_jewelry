<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>ul.ui-autocomplete { max-height: 200px !important; overflow-y: auto !important; overflow-x: hidden; }</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-edit"></i><?= lang('add_reward_stock_received'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'class' => 'edit-po-form'];
                echo admin_form_open_multipart('products/add_reward_stock_received/' . $inv->id, $attrib); ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['change_date']) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('date', 'podate'); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : date('d/m/Y H:i')), 'class="form-control input-tip datetime" id="podate" required="required"'); ?>
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
                                <?= lang('reward_reference_no', 'poref'); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $inv->reference_no), 'class="form-control input-tip" id="poref" required="required" readonly'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('warehouse', 'powarehouse'); ?>
                                <?php
                                $wh[$warehouse->id] = $warehouse->name;
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $inv->warehouse_id), 'id="powarehouse" class="form-control input-tip select" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('warehouse') . '" required="required" style="width:100%;"');
                                ?>
                            </div>
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
                                <label class="table-label"><?= lang('rewards'); ?></label>
                                <div class="controls table-controls">
                                    <table id="rewardTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
                                            <tr>
                                                <th class="col-md-6"><?= lang('product') . ' (' . lang('code') . ' - ' . lang('name') . ')'; ?></th> 
                                                <th class="col-md-3"><?= lang('quantity'); ?></th>
                                                <th class="col-md-3"><?= lang('quantity_balance'); ?></th>
                                                <th style="width: 30px !important; text-align: center;">
                                                    <i class="fa fa-arrow-circle-right" aria-hidden="true"></i>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($inv_items as $inv_item) { ?>
                                            <tr>
                                                <td>
                                                    <input type="hidden" class="rex_item_id"           value="<?= $inv_item->reward_exchange_item_id; ?>">
                                                    <input type="hidden" class="rex_product_id"        value="<?= $inv_item->receive_product_id; ?>">
                                                    <input type="hidden" class="rex_product_code"      value="<?= $inv_item->receive_product_code; ?>">
                                                    <input type="hidden" class="rex_product_name"      value="<?= $inv_item->receive_product_name; ?>">
                                                    <input type="hidden" class="rex_unit_id"           value="<?= $inv_item->receive_unit_id; ?>">
                                                    <input type="hidden" class="rex_unit_name"         value="<?= $inv_item->receive_unit_name; ?>">
                                                    <input type="hidden" class="rex_quantity"          value="<?= $inv_item->receive_quantity; ?>">
                                                    <input type="hidden" class="rex_quantity_balance"  value="<?= $inv_item->receive_quantity - $inv_item->received_quantity; ?>">
                                                    <?= $inv_item->receive_product_code . ' - ' . $inv_item->receive_product_name . ' (' . $inv_item->receive_unit_name . ')' ?></td>
                                                <td><?= $this->bpas->formatDecimal($inv_item->receive_quantity); ?></td>
                                                <td><?= $this->bpas->formatDecimal($inv_item->receive_quantity - $inv_item->received_quantity); ?></td>
                                                <th style="width: 30px !important; text-align: center;">
                                                    <?php if ($inv_item->receive_quantity - $inv_item->received_quantity <= 0) { ?>
                                                        &nbsp;
                                                    <?php } else { ?>
                                                        <i class="fa fa-arrow-right addItem" aria-hidden="true"></i>
                                                    <?php } ?>
                                                </th>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang('receive_items'); ?></label>
                                <div class="controls table-controls">
                                    <table id="receiveTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
                                            <tr>
                                                <th class="col-md-<?= ($Settings->product_expiry ? '6' : '9'); ?>"><?= lang('product') . ' (' . lang('code') . ' - ' . lang('name') . ')'; ?></th>
                                                <?php if ($Settings->product_expiry) { ?>
                                                    <th class="col-md-3"><?= lang('expiry'); ?></th>
                                                <?php } ?>
                                                <th class="col-md-3"><?= lang('quantity'); ?></th>
                                                <th style="width: 30px !important; text-align: center;">
                                                    <i class="fa fa-trash-o" style="opacity:0.5; filter: alpha(opacity=50);"></i>
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
                            <div class="form-group">
                                <?= lang('note', 'ponote'); ?>
                                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ''), 'class="form-control" id="ponote" style="margin-top: 10px; height: 100px;"'); ?>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="from-group"><?php echo form_submit('add_stock_receive', $this->lang->line('submit'), 'id="add_stock_receive" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                    <table class="table table-condensed totals" style="margin-bottom:0;">
                        <tr class="warning">
                            <td><?= lang('items') ?> <span class="totals_val pull-right" id="titems">0</span></td>
                        </tr>
                    </table>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        var warehouse = <?php echo json_encode($warehouse); ?>;
        var category  = <?php echo json_encode($inv->category); ?>;
        checkReceivedItems();
        $('.addItem').on('click', function() {
            var rw_tr = $(this).closest('tr');
            var reward_exchange_item_id = $(rw_tr).find('.rex_item_id').val();
            var product_id              = $(rw_tr).find('.rex_product_id').val();
            var product_code            = $(rw_tr).find('.rex_product_code').val();
            var product_name            = $(rw_tr).find('.rex_product_name').val();
            var unit_id                 = $(rw_tr).find('.rex_unit_id').val();
            var unit_name               = $(rw_tr).find('.rex_unit_name').val();
            var quantity                = $(rw_tr).find('.rex_quantity').val();
            var quantity_balance        = $(rw_tr).find('.rex_quantity_balance').val();
            var newTr = $('<tr id="' + reward_exchange_item_id + '"></tr>');
            var tr_html = '<td>' + 
                            '<input name="reward_exchange_item_id[]" type="hidden" class="rreward_exchange_item_id" value="' + reward_exchange_item_id + '">' +
                            '<input name="product[]" type="hidden" class="rproduct_id" value="' + product_id + '">' +
                            '<input name="product_code[]" type="hidden" value="' + product_code + '">' +
                            '<input name="product_name[]" type="hidden" value="' + product_name + '">' +
                            '<input name="product_unit_id[]" type="hidden" value="' + unit_id + '">' +  
                            product_code + ' - ' + product_name + ' (' + unit_name + ')' +
                        '</td>';
            if (site.settings.product_expiry == 1) { 
                tr_html += '<td><input class="form-control date rexpiry" name="expiry[]" type="text" value=""></td>';
            }
            tr_html += '<td><input type="hidden" class="rquantity_balance" value="' + quantity_balance + '"><input class="form-control rquantity" name="quantity[]" type="text" value="' + formatDecimal(quantity_balance) + '"></td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip podel" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#receiveTable");
            checkReceivedItems();
            setFooter();
        });
        var old_row_quantity;
        $(document).on("focus", '.rquantity', function () {
            old_row_quantity = $(this).val();
            $(this).select();
        }).on("change", '.rquantity', function () {
            var row = $(this).closest('tr');
            var new_row_quantity = parseFloat($(this).val());
            var quantity_balance = parseFloat($(row).find('.rquantity_balance').val());
            if (!is_numeric($(this).val()) || parseFloat($(this).val()) <= 0) {
                $(this).val(old_row_quantity);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            if (new_row_quantity > quantity_balance) {
                $(this).val(old_row_quantity);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            checkReceivedItems();
            setFooter();
        });
        $(document).on("change", '.rexpiry', function () {
            checkReceivedItems();
        });
        $(document).on('click', '.podel', function () {
            var row = $(this).closest('tr');
            row.remove();
            checkReceivedItems();
            setFooter();
        });
        $('#reset').click(function(e) {
            bootbox.confirm(lang.r_u_sure, function(result) {
                if (result) {
                    location.reload();
                }
            });
        });
        function setFooter() {
            var count = 0;
            var count_quantity = 0;
            $('#receiveTable tbody tr').each(function() {
                count++;
                count_quantity += parseFloat($(this).find('.rquantity').val());
            });
            $('#total_items').val(count_quantity);
            $('#titems').text((count)+' ('+(formatQty(parseFloat(count_quantity)))+')');
        } 
        function checkOverselling(items) {
            var isOver = false;
            if (category == 'customer') {
                $.each(items, function(index, element) {
                    var key = index.split('_');
                    var product_id = key[0];
                    var expiry     = key[1] != undefined ? key[1] : '';      
                    $.ajax({
                        async: false,
                        type: "get",
                        url: site.base_url + "products/get_product_stock_balance_ajax",
                        data: {
                            warehouse_id: $("#powarehouse").val(),
                            product_id: product_id,
                            expiry: expiry,
                        },
                        success: function(dataResult) {
                            if (dataResult == false) {
                                $('#receiveTable tbody tr').each(function() {
                                    if (product_id == $(this).find('.rproduct_id').val() && expiry == ($(this).find('.rexpiry').val() != undefined ? $(this).find('.rexpiry').val() : '')) {
                                        isOver = true;
                                        $(this).addClass('danger');
                                    }
                                });
                            } else {
                                $('#receiveTable tbody tr').each(function() {
                                    if (product_id == $(this).find('.rproduct_id').val() && expiry == ($(this).find('.rexpiry').val() != undefined ? $(this).find('.rexpiry').val() : '')) {
                                        if (parseFloat(dataResult.quantity) < parseFloat(element)) {
                                            isOver = true;
                                            $(this).addClass('danger');
                                        } else {
                                            $(this).removeClass('danger');
                                        }
                                    }
                                });
                            } 
                        }
                    });
                });
            }
            return isOver;
        }
        function checkOverbalance(items) {
            var isOver = false;
            $.each(items, function(index, element) {
                var row_id = index;  
                $('#receiveTable tbody tr').each(function() {
                    var tr = $(this);
                    if (row_id == $(tr).attr('id')) {
                        if (parseFloat(element) > parseFloat($(tr).find('.rquantity_balance').val())) {
                            isOver = true;
                            $(this).addClass('danger');
                        } else {
                            $(this).removeClass('danger');
                        }
                    }
                });
            });
            return isOver;
        }
        function checkReceivedItems() {
            var gp_by_item    = {};
            var gp_by_product = {};
            $('#receiveTable tbody tr').each(function() {
                var tr = $(this);
                var reward_exchange_item_id = $(tr).find('.rreward_exchange_item_id').val();
                var product_id  = $(tr).find('.rproduct_id').val();
                var expiry      = ($(tr).find('.rexpiry').val() != undefined ? $(tr).find('.rexpiry').val() : '');
                var quantity    = parseFloat($(tr).find('.rquantity').val());
                var key_item    = reward_exchange_item_id;
                gp_by_item[key_item] = (gp_by_item[key_item] != undefined ? gp_by_item[key_item] : 0);
                gp_by_item[key_item] += quantity;
                var key_product = product_id + (expiry ? ('_' + expiry) : '');
                gp_by_product[key_product] = (gp_by_product[key_product] != undefined ? gp_by_product[key_product] : 0);
                gp_by_product[key_product] += quantity;
            });
            if ($('#receiveTable tbody tr').length > 0) {
                if (checkOverbalance(gp_by_item) == true) {
                    $('#add_stock_receive').attr('disabled', true);
                } else if (checkOverselling(gp_by_product) == true && (site.settings.overselling != 1 || (site.settings.overselling == 1 && warehouse.overselling != 1))) {
                    $('#add_stock_receive').attr('disabled', true);
                } else {
                    $('#add_stock_receive').attr('disabled', false);
                }
            } else {
                $('#add_stock_receive').attr('disabled', true);
            }
        }
    });
</script>