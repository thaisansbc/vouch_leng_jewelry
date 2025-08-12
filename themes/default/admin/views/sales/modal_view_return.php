<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row') + 60;
	$font_size = $this->config->item('font_size');
	$td_line_height = $font_size + 15;
	$min_height = $font_size * 6; 
	$margin = $font_size - 5;
	$margin_signature = $font_size * 2;
?>
<div class="modal-dialog modal-lg">
	<div class="no-print" style="height:15px;"></div>
	<?php if($Settings->watermark){
		echo '<p class="bg-text">';
			for($b=0; $b < 7; $b++){
				echo $biller->name.'<br>';
			}
		echo '</p>';
	}
	$hide_print = '';
	if($print==1){
		$hide_print = 'display:none !important;';
	}else if($print==2){
		if($Settings->watermark){
			echo '<p class="bg-text" style="transform:rotate(600deg) !important">';
				for($b=0; $b < 7; $b++){
					echo lang('re-print').'<br>';
				}
			echo '</p>';	
		}else{
			echo '<p class="bg-text">';
				for($b=0; $b < 7; $b++){
					echo lang('re-print').'<br>';
				}
			echo '</p>';
			
		}
		
	}
	?>	
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
								<?= $this->bpas->qrcode('link', urlencode(site_url('sales/view/' . $inv->id)), 2); ?>
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
							<td class="text_center" style="width:20%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('return_sale') ?></i></b></span></td>
							<td valign="bottom" style="width:20%"><hr class="hr_title"></td>
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
									<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('customer') ?></i></b></legend>
									<table>
										<tr>
											<td><?= lang('name') ?></td>
											<td> : <strong><?= $customer->company ?></strong></td>
										</tr>
										<tr>
											<td><?= lang('address') ?></td>
											<td> : <?= $customer->address.$customer->city ?></td>
										</tr>
										<tr>
											<td><?= lang('tel') ?></td>
											<td> : <?= $customer->phone ?></td>
										</tr>
									</table>
								</fieldset>
							</td>
							<td style="width:40%">
								<fieldset style="margin-left:5px !important">
									<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('reference') ?></i></b></legend>
									<table>
										<tr>
											<td><?= lang('ref') ?></td>
											<td style="text-align:left"> : <b><?= $inv->return_sale_ref ?></b></td>
										</tr>
										<tr>
											<td><?= lang('si_reference') ?></td>
											<td style="text-align:left"> : <b><?= $inv->reference_no ?></b></td>
										</tr>
										<tr>
											<td><?= lang('date') ?></td>
											<td style="text-align:left"> : <?= $this->bpas->hrsd($inv->date) ?></td>
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
				foreach ($rows as $row){
					if ($inv->product_discount != 0) {
						$td_discount = '<td class="text_right">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->bpas->formatMoney($row->item_discount) . '</td>';
					}else{
						$td_discount = '';
					}

					$tbody .='<tr>
									<td class="text_center">'.$i.'</td>
									<td class="text_center">'.$row->product_code.'</td>
									<td class="text_left">
										'.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '').'
										'.($row->details ? '<br>' . $row->details : '').'
										'.($row->comment ? '<br>' . $row->comment : '').'
										'.($row->serial_no ? '<br>' . $row->serial_no : '').'
									</td>
									<td class="text_center">'.$this->bpas->formatQuantity(abs($row->unit_quantity)).' '.$row->unit_name.'</td>
									<td class="text_right">'.$this->bpas->formatMoney($row->unit_price).'</td>
									'.$td_discount.'
									<td class="text_right">'.$this->bpas->formatMoney(abs($row->subtotal)).'</td>
								</tr>';		
					$i++;
				}
				
				$footer_colspan = 4;
				$footer_rowspan = 1;
				if($inv->product_discount != 0){
					$footer_colspan++;
				}
				if($inv->grand_total != $inv->total){
					$footer_rowspan++;
				}
				
				if($inv->order_discount != 0){
					$footer_rowspan++;
				}
				if($inv->order_tax != 0){
					$footer_rowspan++;
				}
				if($inv->shipping != 0){
					$footer_rowspan++;
				}
				if ($inv->surcharge != 0) {
					$footer_rowspan++;
				}
				if($payment){
					if ($payment->amount != 0) {
						$footer_rowspan++;
					}
					if ($payment->discount != 0) {
						$footer_rowspan++;
					}	
				}
				$credit_interest=0;
				if ($Settings->module_installment == 1) {
					if($sale_payments){
						foreach($sale_payments as $sale_payment){
							$credit_interest += $sale_payment->interest_paid;
						}
					}
					$credit_interest -= $inv->surcharge_interest;
				}
				if($credit_interest){
					$footer_rowspan++;
				}
				if($inv->surcharge_interest){
					$footer_rowspan++;
				}
				
				$amount_balance = ($inv->grand_total + -($credit_interest)) - (($payment?($payment->amount + $payment->discount):0));
				
				if($amount_balance <> $inv->grand_total){
					$footer_rowspan++;
				}
				$tfooter = '';
				$footer_note = '<td class="footer_des" rowspan="'.$footer_rowspan.'" colspan="'.$footer_colspan.'">'.$this->bpas->decode_html($inv->note).'</td>';
				
				if ($inv->grand_total != $inv->total) {
					$tfooter .= '<tr>
									'.$footer_note.'
									<td class="text_right"><b>'.lang("total").'</b></td>
									<td class="text_right"><b>'.$this->bpas->formatMoney(abs($inv->total)).'</b></td>
								</tr>';
					$footer_note = '';		
				}
				if($credit_interest){
					$tfooter .= '<tr>
									'.$footer_note.'
									<td class="text_right"><b>'.lang("credit_interest").'</b></td>
									<td class="text_right"><b>'.$this->bpas->formatMoney($credit_interest).'</b></td>
								</tr>';
					$footer_note = '';
				}
				if($inv->surcharge_interest){
					$tfooter .= '<tr>
									'.$footer_note.'
									<td class="text_right"><b>'.lang("surcharge_interest").'</b></td>
									<td class="text_right"><b>'.$this->bpas->formatMoney($inv->surcharge_interest).'</b></td>
								</tr>';
					$footer_note = '';	
				}

				if ($inv->surcharge != 0) {
					$tfooter .= '<tr>
									'.$footer_note.'
									<td class="text_right"><b>'.lang("surcharge").'</b></td>
									<td class="text_right"><b>'.$this->bpas->formatMoney($inv->surcharge).'</b></td>
								</tr>';
					$footer_note = '';		
				}
				
				if ($inv->order_discount != 0) {
					$tfooter .= '<tr>
									'.$footer_note.'
									<td class="text_right"><b>'.lang("order_discount").'</b></td>
									<td class="text_right"><b>'.($inv->order_discount_id ? '<small>('.$inv->order_discount_id.')</small> ' : '') . $this->bpas->formatMoney(abs($inv->order_discount)).'</b></td>
								</tr>';
					$footer_note = '';		
				}
				if ($inv->order_tax  != 0) {
					$tfooter .= '<tr>
									'.$footer_note.'
									<td class="text_right"><b>'.lang("order_tax").'</b></td>
									<td class="text_right"><b>'.$this->bpas->formatMoney(abs($inv->order_tax)).'</b></td>
								</tr>';
					$footer_note = '';		
				}
				if ($inv->shipping  != 0) {
					$tfooter .= '<tr>
									'.$footer_note.'
									<td class="text_right"><b>'.lang("shipping").'</b></td>
									<td class="text_right"><b>'.$this->bpas->formatMoney(abs($inv->shipping)).'</b></td>
								</tr>';
					$footer_note = '';		
				}
				$tfooter .= '<tr>
								'.$footer_note.'
								<td class="text_right"><b>'.lang("grand_total").'</b></td>
								<td class="text_right"><b>'.$this->bpas->formatMoney(abs($inv->grand_total) + $credit_interest).'</b></td>
							</tr>';
					$footer_note = '';	
				if($payment){
					if ($payment->amount != 0) {
						$tfooter .= '<tr>
										<td class="text_right"><b>'.lang("paid").'</b></td>
										<td class="text_right"><b>'.$this->bpas->formatMoney(abs($payment->amount) + abs($payment->interest_paid)).'</b></td>
									</tr>';
						$footer_note = '';		
					}	
					if ($payment->discount != 0) {
						$tfooter .= '<tr>
										<td class="text_right"><b>'.lang("discount").'</b></td>
										<td class="text_right"><b>'.$this->bpas->formatMoney(abs($payment->discount)).'</b></td>
									</tr>';
						$footer_note = '';		
					}
				}
				
				if($amount_balance <> $inv->grand_total){
					$tfooter .= '<tr>
									<td class="text_right"><b>'.lang("balance").'</b></td>
									<td class="text_right"><b>'.$this->bpas->formatMoney(abs($amount_balance)).'</b></td>
								</tr>';
					$footer_note = '';		
				}
			?>
			<tr>
				<td>
					<table class="table_item">
						<thead>
							<tr>
								<th><?= lang("#"); ?></th>
								<th><?= lang("code"); ?></th>
								<th><?= lang("description"); ?></th>
								<th><?= lang("quantity"); ?></th>
								<th><?= lang("unit_price"); ?></th>
								<?php 
									if($inv->product_discount != 0){
										echo '<th>'.lang("discount").'</th>';
									}
								?>
								<th><?= lang("subtotal"); ?></th>
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
					<table style="margin-top:<?= $margin_signature ?>px;">
						<tr>
							<td class="text_center" style="width:50%"><?= lang("contractor") .' '. lang("signature") ?></td>
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
	<div class="clearfix"></div>
	<div class="buttons" style="margin-top:15px; margin-bottom:15px">
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
			<?php if ($inv->attachment) { ?>
				<div class="btn-group">
					<a href="<?= site_url('assets/uploads/' . $inv->attachment) ?>" class="tip btn btn-primary" target="_blank" title="<?= lang('attachment') ?>">
						<i class="fa fa-download"></i>
						<span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
					</a>
				</div>
			<?php } ?>
			<div class="btn-group">
				<a href="<?= site_url('sales/add_payment_return/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('view') ?>" data-toggle="modal" data-target="#myModal2">
					<i class="fa fa-dollar"></i>
					<span class="hidden-sm hidden-xs"><?= lang('add_payment') ?></span>
				</a>
			</div>
			<div class="btn-group">
				<a href="<?= site_url('sales/payments/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('view') ?>" data-toggle="modal" data-target="#myModal2">
					<i class="fa fa fa-money"></i>
					<span class="hidden-sm hidden-xs"><?= lang('view_payments') ?></span>
				</a>
			</div>
			<?php if ($inv->attachment) { ?>
				<div class="btn-group">
					<a href="<?= site_url('welcome/download/' . $inv->attachment) ?>" class="tip btn btn-primary" title="<?= lang('attachment') ?>">
						<i class="fa fa-chain"></i>
						<span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
					</a>
				</div>
			<?php } ?>
			<div class="btn-group">
				<a href="<?= site_url('sales/email/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal2" class="tip btn btn-primary" title="<?= lang('email') ?>">
					<i class="fa fa-envelope-o"></i>
					<span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
				</a>
			</div>

			<?php if ( ! $inv->sale_id) { ?>
			<div class="btn-group">
				<a href="<?= site_url('sales/edit/' . $inv->id) ?>" class="tip btn btn-warning sledit" title="<?= lang('edit') ?>">
					<i class="fa fa-edit"></i>
					<span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
				</a>
			</div>
			<div class="btn-group">
				<a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line("delete_sale") ?></b>"
					data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= site_url('sales/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
					data-html="true" data-placement="top">
					<i class="fa fa-trash-o"></i>
					<span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
				</a>
			</div>
			<?php } ?>
		</div>
	</div>
	<div class="clearfix"></div>
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
	.modal-dialog{
		background-color:white !important;
		padding-left:12px; !important;
		padding-right:12px; !important;
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
		window.onafterprint = function(){		
			$.ajax({
				url : site.base_url + "sales/add_print",
				dataType : "JSON",
				type : "GET",
				data : { 
						transaction_id : <?= $inv->id ?>,
						transaction : "Sale Return",
						reference_no : "<?= $inv->return_sale_ref ?>"
					}
			});
		}
		window.addEventListener("beforeprint", function(event) { addTr();});
		function addTr(){
			var page_name = "Sale Return - <?= $inv->customer.' - '.$inv->reference_no ?>";
			document.title = page_name;
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
					<?php if ($inv->product_discount != 0) { ?>
						td_html +='<td class="td_print">&nbsp;</td>';
					<?php } ?>
					td_html +='<td class="td_print">&nbsp;</td></tr>';
				$('#tbody').append(td_html);
			}
		}
		
    });
	
</script>

