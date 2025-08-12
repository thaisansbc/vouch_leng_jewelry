<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_payment_returns'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("sales/edit_payment_returns/" . $payment->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <?php if ($Owner || $Admin || $GP['sales-date']) { ?>
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

                <input type="hidden" value="<?php echo $payment->sale_id; ?>" name="sale_id"/>
            </div>
            <div class="clearfix"></div>
			
			<?php 
				$now = time();
				$invoice_date = strtotime($inv->date);
				$datediff = $now - $invoice_date;
				$payment_date = round($datediff / (60 * 60 * 24));
				$discount = 0;
				if($payment_term){
					if($payment_date < $payment_term->due_day_discount){
						if($payment_term->discount_type == "Percentage"){
							$discount = ($payment_term->discount * $inv->grand_total) / 100;
						}else{
							$discount = $payment_term->discount;
						}
					}
				}
			?>
            <div id="payments">

                <div class="well well-sm well_1">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="payment">
                                    <div class="form-group">
                                        <?php 
											echo lang("amount", "amount_1"); 
											if($discount > 0){
												echo "&nbsp;<small style='color:red'>( ".lang("payment_term")." ".$payment_term->discount." ".$payment_term->discount_type." )</small>";
											}
										?>
										
                                        <input name="amount-paid" readonly
                                               value="<?= $this->bpas->formatDecimal(abs($payment->amount)); ?>" type="text"
                                               id="amount_1" class="pa form-control kb-pad amount"/>
                                    </div>
                                </div>
                            </div>
							
							<div class="col-sm-12">
                                <div class="payment">
                                    <div class="form-group">
                                        <?= lang("discount", "discount"); ?>
                                        <input name="discount" value="<?= abs($payment->discount); ?>" type="text" class="form-control" id="discount"/>
                                    </div>
                                </div>
                            </div>
	
							<?php if($Settings->module_installment==1){ ?>
								<div class="col-sm-6">
									<div class="form-group">
										<?= lang("interest_paid", "interest_paid"); ?>
										<input type="text" name="interest-paid" class="form-control interest_paid" value="<?= abs($payment->interest_paid) ?>" />
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<?= lang("principal_paid", "principal_paid"); ?>
										<input type="text" name="principal-paid" class="form-control principal_paid" value="<?= abs($payment->amount) ?>" />
									</div>
								</div>
							<?php } ?>
							
							<?php
							$p_currencies = array();
							$g_currencies = json_decode($payment->currencies);							
							foreach($g_currencies as $currency){
								$p_currencies[$currency->currency] = array('amount'=>$currency->amount, 'rate'=>$currency->rate);
							}
							$base_currency = $this->site->getCurrencyByCode($Settings->default_currency);
							foreach($currencies as $currency){ 
								$amount = $p_currencies[$currency->code] ? $p_currencies[$currency->code]['amount'] : 0;
								$rate = $p_currencies[$currency->code] ? $p_currencies[$currency->code]['rate'] : $currency->rate;
							?>
							
								<div class="col-sm-8">								
									<div class="form-group">
										<?= lang("amount", "amount"); ?>
										<input c_code="<?= $currency->code ?>" name="c_amount[]" rate="<?= $base_currency->rate ?>" type="text"  <?= ($base_currency->code==$currency->code?"default=true":"") ?> value="<?= $amount ?>" class="form-control c_amount"/>
										<input name="currency[]" value="<?= $currency->code ?>" type="hidden" />								
									</div>                                
								</div>
								<div class="col-sm-4">								
									<div class="form-group">
										<?= lang("rate", "rate"); ?>
										<input <?= ($currency->code == 'USD' ? 'readonly' : '') ?> id="<?= $currency->code ?>" name="rate[]" value="<?= $rate ?>" type="text" class="form-control rate" />										
									</div>                                
								</div>
								
							<?php } ?>
							<div class="col-sm-12">
                                <div class="form-group">
                                    <?= lang("paying_by", "paid_by_1"); ?>
                                    <select name="paid_by" id="paid_by_1" class="form-control paid_by">
										<?= $this->bpas->paid_opts($payment->paid_by); ?>
                                    </select>
                                </div>
                            </div>
							
                        </div>
                        <div class="clearfix"></div>
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

            </div>

            <div class="form-group">
                <?= lang("attachment", "attachment") ?>
                <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false"
                       class="form-control file">
            </div>

            <div class="form-group">
                <?= lang("note", "note"); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $payment->note), 'class="form-control" id="note"'); ?>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_payment_returns', lang('edit_payment_returns'), 'class="btn btn-primary"'); ?>
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
	
		var old_rate;
		$(document).on("focus", '.rate', function () {
			old_rate = $(this).val();
		}).on("change", '.rate', function () {
			var row = $(this).closest('tr');
			if($(this).val() == ''){
				$(this).val(0);
			}else if (!is_numeric($(this).val())) {
				$(this).val(old_rate);
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
			$(".principal_paid").val(c_total);	
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
		$(".interest_paid,.principal_paid").on("change",function(){
			var interest_paid = $(".interest_paid").val() - 0;
			var principal_paid = $(".principal_paid").val() - 0;
			var amount_paid = interest_paid + principal_paid; 
			$(".c_amount").each(function(){
				$("[default=true]").val(amount_paid);
			});
		});
		
    });
</script>
