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
										<?= $this->bpas->qrcode('link', urlencode(admin_url('payrolls/modal_view_salary/' . $salary->id)), 2); ?>
									</td>
								</tr>
							</table>
						</th>
					</tr>
					<tr>
						<th>
							<table>
								<tr>
									<td valign="bottom" style="width:65%"><hr class="hr_title"></td>
									<td class="text_center" style="width:15%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('payslip') ?></i></b></span></td>
									<td valign="bottom" style="width:20%"><hr class="hr_title"></td>
								</tr>
							</table>
						</th>
					</tr>
					<tr>
						<th>
							<fieldset>
								<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('information') ?></i></b></legend>
								<table style="width:100%">
									<tr>
										<td class="text_left" style="width:15%"><?= lang("month") ?></td>
										<td class="text_left"> : <?= $salary->month."/".$salary->year ?></td>
										<td class="text_left" style="width:15%"><?= lang("code") ?></td>
										<td class="text_left"> : <?= $salary_item->empcode ?></td>
									</tr>
									<tr>
										<td class="text_left"><?= lang("name") ?></td>
										<td class="text_left"> : <?= $salary_item->lastname." ".$salary_item->firstname ?></td>
										<td class="text_left"><?= lang("position") ?></td>
										<td class="text_left"> : <?= $salary_item->position ?></td>
									</tr>
									<tr>
										<td class="text_left"><?= lang("department") ?></td>
										<td class="text_left"> : <?= $salary_item->department ?></td>
										<td class="text_left"><?= lang("group") ?></td>
										<td class="text_left"> : <?= $salary_item->group ?></td>
									</tr>
									<tr>
										<td class="text_left"><?= lang("working_day") ?></td>
										<td class="text_left"> : <?= $salary_item->working_day ?> <?= ($salary_item->working_day > 1 ? lang("days") : lang("day")) ?></td>
										<td class="text_left"><?= lang("present") ?></td>
										<?php $present = $salary_item->working_day - $salary_item->permission - $salary_item->absent; ?>
										<td class="text_left"> : <?= $present ?> <?= ($present > 1 ? lang("days") : lang("day")) ?></td>
									</tr>
									<tr>
										<td class="text_left"><?= lang("permission") ?></td>
										<td class="text_left"> : <?= $salary_item->permission ?> <?= ($salary_item->permission > 1 ? lang("days") : lang("day")) ?></td>
										<td class="text_left"><?= lang("absent") ?></td>
										<td class="text_left"> : <?= $salary_item->absent ?> <?= ($salary_item->absent > 1 ? lang("days") : lang("day")) ?></td>
									</tr>
									<tr>
										<td class="text_left"><?= lang("late/early") ?></td>
										<td class="text_left"> : <?= $salary_item->late ?> <?= ($salary_item->late > 1 ? lang("hours") : lang("hour")) ?></td>
										<td class="text_left"><?= lang("normal_ot") ?></td>
										<td class="text_left"> : <?= $salary_item->normal_ot ?> <?= ($salary_item->normal_ot > 1 ? lang("hours") : lang("hour")) ?></td>
									</tr>
									<tr>
										<td class="text_left"><?= lang("weekend_ot") ?></td>
										<td class="text_left"> : <?= $salary_item->weekend_ot ?> <?= ($salary_item->weekend_ot > 1 ? lang("hours") : lang("hour")) ?></td>
										<td class="text_left"><?= lang("holiday_ot") ?></td>
										<td class="text_left"> : <?= $salary_item->holiday_ot ?> <?= ($salary_item->holiday_ot > 1 ? lang("hours") : lang("hour")) ?></td>
									</tr>
								</table>
							</fieldset>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
						$td_addtion = "";
						if($salary_item->additions){
							$additions = json_decode($salary_item->additions);
							foreach($additions as $addition){
								$td_addtion .= "<tr>
													<td class='text_left'>".$addition->name."</td>
													<td></td>
													<td class='text_right'>".$this->bpas->formatMoney($addition->value)."</td>
												</tr>";
							}
						}
						$td_deduction = "";
						if($salary_item->deductions){
							$deductions = json_decode($salary_item->deductions);
							foreach($deductions as $deduction){
								$td_deduction .= "<tr>
													<td class='text_left'>".$deduction->name."</td>
													<td class='text_right'>".$this->bpas->formatMoney($deduction->value)."</td>
													<td></td>
												</tr>";
							}
						}
					?>
					<tr>
						<td>
							<table class="table_item">
								<thead>
									<tr>
										<th><?= lang("description") ?></th>
										<th><?= lang("deduction") ?></th>
										<th><?= lang("earning") ?></th>
									</tr>
								</thead>
								<tbody id="tbody">
									<tr>
										<td class="text_left"><?= lang("basic_salary") ?></td>
										<td></td>
										<td class="text_right"><?= $this->bpas->formatMoney($salary_item->basic_salary) ?></td>
										
									</tr>
									<tr>
										<td class="text_left"><?= lang("overtime") ?></td>
										<td></td>
										<td class="text_right"><?= $this->bpas->formatMoney($salary_item->overtime) ?></td>
									</tr>
									<?= $td_addtion ?>
									<tr>
										<td class="text_left"><?= lang("absent") ?></td>
										<td class="text_right"><?= $this->bpas->formatMoney($salary_item->absent_amount) ?></td>
										<td></td>
									</tr>
									<tr>
										<td class="text_left"><?= lang("permission") ?></td>
										<td class="text_right"><?= $this->bpas->formatMoney($salary_item->permission_amount) ?></td>
										<td></td>
									</tr>
									<tr>
										<td class="text_left"><?= lang("late_early") ?></td>
										<td class="text_right"><?= $this->bpas->formatMoney($salary_item->late_amount) ?></td>
										<td></td>
									</tr>
									<?= $td_deduction ?>
									<tr>
										<td class="text_left"><?= lang("cash_advance") ?></td>
										<td class="text_right"><?= $this->bpas->formatMoney($salary_item->cash_advanced) ?></td>
										<td></td>
									</tr>
									<tr>
										<td class="text_left"><?= lang("tax") ?></td>
										<td class="text_right"><?= $this->bpas->formatMoney($salary_item->tax_payment) ?></td>
										<td></td>
									</tr>
									<tr>
										<td colspan="2" class="text_left"><b><?= lang("net_salary") ?></b></td>
										<td class="text_right"><b><?= $this->bpas->formatMoney($salary_item->net_salary) ?></b></td>
									</tr>
								</tbody>
								<tbody id="tfooter">
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
									<td class="text_center" style="width:33%"><?= lang("preparer").' '. lang("signature") ?></td>
									<td class="text_center" style="width:33%"><?= lang("employee").' '. lang("signature") ?></td>
									<td class="text_center" style="width:33%"><?= lang("approver") .' '. lang("signature") ?></td>

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
					<?php if ($salary->attachment) { ?>
						<div class="btn-group">
							<a href="<?= admin_url('assets/uploads/' . $salary->attachment) ?>" class="tip btn btn-primary" target="_blank" title="<?= lang('attachment') ?>">
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
</style>

