<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$wm = ['0' => lang('no'), '1' => lang('yes')];
$ps = ['0' => lang('disable'), '1' => lang('enable')];
?>
<script>
    /*function passwordCheck(){
        var password = prompt("Please enter the password.");
        if (password==="123"){
            window.location=<?= admin_url('system_settings/modules') ?>;
        } else if (password!='' && password!=null) {
            while(password !=="123"){
                password = prompt("Please enter the password.");
            }
            window.location=<?= admin_url('system_settings') ?>;
        }
    }
    window.onload=passwordCheck;*/
    $(document).ready(function () {
        <?php if (isset($message)) {
            echo 'localStorage.clear();';
        } ?>
        var timezones = <?= json_encode(DateTimeZone::listIdentifiers(DateTimeZone::ALL)); ?>;
        $('#timezone').autocomplete({
            source: timezones
        });
        if ($('#protocol').val() == 'smtp') {
            $('#smtp_config').slideDown();
        } else if ($('#protocol').val() == 'sendmail') {
            $('#sendmail_config').slideDown();
        }
        $('#protocol').change(function () {
            if ($(this).val() == 'smtp') {
                $('#sendmail_config').slideUp();
                $('#smtp_config').slideDown();
            } else if ($(this).val() == 'sendmail') {
                $('#smtp_config').slideUp();
                $('#sendmail_config').slideDown();
            } else {
                $('#smtp_config').slideUp();
                $('#sendmail_config').slideUp();
            }
        });
        $('#overselling').change(function () {
            if ($(this).val() == 1) {
                if ($('#accounting_method').select2("val") != 2) {
                    bootbox.alert('<?=lang('overselling_will_only_work_with_AVCO_accounting_method_only')?>');
                    $('#accounting_method').select2("val", '2');
                }
            }
        });
        $('#accounting_method').change(function () {
            var oam = <?=$Settings->accounting_method?>, nam = $(this).val();
            if (oam != nam) {
                bootbox.alert('<?=lang('accounting_method_change_alert')?>');
            }
        });
        $('#accounting_method').change(function () {
            if ($(this).val() != 2) {
                if ($('#overselling').select2("val") == 1) {
                    bootbox.alert('<?=lang('overselling_will_only_work_with_AVCO_accounting_method_only')?>');
                    $('#overselling').select2("val", 0);
                }
            }
        });
        $('#item_addition').change(function () {
            if ($(this).val() == 1) {
                bootbox.alert('<?=lang('product_variants_feature_x')?>');
            }
        });
        var sac = $('#sac').val()
        if(sac == 1) {
            $('.nsac').slideUp();
        } else {
            $('.nsac').slideDown();
        }
        $('#sac').change(function () {
            if ($(this).val() == 1) {
                $('.nsac').slideUp();
            } else {
                $('.nsac').slideDown();
            }
        });
    });
</script>
<style type="text/css">
    .switch {
      position: relative;
      display: inline-block;
      width: 60px;
      height: 34px;
    }
    .switch input {display:none;}
    .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #ccc;
      -webkit-transition: .4s;
      transition: .4s;
    }
    .slider:before {
      position: absolute;
      content: "";
      height: 26px;
      width: 26px;
      left: 4px;
      bottom: 4px;
      background-color: white;
      -webkit-transition: .4s;
      transition: .4s;
    }

    input:checked + .slider {
      background-color: #2196F3;
    }

    input:focus + .slider {
      box-shadow: 0 0 1px #2196F3;
    }

    input:checked + .slider:before {
      -webkit-transform: translateX(26px);
      -ms-transform: translateX(26px);
      transform: translateX(26px);
    }

    /* Rounded sliders */
    .slider.round {
      border-radius: 34px;
    }

    .slider.round:before {
      border-radius: 50%;
    }
</style>
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-fw fa fa-cog"></i><?= lang('modules'); ?></h2>
</div>
<div class="box">

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('update_info'); ?></p>

                <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('system_settings/modules', $attrib);
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <fieldset class="scheduler-border">
                        <legend class="scheduler-border"><?= lang('modules') ?></legend>

                   
                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('invetory', 'inventory'); ?>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="module_inventory" class="skip" value="1" <?= $Settings->module_inventory? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('asset', 'asset'); ?>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="module_asset" class="skip" value="1" <?= $Settings->module_asset? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('purchase', 'purchase'); ?>
                                <div class="controls"> 
                                    <label class="switch">
                                        <input type="checkbox" name="module_purchase" class="skip" value="1" <?= $Settings->module_purchase? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label> 
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('sales', 'sale'); ?>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="module_sale" class="skip" value="1" <?= $Settings->module_sale? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label> 
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('pos', 'pos'); ?>
                                <div class="controls">
                                     <label class="switch">
                                        <input type="checkbox" name="pos" class="skip" value="1" <?= $Settings->pos? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label> 
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('project', 'project'); ?>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="project" class="skip" value="1" <?= $Settings->project? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label> 
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="crm"><?= lang('manufacturing'); ?></label>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="module_manufacturing" class="skip" value="1" <?= $Settings->module_manufacturing? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label> 
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="accounting"><?= lang('accounting'); ?></label>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="module_account" class="skip" value="1" <?= $Settings->module_account? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="crm"><?= lang('hr'); ?></label>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="module_hr" class="skip" value="1" <?= $Settings->module_hr? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="crm"><?= lang('payroll'); ?></label>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="payroll" class="skip" value="1" <?= $Settings->payroll? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="crm"><?= lang('attendance'); ?></label>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="attendance" class="skip" value="1" <?= $Settings->attendance? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="crm"><?= lang('crm'); ?></label>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="module_crm" class="skip" value="1" <?= $Settings->module_crm? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="crm"><?= lang('property'); ?></label>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="module_property" class="skip" value="1" <?= $Settings->module_property? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="crm"><?= lang('clinic'); ?></label>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="module_clinic" class="skip" value="1" <?= $Settings->module_clinic? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                         <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="tax"><?= lang('tax'); ?></label>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="module_tax" class="skip" value="1" <?= $Settings->module_tax? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="crm"><?= lang('school'); ?></label>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="module_school" class="skip" value="1" <?= $Settings->module_school? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="hotel"><?= lang('hotel'); ?></label>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="module_hotel_apartment" class="skip" value="1" <?= $Settings->module_hotel_apartment? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('ecommerce', 'ecommerce'); ?>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="shop" class="skip" value="1" <?= $Settings->shop? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label" for="crm"><?= lang('email_marketing'); ?></label>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="module_email" class="skip" value="1" <?= $Settings->module_email? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('express', 'express'); ?>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="module_express" class="skip" value="1" <?= $Settings->module_express? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                          <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('loan', 'loan'); ?>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="module_loan" class="skip" value="1" <?= $Settings->module_loan? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                          <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('pawns', 'pawn'); ?>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="module_pawn" class="skip" value="1" <?= $Settings->module_pawn? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                         <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('savings', 'save'); ?>
                                <div class="controls"> 
                                    <label class="switch">
                                        <input type="checkbox" name="module_save" class="skip" value="1" <?= $Settings->module_save? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                       <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('installmentings', 'installment'); ?>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="module_installment" class="skip" value="1" <?= $Settings->module_installment? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>  
                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('gyms', 'gym'); ?>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="module_gym" class="skip" value="1" <?= $Settings->module_gym? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('repair', 'repair'); ?>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="module_repair" class="skip" value="1" <?= $Settings->module_repair? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('rental', 'rental'); ?>
                                <div class="controls">
                                    <label class="switch">
                                        <input type="checkbox" name="module_rental" class="skip" value="1" <?= $Settings->module_rental? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('concrete', 'concrete'); ?>
                                <div class="controls"> 
                                    <label class="switch">
                                        <input type="checkbox" name="module_concrete" class="skip" value="1" <?= $Settings->module_concrete? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('truckings', 'truckings'); ?>
                                <div class="controls"> 
                                    <label class="switch">
                                        <input type="checkbox" name="module_truckings" class="skip" value="1" <?= $Settings->module_truckings? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('clearance', 'clearance'); ?>
                                <div class="controls"> 
                                    <label class="switch">
                                        <input type="checkbox" name="module_clearance" class="skip" value="1" <?= $Settings->module_clearance? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('reward_exchange', 'reward_exchange'); ?>
                                <div class="controls"> 
                                    <label class="switch">
                                        <input type="checkbox" name="reward_exchange" class="skip" value="1" <?= $Settings->reward_exchange? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('module_fuel', 'module_fuel'); ?>
                                <div class="controls"> 
                                    <label class="switch">
                                        <input type="checkbox" name="module_fuel" class="skip" value="1" <?= $Settings->module_fuel? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('e_ticket', 'e_ticket'); ?>
                                <div class="controls"> 
                                    <label class="switch">
                                        <input type="checkbox" name="e_ticket" class="skip" value="1" <?= $Settings->module_e_ticket? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('module_expense', 'module_expense'); ?>
                                <div class="controls"> 
                                    <label class="switch">
                                        <input type="checkbox" name="module_expense" class="skip" value="1" <?= $Settings->module_expense? 'checked':''; ?>>
                                        <span class="slider round"></span> 
                                    </label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border"><?= lang('sub_modules') ?></legend>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('multi_level', 'ui'); ?>
                                <?php 
                                $opt_level = [1 => lang('yes'), 0 => lang('no')];
                                echo form_dropdown('multi_level', $opt_level, $Settings->multi_level, 'class="form-control tip" required="required" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('multi_biller', 'multi_biller'); ?>
                                <?php $multi_biller = ['1' => 'Yes', '0' => 'No']; ?>
                                <?= form_dropdown('multi_biller', $multi_biller, $Settings->multi_biller, 'class="form-control tip" required="required"'); ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('multi_warehouse', 'multi_warehouse'); ?>
                                <?php $multi_warehouse = ['1' => 'Yes', '0' => 'No']; ?>
                                <?= form_dropdown('multi_warehouse', $multi_warehouse, $Settings->multi_warehouse, 'class="form-control tip" required="required"'); ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('stock_using', 'stock_using'); ?>
                                <?php $multi_warehouse = ['0' => 'No','1' => 'Yes']; ?>
                                <?= form_dropdown('stock_using', $multi_warehouse, $Settings->stock_using, 'class="form-control tip" required="required"'); ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label" for="sale_consignment"><?= lang("sale_consignment"); ?></label>
                                <div class="controls">
                                    <?php
                                    $sale_consignment = [1 => lang('yes'), 0 => lang('no')];
                                    echo form_dropdown('sale_consignment', $sale_consignment, $Settings->sale_consignment, 'class="form-control tip" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('quotation', 'quotation'); ?>
                                <?php $multi_warehouse = ['0' => 'No','1' => 'Yes']; ?>
                                <?= form_dropdown('quotation', $multi_warehouse, $Settings->quotation, 'class="form-control tip" required="required"'); ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('sale_order', 'sale_order'); ?>
                                <?php $multi_warehouse = ['0' => 'No','1' => 'Yes']; ?>
                                <?= form_dropdown('sale_order', $multi_warehouse, $Settings->sale_order, 'class="form-control tip" required="required"'); ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('monthly_auto_invoice', 'monthly_auto_invoice'); ?>
                                <?php $monthly_auto_invoice = ['0' => 'No','1' => 'Yes']; ?>
                                <?= form_dropdown('monthly_auto_invoice', $monthly_auto_invoice, $Settings->monthly_auto_invoice, 'class="form-control tip" required="required"'); ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('purchase_request', 'purchase_request'); ?>
                                <?php $multi_warehouse = ['0' => 'No','1' => 'Yes']; ?>
                                <?= form_dropdown('purchase_request', $multi_warehouse, $Settings->purchase_request, 'class="form-control tip" required="required"'); ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('purchase_order', 'purchase_order'); ?>
                                <?php $multi_warehouse = ['0' => 'No','1' => 'Yes']; ?>
                                <?= form_dropdown('purchase_order', $multi_warehouse, $Settings->purchase_order, 'class="form-control tip" required="required"'); ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label"><?= lang('sale_man') . ' or ' . lang('agency'); ?></label>
                                <div class="controls">
                                    <?php
                                    $opt = [1 => lang('yes'), 0 => lang('no')];
                                    echo form_dropdown('sale_man', $opt, $Settings->sale_man, 'class="form-control tip"  required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label"><?= lang('commission'); ?></label>
                                <div class="controls">
                                    <?php
                                    $opt = [1 => lang('yes'), 0 => lang('no')];
                                    echo form_dropdown('commission', $opt,(isset($_POST['commission']) ? $_POST['commission'] : $Settings->commission), 'class="form-control tip"  required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label"><?= lang('maintenance'); ?></label>
                                <div class="controls">
                                    <?php
                                    $opt = [1 => lang('yes'), 0 => lang('no')];
                                    echo form_dropdown('maintenance', $opt,(isset($_POST['maintenance']) ? $_POST['maintenance'] : $Settings->maintenance), 'class="form-control tip"  required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label"><?= lang('delivery'); ?></label>
                                <div class="controls">
                                    <?php
                                    $opt = [1 => lang('yes'), 0 => lang('no')];
                                    echo form_dropdown('delivery', $opt,(isset($_POST['delivery']) ? $_POST['delivery'] : $Settings->delivery), 'class="form-control tip"  required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
						<div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label"><?= lang('driver'); ?></label>
                                <div class="controls">
                                    <?php
                                    $opt = [1 => lang('yes'), 0 => lang('no')];
                                    echo form_dropdown('driver', $opt,(isset($_POST['driver']) ? $_POST['driver'] : $Settings->driver), 'class="form-control tip"  required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php //if($Settings->module_school){ ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label"><?= lang('school_level'); ?></label>
                                <div class="controls">
                                    <?php
                                    $opt = [1 => lang('university'), 0 => lang('under_university')];
                                    echo form_dropdown('school_level', $opt,(isset($_POST['school_level']) ? $_POST['school_level'] : $Settings->school_level), 'class="form-control" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php //} ?>

                    </fieldset>
                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border"><?= lang('license_key') ?></legend>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang('user_name', 'user_name'); ?>
                                <?php echo form_password('user_name',(isset($_POST['user_name']) ? $_POST['user_name'] : ''), 'class="form-control" required="required"'); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang('license_key', 'license_key'); ?>
                                <?php echo form_password('license_key',(isset($_POST['license_key']) ? $_POST['license_key'] : ''), 'class="form-control" required="required"'); ?>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
            <div class="cleafix"></div>
            <div class="form-group">
                <div class="controls">
                    <?= form_submit('update_settings', lang('update_settings'), 'class="btn btn-primary btn-lg"'); ?>
                </div>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
 
</div>
</div>
