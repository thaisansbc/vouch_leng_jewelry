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
										<?= $this->bpas->qrcode('link', urlencode(site_url('accountings/view_enter_journal/' . $inv->id)), 2); ?>
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
									<?php if($inv->jn_type == "journal"){ ?>
										<td class="text_center" style="width:20%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('enter_journals') ?></i></b></span></td>
									<?php }else if($inv->jn_type == "payment"){ ?>
										<td class="text_center" style="width:20%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('payment_voucher') ?></i></b></span></td>
									<?php }else{ ?>
										<td class="text_center" style="width:20%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('receipt_voucher') ?></i></b></span></td>
									<?php } ?>
									
									<td valign="bottom" style="width:20%"><hr class="hr_title"></td>
								</tr>
							</table>
						</th>
					</tr>
					<tr>
						<th>
							<table>
								<tr>
									<td>
										<fieldset style="margin-left:5px !important">
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('reference') ?></i></b></legend>
											<table>
												<tr>
													<td><?= lang('ref') ?></td>
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
						$total = 0;
						foreach ($rows as $row){
							if($row->amount < 0){
								$credit = $this->bpas->formatMoney(abs($row->amount));
								$debit = '';
							}else{
								$total += $row->amount;
								$credit = '';
								$debit = $this->bpas->formatMoney($row->amount);
							}
							$tbody .='<tr>
											<td class="text_center">'.$i.'</td>
											<td class="text_left">'.$row->account_code.' - '.$row->account_name.'</td>
											<td class="text_right">'.$debit.'</td>
											<td class="text_right">'.$credit.'</td>
											<td class="text_right">'.$row->description.'</td>
										</tr>';		
							$i++;
						}
					?>
					<tr>
						<td>
							<table class="table_item">
								<thead>
									<tr>
										<th><?= lang("#"); ?></th>
										<th><?= lang("account"); ?></th>
										<th><?= lang("debit"); ?></th>
										<th><?= lang("credit"); ?></th>
										<th><?= lang("description"); ?></th>
									</tr>
								</thead>
								<tbody id="tbody">
									<?= $tbody ?>
								</tbody>
								<tfoot>
									<tr>
										<td colspan="2" class="text_right"><b><?= lang('total') ?> : </b></td>
										<td colspan="2" class="text_right"><b><?= $this->bpas->formatMoney($total) ?></b></td>
										<td></td>
									</tr>
									<?php if($inv->note){ ?>
										<tr>
											<td style="border:0px !important" colspan="4"><b><?= lang('note') ?> : </b> <?= $this->bpas->decode_html($inv->note)  ?></td>
										</tr>
									<?php } ?>
								</tfoot>
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
									<td class="text_center" style="width:50%"><?= lang("approver").' '. lang("signature") ?></td>
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
					<?php if ($inv->attachment) { ?>
						<div class="btn-group">
							<a href="<?= site_url('welcome/download/' . $inv->attachment) ?>" class="tip btn btn-primary" target="_blank" title="<?= lang('attachment') ?>">
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
						transaction_id : <?= $inv->id ?>,
						transaction : "Enter Journal",
						reference_no : "<?= $inv->reference_no ?>"
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
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td></tr>';
				$('#tbody').append(td_html);
			}
		}
    });
	
</script>

