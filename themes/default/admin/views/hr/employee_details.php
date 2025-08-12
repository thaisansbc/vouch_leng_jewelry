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
			<table>
				<thead>
					<tr>
						<th>
							<table>
								<tr>
									<td class="text_center" style="width:20%">
										<?php
											echo '<img width="180px" src="'.base_url().'assets/uploads/logos/' . $biller->logo.'" alt="'.$biller->name.'">';
										?>
									</td>
									<td class="text_center" style="width:60%">
										<div style="font-size:<?= $font_size+15 ?>px"><b><?= $biller->name ?></b></div>
										<div><?= $biller->address.$biller->city ?></div>
										<div><?= lang('tel').' : '. $biller->phone ?></div>	
										<div><?= lang('email').' : '. $biller->email ?></div>	
									</td>
									<td class="text_center" style="width:20%">
										<?= $this->bpas->qrcode('link', urlencode(admin_url('hr/employee_details/' . $row->id)), 2); ?>
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
									<td class="text_center" style="width:25%"><span style="font-size:<?= $font_size+6 ?>px"><b><i><?= lang('employee_details') ?></i></b></span></td>
									<td valign="bottom" style="width:15%"><hr class="hr_title"></td>
								</tr>
							</table>
						</th>
					</tr>
					<tr>
						<th>
							<table>
								<tr>
									<td>
										<fieldset style="margin-left:5px !important; padding-bottom: 5px;">
											<legend style="font-size:<?= $font_size ?>px"><b><i><?= lang('information') ?></i></b></legend>
											<table>
												<tr>
													<td width="170"><?= lang('name') ?></td>
													<td style="text-align:left"> : <b><?= $row->firstname . " " . $row->lastname ?></b></td>
													<td rowspan="9" style="text-align:right !important;">
														<div style="margin:0 10px;">
															<?php if($row->photo!=NULL || $row->photo!=''){  ?>
																<img src="<?= base_url('assets/uploads/'.$row->photo) ?>"  width="100"/>
															<?php }else{ ?>
																<img src="<?= base_url('assets/uploads/no_image.png') ?>"  width="100"/>
															<?php } ?>
														</div>
													</td>
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
												<tr>
													<td><?= lang('policy') ?></td>
													<td style="text-align:left"> : <b><?= $policies->policy ?></b></td>
												</tr>
												<tr>
													<td><?= lang("company") ?></td>
													<td style="text-align:left"> : <b><?= $biller->company ?></b></td>
												</tr>
												<tr>
													<td><?= lang("phone") ?></td>
													<td style="text-align:left"> : <b><?= $row->phone ?></b></td>
												</tr>
												<tr>
													<td><?= lang("nationality") ?></td>
													<td style="text-align:left"> : <b><?= $row->nationality ?></b></td>
												</tr>
												<tr>
													<td><?= lang("nric") ?></td>
													<td style="text-align:left"> : <b><?= $row->nric_no ?></b></td>
												</tr>
												<tr>
													<td><?= lang("employee_date") ?></td>
													<td style="text-align:left"> : <b><?= $this->bpas->hrsd($employee_info->employee_date) ?></b></td>
												</tr>
												<tr>
													<td><?= lang("status") ?></td>
													<td style="text-align:left"> : <b><?= lang($employee_info->status) ?></b></td>
												</tr>
												<?php if($employee_info->status == 'inactive'){ ?>
													<tr>
														<td><?= lang("resigned_date") ?></td>
														<td style="text-align:left"> : <b><?= $this->bpas->hrsd($employee_info->resigned_date) ?></b></td>
													</tr>	
												<?php } ?>

												
												<tr>
													<td><?= lang("non_resident") ?></td>
													<td style="text-align:left"> : <b><?= $row->non_resident ?></b></td>
												</tr>
												<?php if($row->nssf) {?>
												<tr>
													<td><?= lang("nssf") ?></td>
													<td style="text-align:left"> : <b><?= $this->site->yesno($row->nssf); ?></b></td>
												</tr>
												<tr>
													<td><?= lang("nssf_number") ?></td>
													<td style="text-align:left"> : <b><?= $row->nssf_number ?></b></td>
												</tr>
												<?php } ?>
												<tr>
													<td><?= lang("book_type") ?></td>
													<td style="text-align:left"> : <b><?= $row->book_type ?></b></td>
												</tr>
												<?php if($row->book_type == 'workbook'){?>
												<tr>
													<td><?= lang("workbook_number") ?></td>
													<td style="text-align:left"> : <b><?= $row->workbook_number ?></b></td>
												</tr>
												<?php
												}else{ ?>
												<tr>
													<td><?= lang("work_permit_number") ?></td>
													<td style="text-align:left"> : <b><?= $row->work_permit_number ?></b></td>
												</tr>
												<?php }?>
											</table>
											<hr>
											<table>
												<tr>
													<td width="170"><?= lang("basic_salary") ?></td>
													<td>: <?= $this->bpas->formatDecimal($employee_info->net_salary); ?></td>
													<td width="170"><?= lang("annual_leave") ?></td>
													<td>: <?= $employee_info->annual_leave; ?></td>
												</tr>
												<tr>
													<td width="170"></td>
													<td></td>
													<td><?= lang("take_leave") ?></td>
													<td>: <?= $employee_info->annual_leave; ?></td>
												</tr>
												<tr>
													<td width="170"></td>
													<td></td>
													<td><?= lang("available_leave") ?></td>
													<td>: <?= $employee_info->annual_leave; ?></td>
												</tr>
											</table>
										</fieldset>
									</td>
								</tr>
								
							</table>
						</th>
					</tr>
				</thead>
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
	.payslip_table th, .payslip_table td{
		padding:5px;
	}
</style>
