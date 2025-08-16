<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_payment'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("installments/edit_payment/" . $payment->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <?php if ($Owner || $Admin || $GP['installments-date']) { ?>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <?= lang("date", "date"); ?>
                            <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->bpas->hrld($payment->date)), 'class="form-control datetime" id="date" required="required"'); ?>
                        </div>
                    </div>
                <?php } ?>
                <div class="col-sm-6 <?= ((!$Owner && !$Admin && !$GP['reference_no']) ? 'hidden' : '') ?>">
                    <div class="form-group">
                        <?= lang("reference_no", "reference_no"); ?>
                        <?= form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $payment->reference_no), 'class="form-control tip" id="reference_no" required="required"'); ?>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
            <div id="payments">
                <div class="well well-sm well_1">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="payment">
                                    <div class="form-group">
                                        <?= lang("amount", "amount_1"); ?>
                                       <input name="amount-paid" readonly value="<?= $this->bpas->formatDecimal($payment->amount + $payment->penalty_paid); ?>" type="text"   id="amount_1" class="pa form-control kb-pad amount"/>
                                    </div>
                                </div>
                            </div>
							<div class="col-sm-12">
                                <div class="payment">
                                    <div class="form-group">
                                        <?= lang("discount", "discount"); ?>
                                        <input name="discount" value="<?= $this->bpas->formatDecimal($payment->discount); ?>" type="text" class="form-control" id="discount"/>
                                    </div>
                                </div>
                            </div>
							<div class="col-sm-4">
                                <div class="form-group">
									<?= lang("interest_paid", "interest_paid"); ?>
                                    <input type="text" readonly name="interest-paid" class="form-control interest_paid" value="<?= $this->bpas->formatDecimal($payment->interest_paid) ?>" />
                                </div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<?= lang("principal_paid", "principal_paid"); ?>
									<input type="text" readonly name="principal-paid" class="form-control principal_paid" value="<?= $this->bpas->formatDecimal($payment->amount) ?>" />
								</div>
                            </div>
							<div class="col-sm-4">		
								<?= lang("penalty_paid", "penalty_paid"); ?>
								<div class="form-group">
									<input name="penalty-paid" value="<?= $this->bpas->formatDecimal($payment->penalty_paid); ?>" type="text" id="penalty" class="form-control"/>
								</div>                           
							</div>
							<?php
							$currency_details = array();
							$currency_jsons = json_decode($payment->currencies);							
							foreach($currency_jsons as $currency){
								$currency_details[$currency->currency] = $currency->amount;
							}
							foreach ($currencies as $currency) { 
								$base_currency = $this->site->getCurrencyByCode($Settings->default_currency);
								$amount = $currency_details[$currency->code] ? $currency_details[$currency->code] : 0;
							?>
								<div class="col-sm-12">								
									<div class="form-group">
										<?= lang("amount", "amount"); ?> 
										(<?= strtoupper($currency->code); ?>)
										<small><?= $this->bpas->formatDecimal($currency->rate) ?></small>
										<input name="c_amount[]" rate="<?= $base_currency->rate ?>" type="text" value="<?= $amount ?>" class="form-control c_amount"/>
										<input name="currency[]" value="<?= $currency->code ?>" type="hidden" />
										<input name="rate[]" value="<?= $currency->rate ?>" type="hidden" class="rate" />										
									</div>                                
								</div>
							<?php } ?>
							<div class="col-sm-12">
								<div class="form-group">
									<?= lang("paying_by", "paid_by_1"); ?>
                                    <select name="paid_by" id="paid_by_1" class="form-control paid_by">
										<?= $this->bpas->paid_opts($payment->paid_by, true, false, true, true); ?>
                                    </select>
								</div>
							</div>	
                        </div>
                        <div class="clearfix"></div>
						<div class="row cbank" style="display: none;">
							<div class="col-sm-6">
								<div class="form-group">
									<?= lang("account_number", "account_number"); ?>
									<input name="account_number" value="<?= $payment->account_number ?>" type="text" id="account_number" class="form-control"/>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<?= lang("account_name", "account_name"); ?>
									<input name="account_name" value="<?= $payment->account_name ?>" type="text" id="account_name" class="form-control"/>
								</div>
							</div>
						</div>
						<div class="row ccheque" style="display: none;">
							<div class="col-sm-12">
								<div class="form-group">
									<?= lang("bank_name", "bank_name"); ?>
									<input name="bank_name" value="<?= $payment->bank_name ?>" type="text" id="bank_name" class="form-control"/>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<?= lang("cheque_number", "cheque_number"); ?>
									<input name="cheque_number" value="<?= $payment->cheque_number ?>" type="text" id="cheque_number" class="form-control"/>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<?= lang("cheque_date", "cheque_date"); ?>
									<input name="cheque_date" value="<?= $this->bpas->hrsd($payment->cheque_date && $payment->cheque_date != "0000-00-00" ? $payment->cheque_date : date("Y-m-d")) ?>" type="text" id="cheque_date" class="form-control date"/>
								</div>
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
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $payment->note), 'class="form-control" id="note"'); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_payment', lang('edit_payment'), 'class="btn btn-primary"'); ?>
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
		var old_value;
		$(document).on("focus", '.interest_paid, .principal_paid', function () {
			old_value = $(this).val();
		}).on("change", '.interest_paid, .principal_paid', function () {
			var row = $(this).closest('tr');
			if (!is_numeric($(this).val())) {
				$(this).val(old_value);
				bootbox.alert(lang.unexpected_value);
				return;
			}
			var interest_paid  = $(".interest_paid").val() - 0;
			var principal_paid = $(".principal_paid").val() - 0;
			var amount_paid    = interest_paid + principal_paid; 
			$(".c_amount").each(function(){
				$("[default=true]").val(amount_paid);
			});
		}); 
		var old_amount;
		$(document).on("focus", '.c_amount', function () {
			old_amount = $(this).val();
		}).on("change", '.c_amount', function () {
			var c_total = 0;
			$(".c_amount").each(function(){
				var base_rate = formatDecimal($(this).attr("rate"),11);
				var rate = formatDecimal($(this).parent().find(".rate").val(),11);
				var amount = formatDecimal($(this).val(),11);
				var base_amount = amount / rate;
				var camount = base_amount * base_rate;
					c_total += camount;
			});
			var interest = (("<?= $payment->interest_paid; ?>") * 100) / ("<?= ($payment->amount + $payment->interest_paid); ?>");
			var principal = (("<?= $payment->amount; ?>") * 100) / ("<?= ($payment->amount + $payment->interest_paid); ?>");
			var interest_paid  = (c_total * interest / 100);
			var principal_paid = (c_total * principal / 100);
			$(".interest_paid").val(interest_paid);
			$(".principal_paid").val(principal_paid);
			$("#amount_1").val(c_total);
		}); 

		$(document).on('change', '.paid_by', function () {
			var cash_type = $('option:selected', this).attr('cash_type');
            if(cash_type == 'bank'){
				$('.cbank').slideDown();
				$('.gc').slideUp();
				$('.ccheque').slideUp();
			}else if(cash_type == 'cheque'){
				$('.ccheque').slideDown();
				$('.gc').slideUp();
				$('.cbank').slideUp();
			}else if (cash_type == 'gift_card') {
                $('.gc').slideDown();
				$('.cbank').slideUp();
				$('.ccheque').slideUp();
                $('#gift_card_no').focus();
            } else {
                $('.gc').slideUp();
				$('.cbank').slideUp();
				$('.ccheque').slideUp();
            }
        });
		$(".paid_by").change();
	
        $.fn.datetimepicker.dates['bpas'] = <?=$dp_lang?>;
    });
</script>
