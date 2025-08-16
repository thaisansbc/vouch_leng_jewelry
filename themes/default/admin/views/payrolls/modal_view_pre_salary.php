<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row') + 40;
	$font_size = $this->config->item('font_size');
	$td_line_height = $font_size + 15;
	$min_height = $font_size * 6; 
	$margin = $font_size - 5;
	$margin_signature = $font_size * 2;
?>
<div class="modal-dialog modal-lg main_content">
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
										<?= $this->bpas->qrcode('link', urlencode(site_url('payrolls/modal_view_pre_salary/' . $salary->id)), 2); ?>
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
									<td class="text_center" style="width:15%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('pre_salary') ?></i></b></span></td>
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
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('reference') ?></i></b></legend>
											<table>
												<tr>
													<td style="width:15%"><?= lang('date') ?></td>
													<td> : <strong><?= $this->bpas->hrld($salary->date) ?></strong></td>
												</tr>
												<tr>
													<td style="width:15%"><?= lang('month') ?></td>
													<td> : <strong><?= $salary->month.'/'.$salary->year ?></strong></td>
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
						foreach ($salary_items as $salary_item){
							$tbody .='<tr>
											<td class="text_center">'.$i.'</td>
											<td class="text_center">'.$salary_item->empcode.'</td>
											<td class="text_left">'.$salary_item->lastname.' '.$salary_item->firstname.'</td>
											<td class="text_left">'.$salary_item->department.'</td>
											<td class="text_left">'.$salary_item->group.'</td>
											<td class="text_left">'.$salary_item->position.'</td>
											<td class="text_center">'.$this->bpas->hrsd($salary_item->employee_date).'</td>
											<td class="text_right">'.$this->bpas->formatMoney($salary_item->gross_salary).'</td>
										</tr>';		
							$i++;
						}
						$footer_colspan = 6;
						$footer_note = '<td class="footer_des" rowspan="1" colspan="'.$footer_colspan.'">'.$this->bpas->decode_html($salary->note).'</td>';
						$tfooter = '<tr>
										'.$footer_note.'
										<td class="text_right"><b>'.lang("total").'</b></td>	
										<td class="text_right"><b>'.$this->bpas->formatMoney($salary->total_gross_salary).'</b></td>
									</tr>';
					?>
					<tr>
						<td>
							<table class="table_item_main">
								<thead>
									<tr>
										<th><?= lang("#"); ?></th>
										<th><?= lang("code"); ?></th>
										<th><?= lang("name"); ?></th>
										<th><?= lang("department"); ?></th>
										<th><?= lang("group"); ?></th>
										<th><?= lang("position"); ?></th>
										<th><?= lang("employee_date"); ?></th>
										<th><?= lang("gross_salary"); ?></th>
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
					<div class="btn-group">
						<a href="<?= admin_url('payrolls/modal_view_pre_salary_bank_note/' . $salary->id) ?>" class="tip btn btn-success" title="<?= lang('bank_note') ?>" data-toggle="modal" data-target="#myModal2" data-backdrop="static" data-keyboard="false">
							<i class="fa fa-file-text-o"></i>
							<span class="hidden-sm hidden-xs"><?= lang('bank_note') ?></span>
						</a>
					</div>
					<?php if ($salary->attachment) { ?>
						<div class="btn-group">
							<a href="<?= admin_url('welcome/download/' . $salary->attachment) ?>" class="tip btn btn-primary" target="_blank" title="<?= lang('attachment') ?>">
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
	.table_item_main th{
		border:1px solid black !important;
		background-color : #428BCD !important;
		text-align:center !important;
		line-height:30px !important;
	}
	.table_item_main td{
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
			var form_height = $('.table_item_main').height()-0;
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

