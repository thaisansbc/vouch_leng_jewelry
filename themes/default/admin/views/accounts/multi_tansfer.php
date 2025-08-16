<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('multi_tansfer'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("account/multi_tansfers/", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        <?= lang("date", "date"); ?>
                        <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control datetime" id="date" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <?= lang("reference_no", "slref"); ?>
                    <div style="float:left;width:100%;">
                        <div class="form-group">
                            <div class="input-group">  
                                    <?php echo form_input('reference_no', $reference ? $reference :"",'class="form-control input-tip" id="slref"'); ?>
                                    <input type="hidden"  name="temp_reference_no"  id="temp_reference_no" value="<?= $reference ? $reference :"" ?>" />
                                <div class="input-group-addon no-print" style="padding: 2px 5px;background-color:white;">
                                    <input type="checkbox" name="ref_status" id="ref_st" value="1" style="margin-top:3px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <?= lang("biller", "biller"); ?>
                        <?php
                        $bl[''] = lang('select');
                        foreach ($billers as $biller) {
                            $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                        }
                        echo form_dropdown('biller', $bl, '', 'class="form-control" id="posbiller" required="required"');
                        ?>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped">
                    <thead>
                    <tr>
                        <th style="width:15%;"><?= $this->lang->line("date"); ?></th>
                        <th style="width:15%;"><?= $this->lang->line("reference_no"); ?></th>
                        <th style="width:10%;"><?= $this->lang->line("paid_by"); ?></th>
                        <th style="width:15%;"><?= $this->lang->line("amount"); ?></th>
                        <th style="width:15%;"><?= $this->lang->line("bank_charge"); ?></th>
                        <th style="width:15%;"><?= $this->lang->line("bank_amount"); ?></th>
                        <td style="width:5%;"><i class="fa fa-trash-o"></i></td>
                    </tr>
                    </thead>
                    <tbody>
                    <?php 
                    if (!empty($combine_sales)) {
                        $total = 0;
                        $tamountpaid = 0;
                        foreach ($combine_sales as $combine_sale) { ?>
                            <tr class="row<?= $combine_sale->id ?>">
                                <td>
                                    <input type="hidden" name="item_payment_date[]" class="form-control" value="<?= $this->bpas->hrld($combine_sale->date); ?>">
                                    <?= $this->bpas->hrld($combine_sale->date); ?>
                                </td>
                                <td>
                                    <input type="hidden" name="item_payment_ref_no[]" class="form-control" value="<?= $combine_sale->reference_no; ?>">
                                    <?= $combine_sale->reference_no; ?>
                                </td>                           
                                <td>
                                    <input type="hidden" name="item_payment_paid_by[]" class="form-control" value="<?= $combine_sale->paid_by; ?>">
                                    <?= $combine_sale->paid_by; ?>
                                </td>
                                <td class="col-xs-3">
                                    <input type="hidden" name="payment_id[]" class="form-control" value="<?= $combine_sale->id; ?>" id="amount_">
                                    <input type="text" name="amount[]" class="amount_paid_line form-control" value="<?= $this->bpas->formatDecimal($combine_sale->amount); ?>">
                                </td>
                                <td>
                                    <div class="form-group bank_charge_amount_1">
                                        <input name="bank_charge_amount_1[]" class="pa form-control amount" type="text" value="0" >
                                    </div>
                                </td>
                                <td>
                                    <div class="payment">
                                        <div class="form-group bank_account_amount_1">
                                            <input name="bank_account_amount_1[]" type="text" value="<?= $this->bpas->formatDecimal($combine_sale->amount); ?>" class="pa form-control kb-pad amount" required="required"/>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align: center;cursor:default;">
                                    <i class="fa fa-2x remove_line hide">&times;</i>
                                </td>
                            </tr>
                        <?php 
                        $total += $combine_sale->amount;
                        $tamountpaid += $combine_sale->amount;
                        }
                    } else {
                        echo "<tr><td colspan='5'>" . lang('no_data_available') . "</td></tr>";
                        $total = 0;
                        $tamountpaid =0;
                    } ?>

                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td class="text-right"><strong><?= lang('total') ?></strong></td>
                            <td><strong id="total_balance"><?= $this->bpas->formatDecimal($total); ?></strong></td>
                            <td>
                                <strong id="total_bank_charge_amount" style="padding-left: 12px;"></strong>
                            </td>
                            <td>
                                <strong id="total_bank_account_amount" style="padding-left: 12px;"></strong>
                            </td>
                            <td style="text-align: center;cursor:default;"><i class="fa fa-trash-o"></i></td>
                        </tr>
                    </tfoot>
                    <input name="amount-paid" type="hidden" id="amount_1" value="<?= $this->bpas->formatDecimal($total); ?>"  readonly="readonly"​​ c​lass="form-control"/>
                </table>
            </div>
            <div class="row">

                <div class="col-sm-6" id="bank_acc1">
                    <div class="form-group">
                        <?= lang("bank_charge", "bank_charge"); ?>
                        <?php
                            $bank = array('' => '-- Select Bank Account --');
                            foreach($ExpenseAccounts as $bankAcc1) {
                                $bank[$bankAcc1->accountcode] = $bankAcc1->accountcode . ' | '. $bankAcc1->accountname;
                            }
                            echo form_dropdown('bank_charge', $bank, '', 'id="bank_charge" class="ba form-control kb-pad" required="required" data-bv-notempty="true"');
                        ?>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="payment">
                        <div class="form-group">
                            <?= lang('charge_amount', 'charge_amount'); ?>
                            <input name="bank_charge_amount" type="text" id="bank_charge_amount" value="0" 
                                class="form-control" required="required"/>
                        </div>
                    </div>
                </div>
            </div>
                
            <div class="row">
                <div class="col-sm-6" id="bank_acc">
                    <div class="form-group">
                        <?= lang("bank_account", "bank_account_1"); ?>
                        <?php
                            $bank = array('' => '-- Select Bank Account --');
                            foreach($bankAccounts as $bankAcc) {
                                $bank[$bankAcc->accountcode] = $bankAcc->accountcode . ' | '. $bankAcc->accountname;
                            }
                            echo form_dropdown('bank_account', $bank, '', 'id="bank_account_1" class="ba form-control bank_account" required="required" ');
                        ?>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="payment">
                        <div class="form-group">
                            <?= lang('transfer_amount', 'transfer_amount'); ?>
                            <input name="bank_account_amount" type="text" value="<?= $this->bpas->formatDecimal($total); ?>" class="pa form-control kb-pad amount" id="bank_account_amount" readyonly="readyonly" required="required"/>
                            <input type="hidden" value="<?= $this->bpas->formatDecimal($total); ?>" id="bank_total_amount"/>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-sm-6" id="bank_acc">
                    <div class="form-group">
                        <?= lang("bank_account2", "bank_account_1"); ?>
                        <?php
                            $bank = array('' => '-- Select Bank Account --');
                       
                            foreach($bankAccounts as $bankAcc) {
                                $bank[$bankAcc->accountcode] = $bankAcc->accountcode . ' | '. $bankAcc->accountname;
                            }
                            echo form_dropdown('bank_account2', $bank, '', 'id="bank_account2_1" class="ba form-control bank_account" ');
                        
                        ?>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="payment">
                        <div class="form-group">
                            <?= lang('transfer_amount2', 'transfer_amount'); ?>
                            <input name="bank_account_amount2" type="text" value="0" class="pa form-control kb-pad amount" id="bank_account_amount2" readyonly="readyonly" required="required"/>
                      
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6" id="bank_acc">
                    <div class="form-group">
                        <?= lang("bank_account3", "bank_account_1"); ?>
                        <?php
                            $bank = array('' => '-- Select Bank Account --');
                       
                            foreach($bankAccounts as $bankAcc) {
                                $bank[$bankAcc->accountcode] = $bankAcc->accountcode . ' | '. $bankAcc->accountname;
                            }
                            echo form_dropdown('bank_account3', $bank, '', 'id="bank_account3_1" class="ba form-control bank_account" ');
                        
                        ?>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="payment">
                        <div class="form-group">
                            <?= lang('transfer_amount3', 'transfer_amount'); ?>
                            <input name="bank_account_amount3" type="text" value="0" class="pa form-control kb-pad amount" id="bank_account_amount3" readyonly="readyonly" required="required"/>
                      
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6" id="bank_acc">
                    <div class="form-group">
                        <?= lang("bank_account4", "bank_account_1"); ?>
                        <?php
                            $bank = array('' => '-- Select Bank Account --');
                       
                            foreach($bankAccounts as $bankAcc) {
                                $bank[$bankAcc->accountcode] = $bankAcc->accountcode . ' | '. $bankAcc->accountname;
                            }
                            echo form_dropdown('bank_account4', $bank, '', 'id="bank_account4_1" class="ba form-control bank_account" ');
                        
                        ?>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="payment">
                        <div class="form-group">
                            <?= lang('transfer_amount4', 'transfer_amount'); ?>
                            <input name="bank_account_amount4" type="text" value="0" class="pa form-control kb-pad amount" id="bank_account_amount4" readyonly="readyonly" required="required"/>
                      
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 text-right">
                        <?= lang("total"); ?>
                    
                </div>
                <div class="col-sm-6">
                    <strong><div id="total_transfer"></div></strong>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="form-group">
                <?= lang("attachment", "attachment") ?>
                <input id="attachment" type="file" name="userfile" data-show-upload="false" data-show-preview="false"
                       class="form-control file">
            </div>
            <div class="form-group">
                <?= lang("note", "note"); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="note"'); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_payment', lang('add_payment'), 'class="btn btn-primary" id="add_submit"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['bpas'] = <?=$dp_lang?>;
</script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {

        var total_bank_charge_amount = 0;
        var total_bank_account_amount = 0;
        $(".bank_charge_amount_1 :input").each(function(){
            total_bank_charge_amount += parseFloat($(this).val()); 
        });
        $(".bank_account_amount_1 :input").each(function(){
            total_bank_account_amount += parseFloat($(this).val()); 
        });

        $("#total_bank_charge_amount").html(parseFloat(total_bank_charge_amount).toFixed(2));
        $("#total_bank_account_amount").html(parseFloat(total_bank_account_amount).toFixed(2));

        $(document).on('click', 'input[name="bank_charge_amount_1[]"], input[name="bank_account_amount_1[]"]' , function(){
            $(this).select();
        });

        $(document).on('change', '#bank_charge_amount', function () {
            var bank_charge_amount = $(this).val();
            var bank_account_amount = $("#bank_total_amount").val();
            $("#bank_account_amount").val(bank_account_amount - bank_charge_amount);
        });

        $(document).on('change', 'input[name="bank_charge_amount_1[]"]' , function(){
            var bank_total_amount = $('#bank_total_amount').val();
            var inputs = $(".bank_charge_amount_1 :input");
            var value = 0;

            inputs.each(function(item, index){  
                value += parseFloat($(this).val());            
            });
            $("#bank_charge_amount").val(parseFloat(value).toFixed(2));
            $("#total_bank_charge_amount").html(parseFloat(value).toFixed(2));
            $("#total_bank_account_amount").html(parseFloat(bank_total_amount - value).toFixed(2));
            
            var amount = $(this).closest('tr').find('input[name="amount[]"]').val();
            var back_charge = $(this).val();
            var bank_amount = parseFloat(amount - back_charge);
            $(this).closest('tr').find('input[name="bank_account_amount_1[]"]').val(parseFloat(bank_amount).toFixed(2));

            $('input[name="bank_account_amount"]').val(parseFloat(bank_total_amount - value).toFixed(2));
        });

        $(document).on('change', 'input[name="bank_account_amount_1[]"]' , function(){
            var bank_total_amount = $('#bank_total_amount').val();
            var inputs = $(".bank_account_amount_1 :input");
            var value = 0;

            inputs.each(function(item, index){
                value += parseFloat($(this).val());            
            });
            $("#bank_account_amount").val(parseFloat(value).toFixed(2));
            $("#total_bank_account_amount").html(parseFloat(value).toFixed(2));
            $("#total_bank_charge_amount").html(parseFloat(bank_total_amount - value).toFixed(2));

            var amount = $(this).closest('tr').find('input[name="amount[]"]').val();
            var bank_account = $(this).val();
            var bank_amount = parseFloat(amount - bank_account);
            $(this).closest('tr').find('input[name="bank_charge_amount_1[]"]').val(parseFloat(bank_amount).toFixed(2));

            $('input[name="bank_charge_amount"]').val(parseFloat(bank_total_amount - value).toFixed(2));
        });

        $(document).on('blur', 'input[name="bank_charge_amount_1[]"], input[name="bank_account_amount_1[]"]' , function(){
            $(this).val(parseFloat($(this).val()).toFixed(2));
        });

        $(document).on('change', '#bank_account_amount,#bank_account_amount2,#bank_account_amount3,#bank_account_amount4', function () {
            var bank_charge_amount = $("#bank_charge_amount").val();
            var bank_account_amount = $("#bank_account_amount").val();
            var bank_account_amount2 = $("#bank_account_amount2").val();
            var bank_account_amount3 = $("#bank_account_amount3").val();
            var bank_account_amount4 = $("#bank_account_amount4").val();

            $("#total_transfer").html(parseFloat(bank_charge_amount)+parseFloat(bank_account_amount)+parseFloat(bank_account_amount2)+parseFloat(bank_account_amount3)+parseFloat(bank_account_amount4));
        });

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
        
        /**=========================**/
        $('.amount').trigger("change");
        
        function formatDecimals(x) {
            return parseFloat(parseFloat(x).toFixed(7));
        }
        var code = 0;
        var value = 0;
        var rate = 0;
        function autotherMoney(value){
            $(".amount_other").each(function(){
                if(value != 0){
                    $(this).val(formatDecimals(value));
                }else{
                    $(this).val('0');
                }
            });
        }

        /**============================**/
        
    });
    
    function removeCommas(str) {
        while (str.search(",") >= 0) {
            str = (str + "").replace(',', '');
        }
        return Number(str);
    }

</script>
