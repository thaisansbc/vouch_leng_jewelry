<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row') + 110;
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
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-body">
			<table>
				<tr>
					<td class="text_center">
						<div style="font-family: Khmer Muol Light !important; font-size:<?= $font_size+6 ?>px">សាលាអន្តរជាតិអាប៊ែនឌែនឡៃ</div>
						<div style="font-weight:bold !important; font-size:<?= $font_size+2 ?>px">Abundant Life International School</div>
					</td>
				</tr>
				<tr>
					<th class="text_center">
						<div style="margin-top:10px; font-size:<?= $font_size+2 ?>px">តារាងបង់ប្រាក់ជាដំណាក់កាល/ PAYMENT SCHEDULE</div>
					</th>
				</tr>
				<tr>
					<td>
						<table class="table_school" style="margin-top:20px">
							<tr>
								<td class="text_left" style="border-bottom:none !important">សិស្សឈ្មោះ/Name: <?= $student ? $student->lastname.' '.$student->firstname : '' ?></td>
								<td class="text_left" style="border-bottom:none !important">អត្តលេខ/ID No: <?= $student ? $student->number : '' ?></td>
								<td class="text_left" style="border-bottom:none !important">Schedule No: <?= $installment->reference_no ?></td>
							</tr>
							<tr>
								<td class="text_left" style="border-top:none !important; border-bottom:none !important">ថ្ងៃខែឆ្នាំកំណើត/DOB: <?= $student ? $this->bpas->hrsd($student->dob) : '' ?></td>
								<td class="text_left" style="border-top:none !important; border-bottom:none !important">កូនទី/Family order: <?= $student ? $student->child_no : '' ?></td>
								<td class="text_left" style="border-top:none !important; border-bottom:none !important">Report Printed on: <?= $this->bpas->hrsd($installment->created_date) ?></td>
							</tr>
							<tr>
								<td class="text_left" style="border-top:none !important; border-bottom:none !important">ភេទ/Sex: <?= $student ? ($student->gender == "male" ? "ប្រុស" : "ស្រី") : '' ?></td>
								<td class="text_left" style="border-top:none !important; border-bottom:none !important">ថ្នាក់ទី/Grade: <?= $grade ? $grade->name : '' ?></td>
								<td class="text_left" style="border-top:none !important; border-bottom:none !important">Invoice No: <?= $sale ? $sale->reference_no : '' ?></td>
							</tr>
						</table>
					</td>
				</tr>
				<tbody>
					<?php
						$tbody = '';
						if($installment_items){
							$tbody = '<tr>
										<td colspan="7"></td>
										<td style="font-weight:bold" class="text_right">'.$this->bpas->formatMoney($installment->principal_amount).'</td>
									</tr>';
							$balance = $installment->principal_amount;
							foreach ($installment_items as $i => $installment_item){
								$balance -= $installment_item->paid;
								$tbody .='<tr>
											<td class="text_center">'.($i+1).'</td>
											<td class="text_center">'.$this->bpas->hrsd($installment_item->deadline).'</td>
											<td class="text_right">'.$this->bpas->formatMoney($installment_item->payment).'</td>
											<td class="text_right">'.$this->bpas->formatMoney($installment_item->penalty_paid).'</td>
											<td class="text_right">'.$this->bpas->formatMoney($installment_item->paid - $installment_item->penalty_paid).'</td>
											<td class="text_center">'.$installment_item->payment_no.'</td>
											<td class="text_center">'.($installment_item->payment_date ? $this->bpas->hrsd($installment_item->payment_date) : '').'</td>
											<td class="text_right">'.$this->bpas->formatMoney($balance).'</td>
										</tr>';		
							}
						}
					?>
					<tr>
						<td>
							<table class="table_item" style="margin-top:5px;">
								<thead>
									<tr>
										<th rowspan="2"><?= lang("#"); ?></th>
										<th rowspan="2"><?= lang("deadline"); ?></th>
										<th rowspan="2"><?= lang("amount"); ?></th>
										<th rowspan="2"><?= lang("penalty"); ?></th>
										<th colspan="3"><?= lang("paid"); ?></th>
										<th rowspan="2"><?= lang("balance"); ?></th>
									</tr>
									<tr>
										<th><?= lang("amount") ?></th>
										<th><?= lang("ref_no") ?></th>
										<th><?= lang("ref_date") ?></th>
									</tr>
								</thead>
								<tbody id="tbody">
									<?= $tbody ?>
									<tr>
										<td colspan="8" style="font-size:12px !important; border: none !important" class="footer_des">
											<div style="font-family: Khmer Muol Light !important; font-size:14px !important">សម្គាល់:</div>
											<div style="font-family: Khmer !important; font-size:11px !important">
												១-សូមលោក លោកស្រីអញ្ជើញមកបង់ប្រាក់តាមកាលបរិច្ឆេទដែលសាលាបានកំណត់ដូចមានចែងក្នុង<span style="font-family: Khmer Muol Light !important; ">គោលការណ៍នៃការបង់ប្រាក់ថ្លៃសិក្សា</span>។<br>
												២-ក្នុងករណីដែលការបង់ប្រាក់ធ្វើឡើងក្រោយកាលបរិច្ឆេទកំណត់ សាលានឹងពិន័យ១០ភាគរយនៃប្រាក់ដែលត្រូវបង់។ ក្នុងករណីដែលមាតាបិតា ឬអាណាព្យាបាលសិស្សមិនបានបង់ប្រាក់ហួសកាលបរិច្ឆេទកំណត់ ចំនួន១០ថ្ងៃនៃថ្ងៃសិក្សា សាលានឹងផ្អាកការសិក្សារបស់សិស្ស ជាបណ្តោះអាសន្ន រហូតដល់ថ្ងៃដែលមាតាបិតា ឬអាណាព្យាបាលសិស្សមកធ្វើការទូទាត់ប្រាក់ថ្លៃសិក្សារួចរាល់។
											</div>
										</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
				<tr class="tr_print">
					<td>
						<table class="table_item" style="margin-top:10px">
							<tr>
								<td class="text_center" style="width:25%; font-family: Khmer !important;">អ្នករៀបចំ/Prepared by</td>
								<td class="text_center" style="width:25%; font-family: Khmer !important;">អ្នកត្រួតពិនិត្យ/Checked by</td>
								<td class="text_center" style="width:25%; font-family: Khmer !important;">អ្នកផ្ទៀងផ្ទាត់/ Verified by</td>
								<td class="text_center" style="width:25%; font-family: Khmer !important;">ឯកភាព/Approved by</td>
							</tr>
							<tr>
								<td style="height:80px">&nbsp;</td>
								<td style="height:80px">&nbsp;</td>
								<td style="height:80px">&nbsp;</td>
								<td style="height:80px">&nbsp;</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
	
			<div id="buttons" style="padding-top:10px;" class="no-print">
				<hr>
				<div class="btn-group btn-group-justified">
					<div class="btn-group">
						<a class="tip btn btn-warning" href="<?= site_url("installments/view/".$installment->id) ?>" title="<?= lang('print') ?>">
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
					<?php if ($installment->status != 'inactive' && $installment->status != 'completed') { ?>
						<div class="btn-group">
							<a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line("delete_installment") ?></b>"
								data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= site_url('installments/delete/' . $installment->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
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
	.table_school{
		border:1px solid black !important;
		text-align:center !important;
		line-height:30px !important;
	}
	.table_school td{
		border:1px solid black;
		font-size:14px;
		font-family: Khmer;
		line-height:<?=$td_line_height?>px !important;
	}
</style>

