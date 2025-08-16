<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$max_row_limit = $this->config->item('form_max_row') - 15;
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
										<?= $this->bpas->qrcode('link', urlencode(admin_url('attendances/daily_time_card/' . $id)), 2); ?>
									</td>
								</tr>
							</table>
						</th>
					</tr>
					<tr>
						<th>
							<table>
								<tr>
									<td valign="bottom" style="width:55%"><hr class="hr_title"></td>
										<td class="text_center" style="width:20%"><span style="font-size:<?= $font_size+5 ?>px"><b><i><?= lang('daily_time_card') ?></i></b></span></td>
									<td valign="bottom" style="width:15%"><hr class="hr_title"></td>
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
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('employee') ?></i></b></legend>
											<table>
												<tr>
													<td><?= lang('full_name') ?></td>
													<td> : <strong><?= $employee->firstname.' '.$employee->lastname  ?></strong></td>
												</tr>
												<tr>
													<td><?= lang('gender') ?></td>
													<td> : <strong><?= ucfirst($employee->gender) ?></strong></td>
												</tr>
												<tr>
													<td><?= lang('date') ?></td>
													<td style="text-align:left"> : <?= $this->bpas->hrsd($date) ?></td>
												</tr>
											</table>
										</fieldset>
									</td>
									<td style="width:40%">
										<fieldset style="margin-left:5px !important">
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('department') ?></i></b></legend>
											<table>
												<tr>
													<td><?= lang('position') ?></td>
													<td style="text-align:left"> : <?= $position->name ?></td>
												</tr>
												<tr>
													<td><?= lang('department') ?></td>
													<td style="text-align:left"> : <?= $department->name ?></td>
												</tr>
												<tr>
													<td><?= lang('group') ?></td>
													<td style="text-align:left"> : <b><?= $group->name ?></b></td>
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

						if($attendances){
							foreach($attendances as $attendance){
								$attedance_array[$attendance->timeshift][$attendance->check_type] = $attendance->time_only;
								if($attendance->check_type=='in' && $attendance->after_time > 0){
									$attedance_late_early['late'] = $attedance_late_early['late'] + $attendance->after_time;
								}else if($attendance->check_type=='out' && $attendance->before_time > 0){
									$attedance_late_early['early'] = $attedance_late_early['early'] + $attendance->before_time;
								}
							}
						}
					
						$tbody ='<tr>
										<td class="text_center">1</td>
										<td class="text_center">'.$this->bpas->hrsd($date).'</td>
										<td class="text_center">'.(isset($attedance_array['one']['in']) ? $attedance_array['one']['in'] : '').'</td>
										<td class="text_center">'.(isset($attedance_array['one']['out']) ? $attedance_array['one']['out'] : '').'</td>
										<td class="text_center">'.(isset($attedance_array['two']['in']) ? $attedance_array['two']['in'] : '').'</td>
										<td class="text_center">'.(isset($attedance_array['two']['out']) ? $attedance_array['two']['out'] : '').'</td>
										<td class="text_center">'.(isset($attedance_late_early['late']) ? $this->bpas->secTotime($attedance_late_early['late']) : '').'</td>
										<td class="text_center">'.(isset($attedance_late_early['early']) ? $this->bpas->secTotime($attedance_late_early['early']) : '').'</td>
									</tr>';		
					
						
					?>
					<tr>
						<td>
							<table class="table_item">
								<thead>
									<tr>
										<th><?= lang("#"); ?></th>
										<th><?= lang("date"); ?></th>
										<th><?= lang("check_in"); ?></th>
										<th><?= lang("check_out"); ?></th>
										<th><?= lang("check_in"); ?></th>
										<th><?= lang("check_out"); ?></th>
										<th><?= lang("coming_late"); ?></th>
										<th><?= lang("leave_early"); ?></th>
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
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td>';
					td_html +='<td class="td_print">&nbsp;</td></tr>';
				$('#tbody').append(td_html);
			}
		}
    });
</script>

