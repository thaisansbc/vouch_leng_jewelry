<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    main {
        display: flex;
        justify-content: center;
        align-items: center;
    }
    #reader {
        width: 100%;
    }
    #result {
        text-align: center;
        font-size: 1.5rem;
    }
    #html5-qrcode-button-camera-permission {
        padding: 5px;
    }
</style>
<div class="box">
    <div class="box-header no-print">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('count_stock'); ?></h2>
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
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext no-print"><?= lang('scan_to_count'); ?></p>
                <?= admin_form_open_multipart('products/update_stock_count_item/'.$stock_count_id, 'id="barcode-print-form" data-toggle="validator"'); ?>
                <div class="well well-sm no-print">
                    <?php if (!$stock_count->finalized) { ?>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('date', 'date'); ?>
                            <?php echo form_input('date', (isset($_POST['date_time']) ? $_POST['date_time'] : $this->bpas->hrld(date('Y-m-d H:i:s'))), 'class="form-control input-tip" id="date" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('status', 'status'); ?>
                            <?php
                            $opt = [0 => lang('Draft'), 1 => lang('Completed')];
                            echo form_dropdown('status', $opt, (isset($_POST['status']) ? $_POST['status'] : ''), 'id="status" required="required" class="form-control select" style="width:100%;"');
                            ?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <main>
                                <div id="reader"></div>
                                <div id="result"></div>
                            </main>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <?= lang('add_product', 'add_item'); ?>
                            <?php echo form_input('add_item', '', 'class="form-control" id="add_item" placeholder="' . $this->lang->line('add_item') . '"'); ?>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="controls table-controls">
                        <table id="bcTable" class="table items table-striped table-bordered table-condensed table-hover">
                            <thead>
                                <tr>
                                    <th class="col-xs-1"><?= lang('no'); ?></th>
                                    <th class="col-xs-4"><?= $this->lang->line('product_code'); ?></th>
                                    <th class="col-xs-4"><?= lang('product_name'); ?></th>
                                    <?php if ($Settings->product_expiry) { ?>
                                        <th class="col-xs-2"><?= lang('expiry'); ?></th>
                                    <?php } ?>
                                    <th class="col-xs-2"><?= lang('variants'); ?></th>
                                    <th class="col-xs-2"><?= lang('Expected'); ?></th>
                                    <th class="col-xs-2"><?= lang('Counted'); ?></th>
                                    <th class="text-center" style="width:30px;">
                                        <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <?php if (!$stock_count->finalized) { ?>
                    <div class="form-group">
                        <?php echo form_submit('check', lang('submit'), 'class="btn btn-primary"'); ?>
                    </div>
                    <?php } ?>
                    <?= form_close(); ?>
                    <div class="clearfix"></div>
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
    <?php } else { ?>
        localStorage.clear();
    <?php } ?>
    $(document).ready(function() {
        //localStorage.removeItem('bcitems');
        if (localStorage.getItem('bcitems')) {
            loadItems();
        }
        $("#date").datetimepicker({format: site.dateFormats.js_ldate, fontAwesome: true, language: 'sma', weekStart: 1, todayBtn: 1, autoclose: 1, todayHighlight: 1, startView: 2, forceParse: 0, startDate: "<?= $this->bpas->hrld(date('Y-m-d H:i:s')); ?>"});
        $("#add_item").autocomplete({
            source: '<?= admin_url('products/product_count_suggestions/');?><?= $stock_count->id; ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    $('#add_item').focus();
                    $(this).val('');
                } else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                } else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    $('#add_item').focus();
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
                    $('#add_item').focus();
                }
            }
        });
        check_add_item_val();
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
            bcitems[item_id].qty = parseFloat(bcitems[item_id].qty) + 1;
        }
        localStorage.setItem('bcitems', JSON.stringify(bcitems));
        loadItems();
        return true;
    }
    function loadItems () {
        if (localStorage.getItem('bcitems')) {
            $("#bcTable tbody").empty();
            bcitems = JSON.parse(localStorage.getItem('bcitems'));
            var i=1;
            $.each(bcitems, function () {
                var item = this;
                var row_no = item.id;
                var vd = '';
                var newTr = $('<tr id="row_' + row_no + '" class="row_' + item.id + '" data-item-id="' + item.id + '"></tr>');
                tr_html = '<td>'+ i+'</td>';
                tr_html += '<td><input name="product[]" type="hidden" value="' + item.product_id + '"><span id="name_' + row_no + '">'+item.code +'</span></td>';
                tr_html += '<td>'+ item.name+'</td>';
                <?php if ($Settings->product_expiry) { ?>
                    tr_html += '<td><input name="expiry[]" type="hidden" value="' + item.expiry + '">'+ item.expiry + '</td>';
                <?php } ?>
                tr_html += '<td>'+vd+'</td>';
                tr_html += '<td>'+item.expected+'</td>';
                tr_html += '<td><input class="form-control quantity text-center" name="quantity[]" type="text" value="' + formatDecimal(item.qty) + '" data-id="' + row_no + '" data-item="' + item.id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
                if(item.variants) {
                    $.each(item.variants, function () {
                        vd += '<input name="vt_'+ item.id +'_'+ this.id +'" type="checkbox" class="checkbox" id="'+this.id+'" data-item-id="'+item.id+'" value="'+this.id+'" '+( item.selected_variants[this.id] == 1 ? 'checked="checked"' : '')+' style="display:inline-block;" /><label for="'+this.id+'" class="padding05">'+this.name+'</label>';
                    });
                }
                tr_html += '<td class="text-center"><i class="fa fa-times tip del" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
                newTr.html(tr_html);
                newTr.appendTo("#bcTable");
                i++;
            });
            return true;
        }
    }
</script>
<script type="text/javascript" src="<?= $assets . 'js/html5-qrcode.min.js' ?>"></script> 
<script>
    const scanner = new Html5QrcodeScanner('reader', { 
        qrbox: {
            width: 650,
            height: 650,
        },  
        fps: 20, 
    });
    scanner.render(success, error);
    function success(result) {
        $("#add_item").val(result);
        $('#add_item').trigger('keydown');
    }
    function error(err) {
        console.error(err);
    }
    $(document).on('click', '.del', function() {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete bcitems[item_id];
        row.remove();
        if (bcitems.hasOwnProperty(item_id)) {} else {
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
            loadItems();
            return;
        }
    });
</script>