<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = 10 + 20;
	$font_size = 12;//$this->config->item('font_size');
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
		} ?>	
			<table>
				<thead>
					<tr>
						<th>
							<table>
								<tr>
									<td class="text_center" style="width:20%">
										<?php
											echo '<img  src="'.base_url().'assets/uploads/logos/' . $biller->logo.'" alt="'.$biller->name.'">';
										?>
									</td>
									<td class="text_center" style="width:60%">
										<div style="font-size:<?= $font_size+15 ?>px"><b><?= $biller->name ?></b></div>
										<div><?= $biller->address.' '.$biller->city ?></div>
										<div><?= lang('tel').' : '. $biller->phone ?></div>	
										<div><?= lang('email').' : '. $biller->email ?></div>	
									</td>
									<td class="text_center" style="width:20%">
										
									</td>
								</tr>
							</table>
						</th>
					</tr>
					<tr>
						<th>
							<table>
								<tr>
									<td style="width:50%">
										<fieldset>
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('account').' '.lang('transaction') ?></i></b></legend>
											<table>
												<tr>
													<td><?= lang('account') ?></td>
													<td> : <strong><?= $account->accountcode.' - '.$account->accountname ?></strong></td>
												</tr>
												<?php if(isset($begin) && $begin){ ?>
													<tr>
														<td><?= lang('end_date') ?></td>
														<td> : <?= $this->bpas->hrsd($start_date) ?></td>
													</tr>
												<?php } else { if($start_date){ ?> 
													<tr>
														<td><?= lang('start_date') ?></td>
														<td> : <?= $this->bpas->hrsd($start_date) ?></td>
													</tr>
													<?php } if($end_date){ ?>
													<tr>
														<td><?= lang('end_date') ?></td>
														<td> : <?= $this->bpas->hrsd($end_date) ?></td>
													</tr>
												<?php } } ?> 
												
												
											</table>
										</fieldset>
									</td>
									<?php if((isset($billers) && $billers) || (isset($projects) && $projects)){ ?>
										<td style="width:45%">
											<fieldset style="margin-left:5px !important">
												<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('information') ?></i></b></legend>
												<table>
													<?php if($billers){
														$u = 0;
														foreach($billers as $row){
															if($u==0){
																$u = 1;
																$biller .= $row->name;
															}else{
																$biller .= '<br>&nbsp;&nbsp'.$row->name;
															}
															
														}	
													?>
														<tr>
															<td><?= lang('biller') ?></td>
															<td> : <?= $biller ?></td>
														</tr>
													<?php } if($projects){ 
														foreach($projects as $row){
															if($u==0){
																$u = 1;
																$project .= $row->name;
															}else{
																$project .= '<br>&nbsp;&nbsp;'.$row->name;
															}
														}	
													?>
														<tr>
															<td><?= lang('project') ?></td>
															<td> : <?= $project ?></td>
														</tr>
													<?php } ?>
													
												</table>
											</fieldset>
										</td>
									<?php } ?>
									
								</tr>
							</table>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
						
						$tbody = '';
						$balance = 0;
						if($rows){
							foreach ($rows as $row){
								if($row->amount != 0){
									$balance += $row->amount;
									$balance_show = $balance;
									if($row->amount > 0){
										$debit = $this->bpas->formatMoney($row->amount);
										$credit = '';
									}else{
										$debit = '';
										$credit = $this->bpas->formatMoney(abs($row->amount));
									}
									if($balance_show > 0){
										$v_balance = $this->bpas->formatMoney($balance_show);
									}else{
										$v_balance = '('.$this->bpas->formatMoney(abs($balance_show)).')';
									}
									$tbody .='<tr>
											<td class="text_left">'.$row->account_code.' - '.$row->narrative.'</td>
											<td class="text_center">'.$this->bpas->hrsd($row->tran_date).'</td>
											<td class="text_center">'.$row->reference_no.'</td>
											<td class="text_left">'.$row->narrative.'</td>
											<td style="max-width:200px" class="text_left">'.$this->bpas->remove_tag($row->description).'</td>
											<td class="text_right">'.$debit.'</td>
											<td class="text_right">'.$credit.'</td>
											<td class="text_right">'.$v_balance.'</td>
										</tr>';		
								}
							}
						}
						
						

					?>
					<tr>
						<td>
							<table class="table_item">
								<thead>
									<tr>
										<th><?= lang("account"); ?></th>
										<th><?= lang("date"); ?></th>
										<th><?= lang("reference"); ?></th>
										<th><?= lang("narrative"); ?></th>
										<th><?= lang("description"); ?></th>
										<th><?= lang("debit"); ?></th>
										<th><?= lang("credit"); ?></th>
										<th><?= lang("balance"); ?></th>
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