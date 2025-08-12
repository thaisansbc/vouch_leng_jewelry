<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1,
        an = 1,
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
        $('#extras').on('ifChecked', function() {
            $('#extras-con').slideDown();
        });
        $('#extras').on('ifUnchecked', function() {
            $('#extras-con').slideUp();
        });
    });
</script>
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('purchase_by_excel'); ?></h2>
</div>
<div class="box">

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'class' => 'edit-po-form'];
                echo admin_form_open_multipart('purchases/purchase_by_excel', $attrib)
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin || $GP['change_date']) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('date', 'podate'); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : date($dateFormats['php_ldate'], now())), 'class="form-control input-tip datetime" id="podate" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('reference_no', 'poref'); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $ponumber), 'class="form-control input-tip" id="poref"'); ?>
                            </div>
                        </div>
                        <?php if (($Owner || $Admin) || empty($user_billers)) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "from_biller"); ?>
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
                                    <?= lang("biller", "from_biller"); ?>
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
                        <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('warehouse', 'powarehouse'); ?>
                                    <div class="input-group" style="width:100%">
                                        <?php
                                        $wh[''] = '';
                                        if (!empty($warehouses)) {
                                            foreach ($warehouses as $warehouse) {
                                                $wh[$warehouse->id] = $warehouse->name;
                                            }
                                        }
                                        echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="powarehouse" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('warehouse') . '" required="required" style="width:100%;" '); ?>
                                        <!-- <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                            <a href="<?php echo admin_url('system_settings/add_warehouse'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                <i class="fa fa-plus-circle" id="addIcon" style="font-size: 1.2em;"></i>
                                            </a>
                                        </div> -->
                                    </div>
                                </div>
                            </div>
                        <?php } elseif (count($count) > 1) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('warehouse', 'powarehouse'); ?>
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
                                    echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="powarehouse" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('warehouse') . '" required="required" style="width:100%;" '); ?>
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
                        <?php if ($this->Settings->project) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("project", "poproject"); ?>
                                <div class="input-group" style="width:100%">
                                    <SELECT class="form-control input-tip select" name="project" style="width:100%;">
                                        <!-- <option value="">--Select--</option> -->
                                        <?php
                                        if (isset($quote)) {
                                            $project_id =  $quote->project_id;
                                        } else {
                                            $project_id =  "";
                                        }
                                        $bl[""] = "";
                                        foreach ($projects as $project) {
                                            $bl[$project->project_id] = $project->project_name;

                                            echo "<option value='" . $project->project_id . "' >" . $project->project_name;
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
                        <?php if ($Settings->stock_received) { ?>
                        <div class="col-md-4 hide">
                            <div class="form-group">
                                <?= lang('status', 'postatus'); ?>
                                <?php
                                $post = ['pending' => lang('pending')];
                                echo form_dropdown('status', $post, (isset($_POST['status']) ? $_POST['status'] : ''), 'id="postatus" class="form-control input-tip select" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('status') . '" required="required" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <?php } else { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('status', 'postatus'); ?>
                                <?php
                                $post = ['received' => lang('received'), 'pending' => lang('pending'), 'ordered' => lang('ordered')];
                                echo form_dropdown('status', $post, (isset($_POST['status']) ? $_POST['status'] : ''), 'id="postatus" class="form-control input-tip select" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('status') . '" required="required" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('supplier', 'posupplier'); ?>
                                <div class="input-group">
                                    <input type="text" name="supplier" value="" id="posupplier" required="required" class="form-control suppliers" style="width:100%;" placeholder="<?= lang('select') . ' ' . lang('supplier') ?>">
                                    <input type="hidden" name="supplier_id" value="" id="supplier_id" class="form-control">
                                    <div class="input-group-addon no-print" style="padding: 2px 5px;"><a href="<?= admin_url('suppliers/add'); ?>" id="add-customer" class="external" data-toggle="modal" data-backdrop="static" data-target="#myModal"><i class="fa fa-2x fa-plus-circle" id="addIcon"></i></a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="clearfix"></div>
                        <div class="well well-sm">
                            <a href="<?php echo base_url(); ?>assets/excel/sample_purchase_products.xlsx" class="btn btn-primary pull-right"><i class="fa fa-download"></i> Download Sample
                                File</a>
                            <span class="text-warning"><?php echo $this->lang->line('csv1'); ?></span><br>
                            <?php echo $this->lang->line('csv2'); ?> <span class="text-info">(<?= lang('product_code') . ', ' . lang('net_unit_cost') . ', ' . lang('quantity') . ', ' . lang('product_variant') . ', ' . lang('tax_rate_name') . ', ' . lang('discount') . ', ' . lang('expiry'); ?>
                                )</span><br> <?php echo lang('csv3'); ?><br>
                            <strong><?= sprintf(lang('x_col_required'), 3); ?></strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang('excel_file', 'excel_file') ?>
                            <input id="" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" required="required" data-show-upload="false" data-show-preview="false" class="form-control file">
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang('document', 'document') ?>
                            <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <input type="hidden" name="total_items" value="" id="total_items" required="required" />
                    <div class="col-md-12">
                        <div class="form-group">
                            <input type="checkbox" class="checkbox" id="extras" value="" /><label for="extras" class="padding05"><?= lang('more_options') ?></label>
                        </div>
                        <div class="row" id="extras-con" style="display: none;">
                            <?php if ($Settings->tax1) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('order_tax', 'potax2') ?>
                                        <div class="input-group" style="width:100%">
                                            <SELECT class="form-control input-tip select" name="order_tax" style="width:100%;">
                                                <?php
                                                $tr[''] = '';
                                                foreach ($tax_rates as $tax) {
                                                    $tr[$tax->id] = $tax->name;
                                                    echo "<option value='" . $tax->id . "' >" . $tax->name;
                                                }
                                                // echo form_dropdown('order_tax', $tr, (isset($_POST['order_tax']) ? $_POST['order_tax'] : $Settings->default_tax_rate2), 'id="sltax2" data-placeholder="' . lang('select') . ' ' . lang('order_tax') . '" class="form-control input-tip select" style="width:100%;"'); 
                                                ?>
                                            </SELECT>
                                            <?php if ($Owner || $Admin) { ?>
                                                <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                                    <a href="<?php echo admin_url('system_settings/add_tax_rate'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                        <i class="fa fa-plus-circle" id="addIcon" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('discount_label', 'podiscount'); ?>
                                    <?php echo form_input('discount', '', 'class="form-control input-tip" id="podiscount"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group" style="margin-bottom:5px;">
                                    <?= lang('shipping', 'poshipping'); ?>
                                    <?php echo form_input('shipping', '', 'class="form-control input-tip" id="poshipping"'); ?>
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
                        <div class="from-group"><?php echo form_submit('add_pruchase', $this->lang->line('submit'), 'id="add_pruchase" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?></div>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
</div>