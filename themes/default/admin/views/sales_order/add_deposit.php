<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_deposit'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("sales_order/add_deposit/" . $sale_order->id, $attrib);
		?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
			
               <?php if ($Owner || $Admin || $GP['sale_orders-date']) { ?>
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
                        <?= form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $payment_ref), 'class="form-control tip" id="reference_no"'); ?>
                    </div>
                </div>

                <input type="hidden" value="<?php echo $sale_order->id; ?>" name="sale_order_id"/>
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
                                        <input name="amount-paid" readonly="readonly" type="text" id="amount_1"
                                               value="0"
                                               class="pa form-control kb-pad amount" required="required"/>
                                    </div>
                                </div>
                            </div>
							<?php foreach($currencies as $currency){ 
                                $sale_grand_total = isset($sale_order->grand_total)? $sale_order->grand_total: 0;
                                $sale_ord_paid = isset($sale_order->paid)? $sale_order->paid: 0;
                                $ord_discount = isset($discount)? $discount: 0;
								
								$base_currency = $this->site->getCurrencyByCode($Settings->default_currency);
								$base_amount = ($sale_grand_total - $sale_ord_paid - $ord_discount) / $base_currency->rate;
								$amount = $base_amount * $currency->rate;
                                ?>
								<div class="col-sm-12">								
									<div class="form-group">
										<?= lang("amount", "amount"); ?> : <?= $this->bpas->formatDecimal($amount) ?>
										(<?= strtoupper($currency->code); ?>)
										<a href="<?= admin_url("system_settings/edit_currency/".$currency->id) ?>" data-toggle="modal" data-target="#myModal2">
											<small><?= $this->bpas->formatDecimal($currency->rate) ?></small>
										</a>
										<input name="c_amount[]" value="0.00" rate="<?= $base_currency->rate ?>" type="text" class="form-control c_amount"/>
										<input name="currency[]" value="<?= $currency->code ?>" type="hidden" />
										<input name="rate[]" value="<?= $currency->rate ?>" type="hidden" class="rate" />										
									</div>                                
								</div>
							<?php } ?>
							
							<div class="col-sm-12">                         
                                <div class="form-group">
                                    <?= lang('paying_by', 'paid_by_1'); ?>
                                    <select name="paid_by" id="paid_by_1" class="form-control paid_by" data="" required="required">
                                        <?= $this->bpas->paid_opts(); ?>
                                    </select>
                                </div>
                         
                            </div>
                        </div>
                        <div class="clearfix"></div>
						<div class="row cbank" style="display: none;">
							<div class="col-sm-6">
								<div class="form-group">
									<?= lang("account_number", "account_number"); ?>
									<input name="account_number" type="text" id="account_number" class="form-control"/>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<?= lang("account_name", "account_name"); ?>
									<input name="account_name" type="text" id="account_name" class="form-control"/>
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
            <?php echo form_submit('add_deposit', lang('add_deposit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['bms'] = <?=$dp_lang?>;
</script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
		var old_amount;
		$(document).on("focus", '.c_amount', function () {
			old_amount = $(this).val();
		}).on("change", '.c_amount', function () {
			if($(this).val() == ''){
				$(this).val(0);
			}else if (!is_numeric($(this).val())) {
				$(this).val(old_amount);
				return;
			}
			var c_total = 0;
			$(".c_amount").each(function(){
				var base_rate = formatDecimal($(this).attr("rate"),11);
				var rate = formatDecimal($(this).parent().find(".rate").val(),11);
				var amount = formatDecimal($(this).val(),11);
				var base_amount = amount / rate;
				var camount = base_amount * base_rate;
					c_total += camount;
			});
			$("#amount_1").val(c_total);	
		});
		
		$(document).on('change', '.paid_by', function () {
			var cash_type = $('option:selected', this).attr('cash_type');
            if(cash_type == 'bank'){
				$('.cbank').slideDown();
				$('.ccheque').slideUp();
			}else if(cash_type == 'cheque'){
				$('.ccheque').slideDown();
				$('.cbank').slideUp();
			}else if (cash_type == 'gift_card') {
				$('.cbank').slideUp();
				$('.ccheque').slideUp();
            } else {
				$('.cbank').slideUp();
				$('.ccheque').slideUp();
            }
        });
		$(".paid_by").change();
        $("#date").datetimepicker({
            <?= ($Settings->date_with_time == 0 ? 'format: site.dateFormats.js_sdate, minView: 2' : 'format: site.dateFormats.js_ldate') ?>,
            fontAwesome: true,
            language: 'bms',
            weekStart: 1,
            todayBtn: 1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            forceParse: 0
        }).datetimepicker('update', new Date());
    });
</script>
