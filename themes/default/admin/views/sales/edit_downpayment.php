<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content" style="width:750px;">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_downpayment'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('sales/edit_Downpayment/' . $payments->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <?php //if ($Owner || $Admin) { ?>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <?= lang('date', 'date'); ?>
                            <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->bpas->hrld($payments->created_at)), 'class="form-control datetime" id="date" required="required"'); ?>
                        </div>
                    </div>
                <?php //} ?>
                <div class="col-sm-6">
                    <div class="form-group">
                        <?= lang('balance_amount', 'total_payable_usd'); ?>
                        <input name="total_payable_usd" type="text" readonly id="total_payable_usd" value="<?= $this->bpas->formatDecimal($inv->grand_total - $inv->paid); ?>" class="pa form-control kb-pad amount" required="required"/>
                    </div>
                </div>
                <div class="col-sm-6 hide">
                    <div class="form-group">
                        <?= lang('status', 'status'); ?>
                        <?php $dpst = ['0' => lang('pending'),'1'=>lang('paid')];
                        echo form_dropdown('down_payment_status', $dpst, $payments->status , 'class="form-control input-tip" id="down_payment_status"'); ?>
                    </div>
                </div>

                <input type="hidden" value="<?php echo $inv->id; ?>" name="sale_id"/>
            </div>
            <div class="clearfix"></div>
            <div id="payments">
                <?php 
                    $total_payable_usd = 0;
                    $total_payable_khr = 0;
                    $total_payable_thb = 0;

                    $count_amount_dpm=sizeof($get_downpayment);
                    $last_amount_dpm=end($get_downpayment);
                    $last_downpayment_amount= $last_amount_dpm->total_amount;
                    $n=0;
                    for($i=0 ; $i < $count_amount_dpm;$i++){
                        $n +=  (float) ($get_downpayment[$i]->total_amount);
                    }
                    $amount_dmp = $n - $last_downpayment_amount;
                    
                    if($count_amount_dpm > 1){
                        $total_payable_usd = $inv->total - $amount_dmp;
                    }else{
                        $total_payable_usd = $inv->total;
                    }


                ?>

                <div class="well well-sm well_1">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <?= lang('title', 'title'); ?>
                                    <?php echo form_input('title', (isset($_POST['title']) ? $_POST['title'] : $payments->title), 'class="form-control" id="title"'); ?>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <?= lang(lang('amount'), 'amount_paid_usd'); ?>
                                    <input name="amount_paid_usd" type="text" id="amount_paid_usd" value="<?= $payments->percent;?>" data="" class="pa form-control kb-pad amount"/>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="type"><?= lang('type'); ?></label>
                                    <?php
                                    $type = ['down_payment' => lang('down_payment'), 'installment' => lang('installment')];
                                    echo form_dropdown('type', $type, $payments->type, 'class="form-control" style="width:100%;" required="required"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang('payment_date', 'payment_date'); ?>
                                    <?= form_input('payment_date', (isset($_POST['payment_date']) ? $_POST['payment_date'] : $this->bpas->hrsd($payments->payment_date)), 'class="form-control date" id="payment_date" required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <hr style="margin-top: 1rem; margin-bottom: 1rem; border: 0; border-top: 1px solid rgba(0,0,0,0.1);">
                            </div>
                        </div>
                  

                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>

            <div class="form-group">
                <?= lang('note', 'note'); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $payments->description), 'class="form-control" id="note"'); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_Downpayment', lang('Edit_Downpayment'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript">
    $('#MyModal').on('hidden.bs.modal', function () {
        $(this).find('form').trigger('reset');
    })
</script>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
</script>
<?= $modal_js ?>
<script type="text/javascript">
    var deposit = jQuery.parseJSON('<?php echo json_encode($deposit); ?>');

</script>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
        $('.paid_by').attr('data', $('.paid_by').find(":selected").val());
        $(document).on('change', '#gift_card_no', function () {
            var cn = $(this).val() ? $(this).val() : '';
            if (cn != '') {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url + "sales/validate_gift_card/" + cn,
                    dataType: "json",
                    success: function (data) {
                        if (data === false) {
                            $('#gift_card_no').parent('.form-group').addClass('has-error');
                            bootbox.alert('<?=lang('incorrect_gift_card')?>');
                        } else if (data.customer_id !== null && data.customer_id != <?=$inv->customer_id?>) {
                            $('#gift_card_no').parent('.form-group').addClass('has-error');
                            bootbox.alert('<?=lang('gift_card_not_for_customer')?>');
                        } else {
                            var due = <?=$inv->grand_total - $inv->paid?>;
                            if (due > data.balance) {
                                $('#amount_1').val(formatDecimal(data.balance));
                            }
                            $('#gc_details').html('<small>Card No: <span style="max-width:60%;float:right;">' + data.card_no + '</span><br>Value: <span style="max-width:60%;float:right;">' + currencyFormat(data.value) + '</span><br>Balance: <span style="max-width:60%;float:right;">' + currencyFormat(data.balance) + '</span></small>');
                            $('#gift_card_no').parent('.form-group').removeClass('has-error');
                        }
                    }
                });
            }
        });

        $(document).on('change', '.paid_by', function () {
            var p_val = $(this).val();
            $('#rpaidby').val(p_val);
            if (p_val == 'cash') {
                $('.pcheque_1').hide();
                $('.pcc_1').hide();
                $('.pcash_1').show();
                $('#amount_1').focus();
            } else if (p_val == 'CC') {
                $('.pcheque_1').hide();
                $('.pcash_1').hide();
                $('.pcc_1').show();
                $('#pcc_no_1').focus();
            } else if (p_val == 'Cheque') {
                $('.pcc_1').hide();
                $('.pcash_1').hide();
                $('.pcheque_1').show();
                $('#cheque_no_1').focus();
            } else {
                $('.pcheque_1').hide();
                $('.pcc_1').hide();
                $('.pcash_1').hide();
            }
            if (p_val == 'gift_card') {
                $('.gc').show();
                $('#gift_card_no').focus();
            }  else {
                $('.gc').hide();
            }

            var deposit_usd = 0;
            var deposit_khr = 0;
            var deposit_thb = 0;
            var total_deposit = 0;
            var ex_rate_usd = parseFloat($('input[name="exchange_rate_usd"]').val());
            var ex_rate_khr = parseFloat($('input[name="exchange_rate_khr"]').val());
            var ex_rate_thb = parseFloat($('input[name="exchange_rate_thb"]').val());
            var payable     = parseFloat($('#total_payable_usd').val());

            if (p_val == 'deposit') {
                if(!deposit || parseFloat(deposit['amount']) == 0) {
                    $(this).val($('.paid_by').attr('data'));
                    $('.paid_by').find('span').first().text($(this).find(":selected").text());
                    alert("This customer don't have deposit yet!");
                } else {
                    deposit_usd   = parseFloat(deposit['amount_usd']) > 0 ? parseFloat(deposit['amount_usd']) : 0;
                    deposit_khr   = parseFloat(deposit['amount_khr']) > 0 ? parseFloat(deposit['amount_khr']) : 0;
                    deposit_thb   = parseFloat(deposit['amount_thb']) > 0 ? parseFloat(deposit['amount_thb']) : 0;
                    total_deposit = parseFloat(deposit['amount']);
                    $('.paid_by').attr('data', $('.paid_by').find(":selected").val());
                    if(payable >= total_deposit){
                        $('#amount_paid_usd').val(parseFloat(deposit_usd).toFixed(2));
                        $('#amount_paid_khr').val(parseFloat(deposit_khr).toFixed(2));
                        $('#amount_paid_thb').val(parseFloat(deposit_thb).toFixed(2));
                    } else {
                        if(payable <= deposit_usd){
                            $('#amount_paid_usd').val(parseFloat(payable).toFixed(2));
                            $('#amount_paid_khr').val(parseFloat('0').toFixed(2));
                            $('#amount_paid_thb').val(parseFloat('0').toFixed(2));
                        } else {
                            if(payable <= (deposit_usd + (deposit_khr / ex_rate_khr))){
                                var tmp_1 = (payable - deposit_usd) * ex_rate_khr;
                                var tmp_2 = (tmp_1 - deposit_khr) + deposit_khr;
                                $('#amount_paid_usd').val(parseFloat(deposit_usd).toFixed(2));
                                $('#amount_paid_khr').val(parseFloat(tmp_2).toFixed(2));
                                $('#amount_paid_thb').val(parseFloat('0').toFixed(2));
                            } else {
                                if(payable <= (deposit_usd + (deposit_khr / ex_rate_khr) + (deposit_thb / ex_rate_thb))){
                                    var tmp_1 = deposit_usd + (deposit_khr / ex_rate_khr);
                                    var tmp_2 = (payable - tmp_1) * ex_rate_thb;
                                    var tmp_3 = (tmp_2 - deposit_thb) + deposit_thb;
                                    $('#amount_paid_usd').val(parseFloat(deposit_usd).toFixed(2));
                                    $('#amount_paid_khr').val(parseFloat(deposit_khr).toFixed(2));
                                    $('#amount_paid_thb').val(parseFloat(tmp_3).toFixed(2));
                                }
                            }
                        }
                    }
                }
            } else {
                $('.paid_by').attr('data', $('.paid_by').find(":selected").val());
                $('#amount_paid_usd').val(parseFloat(payable).toFixed(2));
                $('#amount_paid_khr').val(parseFloat('0').toFixed(2));
                $('#amount_paid_thb').val(parseFloat('0').toFixed(2));
            }
            $('#amount_paid_usd').attr('data', $('#amount_paid_usd').val());
            $('#amount_paid_khr').attr('data', $('#amount_paid_khr').val());
            $('#amount_paid_thb').attr('data', $('#amount_paid_thb').val());

            var amount_paid = parseFloat($('#amount_paid_usd').val());
            $('input[name="amount-paid"]').val(parseFloat(amount_paid).toFixed(2));
        });

        $('#pcc_no_1').change(function (e) {
            var pcc_no = $(this).val();
            localStorage.setItem('pcc_no_1', pcc_no);
            var CardType = null;
            var ccn1 = pcc_no.charAt(0);
            if (ccn1 == 4)
                CardType = 'Visa';
            else if (ccn1 == 5)
                CardType = 'MasterCard';
            else if (ccn1 == 3)
                CardType = 'Amex';
            else if (ccn1 == 6)
                CardType = 'Discover';
            else
                CardType = 'Visa';

            $('#pcc_type_1').select2("val", CardType);
        });

        // $("#date").datetimepicker({
        //     format: site.dateFormats.js_ldate,
        //     fontAwesome: true,
        //     language: 'sma',
        //     weekStart: 1,
        //     todayBtn: 1,
        //     autoclose: 1,
        //     todayHighlight: 1,
        //     startView: 2,
        //     forceParse: 0
        // }).datetimepicker('update', new Date());
    });
</script>

<script type="text/javascript">
    $(document).ready(function(){
        var default_currency = jQuery.parseJSON('<?php echo json_encode($Settings->default_currency); ?>');
        var payable = 0;

        if($('.paid_by').find(":selected").val() != 'deposit') {
            if(default_currency == "USD" ) {
                payable = $('#total_payable_usd').val();
                // if(payable != ''){
                //     $('input[name="amount_paid_usd"]').val(parseFloat(payable).toFixed(2));
                // }
            } else if (default_currency == 'KHR'){
                payable = $('#total_payable_khr').val();
                // if(payable != ''){
                //     $('input[name="amount_paid_khr"]').val(parseFloat(payable).toFixed(2));
                // }
            }
            $('input[name="amount-paid"]').val(parseFloat(payable).toFixed(2));
        }
        
        $(document).on('focus', '.amount', function () {
            $(this).select();
        });

        // $(document).on('focusout', '#amount_paid_usd, #amount_paid_khr, #amount_paid_thb', function () {
        //     if($(this).val() == '' || $(this).val() < 0){
        //         $(this).val('0.00');
        //     }
        //     $(this).val(parseFloat($(this).val()).toFixed(2));
        // });
        
        $(document).on('change', '#amount_paid_usd, #amount_paid_khr, #amount_paid_thb', function () {
            var input_amount = parseFloat($(this).val());
            if ($('.paid_by').find(":selected").val() == 'deposit') {
                if ($(this).attr('id') == 'amount_paid_usd'){
                    if (input_amount > parseFloat(deposit['amount_usd'])){
                        $(this).val($(this).attr('data'));
                        alert('<?= lang('amount_greater_than_deposit') ?>');
                    } else {
                        $(this).attr('data', $(this).val());
                    }
                } else if ($(this).attr('id') == 'amount_paid_khr') {
                    if (input_amount > parseFloat(deposit['amount_khr'])){
                        $(this).val($(this).attr('data'));
                        alert('<?= lang('amount_greater_than_deposit') ?>');
                    } else {
                        $(this).attr('data', $(this).val());
                    } 
                } else {
                    if (input_amount > parseFloat(deposit['amount_thb'])){
                        $(this).val($(this).attr('data'));
                        alert('<?= lang('amount_greater_than_deposit') ?>');
                    } else {
                        $(this).attr('data', $(this).val());
                    }
                }
            } 

            // else {
            //     $(this).attr('data', $(this).val());
            // }

            var total_payable_usd = parseFloat($("#total_payable_usd").val());
            var total_payable_thb = parseFloat($("#total_payable_thb").val());
            var total_payable_khr = parseFloat($("#total_payable_khr").val());
            
            var amount_paid     = 0;
            var ex_rate_khr     = parseFloat($('input[name="exchange_rate_khr"]').val());
            var ex_rate_thb     = parseFloat($('input[name="exchange_rate_thb"]').val());
            var amount_paid_usd = parseFloat($('input[name="amount_paid_usd"]').val());
            var amount_paid_khr = parseFloat($('input[name="amount_paid_khr"]').val());
            var amount_paid_thb = parseFloat($('input[name="amount_paid_thb"]').val());

            if(default_currency == 'KHR'){
                amount_paid = (amount_paid_usd * ex_rate_khr) + amount_paid_khr + (amount_paid_thb / ex_rate_thb) * ex_rate_khr;

                $('#amount_paid_khr').attr('data', $('#amount_paid_khr').val());
                $('#amount_paid_usd').attr('data', $('#amount_paid_usd').val());
                $('#amount_paid_thb').attr('data', $('#amount_paid_thb').val());
                $('input[name="amount-paid"]').attr('data', parseFloat(amount_paid).toFixed(2));
                
                // if(total_payable_khr < amount_paid){
                //     alert("Your amount is granter than payable");
                //     $('input[name="amount_paid_khr"]').val($('input[name="amount_paid_khr"]').attr('data'));
                //     $('input[name="amount_paid_usd"]').val($('input[name="amount_paid_usd"]').attr('data'));
                //     $('input[name="amount_paid_thb"]').val($('input[name="amount_paid_thb"]').attr('data'));
                //     $('input[name="amount-paid"]').val($('input[name="amount-paid"]').attr('data'));
                //     return;
                // } else{
                //     $('#amount_paid_khr').attr('data', $('#amount_paid_khr').val());
                //     $('#amount_paid_usd').attr('data', $('#amount_paid_usd').val());
                //     $('#amount_paid_thb').attr('data', $('#amount_paid_thb').val());
                //     $('input[name="amount-paid"]').attr('data', parseFloat(amount_paid).toFixed(2));
                // }

            } else if(default_currency == 'USD'){
                amount_paid = amount_paid_usd + (amount_paid_khr / ex_rate_khr) + (amount_paid_thb / ex_rate_thb);

                $('#amount_paid_khr').attr('data', $('#amount_paid_khr').val());
                $('#amount_paid_usd').attr('data', $('#amount_paid_usd').val());
                $('#amount_paid_thb').attr('data', $('#amount_paid_thb').val());
                $('input[name="amount-paid"]').attr('data', parseFloat(amount_paid).toFixed(2));
                    
                //   if(total_payable_usd < amount_paid){
                //     alert("Your amount is granter than payable");
                //     $('input[name="amount_paid_khr"]').val($('input[name="amount_paid_khr"]').attr('data'));
                //     $('input[name="amount_paid_usd"]').val($('input[name="amount_paid_usd"]').attr('data'));
                //     $('input[name="amount_paid_thb"]').val($('input[name="amount_paid_thb"]').attr('data'));
                //     $('input[name="amount-paid"]').val($('input[name="amount-paid"]').attr('data'));
                //     return;
                // } else {
                //     $('#amount_paid_khr').attr('data', $('#amount_paid_khr').val());
                //     $('#amount_paid_usd').attr('data', $('#amount_paid_usd').val());
                //     $('#amount_paid_thb').attr('data', $('#amount_paid_thb').val());
                //     $('input[name="amount-paid"]').attr('data', parseFloat(amount_paid).toFixed(2));
                // }

            } else {
                amount_paid = amount_paid_usd * ex_rate_thb + (amount_paid_khr / ex_rate_khr) * ex_rate_thb + amount_paid_thb;

                $('#amount_paid_khr').attr('data', $('#amount_paid_khr').val());
                    $('#amount_paid_usd').attr('data', $('#amount_paid_usd').val());
                    $('#amount_paid_thb').attr('data', $('#amount_paid_thb').val());
                    $('input[name="amount-paid"]').attr('data', parseFloat(amount_paid).toFixed(2)); 

                // if(total_payable_thb < amount_paid){
                //     alert("Your amount is granter than payable");
                //     $('input[name="amount_paid_khr"]').val($('input[name="amount_paid_khr"]').attr('data'));
                //     $('input[name="amount_paid_usd"]').val($('input[name="amount_paid_usd"]').attr('data'));
                //     $('input[name="amount_paid_thb"]').val($('input[name="amount_paid_thb"]').attr('data'));
                //     $('input[name="amount-paid"]').val($('input[name="amount-paid"]').attr('data'));
                //     return;
                // } else {
                //     $('#amount_paid_khr').attr('data', $('#amount_paid_khr').val());
                //     $('#amount_paid_usd').attr('data', $('#amount_paid_usd').val());
                //     $('#amount_paid_thb').attr('data', $('#amount_paid_thb').val());
                //     $('input[name="amount-paid"]').attr('data', parseFloat(amount_paid).toFixed(2));
                // }

            }

            // amount_paid = amount_paid_usd + (amount_paid_khr / ex_rate_khr) + (amount_paid_thb / ex_rate_thb);
            
            $('input[name="amount-paid"]').val(parseFloat(amount_paid).toFixed(2));

        });
        // $('.amount').keypress(function(event) {
        //     if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
        //         event.preventDefault();
        //     }
        // });
    });
</script>
