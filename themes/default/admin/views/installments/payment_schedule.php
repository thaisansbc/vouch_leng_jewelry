<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row') - 60;
	$font_size = $this->config->item('font_size');
	$td_line_height = $font_size + 15;
	$min_height = $font_size * 6; 
	$margin = $font_size - 5;
	$margin_signature = $font_size * 5;
	$months = $installment->term;
	$number = 0;
	$end_payment_date = array();
	foreach($installment_items as $l){
		$number += 1;
		$end_payment_date[] = $l->deadline;
	}
	
?>
<div class="modal-dialog modal-lg no-modal-header">
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
										<div><?= ucfirst($biller->address).' '.ucfirst($biller->city) ?></div>
										<div><?= lang('tel').' : '. $biller->phone ?></div>	
										<div><?= lang('email').' : '. $biller->email ?></div>	
									</td>
									<td class="text_center" style="width:20%">
										<?= $this->bpas->qrcode('link', urlencode(admin_url('installments/payment_schedule/' . $id)), 2); ?>
									</td>
								</tr>
							</table>
						</th>
					</tr>
					<tr>
						<th>
							<table>
								<tr>
									<td valign="bottom" style="width:40%"><hr class="hr_title"></td>
									<td class="text_center" style="width:20%"><span style="font-size:<?= $font_size+5 ?>px"><b><span><?= lang('payment_schedule') ?></i></b></span></td>
									<td valign="bottom" style="width:40%"><hr class="hr_title"></td>
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
											<legend style="font-size:<?= $font_size ?>px"><b><span><?= lang('customer') ?></span></b></legend>
											<table>
												<tr>
													<td style="width:200px;"><?= lang('customer') ?></td>
													<td> : <strong><?= $customer->company ?></strong></td>
												</tr>
												<tr>
													<td><?= lang('phone') ?></td>
													<td> : <?= $customer->phone ?></td>
												</tr>
												<tr>
													<td><?= lang('first_payment_date') ?></td>
													<td> : <strong><?= $this->bpas->hrsd($installment->payment_date) ?></strong></td>
												</tr>
												<tr>
													<td><?= lang('last_payment_date') ?></td>
													<td> : <strong><?= $this->bpas->hrsd($end_payment_date[$number-1]) ?></strong></td>
												</tr>
												<?php if($installment->product_id){
													$row = $this->site->getProductByID($installment->product_id);
												?>
												<tr>
													<td><?= lang('product_name') ?></td>
													<td> : <strong><?= $row->name; ?></strong></td>
												</tr>
												<?php }?>
											</table>
										</fieldset>
									</td>
									<td style="width:40%">
										<fieldset style="margin-left:5px !important">
											<legend style="font-size:<?= $font_size ?>px"><b><span><?= lang('reference') ?></i></span></legend>
											<table>
												<tr>
													<td><?= lang('ref') ?></td>
													<td style="text-align:left"> : <b><?= $installment->reference_no ?></b></td>
												</tr>
												<tr>
													<td><?= lang('installment_amount') ?></td>
													<td style="text-align:left"> : <b><?= $this->bpas->formatMoney($installment->installment_amount) ?></b></td>
												</tr>
												<tr>
													<td><?= lang('deposit') ?></td>
													<td style="text-align:left"> : <b><?= $this->bpas->formatMoney($installment->deposit) ?></b></td>
												</tr>
												<tr>
													<td style="white-space:nowrap !important"><?= lang('principal_amount') ?></td>
													<td style="text-align:left"> : <b><?= $this->bpas->formatMoney($installment->principal_amount) ?></b></td>
												</tr>
												<tr>
													<td style="white-space:nowrap !important"><?= lang('interest_rate') ?></td>
													<td style="text-align:left"> : <b><?= $installment->interest_rate ?> <?php echo("%"); ?></b></td>
												</tr>
												<tr>
													<td><?= lang('term') ?></td>
													<td style="text-align:left"> : <b><?= ($months) ?></b></td>
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
						if($installment_items){
							$tbody = '<tr>
										<td colspan="5"></td>
										<td style="font-weight:bold" class="text_right">'.$this->bpas->formatMoney($installment->principal_amount).'</td>
										<td></td>
									</tr>';
							foreach ($installment_items as $i => $installment_item){
								$tbody .='<tr>
											<td class="text_center">'.($i+1).'</td>
											<td class="text_center">'.$this->bpas->hrsd($installment_item->deadline).'</td>
											<td class="text_right">'.$this->bpas->formatMoney($installment_item->payment).'</td>
											<td class="text_right">'.$this->bpas->formatMoney($installment_item->interest).'</td>
											<td class="text_right">'.$this->bpas->formatMoney($installment_item->principal).'</td>
											<td class="text_right">'.$this->bpas->formatMoney($installment_item->balance).'</td>
											<td class="text_left">'.$installment_item->note.'</td>
										</tr>';		
							}
						}
					?>
					<tr>
						<td>
							<table class="table_item" style="margin-top:5px;">
								<thead>
									<tr>
										<th style="width:30px;"><?= lang("Nº"); ?></th>
										<th style="width:125px;"><?= lang("deadline"); ?></th>
										<th style="width:125px;"><?= lang("payment"); ?></th>
										<th style="width:125px;"><?= lang("interest"); ?></th>
										<th style="width:125px;"><?= lang("principal"); ?></th>
										<th style="width:125px;"><?= lang("balance"); ?></th>
										<th><?= lang("note"); ?></th>
									</tr>
								</thead>
								<tbody id="tbody">
									<?= $tbody ?>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
				<tfoot>
					<tr class="tr_print">
						<td>
							<table style="font-weight:bold; margin-top:<?= $margin_signature ?>px;">
								<tr>
									<td class="text_center" style="width:33%"><?= lang("contractor") .' '. lang("signature") ?></td>
									<td class="text_center" style="width:33%"><?= lang("witness").' '. lang("signature") ?></td>
									<td class="text_center" style="width:33%"><?= lang("customer").' '. lang("signature") ?></td>
								</tr>
								<tr>
									<td class="text_center" style="width:33%; padding-top:60px">______________________</td>
									<td class="text_center" style="width:33%; padding-top:60px">______________________</td>
									<td class="text_center" style="width:33%; padding-top:60px">______________________</td>
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
						<a class="tip btn btn-warning" href="<?= admin_url("installments/view/".$installment->id) ?>" title="<?= lang('print') ?>">
							<i class="fa fa-usd"></i>
							<span class="hidden-sm hidden-xs"><?= lang('installment_details') ?></span>
						</a>
					</div>
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
					<!--<div class="btn-group">
						<a href="<?= admin_url('installments/view_agreement/' . $installment->id) ?>" class="tip btn btn-success" title="<?= lang('agreement') ?>" data-toggle="modal" data-target="#myModal2" data-backdrop="static" data-keyboard="false">
							<i class="fa fa-file-text-o"></i>
							<span class="hidden-sm hidden-xs"><?= lang('agreement') ?></span>
						</a>
					</div>-->
					<?php if ($installment->status != 'inactive' && $installment->status != 'completed') { ?>
						<div class="btn-group">
							<a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line("delete_installment") ?></b>"
								data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('installments/delete/' . $installment->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
								data-html="true" data-placement="top">
								<i class="fa fa-trash-o"></i>
								<span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
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
		min-height:<?= ($min_height+50) ?>px !important;
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
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td></tr>';
				$('#tbody').append(td_html);
			}
		}
    });
</script>

