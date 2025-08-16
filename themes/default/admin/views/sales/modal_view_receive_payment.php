<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row') + 50;
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
											if($logo){
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
										<?= $this->bpas->qrcode('link', urlencode(site_url('sales/modal_view_receive_payment/' . $receive_payment->id)), 2); ?>
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
									<td class="text_center" style="width:30%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('receive_payment') ?></i></b></span></td>
									<td valign="bottom" style="width:10%"><hr class="hr_title"></td>
								</tr>
							</table>
						</th>
					</tr>
					<tr>
						<th>
							<table>
								<tr>				
									<td style="width:49%">
										<fieldset style="margin-left:5px !important">
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('info') ?></i></b></legend>
											<table>	
												<?php if($receive_payment->paid_by){ 
													$td_paid_by = "";
													$cash_accounts = $this->site->getCashAccountByIDs(json_decode($receive_payment->paid_by));
													if($cash_accounts){
														$i = 1;
														foreach($cash_accounts as $cash_account){
															$td_paid_by .= ($i > 1 ? ", ":"").$cash_account->name;
															$i++;
														}
													}
													echo "<tr>
															<td>".lang('paid_by')."</td>
															<td>: ".$td_paid_by."</td>
														</tr>";
												} ?>
												<tr>
													<td><?= lang('from_date') ?></td>
													<td>: <?= $this->bpas->hrsd($receive_payment->from_date) ?></td>
												</tr>	
												<tr>
													<td><?= lang('to_date') ?></td>
													<td>: <?= $this->bpas->hrsd($receive_payment->to_date) ?></td>
												</tr>	
												
											</table>
										</fieldset>
									</td>
									<td style="width:49%">
										<fieldset style="margin-left:5px !important">
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('reference') ?></i></b></legend>
											<table>
												<tr>
													<td><?= lang('reference_no') ?></td>
													<td>: <?= $receive_payment->reference_no ?></td>
												</tr>	
												<tr>
													<td><?= lang('date') ?></td>
													<td>: <?= $this->bpas->hrsd($receive_payment->date) ?></td>
												</tr>	
												<tr>
													<td><?= lang('status') ?></td>
													<td>: <?= lang($receive_payment->status)?></td>
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
						$tbody = "";
						$i = 1;
						if($receive_payment_items){
							foreach($receive_payment_items as $receive_payment_item){

								$tbody .="<tr>
											<td class='text_center'>".$i++."</td>
											<td class='text_left'>".$receive_payment_item->payment_created_by."</td>
											<td class='text_center'>".$this->bpas->hrld($receive_payment_item->payment_date)."</td>
											<td class='text_center'>".$receive_payment_item->sale_ref."</td>
											<td class='text_center'>".$receive_payment_item->payment_ref."</td>
											<td class='text_left'>".$receive_payment_item->customer."</td>
											<td class='text_center'>".$receive_payment_item->payment_paid_by."</td>
											<td class='text_right'>".$this->bpas->formatMoney($receive_payment_item->payment_amount)."</td>
										</tr>";
							}
						}
						$footer_colspan = 6;
						$footer_note = '<td class="footer_des" colspan="'.$footer_colspan.'">'.$this->bpas->decode_html($receive_payment->note).'</td>';
						$tfooter = '<tr>
										'.$footer_note.'
										<td class="text_right"><b>'.lang("total").'</b></td>
										<td class="text_right"><b>'.$this->bpas->formatMoney($receive_payment->amount).'</b></td>
									</tr>';
										
					?>
					<tr>
						<td>
							<table class="table_item">
								<thead>
									<tr>
										<th><?= lang("#") ?></th>
										<th><?= lang("received_by") ?></th>
										<th><?= lang("date") ?></th>
										<th><?= lang("sale_ref") ?></th>
										<th><?= lang("payment_ref") ?></th>
										<th><?= lang("customer") ?></th>
										<th><?= lang("paid_by") ?></th>
										<th><?= lang("amount") ?></th>
									</tr>
								</thead>
								<tbody id="tbody">
									<?= $tbody ?>
								</tbody>
								<tbody id="tfooter">
									<?= $tfooter ?>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
				<tfoot>
					<tr class="tr_print">
						<td>
							<table style="margin-top:20px;">
								<tr>
									<td class="text_center" style="width:25%"><?= lang("received_by") ?></td>
									<td class="text_center" style="width:25%"><?= lang("checked_by") ?></td>
									<td class="text_center" style="width:25%"><?= lang("verified_by") ?></td>
									<td class="text_center" style="width:25%"><?= lang("approved_by") ?></td>
								</tr>
								<tr>
									<td class="text_center" style="width:25%; padding-top:60px"><?= $created_by->last_name.' '.$created_by->first_name ?></td>
									<td class="text_center" style="width:25%; padding-top:60px"><?= $receive_payment->checked_name ?></td>
									<td class="text_center" style="width:25%; padding-top:60px"><?= $receive_payment->verified_name ?></td>
									<td class="text_center" style="width:25%; padding-top:60px"><?= $receive_payment->approved_name ?></td>
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
	.bg-text{
		opacity: 0.1;
		color:lightblack;
		font-size:100px;
		position:absolute;
		transform:rotate(300deg);
		-webkit-transform:rotate(300deg);
		display:none;
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
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td></tr>';
				$('#tbody').append(td_html);
			}
		}
		
    });
	
</script>

