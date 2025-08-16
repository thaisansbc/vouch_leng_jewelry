<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row') - 200;
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
											if($logo){
												echo '<img  src="'.base_url().'assets/uploads/logos/' . $biller->logo.'" alt="'.(($biller->company !='-') ?$biller->company:$biller->name).'">';
											}
										?>
									</td>
									<td class="text_center" style="width:60%">
										<div style="font-size:<?= $font_size+15 ?>px"><b><?= ($biller->company !='-') ?$biller->company:$biller->name; ?></b></div>
										<div><?= $biller->address.$biller->city ?></div>
										<div><?= lang('tel').' : '. $biller->phone ?></div>	
										<div><?= lang('email').' : '. $biller->email ?></div>	
									</td>
									<td class="text_center" style="width:20%"></td>
								</tr>
							</table>
						</th>
					</tr>
					<tr>
						<th>
							<table>
								<tr>
									<td valign="bottom" style="width:55%"><hr class="hr_title"></td>
									<td class="text_center" style="width:25%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('fuel_sale_note') ?></i></b></span></td>
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
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('saleman') ?></i></b></legend>
											<table>
												<tr>
													<td><?= lang('name') ?></td>
													<td> : <strong><?= $saleman->last_name.' '.$saleman->first_name ?></strong></td>
												</tr>
												<tr>
													<td><?= lang('time') ?></td>
													<td> : <?= ($time->open_time .' - '.$time->close_time) ?></td>
												</tr>
											</table>
										</fieldset>
									</td>
									<td style="width:40%">
										<fieldset style="margin-left:5px !important">
										<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('Nº') ?></i></b></legend>
											<table>
												<tr>
													<td><?= lang('ref') ?></td>
													<td style="text-align:left"> : <b><?= $fuel_sale->reference_no ?></b></td>
												</tr>
												<tr>
													<td><?= lang('date') ?></td>
													<td style="text-align:left"> : <?= $this->bpas->hrld($fuel_sale->date) ?></td>
												</tr>
												<tr>
													<td><?= lang('created_by') ?></td>
													<td> : <strong><?= $created_by->last_name.' '.$created_by->first_name ?></strong></td>
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
						$i = 1;
						$tquantity = 0;
						$tsubtotal = 0;
						$cquantity = 0;
						$uquantity = 0;
						foreach ($rows as $row){
							$tquantity += $row->quantity;
							$tsubtotal += $row->subtotal;
							$cquantity += $row->customer_qty;
							$uquantity += $row->using_qty;
							$tbody .='<tr>
										<td class="text_left">'.$row->product_name.'</td>
										<td class="text_left">'.$row->tank_name.'</td>
										<td class="text_center">'.$row->nozzle_no.'</td>
										<td class="text_center">'.$this->bpas->formatQuantity($row->nozzle_start_no).'</td>
										<td class="text_center">'.$this->bpas->formatQuantity($row->nozzle_end_no).'</td>
										<td class="text_right">'.$this->bpas->formatMoney($row->unit_price).'</td>
										<td class="text_right">'.$this->bpas->formatQuantity($row->customer_qty).'</td>
										<td class="text_right">'.$this->bpas->formatQuantity($row->using_qty).'</td>
										<td class="text_right">'.$this->bpas->formatQuantity($row->quantity).'</td>
        								<td class="text_right">'.$this->bpas->formatMoney($row->subtotal).'</td>
									  </tr>';		
							$i++;
						}
						$tbody .= '<tr>
										<td colspan="6"></td>
										<td class="text_right bold">'.$this->bpas->formatQuantity($cquantity).'</td>
										<td class="text_right bold">'.$this->bpas->formatQuantity($uquantity).'</td>
										<td class="text_right bold">'.$this->bpas->formatQuantity($tquantity).'</td>
										<td class="text_right bold">'.$this->bpas->formatMoney($tsubtotal).'</td>
									</tr>';
					?>
					<tr>
						<td>
							<table class="table_item" style="margin-top:10px;">
								<thead>
									<tr>
										<th><?= lang("name"); ?></th>
										<th><?= lang("tank"); ?></th>
										<th><?= lang("nozzle_no"); ?></th>
										<th><?= lang("nozzle_start_no"); ?></th>
										<th><?= lang("nozzle_end_no"); ?></th>
										<th><?= lang("unit_price"); ?></th>
										<th><?= lang("customer_qty"); ?></th>
										<th><?= lang("using_qty"); ?></th>
										<th><?= lang("fuel_qty"); ?></th>
										<th><?= lang("fuel_amount"); ?></th>
									</tr>
								</thead>
								<tbody id="tbody">
									<?= $tbody ?>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
			
			<!--<table style="margin:20px 0;">
					<?php 
						$json_total_cash_count = json_decode($fuel_sale->json_total_cash_count);
						$json_total_cash_open = json_decode($fuel_sale->json_total_cash_open);
						if($json_total_cash_count){
							echo '<tr>';
								$i = 0;
								foreach($json_total_cash_count as $key_c => $total_cash_count){
									$br = "";
									if($i == 0){
										$br = "border-right:0px !important;";
									}
									echo '<td valign="top">
											<table class="table_item">';
											
											echo '<tr>';
												echo '<td class="text_center" colspan="3" style="width:120px; font-weight:bold; '.$br.'">'.lang($key_c).'</td>';
											echo '</tr>';
									
										$change_amount = $json_total_cash_open->{$key_c}->amount?$json_total_cash_open->{$key_c}->amount:0;									
										$rows = json_decode($total_cash_count);
										$total = 0;
										foreach($rows as $key=>$row){
											$subamount = $key * $row;
											$total += $subamount;
											if($key <= 0){
												$key = 0;
											}
											echo '<tr>';
												echo '<td class="text_right" style="width:120px;">'.number_format($key,-1).'</td>';
												echo '<td class="text_right" style="width:120px;">'.$row.'</td>';
												echo '<td class="text_right" style="width:120px;">'.number_format($subamount,-1).'</td>';
											echo '</tr>';
										}
										echo '<tr>
												<td class="text_right" style="font-weight:bold;" colspan="2">'.lang("count").' : </td>
												<td class="text_right" style="font-weight:bold;">'.number_format($total,-1).'</td>
											 </tr>';
										echo '<tr>
												<td class="text_right" style="font-weight:bold;" colspan="2">'.lang("change").' : </td>
												<td class="text_right" style="font-weight:bold;">'.number_format($change_amount,-1).'</td>
											 </tr>';
									echo '</table>
										 </td>';
									$i++;
								}
							echo '</tr>';
						}
					?>
			</table>-->

			<table width="100%" class="table_item" style="margin-top:10px; ">
				<tr>
					<td class="text_right" style="font-size:14px;font-weight:bold;"><?= lang("credit_amount") ?>&nbsp; : &nbsp;</td>
					<td class="text_right" style="font-size:14px;font-weight:bold;"> &nbsp; <?= $this->bpas->formatMoney($fuel_sale->credit_amount); ?></td>
				</tr>
				<tr>
					<td class="text_right" style="font-size:14px;font-weight:bold;"><?= lang("bank_amount") ?>&nbsp; : &nbsp;</td>
					<td class="text_right" style="font-size:14px;font-weight:bold;"> &nbsp; <?= $this->bpas->formatMoney($fuel_sale->bank_amount); ?></td>
				</tr>
				<tr>
					<td class="text_right" style="font-size:14px;font-weight:bold;"><?= lang("cash_submit") ?>&nbsp; : &nbsp;</td>
					<td class="text_right" style="font-size:14px;font-weight:bold;"> &nbsp; <?= $this->bpas->formatMoney($fuel_sale->total_cash); ?></td>
				</tr>
				<tr>
					<td class="text_right" style="font-size:14px;font-weight:bold;width:300px;"><?= lang("cash_change") ?>&nbsp; : &nbsp;</td>
					<td class="text_right" style="font-size:14px;font-weight:bold;"> &nbsp; <?= $this->bpas->formatMoney($fuel_sale->total_cash_open); ?></td>
				</tr>
				<tr>
					<td class="text_right" style="font-size:14px;font-weight:bold;width:300px;"><?= lang("total") ?>&nbsp; : &nbsp;</td>
					<td class="text_right" style="font-size:14px;font-weight:bold;"> &nbsp; <?= $this->bpas->formatMoney(($fuel_sale->total_cash - $fuel_sale->total_cash_open + $fuel_sale->credit_amount + $fuel_sale->bank_amount)); ?></td>
				</tr>
			</table>

			<table width="100%" class="table_item" style="margin-top:20px;">
				<tr>
					<td class="text_right" style="font-size:14px;font-weight:bold;width:300px;"><?= lang("total_sales") ?>&nbsp; : &nbsp;</td>
					<td class="text_right"​ style="font-size:14px;font-weight:bold;"> &nbsp; <u><?= $this->bpas->formatMoney($tsubtotal); ?></u></td>
				</tr>
				<tr>
					<td class="text_right" style="font-size:14px;font-weight:bold;"><?= lang("different") ?>&nbsp; : &nbsp;</td>
					<td class="text_right" style="font-size:14px;font-weight:bold;color:red;"> &nbsp; <?= $this->bpas->formatMoney(($fuel_sale->total_cash - $fuel_sale->total_cash_open + $fuel_sale->credit_amount + $fuel_sale->bank_amount) - $tsubtotal); ?></td>
				</tr>
			</table>
			<?php if ($fuel_sale->note) { ?>
				<table width="100%"  style="margin-top:10px;">
					<tr>
						<td><?= $this->bpas->decode_html($fuel_sale->note); ?></td>
					</tr>
				</table>
			<?php }
				$html_cash_open = '';
				if(!empty($json_total_cash_open)){
					foreach($json_total_cash_open as $total_cash_open){
						$html_cash_open .= "<u style='font-size:10px; font-weight:bold;'>".$total_cash_open->code . "=" .$total_cash_open->rate."</u> &nbsp;";
					}
				}
				$html_sale = ''; $total_sale = 0;
				if($sales){
					$html_sale .= "<br/><span style='font-size:10px; font-weight:bold;'><u>".lang("reference_no")."</u></span><br/>";
					foreach($sales as $sale){
						$total_sale += $sale->grand_total;
						$html_sale .= "<span style='font-size:10px; font-weight:bold;'>".$sale->reference_no."</span><br/>";
					}
				}
			?>
			<br/>
			<?=$html_cash_open?>
			<?=$html_sale?>
			<table width="100%" class="tr_print" style="margin-top:10px; text-align:center;">
				<tr>
					<td><b><?=lang("checked_by")?></b></td>
					<td><b><?=lang("received_by")?></b></td>
				</tr>

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

<script type="text/javascript">
    $(document).ready( function() {
		window.onafterprint = function(){		
			$.ajax({
				url : site.base_url + "sales/add_print",
				dataType : "JSON",
				type : "GET",
				data : { 
						transaction_id : <?= $fuel_sale->id ?>,
						transaction : "FuelSale",
						reference_no : "<?= $fuel_sale->reference_no ?>"
					}
			});
		}
    });
</script>
<style type="text/css">
	@media print{
		.no-print{
			display:none !important;
		}
		.modal-dialog{
			<?= $hide_print ?>
		}
		.table_item th, .table_item td{
			border:1px solid black !important;
		}
		.mt-contain-sticker{
			display: block !important;
		}
		.tr_print{
			display:table !important;
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
	.hr_title{
		border:3px double #428BCD !important;
		margin-bottom:<?= $margin ?>px !important;
		margin-top:<?= $margin ?>px !important;
	}
	.table_item th{
		border:1px solid #357EBD;
		background-color : #428BCD !important;
		text-align:center !important;
		line-height:30px !important;
		color:#FFF;
	}
	.table_item td{
		border:1px solid #357EBD;
		line-height:<?=$td_line_height?>px !important;
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