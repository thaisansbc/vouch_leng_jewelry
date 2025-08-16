<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog  modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_depreciation'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('purchases/add_expense', $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <?php if ($Owner || $Admin) {
            ?>

                <div class="form-group">
                    <?= lang('date', 'date'); ?>
                    <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : ''), 'class="form-control datetime" id="date" required="required"'); ?>
                </div>
            <?php
            } ?>

            <div class="form-group">
                <?= lang('reference', 'reference'); ?>
                <?= form_input('reference', (isset($_POST['reference']) ? $_POST['reference'] : $exnumber), 'class="form-control tip" id="reference"'); ?>
            </div>
            <?php if ($Owner || $Admin) { ?>
                <div class="form-group">
                    <?= lang("biller", "biller"); ?>
                    <?php
                    foreach ($billers as $biller) {
                        $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                    }
                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'class="form-control" id="posbiller" required="required"');
                    ?>
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
            <?php if($this->Settings->project) {?>
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
            <?php } ?>
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
            <?php if ($Owner || $Admin) { ?>
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
            <?php if ($this->Settings->accounting) { ?>
                <table width="100%" id="dynamic_field" border="0">
                    <tr>
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
                            <div class="form-group bank_pay" style="margin:0 6px;">
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
                        <td>
                            <div class="form-group bank_pay" style="margin:0px;">
                                <?= lang('amount', 'amount'); ?>
                                <input name="amount[]" type="text" id="amount" value="" class="form-control kb-pad amount" required="required" />
                            </div>
                        </td>
                        <td>
                            <div class="form-group bank_pay"><?= lang("  ", " "); ?><button type="button" name="add" id="add" class="btn btn-success" style="margin-top:45px;">
                                    <li class="fa fa-plus"></li>
                                </button></div>
                        </td>
                    </tr>
                </table><br>
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

        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_expense', lang('add_expense'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['bpas'] = <?= $dp_lang ?>;
</script>
<?= $modal_js ?>
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
            $('#dynamic_field').append('<tr id="row' + i + '" class="dynamic-added"><td> <div class="form-group bank_pay" style="margin:0px;"><?= lang("category_expense    ", "category_expense"); ?>' + complex + '</div></td><td><div class="form-group bank_pay" style="margin:0 6px;"><?= lang("paid_by *", "paid_by"); ?>' + complex2 + '</div></td><td><div class="form-group bank_pay" style="margin:0px;"><?= lang('amount', 'amount'); ?><input name="amount[]" type="text" id="amount" value="" class="pa form-control kb-pad amount" required="required" /></div></td><td><div class="form-group bank_pay" "><button type="button" name="remove" id="' + i + '" class="btn btn-danger btn_remove" style="margin-top:45px;"><li class="fa fa-remove"></li></button></div></td></tr>');
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