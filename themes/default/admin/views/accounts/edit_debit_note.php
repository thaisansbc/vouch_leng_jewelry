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
        <?= ($inv->status == 'received' || $inv->status == 'partial') ? '$(".rec_con").show();' : '$(".rec_con").hide();'; ?>
    });
    $(document).ready(function() {
        <?= ($inv->status == 'received' || $inv->status == 'partial') ? '$(".rec_con").show();' : '$(".rec_con").hide();'; ?>
        $('#postatus').change(function() {
            var st = $(this).val();
            if (st == 'received' || st == 'partial') {
                $(".rec_con").show();
            } else {
                $(".rec_con").hide();
            }
        });
        <?php if ($inv) { ?>
            localStorage.setItem('podate', '<?= date($dateFormats['php_ldate'], strtotime($inv->date)) ?>');
            localStorage.setItem('posupplier', '<?= $inv->supplier_id ?>');
            localStorage.setItem('poref', '<?= $inv->reference_no ?>');

            localStorage.setItem('postatus', '<?= $inv->status ?>');
            localStorage.setItem('ponote', '<?= str_replace(["\r", "\n"], '', $this->bpas->decode_html($inv->note)); ?>');
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
            $(document).on('change', '#podate', function(e) {
                localStorage.setItem('podate', $(this).val());
            });
            if (podate = localStorage.getItem('podate')) {
                $('#podate').val(podate);
            }
        <?php } ?>
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
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-fw fa fa-edit"></i><?= lang('edit_debit_note'); ?></h2>
</div>
<div class="box">

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'class' => 'edit-po-form'];
                echo admin_form_open_multipart('account/edit_debit_note/' . $inv->id, $attrib)
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['change_date']) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('date', 'podate'); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->bpas->hrld($inv->date)), 'class="form-control input-tip datetime" id="podate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('reference_no', 'poref'); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $inv->reference_no), 'class="form-control input-tip" id="poref" required="required"'); ?>
                            </div>
                        </div>
                        <?php if (($Owner || $Admin) || empty($user_billers)) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "biller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company . '/' . $biller->name : $biller->name;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $inv->biller_id), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } elseif (count($user_billers) > 1) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "biller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        foreach ($user_billers as $value) {
                                            if ($biller->id == $value) {
                                                $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company . '/' . $biller->name : $biller->name;
                                            }
                                        }
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $inv->biller_id), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = array(
                                'type'  => 'hidden',
                                'name'  => 'biller',
                                'id'    => 'slbiller',
                                'value' => $inv->biller_id,
                            );
                            echo form_input($biller_input);
                        } ?>

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
                        <div class="col-md-4 hide">
                            <div class="form-group">
                                <?= lang("purchase_invoice_no", "purchase_invoice_no"); ?>
                                <?php
                                $po_opts[""] =  lang('select_po') ;
                                if($purchases){
                                    foreach ($purchases as $purchase) {
                                        $po_opts[$purchase->id] = $purchase->reference_no;
                                    }
                                }
                                
                                echo form_dropdown('pu_reference', $po_opts,$inv->purchase_id, 'id="pu_reference" class="form-control input-tip select" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
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
      
                        <div class="col-md-4 hide">
                            <div class="form-group">
                                <?= lang('status', 'postatus'); ?>
                                <?php
                                $post = [
                                    'issued' => lang('issued'),
                                    'paid' => lang('paid'),
                                    'voiced' => lang('voiced')
                                ];
                                echo form_dropdown('status', $post, (isset($_POST['status']) ? $_POST['status'] : $purchase->status), 'id="postatus" class="form-control input-tip select" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('status') . '" required="required" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('subject', 'amount'); ?>
                                <?php echo form_input('subject', $inv->subject, 'class="form-control input-tip" id="subject"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-3 hide">
                            <div class="form-group">
                                <?= lang('paying_by', 'paid_by_1'); ?>
                                <select name="paid_by" id="paid_by_1" class="form-control paid_by">
                                    <?= $this->bpas->paid_opts($payment->paid_by); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('document', 'document') ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        
                        <div class="col-md-12 hide" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . $this->lang->line('add_product_to_order') . '"'); ?>
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <a href="#" id="addManually"><i class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i></a></div>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                    
                        <div class="clearfix"></div>
                        <input type="hidden" name="total_items" value="" id="total_items" required="required" />
              
                        <table id="comboProduct" class="table items table-striped table-bordered table-condensed">
                            <thead>
                                <tr>
                                    <th><?= lang('description', 'ponote'); ?></th>
                                    <th><?= lang('amount', 'amount'); ?></th>
                                     <th width="3%">
                                       <?= lang('actions'); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($debit_note_items) {
                                foreach ($debit_note_items as $item) { ?>
                                <tr>
                                    <td>
                                        <?= form_textarea('item_note[]', $item->description, 'class="form-control" style="margin-top: 10px; height: 50px;"'); ?>
                                    </td>
                                    <td>
                                        <?= form_input('amount[]',$item->unit_cost, 'class="form-control text-right input-tip" required'); ?>
                                        
                                    </td>
                                    <td class="text-center">
                                        <a href="#" class="btn btn-sm copy_combo_product"><i class="fa fa-copy"></i></a>
                                        <a href="#" class="btn btn-sm delete_combo_product"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php }
                                } ?>
                            </tbody>
                        </table>
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
                            <td><?= lang('total') ?> <span class="totals_val pull-right" id="total">0.00</span></td>
                            <td><?= lang('order_discount') ?> <span class="totals_val pull-right" id="tds">0.00</span></td>
                            <?php if ($Settings->tax2) { ?>
                                <td><?= lang('order_tax') ?> <span class="totals_val pull-right" id="ttax2">0.00</span></td>
                            <?php } ?>
                            <td><?= lang('shipping') ?> <span class="totals_val pull-right" id="tship">0.00</span></td>
                            <td><?= lang('grand_total') ?> <span class="totals_val pull-right" id="gtotal">0.00</span></td>
                        </tr>
                    </table>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>