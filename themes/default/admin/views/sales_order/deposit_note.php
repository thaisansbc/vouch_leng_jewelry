<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = 5;// $this->config->item('form_max_row');
	$font_size = $this->config->item('font_size');
	$td_line_height = $font_size + 15;
	$min_height = $font_size * 6; 
	$margin = $font_size - 5;
	$margin_signature = $font_size * 5;
?>
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-body">
	<?= $this->site->RePrint($print); ?>
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
								<?= $this->bpas->qrcode('link', urlencode(admin_url('sale_orders/deposit_note/' . $deposit->id)), 2); ?>
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
							<td class="text_center" style="width:25%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('receipt_voucher') ?></i></b></span></td>
							<td valign="bottom" style="width:15%"><hr class="hr_title"></td>
						</tr>
					</table>
				</th>
			</tr>
			<?php
				if ($deposit->paid_by == 'gift_card' || $deposit->paid_by == 'CC' || $deposit->paid_by == 'ppp' || $deposit->paid_by == 'stripe' || $deposit->paid_by == 'authorize') {
					$deposit_info = ' (' . substr($deposit->cc_no, -4) . ')';
				} elseif ($deposit->paid_by == 'Cheque') {
					$deposit_info = ' (' . $deposit->cheque_no . ')';
				}else{
					$deposit_info = '';
				}
			
			?>
			<tr>
				<th>
					<table>
						<tr>
							<td style="width:50%">
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
							<td style="width:50%">
								<fieldset style="margin-left:5px !important">
									<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('reference') ?></i></b></legend>
									<table>
										<tr>
											<td><?= lang('payment_reference') ?></td>
											<td style="text-align:left"> : <b><?= $deposit->reference_no ?></b></td>
										</tr>
										<tr>
											<td><?= lang('date') ?></td>
											<td style="text-align:left"> : <?= $this->bpas->hrsd($deposit->date) ?></td>
										</tr>
										<tr>
											<td><?= lang('paid_by') ?></td>
											<td style="text-align:left"> : <b><?= $deposit->cash_account.$deposit_info ?></b></td>
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
				$footer_rowspan = 2;
				$i=1;
				$tbody .='<tr>
							<td class="text_center">'.$i.'</td>
							<td class="text_left">'.$sale_order->reference_no.'</td>
							<td class="text_center">'.$this->bpas->hrsd($sale_order->date).'</td>
							<td class="text_right">'.$this->bpas->formatMoney($deposit->amount).'</td>
						</tr>';		

				
			?>
			<tr>
				<td>
					<table class="table_item">
						<thead>
							<tr>
								<th><?= lang("#"); ?></th>
								<th><?= lang("reference"); ?></th>
								<th><?= lang("date"); ?></th>
								<th><?= lang("deposit"); ?></th>
							</tr>
						</thead>
						<tbody id="tbody">
							<?= $tbody ?>
						</tbody>
						<tbody id="tfooter">
							<tr>
								<td class="text_right" colspan="3"><b><?= lang('total') ?> : </b></td>
								<td class="text_right"><b><?= $this->bpas->formatMoney($deposit->amount) ?></b></td>
							</tr>
							<?php if($deposit->note){ ?>
								<tr>
									<td style="border:none !important" colspan="4"><b><?= lang('note') ?> : </b><?= $this->bpas->decode_html($deposit->note); ?></td>
								</tr>
							<?php } ?>
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
							<i class="fa fa-close"></i>
							<span class="hidden-sm hidden-xs"><?= lang('close') ?></span>
						</a>
					</div>
					<?php if(isset($inv_payments) && $inv_payments && $inv_payment->installment_item_id <= 0){ ?>
						<div class="btn-group">
							<a data-toggle="modal" data-target="#myModal2" class="tip btn btn-warning" href="<?= admin_url("sales/edit_multi_payment/".$sale_ids."/".$deposit->id) ?>" title="<?= lang('edit') ?>">
								<i class="fa fa-edit"></i>
								<span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
							</a>
						</div>
					<?php } ?>
					<div class="btn-group">
						<a onclick="window.print()"  aria-hidden="true" class="tip btn btn-success" title="<?= lang('print') ?>">
							<i class="fa fa-print"></i>
							<span class="hidden-sm hidden-xs"><?= lang('print') ?></span>
						</a>
					</div>
					<?php if ($deposit->attachment) { ?>
						<div class="btn-group">
							<a href="<?= admin_url('assets/uploads/' . $deposit->attachment) ?>" class="tip btn btn-primary" target="_blank" title="<?= lang('attachment') ?>">
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
		window.onafterprint = function(){		
			$.ajax({
				url : site.base_url + "sales/add_print",
				dataType : "JSON",
				type : "GET",
				data : { 
						transaction_id : <?= $deposit->id ?>,
						transaction : "Deposit Note",
						reference_no : "<?= $deposit->reference_no ?>"
					}
			});
		}
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
					td_html +='<td class="td_print">&nbsp;</td><tr>';
				$('#tbody').append(td_html);
			}
		}
		
    });
	
</script>
