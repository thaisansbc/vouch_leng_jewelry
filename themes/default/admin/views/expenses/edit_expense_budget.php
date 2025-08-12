<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog  modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_expense_budget'); ?></h4>
        </div>
        <!-- 'data-toggle' => 'validator', -->
        <?php $attrib = ['role' => 'form'];
        echo admin_form_open_multipart('expenses/edit_expense_budget/' . $expense_budget->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <?php if ($Owner || $Admin || $GP['change_date']) {
            ?>
                <div class="form-group">
                    <?= lang('date', 'date'); ?>
                    <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->bpas->hrld($expense_budget->date)), 'class="form-control datetime" required="required"'); ?>
                </div>
            <?php } ?>
            <div class="form-group">
                <?= lang('reference', 'reference'); ?>
                <?= form_input('reference', (isset($_POST['reference']) ? $_POST['reference'] : $expense_budget->reference), 'class="form-control tip" id="reference" required="required"'); ?>
            </div>
            <?php if (($Owner || $Admin) || empty($user_billers)) { ?>
                <div class="form-group">
                    <?= lang("biller", "biller"); ?>
                    <?php
                    $bl[""] = "";
                    foreach ($billers as $biller) {
                        $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company . '/' . $biller->name : $biller->name;
                    }
                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $expense_budget->biller_id), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                    ?>
                </div>
            <?php } elseif (count($user_billers) > 1) { ?>
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
                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $expense_budget->biller_id), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                    ?>
                </div>
            <?php } else {
                $biller_input = array(
                    'type'  => 'hidden',
                    'name'  => 'biller',
                    'id'    => 'slbiller',
                    'value' => $expense_budget->biller_id,
                );
                echo form_input($biller_input);
            } ?>

            <?php if($this->Settings->project) {?>
            <div class="form-group">
                <?= lang("project", "project"); ?>
                <?php
                $project_id = $expense_budget->project_id;
                $pro[""] = "---Please select---";
                foreach ($projects as $project) {
                    $pro[$project->project_id] = $project->project_name;
                }
                echo form_dropdown('project', $pro, (isset($_POST['project']) ? $_POST['project'] : $project_id), 'data-placeholder="' . lang("select") . ' ' . lang("project") . '" class="form-control input-tip select" style="width:100%;"');
                ?>
            </div>
            <?php } ?>
            <!-- <div class="form-group">
                <?= lang('category', 'category'); ?>
                <?php
                $ct[''] = lang('select') . ' ' . lang('category');
                if ($categories) {
                    // foreach ($categories as $category) {
                    //     $ct[$category->id] = $category->name;
                    // }
                    foreach ($categories as $key => $category) {
                        if($category->parent_id == 0){
                            $ct[$category->id] = ($category->parent_id !=0) ? $category->name: $category->name;
                            foreach ($categories as $key => $cate) {
                                if ($category->id == $cate->parent_id) {
                                    $ct[$cate->id] = "&emsp;" . $cate->name;
                                }
                            }
                        }
                    }
                } ?>
                <?= form_dropdown('category', $ct, set_value('category', $expense_budget->category_id), 'class="form-control tip" id="category"'); ?>
            </div> -->

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
                    echo formMultiLevelCategory($nest_categories, 1, '', $expense_budget->category_id); ?>
                </div>
            </div>

            <div class="form-group">
                <?= lang('budget', 'budget'); ?>
                <?php
                $bg[''] = lang('select') . ' ' . lang('budget');
                if ($budgets) {
                    foreach ($budgets as $budget) {
                        $amt = 0;
                        $amt_balance = 0;
                        $b_name = "";
                        if($expenses){
                            foreach ($expenses as $expense) {
                                if($budget->id == $expense->budget_id){
                                    $amt += $expense->amount;    
                                }
                            }
                        }
                        if($budget->biller_id){
                            foreach ($billers as $biller) {
                                if($budget->biller_id == $biller->id){
                                    $b_name = " - ";
                                    $b_name .= $biller->company != '-' ? $biller->company : $biller->name;
                                }
                            }
                        }
                        $amt_balance = $budget->amount - $amt;
                        $bg[$budget->id] = $budget->title . ' (' . $amt_balance . ') ' . $b_name;
                    }
                } ?>
                <?= form_dropdown('budget', $bg, set_value('budget', $expense_budget->budget_id), 'class="form-control tip" id="budget"'); ?>
            </div>
            <table width="100%" id="dynamic_field" border="0"> 
                <?php if ($this->Settings->module_account) {
                    $i = 0;
                    foreach ($expenseBudgetByReference as $value) {
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
                                        echo form_dropdown('paid_by[]', $acc_section, $value->bank_code, 'id="paid_by-' . $value->id . '" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("paid_by") . '" required="required" style="width:100%;" ');
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
                <div class="form-group hide">
                    <?= lang('budget_amount', 'budget_amount'); ?>
                    <input name="budget_amount" type="text" id="budget_amount" value="" class="pa form-control kb-pad amount"/>
                </div>
                <div class="form-group hide">
                    <?= lang('ex_rate', 'ex_rate'); ?>
                    <input name="ex_rate" type="text" id="ex_rate" value="<?= $ex_rate; ?>" class="pa form-control kb-pad amount"/>
                </div>

                <div class="form-group">
                    <?= lang('amount_usd', 'amount_usd') . '*'; ?>
                    <input name="amount_usd" type="text" id="amount_usd" value="<?= $this->bpas->formatDecimal($expense_budget->amount_usd); ?>" class="pa form-control kb-pad amount"/>
                </div>
                <div class="form-group">
                    <?= lang('amount_khm', 'amount_khm') . '*'; ?>
                    <input name="amount_khm" type="text" id="amount_khm" value="<?= $this->bpas->formatDecimal($expense_budget->amount_khm); ?>" class="pa form-control kb-pad amount"/>
                </div>
                <div class="form-group">
                    <?= lang('total_amount', 'amount') . ' (USD) ' . ' *' . lang('rate') . ': USD 1.00 = KHM ' . $ex_rate; ?>
                    <input name="amount[]" type="text" id="amount" value="<?= $this->bpas->formatDecimal($expense_budget->amount); ?>" class="pa form-control kb-pad amount" required="required" readonly />
                </div>
                <?php } ?>
            </table>
            <div class="form-group">
                <?= lang('attachment', 'attachment') ?>
                <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false" class="form-control file">
            </div>

            <div class="form-group">
                <?= lang('note', 'note'); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $expense_budget->note), 'class="form-control" id="note"'); ?>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_expense_budget', lang('edit_expense_budget'), 'class="btn btn-primary"'); ?>
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
            $('#row2' + button_id + '').remove();
        });
        $(document).on('click', '.btn_removes', function() {
            var button_id = $(this).attr("id");
            if (button_id != 1) {
                $('#row' + button_id + '').remove();
            } else {
                alert("The row can't removed!");
                return false;
            }
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        setBudgetBalance;
        $("#budget").change(setBudgetBalance);
        $(document).on('focus', '#amount_usd, #amount_khm, #amount', function () {
            $(this).select();
        });
        $(document).on('keypress', '#amount_usd, #amount_khm', function (event) {
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
        });
        $(document).on('focusout', '#amount_usd', function (event) {
            if($(this).val() == ''){
                $(this).val(0);
            }
            var amt_usd = parseFloat($(this).val());
            var amt_khm = parseFloat($("#amount_khm").val());
            var ex_rate = parseFloat($("#ex_rate").val());
            var budget_amount = parseFloat($("#budget_amount").val());

            var exchange_to_usd = amt_khm / ex_rate;
            var total_amount = exchange_to_usd + amt_usd;
            $("#amount").val(parseFloat(total_amount).toFixed(2));
            if($("#budget_amount").val() != ""){
                if(total_amount > budget_amount){
                    $(this).val(0);
                    $("#amount").val(parseFloat(exchange_to_usd).toFixed(2));
                    alert("Please input amount less than or equal budget balance is: " + budget_amount.toFixed(2));
                }
            }
            $(this).val(parseFloat($(this).val()).toFixed(2));
        });
        $(document).on('focusout', '#amount_khm', function (event) {
            if($(this).val() == ''){
                $(this).val(0);
            }
            var amt_khm = parseFloat($(this).val());
            var amt_usd = parseFloat($("#amount_usd").val());
            var ex_rate = parseFloat($("#ex_rate").val());
            var budget_amount = parseFloat($("#budget_amount").val());

            var exchange_to_usd = amt_khm / ex_rate;
            var total_amount = exchange_to_usd + amt_usd;
            $("#amount").val(parseFloat(total_amount).toFixed(2));
            if($("#budget_amount").val() != ""){
                if(total_amount > budget_amount){
                    $(this).val(0);
                    $("#amount").val(parseFloat(amt_usd).toFixed(2));
                    alert("Please input amount less than or equal budget balance is: " + budget_amount.toFixed(2));
                }
            } 
            $(this).val(parseFloat($(this).val()).toFixed(2));
        });
    });

    function setBudgetBalance(){
        var budget_id = $('#budget').val() ?  $('#budget').val() : '';
        $("#amount_khm").val(parseFloat(0).toFixed(2));
        $("#amount_usd").val(parseFloat(0).toFixed(2));
        $("#amount").val(parseFloat(0).toFixed(2));
        $("#budget_amount").val("");
        if(budget_id != ""){
            $.ajax({
                type: "get",
                url: site.base_url + "purchases/getBudgetBalanceByID_ajax/" + budget_id,
                dataType: "json",
                success: function (data) {
                    if(data != false){
                        if(data['budget_balance'] == 0){
                            alert("This budget is empty balance! Please choose other budget.");
                            $("#budget option:first").attr('selected','selected').trigger('change');
                            $("#budget_amount").val("");
                        } else {
                            $("#budget_amount").val(data['budget_balance']);
                        }
                    }
                },
            }).fail(function(xhr, error){
                console.debug(xhr); 
                console.debug(error);
            });
        }
    }
</script>