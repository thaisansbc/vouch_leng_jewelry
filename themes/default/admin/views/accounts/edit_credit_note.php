<?php defined('BASEPATH') or exit('No direct script access allowed');
    $allow_discount = ($Owner || $Admin || $this->session->userdata('allow_discount') || $inv->order_discount_id);
?>
<style>
    ul.ui-autocomplete { max-height: 200px !important; overflow-y: auto !important; overflow-x: hidden; }
</style>
<script type="text/javascript">
    var count = 1, an = 1, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>,
    product_tax = 0, invoice_tax = 0, total_discount = 0, total = 0, allow_discount = <?= $allow_discount ? 1 : 0; ?>,
    tax_rates = <?php echo json_encode($tax_rates); ?>;
    var quote = null;
    var is_editing = true;
    $(document).ready(function () {
        // $('.skip2').prop('readonly', false);
        <?php if ($inv) { ?>
            localStorage.setItem('sldate', '<?= $this->bpas->hrld($inv->date) ?>');
            localStorage.setItem('slcustomer', '<?= $inv->customer_id ?>');
            localStorage.setItem('slbiller', '<?= $inv->biller_id ?>');
            localStorage.setItem('slref', '<?= $inv->reference_no ?>');
            localStorage.setItem('slwarehouse', '<?= $inv->warehouse_id ?>');
            localStorage.setItem('slsale_status', '<?= $inv->sale_status ?>');
            localStorage.setItem('slpayment_status', '<?= $inv->payment_status ?>');
            localStorage.setItem('slpayment_term', '<?= $inv->payment_term ?>');
            localStorage.setItem('slnote', '<?= str_replace(["\r", "\n"], '', $this->bpas->decode_html($inv->note)); ?>');
            localStorage.setItem('slinnote', '<?= str_replace(["\r", "\n"], '', $this->bpas->decode_html($inv->staff_note)); ?>');
            localStorage.setItem('sldiscount', '<?= $inv->order_discount_id ?>');
            localStorage.setItem('sltax2', '<?= $inv->order_tax_id ?>');
            localStorage.setItem('slshipping', '<?= $inv->shipping ?>');
            localStorage.setItem('slitems', JSON.stringify(<?= $inv_items; ?>));
        <?php } ?>

        <?php if ($Owner || $Admin || $GP['change_date']) { ?>
            $(document).on('change', '#sldate', function (e) {
                localStorage.setItem('sldate', $(this).val());
            });
            if (sldate = localStorage.getItem('sldate')) {
                $('#sldate').val(sldate);
            }
        <?php } ?>

        $(document).on('change', '#slbiller', function (e) {
            localStorage.setItem('slbiller', $(this).val());
        });
        if (slbiller = localStorage.getItem('slbiller')) {
            $('#slbiller').val(slbiller);
        }
 
        $(window).bind('beforeunload', function (e) {
            localStorage.setItem('remove_slls', true);
            if (count > 1) {
                var message = "You will loss data!";
                return message;
            }
        });
        $('#reset').click(function (e) {
            $(window).unbind('beforeunload');
        });
    });
</script>
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('edit_credit_note'); ?></h2>
</div>
<div class="box">
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                    $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'class' => 'edit-so-form'];
                    echo admin_form_open_multipart('account/edit_credit_note/' . $inv->id, $attrib)
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['change_date']) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('date', 'sldate'); ?>
                                <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->bpas->hrld($inv->date)), 'class="form-control input-tip datetime" id="sldate" required="required"'); ?>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('reference_no', 'slref'); ?>
                                <?php 
                                if ($Owner || $Admin || $GP['change_invoiceNo']) {
                                    echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="slref" required="required"'); ?>
                                <?php }else{
                                    echo form_input('reference_no1', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="slref" disabled="disabled" required="required"'); 
                                    echo form_hidden('reference_no',$inv->reference_no , 'required="required"');
                                } 
                                ?>
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
                        <div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                                <div class="panel-body" style="padding: 5px;">
                                    <div class="col-md-4 hide">
                                        <div class="form-group">
                                            <?= lang("sale_invoice_no", "sale_invoice_no"); ?>
                                            <?php

                                            $sa_opts[""] =  lang('select_si') ;
                                            if ($sales) {
                                                foreach ($sales as $sale) {
                                                    $sa_opts[$sale->id] = $sale->reference_no;
                                                }
                                            }
                                            echo form_dropdown('si_reference', $sa_opts,($getsale? $getsale->id:''), 'id="si_reference" class="form-control input-tip select" style="width:100%;" ');
                                            ?>
                                        </div>
                                    </div>
                                    <?php if ($this->Settings->project) { ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang("project", "poproject"); ?>
                                                <?php
                                                if(isset($inv)){
                                                    $project_id=  $inv->project_id;
                                                }else{
                                                    $project_id=  "";
                                                }
                                                $pro[""] = "";
                                                foreach ($projects as $project) {
                                                    $pro[$project->project_id] = $project->project_name;
                                                }
                                                echo form_dropdown('project', $pro, (isset($_POST['project']) ? $_POST['project'] : $project_id), 'id="poproject" data-placeholder="' . lang("select") . ' ' . lang("project") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                                ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) { ?>
                                        <div class="col-md-4 hide">
                                            <div class="form-group">
                                                <?= lang('warehouse', 'slwarehouse'); ?>
                                                <?php
                                                $wh[''] = '';
                                                foreach ($warehouses as $warehouse) {
                                                    $wh[$warehouse->id] = $warehouse->name;
                                                }
                                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $inv->warehouse_id), 'id="slwarehouse" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('warehouse') . '" required="required" style="width:100%;" '); ?>
                                            </div>
                                        </div>
                                    <?php } else {
                                        $warehouse_input = [
                                            'type'  => 'hidden',
                                            'name'  => 'warehouse',
                                            'id'    => 'slwarehouse',
                                            'value' => $this->session->userdata('warehouse_id'),
                                        ];
                                        echo form_input($warehouse_input);
                                    } ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang('customer', 'slcustomer'); ?>
                                            <div class="input-group">
                                                <?php
                                                echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ''), 'id="slcustomer" data-placeholder="' . lang('select') . ' ' . lang('customer') . '" required="required" class="form-control input-tip" style="width:100%;"');
                                                ?>
                                                <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                                    <a href="#" id="removeReadonly">
                                                        <i class="fa fa-unlock" id="unLock"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang('subject', 'subject'); ?>
                                            <?php echo form_input('subject', (isset($_POST['subject']) ? $_POST['subject'] :$inv->subject), 'class="form-control input-tip" id="subject" required'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang('document', 'document') ?>
                                            <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                            data-show-preview="false" class="form-control file">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12" id="sticker">
                            
                            <?= form_hidden('payment_status', $inv->payment_status); ?>
                          
                            <div class="clearfix"></div>
                            <input type="hidden" name="total_items" value="" id="total_items" required="required"/>
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
                                    <?php if ($credit_note_items) {
                                    foreach ($credit_note_items as $index=> $item) { ?>
                                    <tr>
                                        <td>
                                            <?= form_textarea('item_note[]', $item->description, 'class="form-control l test-editable" id="crnote" style="margin-top: 10px; height: 25px;"'); ?>
                                        </td>
                                        <td>
                                            <?= form_input('amount[]',$item->unit_price, 'class="form-control text-right input-tip" required'); ?>
                                            
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
                                <div class="fprom-group"><?php echo form_submit('edit_sale', lang('submit'), 'id="edit_sale" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
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
                            <?php if ($allow_discount) { ?>
                                <td><?= lang('order_discount') ?> <span class="totals_val pull-right" id="tds">0.00</span></td>
                            <?php } ?>
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

<script type="text/javascript">
$(document).ready(function(){

    $('.test-editable').prop('readonly', false).removeAttr('disabled');


    $('#sltax2').change(function(){
        $.ajax({
            type: "get",
            url: site.base_url + "sales/getSaleRef/" + $(this).val(),
            success: function(dataResult){
                if(dataResult){
                    if('<?= $inv->reference_no ?>'.match(/SALE.*/)){
                        if (dataResult.match(/SALE.*/)) {
                            localStorage.setItem('slref', '<?= $inv->reference_no ?>');
                            $('#slref').val('<?= $inv->reference_no ?>');
                        } else {
                            localStorage.setItem('slref', dataResult);
                            $('#slref').val(dataResult);
                        }
                    } else {
                        if (dataResult.match(/TAX.*/)) {
                            localStorage.setItem('slref', '<?= $inv->reference_no ?>');
                            $('#slref').val('<?= $inv->reference_no ?>');
                        } else {
                            localStorage.setItem('slref', dataResult);
                            $('#slref').val(dataResult);
                        }
                    }
                } else {
                    console.log("Not Found!");
                }
            }
        });
    });
 
     
});
</script>