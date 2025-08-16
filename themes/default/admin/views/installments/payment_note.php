<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row');
	$font_size = $this->config->item('font_size');
	$td_line_height = $font_size + 15;
	$min_height = $font_size * 6; 
	$margin = $font_size - 5;
	$margin_signature = $font_size * 5;
?>
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-body">
			<table>
				<thead>
					<tr>
						<th>
							<table>
								<tr>
									<td class="text_center" style="width:20%">
										<?php
											if($biller->logo){
												echo '<img  src="'.base_url().'assets/uploads/logos/' . $biller->logo.'" alt="'.$biller->name.'">';
											}
										?>
									</td>
									<td class="text_center" style="width:60%">
										<div style="font-size:<?= $font_size+15 ?>px"><b><?= $biller->name ?></b></div>
										<div><?= $biller->address.$biller->city ?></div>
										<div><?= lang('tel').' : '. $biller->phone ?></div>	
										<div><?= lang('email').' : '. $biller->email ?></div>	
									</td>
									<td class="text_center" style="width:20%">
										<?= $this->bpas->qrcode('link', urlencode(admin_url('sales/payment_note/' . $payment->id)), 2); ?>
									</td>
								</tr>
							</table>
						</th>
					</tr>
					<tr>
						<th>
							<table>
								<tr>
									<td valign="bottom" style="width:60%"><hr class="hr_title"></td>
									<?php if($payment->type == 'returned'){ ?>
										<td class="text_center" style="width:25%"><span style="font-size:<?= $font_size+5 ?>px"><b><span><?= lang('payment_voucher') ?></i></b></span></td>
									<?php }else{ ?>
										<td class="text_center" style="width:25%"><span style="font-size:<?= $font_size+5 ?>px"><b><span><?= lang('receipt_voucher') ?></i></b></span></td>
									<?php } ?>
									<td valign="bottom" style="width:15%"><hr class="hr_title"></td>
								</tr>
							</table>
						</th>
					</tr>
					<?php
						if ($payment->paid_by == 'gift_card' || $payment->paid_by == 'CC' || $payment->paid_by == 'ppp' || $payment->paid_by == 'stripe' || $payment->paid_by == 'authorize') {
							$payment_info = ' (' . substr($payment->cc_no, -4) . ')';
						} elseif ($payment->paid_by == 'Cheque') {
							$payment_info = ' (' . $payment->cheque_no . ')';
						}else{
							$payment_info = '';
						}
					
					?>
					<tr>
						<th>
							<table>
								<tr>
									<td style="width:50%">
										<fieldset>
											<legend style="font-size:<?= $font_size ?>px"><b><span><?= lang('customer') ?></i></b></legend>
											<table>
												<tr>
													<td><?= lang('name') ?></td>
													<td> : <strong><?= $customer->company ?></strong></td>
												</tr>
												<tr>
													<td><?= lang('tel') ?></td>
													<td> : <?= $customer->phone ?></td>
												</tr>
												<tr>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
												</tr>
												<tr>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
												</tr>
												<tr>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
												</tr>
											</table>
										</fieldset>
									</td>
									<td style="width:50%">
										<fieldset style="margin-left:5px !important">
											<legend style="font-size:<?= $font_size ?>px"><b><span><?= lang('reference') ?></span></b></legend>
											<table>
												<tr>
													<td><?= lang('ref') ?></td>
													<td style="text-align:left"> : <b><?= $inv->reference_no ?></b></td>
												</tr>
												<tr>
													<td><?= lang('payment_reference') ?></td>
													<td style="text-align:left"> : <b><?= $payment->reference_no ?></b></td>
												</tr>
												<tr>
													<td><?= lang('date') ?></td>
													<td style="text-align:left"> : <?= $this->bpas->hrsd($payment->date) ?></td>
												</tr>
												<tr>
													<td><?= lang('amount') ?></td>
													<td style="text-align:left"> : <b><?= $this->bpas->formatMoney($payment->amount + $payment->interest_paid) ?></b></td>
												</tr>
												<tr>
													<td><?= lang('paid_by') ?></td>
													<td style="text-align:left"> : <b><?= ($payment->cash_account).''.$payment_info ?></b></td>
												</tr>
											</table>
										</fieldset>
									</td>
								</tr>
								<?php if($payment->note){ ?>
									<tr>
										<td><b><?= lang('note') ?> : </b><?= html_entity_decode($payment->note); ?></td>
									</tr>
								<?php } ?>
							</table>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr class="tr_print">
						<td>
							<table style="margin-top:<?= $margin_signature ?>px;">
								<tr>
									<td class="text_center" style="width:50%"><?= lang("preparer") .' '. lang("signature") ?></td>
									<td class="text_center" style="width:50%"><?= lang("customer").' '. lang("signature") ?></td>
								</tr>
								<tr>
									<td class="text_center" style="width:50%; padding-top:60px">______________________</td>
									<td class="text_center" style="width:50%; padding-top:60px">______________________</td>
								</tr>
							</table>
						</td>
					</tr>
				</tfoot>
			</table>
			<div id="buttons" style="padding-top:10px;" class="no-print">
				<hr>
				<div class="btn-group btn-group-justified">
					<div class="btn-group">
						<a data-dismiss="modal" aria-hidden="true" class="tip btn btn-danger" title="<?= lang('close') ?>">
							<span class="hidden-sm hidden-xs"><?= lang('close') ?></span>
						</a>
					</div>
					<div class="btn-group">
						<a onclick="window.print()"  aria-hidden="true" class="tip btn btn-success" title="<?= lang('print') ?>">
							<span class="hidden-sm hidden-xs"><?= lang('print') ?></span>
						</a>
					</div>
					<?php if ($payment->attachment) { ?>
						<div class="btn-group">
							<a href="<?= admin_url('welcome/download/' . $payment->attachment) ?>" class="tip btn btn-primary" target="_blank" title="<?= lang('attachment') ?>">
								<i class="fa fa-download"></i>
								<span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
							</a>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>
<style>
	@media print{
		.no-print{
			display:none !important;
		}
		.tr_print{
			display:table-row !important;
		}
		#myModal .modal-content {
            display: none !important;
        }
		@page{
			margin: 5mm; 
		}
		body {
			-webkit-print-color-adjust: exact !important;  
			color-adjust: exact !important;         
		}
	}
	.tr_print{
		display:none;
	}
	#tbody .td_print{
		border:none !important;
		border-left:1px solid black !important;
		border-right:1px solid black !important;
		border-bottom:1px solid black !important;
	}
	.hr_title{
		border:3px double #428BCD !important;
		margin-bottom:<?= $margin ?>px !important;
		margin-top:<?= $margin ?>px !important;
	}
	.table_item th{
		border:1px solid black !important;
		background-color : #428BCD !important;
		text-align:center !important;
		line-height:30px !important;
	}
	.table_item td{
		border:1px solid black;
		line-height:<?=$td_line_height?>px !important;
	}
	.footer_des[rowspan] {
	  vertical-align: top !important;
	  text-align: left !important;
	  border:0px !important;
	}
	.text_center{
		text-align:center !important;
	}
	.text_left{
		text-align:left !important;
		padding-left:3px !important;
	}
	.text_right{
		text-align:right !important;
		padding-right:3px !important;
	}
	fieldset{
		-moz-border-radius: 9px !important;
		-webkit-border-radius: 15px !important;
		border-radius:9px !important;
		border:2px solid #428BCD !important;
		min-height:<?= $min_height ?>px !important;
		margin-bottom : <?= $margin ?>px !important;
		padding-left : <?= $margin ?>px !important;
	}
	legend{
		width: initial !important;
		margin-bottom: initial !important;
		border: initial !important;
	}
	.modal table{
		width:100% !important;
		font-size:<?= $font_size ?>px !important;
		border-collapse: collapse !important;
	}
</style>



