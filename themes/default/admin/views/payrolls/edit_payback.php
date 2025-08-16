<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_payback'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("payrolls/edit_payback/" . $payback->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <?php if ($Owner || $Admin || $GP['payrolls-cash_advances_date']) { ?>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <?= lang("date", "date"); ?>
                            <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->bpas->hrld($payback->date)), 'class="form-control datetime" id="date" required="required"'); ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
			
            <div class="clearfix"></div>
			
			
            <div id="payments">

                <div class="well well-sm well_1">
                    <div class="col-md-12">
                        <div class="row">
						
                            <div class="col-sm-6">
                                <div class="payment">
									<?= lang("amount", "amount_1"); ?>
                                    <div class="form-group">
                                        <input name="amount-paid" readonly
                                               value="<?= $this->bpas->formatDecimal($payback->amount); ?>" type="text"
                                               id="amount_1" class="pa form-control kb-pad amount"/>
                                    </div>
                                </div>
                            </div>
							
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <?= lang("paying_by", "paid_by_1"); ?>
                                    <select name="paid_by" id="paid_by_1" class="form-control paid_by">
										<?= $this->bpas->paid_opts($payback->paid_by,true,false,true,true); ?>
                                    </select>
                                </div>
                            </div>
							
							
							<?php
							$p_currencies = array();
							if($payback->currencies){
								$g_currencies = json_decode($payback->currencies);							
								foreach($g_currencies as $currency){
									$p_currencies[$currency->currency] = $currency->amount;
								}
							}
														
							foreach($currencies as $currency){ ?>
								<div class="col-sm-12">								
									<div class="form-group">
										<?= lang("amount", "amount"); ?> 
										(<?= strtoupper($currency->code); ?>)
										<small><?= $this->bpas->formatDecimal($currency->rate) ?></small>
										<input name="c_amount[]" rate="<?= $currency->rate ?>" type="text" value="<?= (isset($p_currencies[$currency->code]) ? $p_currencies[$currency->code] : 0) ?>" class="form-control c_amount"/>
										<input name="currency[]" value="<?= $currency->code ?>" type="hidden" />
										<input name="rate[]" value="<?= $currency->rate ?>" type="hidden" />										
									</div>                                
								</div>
							<?php } ?>
							
							<?php if($Settings->accounting == 1){ ?>
								<div class="col-sm-12 paying_to_box">
									<div class="form-group">
										<?= lang("paying_to", "paying_to"); ?>
										<select name="paying_to" class="form-control select" id="paying_to" style="width:100%">
											<?= $cash_account ?>
										</select>
									</div>
								</div>
							<?php } ?>
                        </div>
                        <div class="clearfix"></div>
                        <div class="pcc_1" style="display:none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input name="pcc_no" value="<?= $payback->cc_no; ?>" type="text" id="pcc_no_1"
                                               class="form-control" placeholder="<?= lang('cc_no') ?>"/>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">

                                        <input name="pcc_holder" value="<?= $payback->cc_holder; ?>" type="text"
                                               id="pcc_holder_1" class="form-control"
                                               placeholder="<?= lang('cc_holder') ?>"/>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <select name="pcc_type" id="pcc_type_1" class="form-control pcc_type"
                                                placeholder="<?= lang('card_type') ?>">
                                            <option
                                                value="Visa"<?= $payback->cc_type == 'Visa' ? ' checked="checcked"' : '' ?>><?= lang("Visa"); ?></option>
                                            <option
                                                value="MasterCard"<?= $payback->cc_type == 'MasterCard' ? ' checked="checcked"' : '' ?>><?= lang("MasterCard"); ?></option>
                                            <option
                                                value="Amex"<?= $payback->cc_type == 'Amex' ? ' checked="checcked"' : '' ?>><?= lang("Amex"); ?></option>
                                            <option
                                                value="Discover"<?= $payback->cc_type == 'Discover' ? ' checked="checcked"' : '' ?>><?= lang("Discover"); ?></option>
                                        </select>
                                    </div>
                                </div>
								
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input name="pcc_month" value="<?= $payback->cc_month; ?>" type="text"
                                               id="pcc_month_1" class="form-control"
                                               placeholder="<?= lang('month') ?>"/>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">

                                        <input name="pcc_year" value="<?= $payback->cc_year; ?>" type="text"
                                               id="pcc_year_1" class="form-control" placeholder="<?= lang('year') ?>"/>
                                    </div>
                                </div>
								
                            </div>
                        </div>
                        <div class="pcheque_1" style="display:none;">
                            <div class="form-group"><?= lang("cheque_no", "cheque_no_1"); ?>
                                <input name="cheque_no" value="<?= $payback->cheque_no; ?>" type="text" id="cheque_no_1"
                                       class="form-control cheque_no"/>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>

            </div>

            <div class="form-group">
                <?= lang("attachment", "attachment") ?>
                <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false"
                       class="form-control file">
            </div>

            <div class="form-group">
                <?= lang("note", "note"); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $payback->note), 'class="form-control" id="note"'); ?>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_payback', lang('edit_payback'), 'class="btn btn-primary"'); ?>
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
		$(".c_amount").on("keyup",function(){
			var c_total = 0;
			$(".c_amount").each(function(){
				var amount = ($(this).val()-0);
				var rate = ($(this).attr("rate")-0);
				var camount = amount / rate;
					c_total += camount;
			});
			$("#amount_1").val(c_total);	
		});	
		
        $.fn.datetimepicker.dates['bms'] = <?=$dp_lang?>;
        $(document).on('change', '.paid_by', function () {
            var p_val = $(this).val();
            localStorage.setItem('paid_by', p_val);
            if(p_val == 'deposit'){
				$('.paying_to_box').slideUp();
			}else if (p_val == 'cash') {
                $('.pcheque_1').hide();
                $('.pcc_1').hide();
                $('.pcash_1').show();
                $('#amount_1').focus();
				$('.paying_to_box').slideDown();
            } else if (p_val == 'CC') {
                $('.pcheque_1').hide();
                $('.pcash_1').hide();
                $('.pcc_1').show();
                $('#pcc_no_1').focus();
				$('.paying_to_box').slideDown();
            } else if (p_val == 'Cheque') {
                $('.pcc_1').hide();
                $('.pcash_1').hide();
                $('.pcheque_1').show();
                $('#cheque_no_1').focus();
				$('.paying_to_box').slideDown();
            } else {
                $('.pcheque_1').hide();
                $('.pcc_1').hide();
                $('.pcash_1').hide();
				$('.paying_to_box').slideDown();
            }
        });
        var p_val = '<?=$payback->paid_by?>';
        localStorage.setItem('paid_by', p_val);
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
        $('#paid_by_1').select2("val", '<?=$payback->paid_by?>');
    });
</script>
