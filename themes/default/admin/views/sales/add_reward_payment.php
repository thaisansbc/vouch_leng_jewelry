<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_payment'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('products/add_payment/'. $inv->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <?php if ($Owner || $Admin|| $GP['change_date']) { ?>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <?= lang('date', 'date'); ?>
                            <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : date('d/m/Y')), 'class="form-control datetime" id="date" required="required"'); ?>
                        </div>
                    </div>
                <?php } ?>
                <div class="col-sm-6">
                    <div class="form-group">
                        <?= lang('reference_no', 'reference_no'); ?>
                        <?= form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $payment_ref), 'class="form-control tip" id="reference_no"'); ?>
                    </div>
                </div>
                <input type="hidden" value="<?php echo $inv->id; ?>" name="reward_id"/>
            </div>
            <div class="clearfix"></div>
            <div id="payments">
                <input type="hidden" name="payment_term" value="<?php echo $payment_term; ?>"/>
                <div class="well well-sm well_1">
                    
                    <div class="col-sm-12">
                        <div class="payment">
                            <div class="form-group">
                                <?= lang("amount", "amount_1"); ?>
                                <input type="text" name="amount-paid" readonly="readonly" class="pa form-control kb-pad amount" id="amount_1" value="<?= $inv->grand_total ? $inv->grand_total - $inv->paid :0;?>" data="0"  required="required"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="payment">
                            <div class="form-group">
                                <?= lang("discount", "discount"); ?>
                                <input name="discount" value="0" type="text" class="form-control" id="discount"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="row">
                           
                            <?php 
                            foreach($currencies as $currency){ 
                                $base_currency = $this->site->getCurrencyByCode($Settings->default_currency);
                                $base_amount = $this->bpas->formatDecimal(($inv->grand_total - $inv->paid) / $base_currency->rate);
                                $amount = $base_amount * $currency->rate;
                            ?>
                                <div class="col-sm-8">                              
                                    <div class="form-group">
                                        <?= lang("amount", "amount"); ?> : <span id="am_<?= $currency->code ?>"><?= $this->bpas->formatMoney($amount) ?> (<?= $currency->code ?>)</span>
                                        <input c_code="<?= $currency->code ?>" name="c_amount[]" value="0" rate="<?= $base_currency->rate ?>" type="text" class="form-control c_amount"/>
                                        <input name="currency[]" value="<?= $currency->code ?>" type="hidden" />                                
                                    </div>                                
                                </div>
                                <div class="col-sm-4">                              
                                    <div class="form-group">
                                        <?= lang("rate", "rate"); ?>
                                        <input <?= ($currency->code == 'USD' ? 'readonly' : '') ?> id="<?= $currency->code ?>" name="rate[]" value="<?= $currency->rate ?>" type="text" class="form-control rate" />                                        
                                    </div>                                
                                </div>
                            <?php } ?>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <?= lang('paying_by', 'paid_by_1'); ?>
                                    <select name="paid_by" id="paid_by_1" class="form-control paid_by" data="" required="required">
                                        <?= $this->bpas->paid_opts(); ?>
                                    </select>
                                </div>
                            </div>
                            <?php 
                            if($this->Settings->accounting) {
                                $bankAccounts =  $this->site->getAllBankAccounts();
                            ?>
                                <div class="col-sm-6">
                                    <label class="checkbox" for="write_off">
                                        <input type="checkbox" name="write_off" value="1" id="write_off" />
                                        <?= lang('write_off') ?>
                                    </label>
                                </div>
                            <?php } ?>
                            <div class="col-sm-12">
                                <hr style="margin-top: 1rem; margin-bottom: 1rem; border: 0; border-top: 1px solid rgba(0,0,0,0.1);">
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="form-group gc" style="display: none;">
                            <?= lang('gift_card_no', 'gift_card_no'); ?>
                            <input name="gift_card_no" type="text" id="gift_card_no" class="pa form-control kb-pad"/>
                            <div id="gc_details"></div>
                        </div>
                        <div class="pcc_1" style="display:none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input name="pcc_no" type="text" id="pcc_no_1" class="form-control" placeholder="<?= lang('cc_no') ?>"/>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input name="pcc_holder" type="text" id="pcc_holder_1" class="form-control" placeholder="<?= lang('cc_holder') ?>"/>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <select name="pcc_type" id="pcc_type_1" class="form-control pcc_type" placeholder="<?= lang('card_type') ?>">
                                            <option value="Visa"><?= lang('Visa'); ?></option>
                                            <option value="MasterCard"><?= lang('MasterCard'); ?></option>
                                            <option value="Amex"><?= lang('Amex'); ?></option>
                                            <option value="Discover"><?= lang('Discover'); ?></option>
                                        </select>
                                        <!-- <input type="text" id="pcc_type_1" class="form-control" placeholder="<?= lang('card_type') ?>" />-->
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input name="pcc_month" type="text" id="pcc_month_1" class="form-control" placeholder="<?= lang('month') ?>"/>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input name="pcc_year" type="text" id="pcc_year_1" class="form-control" placeholder="<?= lang('year') ?>"/>
                                    </div>
                                </div>
                                <!--<div class="col-md-3">
                                    <div class="form-group">
                                        <input name="pcc_ccv" type="text" id="pcc_cvv2_1" class="form-control" placeholder="<?= lang('cvv2') ?>" />
                                    </div>
                                </div>-->
                            </div>
                        </div>
                        <div class="pcheque_1" style="display:none;">
                            <div class="form-group"><?= lang('cheque_no', 'cheque_no_1'); ?>
                                <input name="cheque_no" type="text" id="cheque_no_1" class="form-control cheque_no"/>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
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
            <?php echo form_submit('add_payment', lang('add_payment'), 'class="btn btn-primary"'); ?>
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
                        } else if (data.company_id !== null && data.company_id != <?=$inv->company_id?>) {
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
    $(document).ready(function(){
        var default_currency = jQuery.parseJSON('<?php echo json_encode($Settings->default_currency); ?>');
        var payable = 0;

        $(document).on('focus', '.amount', function () {
            $(this).select();
        });

        $('.amount').keypress(function(event) {
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
        });
        var old_rate;
        $(document).on("focus", '.rate', function () {
            old_rate = $(this).val();
        }).on("change", '.rate', function () {
            if($(this).val() == ''){
                $(this).val(0);
            }else if (!is_numeric($(this).val())) {
                $(this).val(old_rate);
                return;
            }
            $('.c_amount').change();
        }); 
        
        var old_discount;
        $(document).on("focus", '#discount', function () {
            old_discount = $(this).val();
        }).on("change", '#discount', function () {
            if($(this).val() == ''){
                $(this).val(0);
            }else if (!is_numeric($(this).val())) {
                $(this).val(old_discount);
                return;
            }
            $('.c_amount').change();
        }); 
        
        
        var old_amount;
        $(document).on("focus", '.c_amount', function () {
            old_amount = $(this).val();
        }).on("change", '.c_amount', function () {
            var row = $(this).closest('tr');
            if($(this).val() == ''){
                $(this).val(0);
            }else if (!is_numeric($(this).val())) {
                $(this).val(old_amount);
                return;
            }
            var c_total = 0;
            $(".c_amount").each(function(){
                var base_rate = formatDecimal($(this).attr("rate"),11);
                var code = $(this).attr("c_code");
                var rate =  $("#"+code).val() - 0;
                if(rate > 0){
                    var amount = formatDecimal($(this).val(),11);
                    var base_amount = amount / rate;
                    var camount = base_amount * base_rate;
                    c_total += camount;
                }
            });
            var discount = $('#discount').val() - 0;
            var total_amount = formatDecimal('<?= $base_amount ?>');
            var balance_amount = total_amount - c_total - discount;
            $(".c_amount").each(function(){
                var code = $(this).attr("c_code");
                var rate =  $("#"+code).val() - 0;
                var amount_html = formatMoney(rate * balance_amount) + ' ('+ code +')';
                $("#am_"+code).html(amount_html);
            });
            
            $("#amount_1").val(c_total);    
        }); 
    });
</script>
