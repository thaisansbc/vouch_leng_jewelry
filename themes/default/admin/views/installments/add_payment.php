<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_payment'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("installments/add_payment/" . $id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
           <div class="row">
               <?php if ($Owner || $Admin || $GP['installments-date']) { ?>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <?= lang("date", "date"); ?>
                            <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control datetime" id="date" required="required"'); ?>
                        </div>
                    </div>
                <?php } ?>
                <div class="col-sm-6 <?= ((!$Owner && !$Admin && !$GP['reference_no']) ? 'hidden' : '') ?>">
                    <div class="form-group">
                        <?= lang("reference_no", "reference_no"); ?>
                        <?= form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control tip" id="reference_no"'); ?>
                    </div>
                </div>
            </div> 
			<div class="clearfix"></div>
			  <div id="payments">
                <div class="well well-sm well_1">
                    <div class="col-md-12">
                        <div class="row"> 
							<?php 
								$principal_paid = $installment_item->principal_paid;
								$interest_paid 	= $installment_item->interest_paid;
								$penalty_paid 	= $installment_item->penalty_paid;
								$amount_paid 	= $principal_paid + $interest_paid;
								$payment_amount = $installment_item->payment - $amount_paid;
								$current_date 	= date('Y-m-d');
								$dateline_date  = $installment_item->deadline;
								$days 			= (strtotime($current_date) - strtotime($dateline_date)) / (60 * 60 * 24) - ($Settings->installment_late_days ? ($Settings->installment_late_days -1) : 0);
								if ($Settings->installment_penalty_option == 1) {
									$pay_penalty = $this->site->getPenaltyPayment($payment_amount, $days);
								} else {
									$pay_penalty = $this->site->getPenaltyPayment($payment_amount, $days, $installment->penalty_id);
								}
							?> 
                         	<div class="col-sm-12">
                                <div class="payment">
                                    <div class="form-group">
                                        <?= lang("amount", "amount_1"); ?>
                                        <input name="amount-paid" readonly="readonly" type="text" id="amount_1" 
                                        value="<?= $this->bpas->formatDecimal($payment_amount + $pay_penalty); ?>" class="pa form-control kb-pad amount" required="required"/>
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
                             <div class="col-sm-4">
                                <div class="form-group">
									<?= lang("interest_paid", "interest_paid"); ?>
                                    <input type="text" name="interest-paid" class="form-control interest_paid" value="<?= ($installment_item->interest - $interest_paid) ?>" />
                                </div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<?= lang("principal_paid", "principal_paid"); ?>
									<input type="text" name="principal-paid" class="form-control principal_paid" value="<?= ($installment_item->principal - $principal_paid) ?>" />
								</div>
                            </div>
                            <div class="col-sm-4">		
								<?= lang("penalty_paid", "penalty_paid"); ?> <?= $days . ' ' . lang('days');?>
								<div class="form-group">
									<input name="penalty-paid" value="<?= $this->bpas->formatDecimal($pay_penalty) ?>" type="text" id="penalty" class="penalty_paid form-control"/>
								</div>                           
							</div>
							<?php 
								foreach ($currencies as $i => $currency) {
									$base_currency = $this->site->getCurrencyByCode($Settings->default_currency);
									$base_amount   = $pay_penalty + ($installment_item->payment - $amount_paid) / $base_currency->rate;
									$amount 	   = $base_amount * $currency->rate;
							?>
								<div class="col-sm-12">								
									<div class="form-group">
										<?= lang("amount", "amount"); ?> : <?= $this->bpas->formatDecimal($amount); ?>
										(<?= strtoupper($currency->code); ?>)
										<small><?= $this->bpas->formatDecimal($currency->rate) ?></small>
										<input name="c_amount[]" value="<?= ($currency->code == $base_currency->code ? $this->bpas->formatDecimal($amount) : '0.00'); ?>" rate="<?= ($base_currency->rate); ?>" type="text" class="form-control c_amount" <?= ($base_currency->code==$currency->code?"default=true":""); ?> >
										<input name="currency[]" value="<?= ($currency->code); ?>" type="hidden" />
										<input name="rate[]" value="<?= ($currency->rate); ?>" type="hidden" class="rate" />										
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
                            <?php if ($this->Settings->accounting) { ?>
                                <div class="col-sm-4 hide">
                                    <label class="checkbox" for="write_off">
                                        <input type="checkbox" name="write_off" value="1" id="write_off" />
                                        <?= lang('write_off') ?>
                                    </label>
                           
                                </div>
                            <?php } ?>
						</div>
                        <div class="clearfix"></div>
						<div class="row cbank" style="display: none;">
							<div class="col-sm-6">
								<div class="form-group">
									<?= lang("account_number", "account_number"); ?>
									<input name="account_number" value="<?= (isset($bank_info) && $bank_info ? $bank_info->account_number : '') ?>" type="text" id="account_number" class="form-control"/>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<?= lang("account_name", "account_name"); ?>
									<input name="account_name" value="<?= (isset($bank_info) && $bank_info ? $bank_info->account_name : '') ?>" type="text" id="account_name" class="form-control"/>
								</div>
							</div>
						</div>
						<div class="row ccheque" style="display: none;">
							<div class="col-sm-12">
								<div class="form-group">
									<?= lang("bank_name", "bank_name"); ?>
									<input name="bank_name" type="text" id="bank_name" class="form-control"/>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<?= lang("cheque_number", "cheque_number"); ?>
									<input name="cheque_number" type="text" id="cheque_number" class="form-control"/>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<?= lang("cheque_date", "cheque_date"); ?>
									<input name="cheque_date" value="<?= $this->bpas->hrsd(date("Y-m-d")) ?>" type="text" id="cheque_date" class="form-control date"/>
								</div>
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
                <?= lang("attachment", "attachment") ?>
                <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false" class="form-control file">
            </div>

            <div class="form-group">
                <?= lang("note", "note"); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="note"'); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_payment', lang('add_payment'), 'class="btn btn-primary"'); ?>
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
		var old_discount;
		$(document).on("focus", '#discount', function () {
			old_discount = $(this).val();
		}).on("change", '#discount', function () {
			var row = $(this).closest('tr');
			if (!is_numeric($(this).val())) {
				$(this).val(old_discount);
				bootbox.alert(lang.unexpected_value);
				return;
			}
			var discount = $(this).val() - 0;
			var amount = <?= ($installment_item->payment - $amount_paid); ?>;
			var amount_usd = amount - discount;
			$(".c_amount").each(function(){
				if($(this).attr("default")=="true"){
					$(this).val(amount_usd);
					$(this).change();
				}
			});
		});
		var old_value;
		$(document).on("focus", '.interest_paid, .principal_paid, .penalty_paid', function () {
			old_value = $(this).val();
		}).on("change", '.interest_paid, .principal_paid, .penalty_paid', function () {
			var row = $(this).closest('tr');
			if (!is_numeric($(this).val())) {
				$(this).val(old_value);
				bootbox.alert(lang.unexpected_value);
				return;
			}
			var interest_paid  = $(".interest_paid").val() - 0;
			var principal_paid = $(".principal_paid").val() - 0;
			var penalty_paid   = $(".penalty_paid").val() - 0;
			var amount_paid    = interest_paid + principal_paid + penalty_paid; 
			$(".c_amount").each(function(){
				$("[default=true]").val(amount_paid);
			});
		});
		var old_amount;
		$(document).on("focus", '.c_amount', function () {
			old_amount = $(this).val();
		}).on("change", '.c_amount', function () {
			var row = $(this).closest('tr');
			if (!is_numeric($(this).val())) {
				$(this).val(old_amount);
				bootbox.alert(lang.unexpected_value);
				return;
			}
			var c_total = 0;
			$(".c_amount").each(function(){
				var base_rate = formatDecimal($(this).attr("rate"),11);
				var rate = formatDecimal($(this).parent().find(".rate").val(),11);
				if(rate > 0){
					var amount = formatDecimal($(this).val(),11);
					var base_amount = amount / rate;
					var camount = base_amount * base_rate;
					c_total += camount;
				}
			});
			var interest = (("<?= $installment_item->interest - $interest_paid; ?>") * 100) / ("<?= $installment_item->payment - $amount_paid; ?>");
			var principal = (("<?= $installment_item->principal - $principal_paid; ?>") * 100) / ("<?= $installment_item->payment - $amount_paid; ?>");
			var interest_paid  = formatDecimal(c_total * interest / 100);
			var principal_paid = formatDecimal(c_total * principal / 100);
			$(".interest_paid").val(interest_paid);
			$(".principal_paid").val(principal_paid - interest_paid);
			$("#amount_1").val(c_total);
		}); 
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
                        } else if (data.customer_id !== null && data.customer_id != <?=$installment->customer_id?>) {
                            $('#gift_card_no').parent('.form-group').addClass('has-error');
                            bootbox.alert('<?=lang('gift_card_not_for_customer')?>');
                        } else {
                            var due = <?=$installment_item->payment?>;
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
		
   //      $(document).on('change', '.paid_by', function () {
			// var cash_type = $('option:selected', this).attr('cash_type');
   //          if(cash_type == 'bank'){
			// 	$('.cbank').slideDown();
			// 	$('.gc').slideUp();
			// 	$('.ccheque').slideUp();
			// }else if(cash_type == 'cheque'){
			// 	$('.ccheque').slideDown();
			// 	$('.gc').slideUp();
			// 	$('.cbank').slideUp();
			// }else if (cash_type == 'gift_card') {
   //              $('.gc').slideDown();
			// 	$('.cbank').slideUp();
			// 	$('.ccheque').slideUp();
   //              $('#gift_card_no').focus();
   //          } else {
   //              $('.gc').slideUp();
			// 	$('.cbank').slideUp();
			// 	$('.ccheque').slideUp();
   //          }
   //      });

   		 $('.paid_by').attr('data', $('.paid_by').find(":selected").val());
   		 $(document).on('change', '.paid_by', function () {
            var p_val = $(this).val();
            $('#rpaidby').val(p_val);
            if (p_val == 'cash') {
                $('.pcheque_1').hide();
                $('.pcc_1').hide();
                $('.pcash_1').show();
                // $('#amount_1').focus();
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

            var amount_paid = parseFloat($('#amount_paid_usd').val()) + (parseFloat($('#amount_paid_khr').val()) / ex_rate_khr) + (parseFloat($('#amount_paid_thb').val()) / ex_rate_thb);
            // $('input[name="amount-paid"]').val(parseFloat(amount_paid).toFixed(2));
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
		$(".paid_by").change();
        $("#date").datetimepicker({
            <?= ($Settings->date_with_time == 0 ? 'format: site.dateFormats.js_sdate, minView: 2' : 'format: site.dateFormats.js_ldate') ?>,
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
