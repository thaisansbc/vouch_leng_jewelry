<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i>
            <?php echo lang('add_expense_asset'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>

                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('assets/add_expense', $attrib)
                ?>
                    <?php if ($Owner || $Admin) {
                    ?>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <?= lang('date', 'date'); ?>
                            <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : date('d/m/Y h:i')), 'class="form-control datetime" id="date" required="required"'); ?>
                        </div>
                    </div>
                    <?php
                    } ?>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <?= lang('reference', 'reference'); ?>
                            <?= form_input('reference', (isset($_POST['reference']) ? $_POST['reference'] : $exnumber), 'class="form-control tip" id="reference"'); ?>
                        </div>
                    </div>
                    <?php if ($Owner || $Admin) { ?>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <?= lang("biller", "biller"); ?>
                            <?php
                            foreach ($billers as $biller) {
                                $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                            }
                            echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'class="form-control" id="posbiller" required="required"');
                            ?>
                        </div>
                    </div>
                    <?php } else {
                        $biller_input = array(
                            'type' => 'hidden',
                            'name' => 'biller',
                            'id' => 'posbiller',
                            'value' => $this->session->userdata('biller_id'),
                        );

                        echo form_input($biller_input);
                    }
                    ?>
                    <?php /*if($this->Settings->project) {?>
                    <div class="form-group">
                        <?= lang("project", "project"); ?>
                        <div class="input-group" style="width:100%">
                            <SELECT class="form-control input-tip select" name="project" style="width:100%;">
                                <option value="">--Please Select--</option>
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
                        </div>
                    </div>
                    <?php } */?>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <?= lang('category', 'category'); ?>
                            <?php
                            $ct[''] = lang('select') . ' ' . lang('category');
                            if ($categories) {
                                foreach ($categories as $category) {
                                    $ct[$category->id] = $category->name;
                                }
                            }
                            ?>
                            <?= form_dropdown('category', $ct, set_value('category'), 'class="form-control tip" id="category"'); ?>
                        </div>
                    </div>
                    <?php if ($Owner || $Admin) { ?>
                        <div class="col-sm-4 hide">
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
                                </div>
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
                    } 
                    ?>
                    <div class="col-md-4 hide">
                        <div class="form-group">
                            <label class="control-label" for="restrict_calendar"><?= lang('type'); ?></label>
                            <div class="controls">
                                <?php
                                $opt_cal = [0 => lang('yearly'), 1 => lang('monthly')];
                                echo form_dropdown('type', $opt_cal, 1, 'class="form-control tip" required="required" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?php echo lang('useful', 'useful'); ?>
                            <div class="controls">
                                <?php echo form_input('useful',(isset($_POST['useful']) ? $_POST['useful'] : ''), 'id="useful" class="form-control" required="required"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?php echo lang('residual_value', 'residual_value'); ?>
                            <div class="controls">
                                <?php echo form_input('residual_value', (isset($_POST['residual_value']) ? $_POST['residual_value'] : 0), 'id="residual_value" class="form-control" required="required"'); ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($this->Settings->accounting) { ?>
                    <div class="well well-sm well_1">
                        <table width="100%" id="dynamic_field" border="0">
                            <tr>
                                <td>
                                    <div class="form-group bank_pay" style="margin:0 5px;">
                                        <?= lang("asset_name", "asset"); ?>
                                        <?php
                                        $asset1 = array('0' => '-- Select Bank Account --');
                                        foreach ($get_assets as $asset) {
                                            $asset1[$asset->id] = $asset->name;
                                        }
                                        echo form_dropdown('asset_name[]', $asset1, '', 'id="asset_name" class="ba form-control kb-pad bank_account"');
                                        ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group bank_pay" style="margin:0 5px;">
                                        <?= lang('quantity', 'quantity'); ?>
                                        <input name="quantity[]" type="text" id="quantity" value="1" class="form-control kb-pad amount" required="required" />
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group bank_pay" style="margin:0 5px;">
                                        <?= lang('cost', 'purchase_cost'); ?>
                                        <input name="amount[]" type="text" id="amount" value="" class="form-control kb-pad amount" required="required" />
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group bank_pay" style="margin:0px;">
                                        <?= lang("category_expense", "category_expense"); ?>
                                    
                                        <?php
                                            $bank = array('0' => '-- Select Bank Account --');
                                            foreach ($bankAccounts as $bankAcc) {
                                                $bank[$bankAcc->accountcode] = $bankAcc->accountcode . ' | ' . $bankAcc->accountname;
                                            }
                                            echo form_dropdown('bank_account[]', $bank, '', 'id="bank_account_1" class="ba form-control kb-pad bank_account" data-bv-notempty="true"');
                                            ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group bank_pay" style="margin:0 5px;">
                                        <?= lang("paid_by", "paid_by"); ?>
                                        <?php
                                        $acc_section = array("" => "");
                                        foreach ($paid_by as $section) {
                                            $acc_section[$section->accountcode] = $section->accountcode . ' | ' . $section->accountname;
                                        }
                                        echo form_dropdown('paid_by[]', $acc_section, '', 'id="paid_by"  class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("paid_by") . '" required="required" style="width:100%;" ');
                                        ?>
                                    </div>
                                </td>
                                
                                <td class="hide">
                                    <div class="form-group bank_pay"><?= lang("  ", " "); ?><button type="button" name="add" id="add" class="btn btn-success" style="margin-top:30px;">
                                            <li class="fa fa-plus"></li>
                                        </button></div>
                                </td>
                            </tr>
                        </table><br>
                    </div>
                    <?php } else { ?>
                    <div class="form-group">
                        <?= lang("currency", "currency"); ?>
                        <?php
                        foreach ($currencies as $cur) {
                            $cu[$cur->code] = $cur->name;
                        }
                        echo form_dropdown('currency', $cu, (isset($_POST['currency']) ? $_POST['currency'] : $Settings->default_currency), 'class="form-control tip" id="currency" style="width:100%;"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('amount', 'amount'); ?>
                        <input name="amount[]" type="text" id="amount" value="" class="pa form-control kb-pad amount" required="required" />
                    </div>
                    <?php
                        foreach($currency as $money){
                            if($money->code == 'USD'){

                            }else{ ?>
                        <div class="form-group">
                            <?= lang("amount", "amount").($money->code == 'USD' ? ' (USD)' : ' (Rate: USD1 = '.$money->code.' '.number_format($money->rate).')'); ?>
                            <input name="other_amount[]" type="text" id="<?=$money->code;?>" value="" rate="<?=$money->rate?>" class="pa form-control kb-pad amount_other"/>
                        </div>
                    <?php
                            }
                        }
                    ?>
                <?php
                } ?>
                <div class="form-group">
                    <?= lang('attachment', 'attachment') ?>
                    <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false" class="form-control file">
                </div>

                <div class="form-group">
                    <?= lang('note', 'note'); ?>
                    <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ''), 'class="form-control" id="note"'); ?>
                </div>
                
                <div class="col-md-12">
                    <div class="from-group"><?php echo form_submit('add_expense', $this->lang->line('submit'), 'id="add_expense" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                        <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></button>
                    </div>
                </div>
                <?= form_close(); ?>

            </div>

        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['bpas'] = <?= $dp_lang ?>;
</script>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function() {
        $.fn.datetimepicker.dates['bpas'] = <?= $dp_lang ?>;
        $("#date").datetimepicker({
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
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        var i = 1;
        <?php
        $bank = array('' => '-- Select Bank Account --');
        foreach ($bankAccounts as $bankAcc) {
            $bank[$bankAcc->accountcode] = $bankAcc->accountcode . ' | ' . $bankAcc->accountname;
        }
        $dropdown = form_dropdown('bank_account[]', $bank, '', 'id="bank_account_1" class="ba form-control kb-pad bank_account" data-bv-notempty="true"');
        $acc_section = array("" => "-- Select Paid By --");
        foreach ($paid_by as $section) {
            $acc_section[$section->accountcode] = $section->accountcode . ' | ' . $section->accountname;
        }
        $dropdown2 = form_dropdown('paid_by[]', $acc_section, '', 'id="paid_by" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("paid_by") . '" required="required" style="width:100%;" ');
        ?>
        var complex = <?php echo json_encode($dropdown); ?>;
        var complex2 = <?php echo json_encode($dropdown2); ?>;
        $('#add').click(function() {
            $('#dynamic_field').append('<tr id="row' + i + '" class="dynamic-added"><td> <div class="form-group bank_pay" style="margin:0px;"><?= lang("category_expense    ", "category_expense"); ?>' + complex + '</div></td><td><div class="form-group bank_pay" style="margin:0 6px;"><?= lang("paid_by *", "paid_by"); ?>' + complex2 + '</div></td><td><div class="form-group bank_pay" style="margin:0px;"><?= lang('amount', 'amount'); ?><input name="amount[]" type="text" id="amount" value="" class="pa form-control kb-pad amount" required="required" /></div></td><td><div class="form-group bank_pay" "><button type="button" name="remove" id="' + i + '" class="btn btn-danger btn_remove" style="margin-top:30px;"><li class="fa fa-remove"></li></button></div></td></tr>');
            i++;
        });
        $(document).on('click', '.btn_remove', function() {
            var button_id = $(this).attr("id");
            $('#row' + button_id + '').remove();
        });

        //-------------currency--------
        var code = 0;
        var value = 0;
        var rate = 0;
        function autotherMoney(value){
            $(".amount_other").each(function(){
                var rate = $(this).attr('rate');
                if(value != 0){
                    $(this).val(formatDecimals(value*rate));
                }else{
                    $(this).val('0');
                }
            });
        }
        function autoMoney(value, rate){
            $(".amount_other").each(function(){
                if(value != 0){
                    $('input[name="amount[]"]').val(formatDecimals(value / rate));
                }else{
                    $('input[name="amount[]"]').val('0');
                }
            });
        }
        $('input[name="amount[]"]').change('change keyup paste',function(){
            value = $(this).val();
            autotherMoney(value);
        });
        $('input[name="other_amount[]"]').change('change keyup paste',function(){
            value = $(this).val();
            rate = $(this).attr('rate');
            var val = value / rate;
            autoMoney(value, rate);
            autotherMoney(val);
        });
    });
</script>
