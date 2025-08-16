<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row') - 50;
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
											echo '<img height="80px" src="'.base_url().'assets/uploads/logos/' . $biller->logo.'" alt="'.$biller->name.'">';
										?>
									</td>
									<td class="text_center" style="width:60%">
										<div style="font-size:<?= $font_size+15 ?>px"><b><?= $biller->name ?></b></div>
										<div><?= $biller->address.$biller->city ?></div>
										<div><?= lang('tel').' : '. $biller->phone ?></div>	
										<div><?= lang('email').' : '. $biller->email ?></div>	
									</td>
									<td class="text_center" style="width:20%">
										<?= $this->bpas->qrcode('link', urlencode(admin_url('hr/modal_view_kpi/' . $kpi->id)), 2); ?>
									</td>
								</tr>
							</table>
						</th>
					</tr>
					<tr>
						<th>
							<table>
								<tr>
									<td valign="bottom" style="width:70%"><hr class="hr_title"></td>
									<td class="text_center" style="width:15%"><span style="font-size:<?= $font_size+6 ?>px"><b><i><?= lang('kpi') ?></i></b></span></td>
									<td valign="bottom" style="width:15%"><hr class="hr_title"></td>
								</tr>
							</table>
						</th>
					</tr>
					<tr>
						<th>
							<table>
								<tr>
									<td style="width:50%">
										<fieldset style="margin-left:5px !important; padding-bottom: 5px;">
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('employee') ?></i></b></legend>
											<table>
												<tr>
													<td><?= lang('name') ?></td>
													<td style="text-align:left"> : <b><?= $kpi->lastname.' '.$kpi->firstname ?></b></td>
												</tr>
												<tr>
													<td><?= lang('position') ?></td>
													<td style="text-align:left"> : <b><?= $position->name ?></b></td>
												</tr>
												<tr>
													<td><?= lang('group') ?></td>
													<td style="text-align:left"> : <b><?= $group->name ?></b></td>
												</tr>
												<tr>
													<td><?= lang('department') ?></td>
													<td style="text-align:left"> : <b><?= $department->name ?></b></td>
												</tr>
											</table>
										</fieldset>
									</td>
									<td style="width:50%">
										<fieldset style="margin-left:5px !important; padding-bottom: 5px;">
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('information') ?></i></b></legend>
											<table>
												<tr>
													<td><?= lang('date') ?></td>
													<td style="text-align:left"> : <b><?= $this->bpas->hrsd($kpi->date) ?></b></td>
												</tr>
												<tr>
													<td><?= lang('month') ?></td>
													<td style="text-align:left"> : <b><?= $kpi->month ?></b></td>
												</tr>
												<tr>
													<td><?= lang('result') ?></td>
													<td style="text-align:left"> : <b><?= $this->bpas->formatDecimal($kpi->result) ?>%</b></td>
												</tr>
												<tr>
													<td><?= lang('credit') ?></td>
													<td style="text-align:left"> : <b><?= $kpi->measure ?></b></td>
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
						if($kpi_questions){
							foreach ($kpi_questions as $kpi_question){
								$tbody .='<tr>
												<td class="text_center">'.$i.'</td>
												<td class="text_left">'.$kpi_question->question.'</td>
												<td class="text_left">'.$kpi_question->question_kh.'</td>
												<td class="text_left">'.$kpi_question->comment.'</td>
												<td class="text_center">'.$this->bpas->formatQuantity($kpi_question->rate).'</td>
											</tr>';		
								$i++;
							}
						}
						
					?>
					<tr>
						<td>
							<table class="table_item">
								<thead>
									<tr>
										<th style="min-width:30px"><?= lang("#"); ?></th>
										<th><?= lang("question"); ?></th>
										<th><?= lang("question_kh"); ?></th>
										<th style="min-width:60px"><?= lang("comment"); ?></th>
										<th style="min-width:40px"><?= lang("rate"); ?></th>
									</tr>
								</thead>
								<tbody id="tbody">
									<?= $tbody ?>
								</tbody>
								<tfoot>
									<?php if($kpi->manager_note){ ?>
										<tr>
											<td colspan="5" style="border:0px !important"><?= lang('manager_note') ?> : <?= $this->bpas->decode_html($kpi->manager_note)  ?></td>
										</tr>

									<?php } if($kpi->employee_note){ ?>
										<tr>
											<td colspan="5"  style="border:0px !important"><?= lang('employee_note') ?> : <?= $this->bpas->decode_html($kpi->employee_note)  ?></td>
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
							<table style="margin-top:<?= $margin_signature ?>px">
								<tr>
									<td class="text_center" style="width:50%"><?= lang("manager").' '. lang("signature") ?></td>
									<td class="text_center" style="width:50%"><?= lang("employee").' '. lang("signature") ?></td>
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
							<span class="hidden-sm hidden-xs"><?= lang('close') ?></span>
						</a>
					</div>
					<div class="btn-group">
						<a onclick="window.print()"  aria-hidden="true" class="tip btn btn-info" title="<?= lang('print') ?>">
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
					td_html +='<td class="td_print">&nbsp;</td></tr>';
				$('#tbody').append(td_html);
			}

		}
		
    });
	
</script>
