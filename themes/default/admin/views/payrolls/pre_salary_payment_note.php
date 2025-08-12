<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row') + 40;
	$font_size = $this->config->item('font_size');
	$td_line_height = $font_size + 15;
	$min_height = $font_size * 6; 
	$margin = $font_size - 5;
	$margin_signature = $font_size * 2;
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
										<?= '<img  src="'.base_url().'assets/uploads/logos/' . $biller->logo.'" alt="'.$biller->name.'">' ?>
									</td>
									<td class="text_center" style="width:60%">
										<div style="font-size:<?= $font_size+15 ?>px"><b><?= $biller->name ?></b></div>
										<div><?= $biller->address.$biller->city ?></div>
										<div><?= lang('tel').' : '. $biller->phone ?></div>	
										<div><?= lang('email').' : '. $biller->email ?></div>	
									</td>
									<td class="text_center" style="width:20%">
										<?= $this->bms->qrcode('link', urlencode(site_url('payrolls/pre_salary_payment_note/' . $payment->id)), 2); ?>
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
									<td class="text_center" style="width:25%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('pre_salary_payment') ?></i></b></span></td>
									<td valign="bottom" style="width:15%"><hr class="hr_title"></td>
								</tr>
							</table>
						</th>
					</tr>
					<tr>
						<th>
							<table>
								<tr>
									<td style="width:60%">
										<fieldset>
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('reference') ?></i></b></legend>
											<table>
												<tr>
													<td style="width:15%"><?= lang('date') ?></td>
													<td> : <strong><?= $this->bms->hrld($payment->date) ?></strong></td>
												</tr>
												<tr>
													<td style="width:15%"><?= lang('year') ?></td>
													<td> : <strong><?= $payment->year ?></strong></td>
												</tr>
												<tr>
													<td><?= lang('created_by') ?></td>
													<td> : <?= $created_by->last_name.' '.$created_by->first_name ?></td>
												</tr>
											</table>
										</fieldset>
									</td>
								</tr>
							</table>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
						$tbody = '';
						$i=1;
						$grand_total = 0;
						$grand_paid = 0;
						$grand_usd = 0;
						$grand_khr = 0;
						$total_usd_100 = 0;
						$total_usd_50 = 0;
						$total_usd_20 = 0;
						$total_usd_10 = 0;
						$total_khr_20000 = 0;
						$total_khr_10000 = 0;
						$total_khr_5000 = 0;
						$total_khr_2000 = 0;
						$total_khr_1000 = 0;
						$total_khr_500 = 0;
						$total_khr_100 = 0;
						foreach ($payment_items as $payment_item){
							$td_bank_note = "";
							if($this->config->item("bank_note")){
								$grand_usd += $payment_item->total_usd;
								$grand_khr += $payment_item->total_khr;
								$total_usd_100 += $payment_item->usd_100;
								$total_usd_50 += $payment_item->usd_50;
								$total_usd_20 += $payment_item->usd_20;
								$total_usd_10 += $payment_item->usd_10;
								$total_khr_20000 += $payment_item->khr_20000;
								$total_khr_10000 += $payment_item->khr_10000;
								$total_khr_5000 += $payment_item->khr_5000;
								$total_khr_2000 += $payment_item->khr_2000;
								$total_khr_1000 += $payment_item->khr_1000;
								$total_khr_500 += $payment_item->khr_500;
								$total_khr_100 += $payment_item->khr_100;
								
								$td_bank_note .= '<td class="text_right">'.$this->bms->formatMoney($payment_item->total_usd).'</th>';
								$td_bank_note .= '<td class="text_right">'.$this->bms->formatMoneyKH($payment_item->total_khr).'</th>';
								$td_bank_note .= '<td class="text_center">'.($payment_item->usd_100 ? $payment_item->usd_100 : '').'</th>';
								$td_bank_note .= '<td class="text_center">'.($payment_item->usd_50 ? $payment_item->usd_50 : '').'</th>';
								$td_bank_note .= '<td class="text_center">'.($payment_item->usd_20 ? $payment_item->usd_20 : '').'</th>';
								$td_bank_note .= '<td class="text_center">'.($payment_item->usd_10 ? $payment_item->usd_10 : '').'</th>';
								$td_bank_note .= '<td class="text_center">'.($payment_item->khr_20000 ? $payment_item->khr_20000 : '').'</th>';
								$td_bank_note .= '<td class="text_center">'.($payment_item->khr_10000 ? $payment_item->khr_10000 : '').'</th>';
								$td_bank_note .= '<td class="text_center">'.($payment_item->khr_5000 ? $payment_item->khr_5000 : '').'</th>';
								$td_bank_note .= '<td class="text_center">'.($payment_item->khr_2000 ? $payment_item->khr_2000 : '').'</th>';
								$td_bank_note .= '<td class="text_center">'.($payment_item->khr_1000 ? $payment_item->khr_1000 : '').'</th>';
								$td_bank_note .= '<td class="text_center">'.($payment_item->khr_500 ? $payment_item->khr_500 : '').'</th>';
								$td_bank_note .= '<td class="text_center">'.($payment_item->khr_100 ? $payment_item->khr_100 : '').'</th>';

							}
							$tbody .='<tr>
											<td class="text_center">'.$i.'</td>
											<td class="text_center">'.$payment_item->empcode.'</td>
											<td class="text_left">'.$payment_item->lastname.' '.$payment_item->firstname.'</td>
											<td class="text_right">'.$this->bms->formatMoney($payment_item->gross_salary).'</td>
											<td class="text_right">'.$this->bms->formatMoney($payment_item->amount).'</td>
											'.$td_bank_note.'
										</tr>';	
							$grand_total += $payment_item->gross_salary;	
							$grand_paid += $payment_item->amount;	
							$i++;
						}
						$tf_bank_note = "";
						if($this->config->item("bank_note")){
							$tf_bank_note .= '<td class="text_right">'.$this->bms->formatMoney($grand_usd).'</th>';
							$tf_bank_note .= '<td class="text_right">'.$this->bms->formatMoneyKH($grand_khr).'</th>';
							$tf_bank_note .= '<td class="text_center">'.$total_usd_100.'</th>';
							$tf_bank_note .= '<td class="text_center">'.$total_usd_50.'</th>';
							$tf_bank_note .= '<td class="text_center">'.$total_usd_20.'</th>';
							$tf_bank_note .= '<td class="text_center">'.$total_usd_10.'</th>';
							$tf_bank_note .= '<td class="text_center">'.$total_khr_20000.'</th>';
							$tf_bank_note .= '<td class="text_center">'.$total_khr_10000.'</th>';
							$tf_bank_note .= '<td class="text_center">'.$total_khr_5000.'</th>';
							$tf_bank_note .= '<td class="text_center">'.$total_khr_2000.'</th>';
							$tf_bank_note .= '<td class="text_center">'.$total_khr_1000.'</th>';
							$tf_bank_note .= '<td class="text_center">'.$total_khr_500.'</th>';
							$tf_bank_note .= '<td class="text_center">'.$total_khr_100.'</th>';
						}
						
						$tfooter = '<tr>
										<td colspan="3" class="text_right"><b>'.lang('total').'</b></td>
										<td class="text_right"><b>'.$this->bms->formatMoney($grand_total).'</b></td>
										<td class="text_right"><b>'.$this->bms->formatMoney($grand_paid).'</b></td>
										'.$tf_bank_note.'
									</tr>';
					?>
					<tr>
						<td>
							<table class="table_item">
								<thead>
									<tr>
										<th rowspan="2"><?= lang("#"); ?></th>
										<th rowspan="2"><?= lang("code"); ?></th>
										<th rowspan="2"><?= lang("name"); ?></th>
										<th rowspan="2"><?= lang("gross_salary"); ?></th>
										<th rowspan="2"><?= lang("amount"); ?></th>
										<?php
											if($this->config->item("bank_note")){
												$th_bank_note = "<th rowspan='2'>".lang("total_usd")."</th>";
												$th_bank_note .= "<th rowspan='2'>".lang("total_khr")."</th>";
												$th_bank_note .= "<th colspan='4'>".lang("usd")."</th>";
												$th_bank_note .= "<th colspan='7'>".lang("khr")."</th>";
												
												$th_bank_note .= "<tr>
																	<th>100</th>
																	<th>50</th>
																	<th>20</th>
																	<th>10</th>
																	<th>20000</th>
																	<th>10000</th>
																	<th>5000</th>
																	<th>2000</th>
																	<th>1000</th>
																	<th>500</th>
																	<th>100</th>
																</tr>";
												echo $th_bank_note;
											}
										?>
										
									</tr>
								</thead>
								<tbody id="tbody">
									<?= $tbody ?>
								</tbody>
								<tbody id="tfooter">
									<?= $tfooter ?>
									<?php
										if($payment->note){
											echo "<tr><td colspan='5'>".$this->bms->decode_html($payment->note)."</td></tr>";
										}
									?>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
				<tfoot>
					<tr class="tr_print">
						<td>
							<table style="margin-top:<?= $margin_signature ?>px;">
								<tr>
									<td class="text_center" style="width:50%"><?= lang("approver") .' '. lang("signature") ?></td>
									<td class="text_center" style="width:50%"><?= lang("preparer").' '. lang("signature") ?></td>
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
							<i class="fa fa-close"></i>
							<span class="hidden-sm hidden-xs"><?= lang('close') ?></span>
						</a>
					</div>
					<div class="btn-group">
						<a onclick="window.print()"  aria-hidden="true" class="tip btn btn-success" title="<?= lang('print') ?>">
							<i class="fa fa-print"></i>
							<span class="hidden-sm hidden-xs"><?= lang('print') ?></span>
						</a>
					</div>
					<?php if ($payment->attachment) { ?>
						<div class="btn-group">
							<a href="<?= site_url('welcome/download/' . $payment->attachment) ?>" class="tip btn btn-primary" target="_blank" title="<?= lang('attachment') ?>">
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
		.modal-dialog{
			<?= $hide_print ?>
		}
		#myModal .modal-content {
            display: none !important;
        }
		.bg-text{
			display:block !important;
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
<script type="text/javascript">
    $(document).ready( function() {
		window.addEventListener("beforeprint", function(event) { addTr();});
		function addTr(){
			$('.blank_tr').remove();
			var page_height = <?= $max_row_limit ?>;
			var form_height = $('.table_item').height()-0;
			if(form_height > page_height && (form_height - page_height) > 15){
				var pages = Math.ceil(form_height / page_height);
				page_height = (page_height - (15 * (pages + 1))) * pages;
			}
			var blank_height = page_height - form_height;
			if(blank_height > 0){
				var td_html = '<tr class="tr_print blank_tr">';
					td_html +='<td class="td_print"><div style="height:'+blank_height+'px !important">&nbsp;</div></td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					<?php if($this->config->item("bank_note")){ ?>
						td_html +='<td class="td_print">&nbsp;</td>';
						td_html +='<td class="td_print">&nbsp;</td>';
						td_html +='<td class="td_print">&nbsp;</td>';
						td_html +='<td class="td_print">&nbsp;</td>';
						td_html +='<td class="td_print">&nbsp;</td>';
						td_html +='<td class="td_print">&nbsp;</td>';
						td_html +='<td class="td_print">&nbsp;</td>';
						td_html +='<td class="td_print">&nbsp;</td>';
						td_html +='<td class="td_print">&nbsp;</td>';
						td_html +='<td class="td_print">&nbsp;</td>';
						td_html +='<td class="td_print">&nbsp;</td>';
						td_html +='<td class="td_print">&nbsp;</td>';
						td_html +='<td class="td_print">&nbsp;</td>';
					<?php } ?>
					td_html +='<td class="td_print">&nbsp;</td></tr>';
				$('#tbody').append(td_html);
			}
		}
		
    });
</script>

