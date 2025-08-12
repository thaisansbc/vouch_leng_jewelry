<?php defined('BASEPATH') or exit('No direct script access allowed'); 
?>
<style>
    ul.ui-autocomplete { max-height: 200px !important; overflow-y: auto !important; overflow-x: hidden; }
</style>
<script type="text/javascript">
    var count = 1,
        an = 1,
        po_edit = false,
        product_variant = 0,
        DT = <?= $Settings->default_tax_rate ?>,
        DC = '<?= $default_currency->code ?>',
        shipping = 0,
        product_tax = 0,
        invoice_tax = 0,
        total_discount = 0,
        total = 0,
        poitems = {};
    // audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3'),
    // audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
    <?php 
    if ($this->session->userdata('remove_pols')) {
    ?>
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
    <?php $this->bpas->unset_data('remove_pols'); } ?>
    <?php if (isset($orderid)) { ?>
        localStorage.setItem('ponote', '<?= str_replace(["\r", "\n"], '', $this->bpas->decode_html($quote->note)); ?>');
        localStorage.setItem('podiscount', '<?= $quote->order_discount_id ?>');
        localStorage.setItem('potax2', '<?= $quote->order_tax_id ?>');
        localStorage.setItem('poshipping', '<?= $quote->shipping ?>');
        localStorage.setItem('amount', '<?= $quote->grand_total ?>');
        <?php if ($quote->supplier_id) { ?>
            localStorage.setItem('posupplier', '<?= $quote->supplier_id ?>');
        <?php } ?>
        localStorage.setItem('poitems', JSON.stringify(<?= $purchase_items; ?>));
    <?php } ?>
    
    $(document).ready(function() {
        <?php if ($this->input->get('supplier')) { ?>
            if (!localStorage.getItem('poitems')) {
                localStorage.setItem('posupplier', <?= $this->input->get('supplier'); ?>);
            }
        <?php } ?>
        <?php if ($Owner || $Admin || $GP['change_date']) { ?>
            if (!localStorage.getItem('podate')) {
                $("#podate").datetimepicker({
                    format: site.dateFormats.js_ldate,
                    fontAwesome: true,
                    language: 'bpas',
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
        if (!localStorage.getItem('potax2')) {
            localStorage.setItem('potax2', <?= $Settings->default_tax_rate2; ?>);
            setTimeout(function() {
                $('#extras').iCheck('check');
            }, 1000);
        }
        ItemnTotals();
        $("#add_item").autocomplete({
            // source: '<?= admin_url('purchases/suggestions'); ?>',
            source: function(request, response) {
                $.ajax({
                    type: 'get',
                    url: '<?= admin_url('purchases/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        supplier_id: $("#posupplier").val(),
                        project_id: $("#poproject").val()
                    },
                    success: function(data) {
                        $(this).removeClass('ui-autocomplete-loading');
                        response(data);
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function(event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    //audio_error.play();
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
                    //audio_error.play();
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
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });

    });
</script>
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_debit_note'); ?></h2>
</div>
<div class="box">

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                if (isset($orderid) && $orderid) {
                    echo admin_form_open_multipart("account/add_debit_note/".$orderid, $attrib);
                }else {
                    echo admin_form_open_multipart("account/add_debit_note", $attrib);
                }
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
                                <?= lang('dn_no', 'poref'); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $ponumber), 'class="form-control input-tip" id="poref"'); ?>
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
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
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
                        } ?>
                  

                        <?php if ($this->Settings->project) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("project", "poproject"); ?>
                                <div class="input-group" style="width:100%">
                                    <SELECT class="form-control input-tip select" name="project" style="width:100%;">
                                        <option value=""><?= lang('select')?></option>
                                        <?php
                                        $project_id =  isset($quote) ? $quote->project_id:'';

                                        foreach ($projects as $project) {
                                            echo "<option ".(($project_id==$project->project_id)?'selected':'')." value='" . $project->project_id . "' >" . $project->project_name;
                                        ?>
                                        <?php } ?>
                                    </SELECT>
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
                                <?= lang('status', 'postatus'); ?>
                                <?php
                                $post = ['pending' => lang('pending'), 'received' => lang('received')];
                                echo form_dropdown('status', $post, (isset($_POST['status']) ? $_POST['status'] : ''), 'id="postatus" class="form-control input-tip select" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('status') . '" required="required" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
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
                                
                                echo form_dropdown('pu_reference', $po_opts, (isset($orderid) ? $orderid: ''), 'id="pu_reference" class="form-control input-tip select" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('supplier', 'posupplier'); ?>
                                <?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) { ?>
                                    <div class="input-group"><?php } ?>
                                    <input type="hidden" name="supplier" value="" id="posupplier" class="form-control" style="width:100%;" placeholder="<?= lang('select') . ' ' . lang('supplier') ?>">
                                    <input type="hidden" name="supplier_id" value="" id="supplier_id" class="form-control">
                                    <?php if ($Owner || $Admin || $GP['suppliers-index']) { ?>
                                        <div class="input-group-addon no-print" style="padding: 2px 5px; border-left: 0;">
                                            <a href="#" id="view-supplier" class="external" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                <i class="fa fa-2x fa-user" id="addIcon"></i>
                                            </a>
                                        </div>
                                    <?php } ?>
                                    <?php if ($Owner || $Admin || $GP['suppliers-add']) { ?>
                                        <div class="input-group-addon no-print" style="padding: 2px 5px;">
                                            <a href="<?= admin_url('suppliers/add'); ?>" id="add-supplier" class="external" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                <i class="fa fa-2x fa-plus-circle" id="addIcon"></i>
                                            </a>
                                        </div>
                                    <?php } ?>
                                    <?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) { ?></div>
                                <?php } ?>
                            </div>
                        </div>
    
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('subject', 'amount'); ?>
                                <?php echo form_input('subject', (isset($_POST['subject']) ? $_POST['subject'] :''), 'class="form-control input-tip" id="subject" required'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('document', 'document') ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
                            </div>
                        </div>

                        <div class="col-md-12 hide" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . lang('add_product_to_order') . '"'); ?>
                                        <!-- <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . $this->lang->line('add_product_to_order') . '"'); ?> -->
                                        <?php if ($Owner || $Admin || $GP['products-add']) {
                                        ?>
                                            <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                                <a href="<?= admin_url('products/add') ?>" id="addManually1"><i class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i></a></div>
                                        <?php
                                        } ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
           
                        <div class="clearfix"></div>
                        <input type="hidden" name="total_items" value="" id="total_items" required="required" />
                        <div class="col-md-12">
                            <div class="clearfix"></div>
                           <table id="comboProduct" class="table items table-striped table-bordered table-condensed">
                            <thead>
                                <tr>
                                    <th><?= lang('description', 'ponote'); ?></th>
                                    <th><?= lang('amount', 'amount'); ?></th>
                                    <th width="3%">
                                       <a href="#" class="btn btn-sm add_MoreProduct"><i class="fa-fw fa fa-plus"></i></a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <?= form_textarea('item_note[]', (isset($_POST['item_note']) ? $_POST['item_note'] : ''), 'class="form-control creditnote" style="margin-top: 10px; height: 50px;"'); ?>

                                    </td>
                                    <td>
                                        <?= form_input('amount[]', (isset($_POST['amount']) ? $_POST['amount'] : ''), 'class="form-control text-right input-tip" id="amount" required'); ?>

                                    </td>
                                    <td class="text-center">
                                        <a href="#" class="btn btn-sm copy_combo_product"><i class="fa fa-copy"></i></a>
                                        <!-- <a href="#" class="btn btn-sm delete_combo_product"><i
                                                class="fa fa-trash"></i></a> -->
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                        <div class="col-md-12">
                            <div class="from-group"><?php echo form_submit('add_pruchase', $this->lang->line('submit'), 'id="add_pruchase" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
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
                            <?php if ($Settings->tax2) {
                            ?>
                                <td><?= lang('order_tax') ?> <span class="totals_val pull-right" id="ttax2">0.00</span></td>
                            <?php
                            } ?>
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

<script type="text/javascript">
    $(document).ready(function () {
        $('#pu_reference').on('change',function(){
            var si_reference = $(this).val();
            location.replace(site.base_url+"account/add_debit_note/"+si_reference);
        });
    });
    $(document).ready(function () {
      
        $('#posupplier').on('select2:select', function (e) {
            var supplier_id = $(this).val();
            if (supplier_id) {
                $.ajax({
                    url: '<?= site_url('account/get_debit_note_ref') ?>',
                    type: 'GET',
                    data: { supplier_id: supplier_id },
                    success: function(data) {
                        $('#poref').val(data);
                    }
                });
            } else {
                $('#poref').val('');
            }
        });
    });
</script>