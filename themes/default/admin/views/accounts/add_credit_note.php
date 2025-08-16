<?php defined('BASEPATH') or exit('No direct script access allowed'); 
?>
<style>
ul.ui-autocomplete {
    max-height: 200px !important;
    overflow-y: auto !important;
    overflow-x: hidden;
}

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
<script type="text/javascript">
var count = 1,
    an = 1,
    product_variant = 0,
    DT = <?= $Settings->default_tax_rate ?>,
    product_tax = 0,
    invoice_tax = 0,
    product_discount = 0,
    order_discount = 0,
    total_discount = 0,
    total = 0,
    allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>,
    tax_rates = <?php echo json_encode($tax_rates); ?>;
// var audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3');
// var audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
var quote = null;
var is_editing = false;
localStorage.setItem('group_price', JSON.stringify(<?= $group_price; ?>));
$(document).ready(function() {

    <?php if (isset($quote_id)) { ?>
    // localStorage.setItem('sldate', '<?= $this->bpas->hrld($quote->date) ?>');
    localStorage.setItem('slcustomer', '<?= $quote->customer_id ?>');
    localStorage.setItem('slbiller', '<?= $quote->biller_id ?>');
    localStorage.setItem('slsaleman_by', '<?= isset($quote->saleman_by) ? $quote->saleman_by : '' ?>');
    localStorage.setItem('slwarehouse', '<?= $quote->warehouse_id ?>');
    localStorage.setItem('slnote',
        '<?= str_replace(["\r", "\n"], '', $this->bpas->decode_html($quote->note)); ?>');
    localStorage.setItem('sldiscount',
        '<?= (isset($quote->order_discount_id) ? $quote->order_discount_id : '') ?>');
    localStorage.setItem('sltax2', '<?= (isset($quote->order_tax_id) ? $quote->order_tax_id : '') ?>');
    localStorage.setItem('slshipping', '<?= (isset($quote->shipping) ? $quote->shipping : '') ?>');
    localStorage.setItem('slitems', JSON.stringify(<?= $quote_items; ?>));
    quote = jQuery.parseJSON('<?php echo json_encode($quote); ?>');
    <?php } ?>
    <?php if ($this->input->get('customer')) { ?>
    if (!localStorage.getItem('slitems')) {
        localStorage.setItem('slcustomer', <?= $this->input->get('customer'); ?>);
    }
    <?php } ?>
    <?php if ($Owner || $Admin || $GP['change_date']) { ?>
    if (!localStorage.getItem('sldate')) {
        $("#sldate").datetimepicker({
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
    $(document).on('change', '#sldate', function(e) {
        localStorage.setItem('sldate', $(this).val());
    });
    if (sldate = localStorage.getItem('sldate')) {
        $('#sldate').val(sldate);
    }
    <?php } ?>
    $(document).on('change', '#slbiller', function(e) {
        localStorage.setItem('slbiller', $(this).val());
    });
    if (slbiller = localStorage.getItem('slbiller')) {
        $('#slbiller').val(slbiller);
    }
    $(document).on('change', '#slsaleman_by', function(e) {
        localStorage.setItem('slsaleman_by', $(this).val());
    });
    if (slsaleman_by = localStorage.getItem('slsaleman_by')) {
        $('#slsaleman_by').val(slsaleman_by);
    }
    if (!localStorage.getItem('slref')) {
        localStorage.setItem('slref', '<?= $slnumber ?>');
    }
    if (!localStorage.getItem('sltax2')) {
        localStorage.setItem('sltax2', <?= $Settings->default_tax_rate2; ?>);
    }
    ItemnTotals();
    $('.bootbox').on('hidden.bs.modal', function(e) {
        $('#add_item').focus();
    });
});
</script>
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_credit_note'); ?></h2>
</div>
<div class="box">
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                if (isset($sale_id)) {
                    echo admin_form_open_multipart("account/add_credit_note/".$sale_id, $attrib);
                } else {
                    echo admin_form_open_multipart("account/add_credit_note", $attrib);
                }
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['change_date']) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('date', 'sldate'); ?>
                                <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : date('d/m/Y H:i:s')), 'class="form-control input-tip datetime" id="sldate" required="required"'); ?>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('credit_note_no', 'slref'); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $slnumber), 'class="form-control input-tip" id="slref"'); ?>
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
                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?>
                                </div>
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
                                        echo form_dropdown('si_reference', $sa_opts, (!empty($sale_id) ? $sale_id : ''), 'id="si_reference" class="form-control input-tip select" style="width:100%;" ');
                                        ?>
                                        </div>
                                    </div>
                                    <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) { ?>
                                    <div class="col-md-4 hide">
                                        <div class="form-group">
                                            <?= lang('warehouse', 'slwarehouse'); ?>
                                            <?php
                                            $wh[''] = '';
                                            if (!empty($warehouses)) {
                                                foreach ($warehouses as $warehouse) {
                                                    $wh[$warehouse->id] = $warehouse->name;
                                                }
                                            }
                                            echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="slwarehouse" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('warehouse') . '" required="required" style="width:100%;" '); ?>
                                        </div>
                                    </div>
                                    <?php } elseif (count($count) > 1) { ?>
                                    <div class="col-md-4 hide">
                                        <div class="form-group">
                                            <?= lang('warehouse', 'slwarehouse'); ?>
                                            <?php
                                            $wh[''] = '';
                                            if (!empty($warehouses)) {
                                                foreach ($warehouses as $warehouse) {
                                                    foreach ($count as $key => $value) {
                                                        if ($warehouse->id == $value) {
                                                            $wh[$warehouse->id] = $warehouse->name;
                                                        }
                                                    }
                                                }
                                            }
                                            echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="slwarehouse" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('warehouse') . '" required="required" style="width:100%;" '); ?>
                                        </div>
                                    </div>
                                    <?php
                                } else {
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
                                                <div class="input-group-addon no-print"
                                                    style="padding: 2px 8px; border-left: 0;">
                                                    <a href="#" id="toogle-customer-read-attr" class="external">
                                                        <i class="fa fa-pencil" id="addIcon"
                                                            style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <div class="input-group-addon no-print"
                                                    style="padding: 2px 7px; border-left: 0;">
                                                    <a href="#" id="view-customer" class="external" data-toggle="modal"
                                                        data-backdrop="static" data-target="#myModal">
                                                        <i class="fa fa-eye" id="addIcon" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <?php if ($Owner || $Admin || $GP['customers-add']) {
                                                ?>
                                                <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                                    <a href="<?= admin_url('customers/add'); ?>" id="add-customer"
                                                        class="external" data-toggle="modal" data-backdrop="static"
                                                        data-target="#myModal">
                                                        <i class="fa fa-plus-circle" id="addIcon"
                                                            style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <?php
                                            } ?>
                                            </div>
                                        </div>
                                    </div>
                           
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang('subject', 'subject'); ?>
                                            <?php echo form_input('subject', (isset($_POST['subject']) ? $_POST['subject'] :''), 'class="form-control input-tip" id="subject" required'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang('document', 'document') ?>
                                            <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>"
                                                name="document" data-show-upload="false" data-show-preview="false"
                                                class="form-control file">
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm hide">
                                <div class="clearfix"></div>
                                <div class="form-group hide">
                                    <main>
                                        <div id="reader"></div>
                                        <div id="result"></div>
                                    </main>
                                </div>
                                <div class="clearfix"></div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a>
                                        </div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . lang('add_product_to_order') . '"'); ?>
                                        <?php if ($Owner || $Admin || $GP['products-add']) { ?>
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <a href="#" id="addManually" class="tip"
                                                title="<?= lang('add_product_manually') ?>">
                                                <i class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i>
                                            </a>
                                        </div>
                                        <?php }?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>

                        <div class="clearfix"></div>
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
                                <tr>
                                    <td>
                                        <?= form_textarea('item_note[]', (isset($_POST['item_note']) ? $_POST['item_note'] : ''), 'class="form-control creditnote" style="margin-top: 10px; height: 50px;"'); ?>

                                    </td>
                                    <td>
                                        <?= form_input('amount[]', (isset($_POST['amount']) ? $_POST['amount'] : ''), 'class="form-control text-right input-tip" id="amount" required'); ?>

                                    </td>
                                    <td class="text-center">
                                        <a href="#" class="btn btn-sm copy_combo_product"><i class="fa fa-copy"></i></a>
                                        <!-- <a href="#" class="btn btn-sm delete_combo_product"><i class="fa fa-trash"></i></a> -->
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
                <div class="clearfix"></div>
                <input type="hidden" name="total_items" value="" id="total_items" required="required" />

                <div class="col-md-12">
                    <div class="fprom-group">
                        <?php echo form_submit('add_sale', lang('submit'), 'id="add_sale" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                        <?php echo form_submit('add_sale_new', lang('submit_new'), 'id="add_sale_new" class="btn btn-info hide" style="padding: 6px 15px; margin:15px 0;"'); ?>
                        <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?>
                    </div>
                </div>
            </div>
        </div>

        <?php echo form_close(); ?>
    </div>
</div>
</div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('#gccustomer').select2({
        minimumInputLength: 1,
        ajax: {
            url: site.base_url + "customers/suggestions",
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
    });

});
</script>
<script type="text/javascript" src="<?= $assets . 'js/html5-qrcode.min.js' ?>"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('#si_reference').on('change', function() {
        var si_reference = $(this).val();
        location.replace(site.base_url + "account/add_credit_note/" + si_reference);
    });
});
</script>