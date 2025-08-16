<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog  modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_expense'); ?></h4>
        </div>
        <!-- 'data-toggle' => 'validator', -->
        <?php $attrib = ['role' => 'form'];
        echo admin_form_open_multipart('expenses/edit_expense/' . $expense->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-md-6">
                    <?php if ($Owner || $Admin || $GP['change_date']) {
                    ?>
                    <div class="form-group">
                        <?= lang('date', 'date'); ?>
                        <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->bpas->hrld($expense->date)), 'class="form-control '.($Settings->date_with_time ? 'datetime':'date').'" required="required"'); ?>
                    </div>
                    <?php
                    } ?>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('reference', 'reference'); ?>
                        <?= form_input('reference', (isset($_POST['reference']) ? $_POST['reference'] : $expense->reference), 'class="form-control tip" id="reference" required="required"'); ?>
                    </div>
                </div>
                <?php if (($Owner || $Admin) || empty($user_billers)) { ?>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang("biller", "biller"); ?>
                            <?php
                            $bl[""] = "";
                            foreach ($billers as $biller) {
                                $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company . '/' . $biller->name : $biller->name;
                            }
                            echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $expense->biller_id), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                            ?>
                        </div>
                    </div>
                <?php } elseif (count($user_billers) > 1) { ?>
                    <div class="col-md-6">
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
                            echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $expense->biller_id), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                            ?>
                        </div>
                    </div>
                <?php } else {
                    $biller_input = array(
                        'type'  => 'hidden',
                        'name'  => 'biller',
                        'id'    => 'slbiller',
                        'value' => $expense->biller_id,
                    );
                    echo form_input($biller_input);
                } ?>
                <div class="col-md-6">
                    <div class="form-group hide">
                        <?= lang('warehouse', 'warehouse'); ?>
                        <?php
                        $wh[''] = lang('select') . ' ' . lang('warehouse');
                        foreach ($warehouses as $warehouse) {
                            $wh[$warehouse->id] = $warehouse->name;
                        }
                        echo form_dropdown('warehouse', $wh, set_value('warehouse', $expense->warehouse_id), 'id="warehouse" class="form-control input-tip select" style="width:100%;" ');
                        ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="user"><?= lang('expense_by'); ?></label>
                        <?php
                        $us[''] = lang('select') . ' ' . lang('user');
                        foreach ($users as $user) {
                            $us[$user->id] = $user->first_name . ' ' . $user->last_name;
                        }
                        echo form_dropdown('expense_by', $us, set_value('expense_by', $expense->expense_by), 'class="form-control" id="user" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('user') . '"');
                        ?>
                    </div>
                </div>
                <?php if($this->Settings->project) {?>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang("project", "project"); ?>
                        <?php
                        $project_id = $expense->project_id;
                        $pro[""] = "---Please select---";
                        foreach ($projects as $project) {
                            $pro[$project->project_id] = $project->project_name;
                        }
                        echo form_dropdown('project', $pro, (isset($_POST['project']) ? $_POST['project'] : $project_id), 'data-placeholder="' . lang("select") . ' ' . lang("project") . '" class="form-control input-tip select" style="width:100%;"');
                        ?>
                    </div>
                </div>
                <?php } ?>
                <!-- <div class="col-md-6">
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
                        <?= form_dropdown('category', $ct, set_value('category', $expense->category_id), 'class="form-control tip" id="category"'); ?>
                    </div>
                </div> -->

                <div class="col-md-6">
                    <div class="form-group all">
                        <?= lang('category', 'category') ?>
                        <div class="input-group" style="width: 100%">
                            <?php 
                            $form_category = null;
                            function formMultiLevelCategory($data, $n, $str = '', $p_category_id)
                            {
                                $form_category = ($n ? '<select id="category" name="category" class="form-control select" style="width: 100%" placeholder="' . lang('select') . ' ' . lang('category') . '" required="required"><option value="" selected>' . lang('select') . ' ' . lang('category') . '</option>' : '');
                                foreach ($data as $key => $categories) {
                                    if (!empty($categories->children)) {
                                        $form_category .= '<option disabled>' . $str . $categories->name . '</option>';
                                        $form_category .= formMultiLevelCategory($categories->children, 0, ($str.'&emsp;&emsp;'), $p_category_id);
                                    } else {
                                        if ($p_category_id == $categories->id) 
                                            $form_category .= ('<option value="' . $categories->id . '" selected>' . $str . $categories->name . '</option>');
                                        else 
                                            $form_category .= ('<option value="' . $categories->id . '">' . $str . $categories->name . '</option>');
                                    }
                                }
                                $form_category .= ($n ? '</select>' : '');
                                return $form_category;
                            }
                            // echo htmlentities(formMultiLevelCategory($nest_categories, 1));
                            echo formMultiLevelCategory($nest_categories, 1, '', $expense->category_id); ?>
                        </div>
                    </div>
                </div>
            </div>

            <table width="100%" id="dynamic_field" border="0"> 
                <?php if ($this->Settings->module_account) {
                    $i = 0;
                    foreach ($expenseByReference as $value) {
                        $i++; ?>
                        <tr id="row<?= $i; ?>" class="dynamic-added">
                            <td>
                                <div class="form-group" style="margin:0px;">
                                    <?= lang("category_expense", "category_expense"); ?>
                                    <?php
                                            $bank = array('0' => '-- Select Bank Account --');
                                            foreach ($bankAccounts as $bankAcc) {
                                                $bank[$bankAcc->accountcode] = $bankAcc->accountcode . ' | ' . $bankAcc->accountname;
                                            }
                                            echo form_dropdown('bank_account[]', $bank, (($value->bank_account) ? $value->bank_account : ''), 'id="bank_account_1-' . $value->id . '" class="ba form-control kb-pad bank_account" data-bv-notempty="true"');
                                    ?>
                                    <input name="id[]" type="hidden" id="id-.'<?= $value->id ?>'." value="<?= $value->id ?>" class="pa form-control kb-pad id" />
                                </div>
                            </td>
                            <td>
                                <div class="form-group" style="margin:0 6px;">
                                    <?= lang("paid_by*", "paid_by"); ?>
                                    <?php
                                        $acc_section = array("" => "");
                                        foreach ($paid_by as $section) {
                                            $acc_section[$section->accountcode] = $section->accountcode . ' | ' . $section->accountname;
                                        }
                                        echo form_dropdown('paid_by[]', $acc_section, $value->account_paying_from, 'id="paid_by-' . $value->id . '" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("paid_by") . '" required="required" style="width:100%;" ');
                                    ?>
                                </div>
                            </td>
                            <td>
                                <div class="form-group" style="margin:0px;">
                                    <?= lang('amount*', 'amount'); ?>
                                    <input name="amount[]" type="text" id="amount-.'<?= $value->id ?>'." value="<?= $this->bpas->formatDecimal($value->amount); ?>" class="pa form-control kb-pad amount" required="required" />
                                </div>
                            </td>
                            <td>
                                <div class="form-group"><?= lang("  ", " "); ?><button type="button" name="remove" id="<?= $i ?>" class="btn btn-danger btn_removes" style="margin-top:45px;">
                                        <li class="fa fa-remove"></li>
                                    </button></div>
                            </td>
                        </tr>
                        <button type="button" name="add" id="add" class="btn btn-success pull-right" style="margin:5px;">
                            <li class="fa fa-plus"></li>
                        </button>
                <?php }
                } else { ?>
                <div class="row">
                    <div class="col-md-6 hide">
                        <div class="form-group">
                            <?= lang("currency", "currency"); ?>
                            <?php
                            foreach ($currencies as $cur) {
                                $cu[$cur->code] = $cur->name;
                            }
                            echo form_dropdown('currency', $cu, (isset($_POST['currency']) ? $_POST['currency'] : $expense->currency), 'class="form-control tip" id="currency" style="width:100%;"'); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang('amount', 'amount'); ?>
                            <input name="amount[]" type="text" value="<?= $this->bpas->formatDecimal($expense->amount); ?>" class="pa form-control kb-pad amount" required="required" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang('paid_by', 'paid_by'); ?>
                            <?php
                            foreach ($cash_accounts as $cash_account) {
                                $cash[$cash_account->code] = $cash_account->name;
                            }
                            echo form_dropdown('paid_by[]', $cash, $expense->paid_by, 'class="form-control tip" id="currency" required="required" style="width:100%;"'); ?>
                        </div>
                    </div>
                    <?php
                        foreach($currency as $money){
                            if($money->code != 'USD'){
                    ?>
                        <div class="form-group hide">
                            <?= lang("amount", "amount").($money->code == 'USD' ? ' (USD)' : ' (Rate: USD1 = '.$money->code.' '.number_format($money->rate).')'); ?>
                            <input name="other_amount[]" type="text" id="<?=$money->code;?>" value="<?= $this->bpas->formatDecimal(($expense->amount) * ($money->rate)); ?>" rate="<?=$money->rate?>" class="pa form-control kb-pad amount_other"/>
                        </div>
                    <?php
                            }
                        }
                    ?>
                </div>
                <?php
                }
                ?>
                
            </table>
            <div class="form-group">
                <?= lang('attachment', 'attachment') ?>
                <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false" class="form-control file">
            </div>

            <div class="form-group">
                <?= lang('note', 'note'); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $expense->note), 'class="form-control" id="note"'); ?>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_expense', lang('edit_expense'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['sma'] = <?= $dp_lang ?>;
</script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function() {
        $.fn.datetimepicker.dates['sma'] = <?= $dp_lang ?>;
        $("#date").datetimepicker({
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
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        var i = 1;
        <?php
        $bank = array('0' => '-- Select Bank Account --');
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
             $('#dynamic_field').append('<tr id="row2' + i + '" class="dynamic-added"><td> <div class="form-group" style="margin:0px;"><?= lang("category_expense    ", "category_expense"); ?>' + complex + '<input name="id[]" type="hidden" id="id-.' +
                 i + '." value="" class="pa form-control kb-pad id" /></div></td><td><div class="form-group" style="margin:0 6px;"><?= lang("paid_by *", "paid_by"); ?>' + complex2 + '</div></td><td><div class="form-group" style="margin:0px;"><?= lang('amount *', 'amount'); ?><input name="amount[]" type="number" id="amount" value="" class="pa form-control kb-pad amount" required="required" /></div></td><td><div class="form-group" "><button type="button" name="remove" id="' + i + '" class="btn btn-danger btn_remove" style="margin-top:45px;"><li class="fa fa-remove"></li></button></div></td></tr>');
             i++;
        });
        $(document).on('click', '.btn_remove', function() {
            var button_id = $(this).attr("id");
            // if (confirm("Are You Sure?")) {
                $('#row2' + button_id + '').remove();
            //     return true;
            // } else {
            //     return false;
            // }
        });
        $(document).on('click', '.btn_removes', function() {
            var button_id = $(this).attr("id");
            if (button_id != 1) {
                // if (confirm("Are You Sure?")) {
                    $('#row' + button_id + '').remove();
                //     return true;
                // } else {
                //     return false;
                // }
            } else {
                alert("The row can't removed!");
                return false;
            }
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