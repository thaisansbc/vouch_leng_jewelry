<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
	.table_item th {
		border: 1px solid black !important;
		background-color: #7daaf2 !important;
		text-align: center !important;
		line-height: 10px !important;
	}
        
        @media print {
            .table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td {
                border-top: 1px solid #000000 !important;
            }
		.table_item th {
			border: 1px solid black !important;
			background-color: #7daaf2 !important;
			text-align: center !important;
			line-height: 0px !important;
	}
	}
    </style>
<?php
	$v = "";
	if ($this->input->post('biller')) {
		$v .= "&biller=" . $this->input->post('biller');
	}
	if ($this->input->post('department')) {
		$v .= "&department=" . $this->input->post('department');
	}
	if ($this->input->post('group')) {
		$v .= "&group=" . $this->input->post('group');
	}
	if ($this->input->post('position')) {
		$v .= "&position=" . $this->input->post('position');
	}
	if ($this->input->post('employee')) {
		$v .= "&employee=" . $this->input->post('employee');
	}
	if ($this->input->post('month')) {
		$v .= "&month=" . $this->input->post('month');
	}
?>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= lang('payslips_report'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="pdf" onclick="window.print()" class="tip" title="<?= lang('print') ?>">
                        <i class="icon fa fa-print"></i>
                    </a>
                </li>
				<li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form" class="no-print">

                    <?php echo admin_form_open("payrolls/payslips_report"); ?>
                    <div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label" for="month"><?= lang("month"); ?></label>
								<?php echo form_input('month', (isset($_POST['month']) ? $_POST['month'] : ""), 'class="form-control month" id="month"'); ?>
							</div>
						</div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("biller"); ?></label>
                                <?php
                                $bl[""] = lang('select').' '.lang('biller');
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
						
						<div class="col-md-4">
							<label class="control-label" for="position"><?= lang("position"); ?></label>
							<div class="position_box form-group">
								<?php
									$ps[""] = lang("select")." ".lang("position");
									if(isset($positions) && $positions){
										foreach ($positions as $position) {
											$ps[$position->id] = $position->name;
										}
									}
									echo form_dropdown('position', $ps, (isset($_POST['position']) ? $_POST['position'] : ""), 'id="position" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("position") . '"  class="form-control input-tip select" style="width:100%;"');
								?>
							</div>
						</div>
						<div class="col-md-4">
							<label class="control-label" for="department"><?= lang("department"); ?></label>
							<div class="department_box form-group">
								<?php
									$dp[""] = lang("select")." ".lang("department");
									if(isset($departments) && $departments){
										foreach ($departments as $department) {
											$dp[$department->id] = $department->name;
										}
									}
									echo form_dropdown('department', $dp, (isset($_POST['department']) ? $_POST['department'] : ""), 'id="department" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("department") . '"  class="form-control input-tip select" style="width:100%;"');
								?>
							</div>
						</div>
						<div class="col-md-4">
							<label class="control-label" for="group"><?= lang("group"); ?></label>
							<div class="group_box form-group">
								<?php
									$gp[""] = lang("select")." ".lang("group");
									if(isset($groups) && $groups){
										foreach ($groups as $group) {
											$gp[$group->id] = $group->name;
										}
									}
									echo form_dropdown('group', $gp, (isset($_POST['group']) ? $_POST['group'] : ""), 'id="group" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("group") . '"  class="form-control input-tip select" style="width:100%;"');
								?>
							</div>
						</div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="suggest_employee"><?= lang("employee"); ?></label>
								<input type="text" name="employee_id" id="suggest_employee" value="<?= set_value('employee_id') ?>" class="form-control ui-autocomplete-input" />
								<input type="hidden" name="employee" value="<?= set_value('employee') ?>" id="suggest_employee_id">
							</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php
	$a4 = true;
	if($a4){
		$font_size = 12;
	}else{
		$font_size = 10;
	}
	$td_line_height = $font_size + 13;
	$min_height = $font_size * 6; 
	$margin = $font_size - 5;
	$margin_signature = $font_size * 2;
	$payslip_tables = "";
	if($payslips){
		$i = 1;
		foreach($payslips as $payslip){
			if($a4 || ($i % 2) == 0){
				$page_break = "page-break-after: always !important";
			}else{
				$page_break = "";
			}
			
			$present = $payslip->working_day - $payslip->permission - $payslip->absent;
			$td_addtion = "";
			if($payslip->additions){
				$additions = json_decode($payslip->additions);
				foreach($additions as $addition){
					$td_addtion .= "<tr>
										<td class='text_left'>".$addition->name."</td>
										<td></td>
										<td class='text_right'>".$this->bpas->formatMoney($addition->value)."</td>
									</tr>";
				}
			}
			$td_deduction = "";
			if($payslip->deductions){
				$deductions = json_decode($payslip->deductions);
				foreach($deductions as $deduction){
					$td_deduction .= "<tr>
										<td class='text_left'>".$deduction->name."</td>
										<td class='text_right'>".$this->bpas->formatMoney($deduction->value)."</td>
										<td></td>
									</tr>";
				}
			}
			$payslip_tables .= "<div style='width:49%;float:left;padding-right:2%;padding-bottom:10px;margin-left: 5px;padding :10px;".$page_break."'>
									<table>
										<thead>
											<tr>
												<th>
													<table>
														<tr>
															<td class='text_center' style='width:20%'>
																<img src='".base_url()."assets/uploads/logos/" .$payslip->logo."' alt='".$payslip->name."'>
															</td>
															<td class='text_center' style='width:80%'>
																<div style='font-size:".($font_size+13)."px'><b>".$payslip->name."</b></div>
																<div>".$payslip->address.$payslip->city."</div>
																<div>".lang('tel').' : '. $payslip->phone."</div>	
																<div>".lang('email').' : '. $payslip->email."</div>	
															</td>
														</tr>
													</table>
												</th>
											</tr>
											<tr>
												<th>
													<table>
														<tr>
															<td valign='bottom' style='width:65%'><hr class='hr_title'></td>
															<td class='text_center' style='width:15%'><span style='font-size:".($font_size+5)."px'><b><i>".lang('payslip')."</i></b></span></td>
															<td valign='bottom' style='width:20%'><hr class='hr_title'></td>
														</tr>
													</table>
												</th>
											</tr>
											<tr>
												<th>
													<fieldset>
														<legend style='font-size:".$font_size."px'><b><i>".lang('information')."</i></b></legend>
														<table style='width:100%'>
															<tr>
																<td class='text_left' style='width:15%'>".lang('month')."</td>
																<td class='text_left'> : ".$payslip->month.'/'.$payslip->year."</td>
																<td class='text_left' style='width:15%'>".lang('code')."</td>
																<td class='text_left'> : ".$payslip->empcode."</td>
															</tr>
															<tr>
																<td class='text_left'>".lang('name')."</td>
																<td class='text_left'> : ".$payslip->lastname.' '.$payslip->firstname."</td>
																<td class='text_left'>".lang('position')."</td>
																<td class='text_left'> : ".$payslip->position."</td>
															</tr>
															<tr>
																<td class='text_left'>".lang('department')."</td>
																<td class='text_left'> : ".$payslip->department."</td>
																<td class='text_left'>".lang('group')."</td>
																<td class='text_left'> : ".$payslip->group."</td>
															</tr>
															<tr>
																<td class='text_left'>".lang('working_day')."</td>
																<td class='text_left'> : ".$payslip->working_day." ".($payslip->working_day > 1 ? lang('days') : lang('day'))."</td>
																<td class='text_left'>".lang('present')."</td>
																<td class='text_left'> : ".$present." ".($present > 1 ? lang('days') : lang('day'))."</td>
															</tr>
															<tr class='hide'>
																<td class='text_left'>".lang('permission')."</td>
																<td class='text_left'> : ".$payslip->permission." ".($payslip->permission > 1 ? lang('days') : lang('day'))."</td>
																<td class='text_left'>".lang('absent')."</td>
																<td class='text_left'> : ".$payslip->absent." ".($payslip->absent > 1 ? lang('days') : lang('day'))."</td>
															</tr>
															<tr class='hide'>
																<td class='text_left'>".lang('late/early')."</td>
																<td class='text_left'> : ".$payslip->late." ".($payslip->late > 1 ? lang('hours') : lang('hour'))."</td>
																<td class='text_left'>".lang('normal_ot')."</td>
																<td class='text_left'> : ".$payslip->normal_ot." ".($payslip->normal_ot > 1 ? lang('hours') : lang('hour'))."</td>
															</tr>
															<tr class='hide'>
																<td class='text_left'>".lang('weekend_ot')."</td>
																<td class='text_left'> : ".$payslip->weekend_ot." ".($payslip->weekend_ot > 1 ? lang('hours') : lang('hour'))."</td>
																<td class='text_left'>".lang('holiday_ot')."</td>
																<td class='text_left'> : ".$payslip->holiday_ot." ".($payslip->holiday_ot > 1 ? lang('hours') : lang('hour'))."</td>
															</tr>
														</table>
													</fieldset>
												</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td>
													<table class='table_item'>
														<thead>
															<tr>
																<th>".lang('description')." បរិយាយ</th>
																<th>".lang('deduction')." កាត់ប្រាក់ចំណូល</th>
																<th>".lang('earning')." ប្រាក់ចំណូល</th>
															</tr>
														</thead>
														<tbody id='tbody'>
															<tr>
																<td class='text_left'>".lang('basic_salary')." ប្រាក់ខែគោល</td>
																<td></td>
																<td class='text_right'>".$this->bpas->formatMoney($payslip->basic_salary)."</td>
																
															</tr>
															<tr>
																<td class='text_left'>".lang('overtime')." ថែមម៉ោង</td>
																<td></td>
																<td class='text_right'>".$this->bpas->formatMoney($payslip->overtime)."</td>
															</tr>
															".$td_addtion."
															<tr>
																<td class='text_left'>".lang('absent')." អវត្តមានពីការងារ</td>
																<td class='text_right'>".$this->bpas->formatMoney($payslip->absent_amount)."</td>
																<td></td>
															</tr>
															<tr>
																<td class='text_left'>".lang('permission')." អនុញ្ញាត</td>
																<td class='text_right'>".$this->bpas->formatMoney($payslip->permission_amount)."</td>
																<td></td>
															</tr>
															<tr>
																<td class='text_left'>".lang('late_early')." យឺតម៉ោង</td>
																<td class='text_right'>".$this->bpas->formatMoney($payslip->late_amount)."</td>
																<td></td>
															</tr>
															".$td_deduction."
															<tr>
																<td class='text_left'>".lang('cash_advance')." បើកប្រាក់ខែមុន</td>
																<td class='text_right'>".$this->bpas->formatMoney($payslip->cash_advanced)."</td>
																<td></td>
															</tr>
															<tr>
																<td class='text_left'>".lang('pension')." ប.ស.ស</td>
																<td class='text_right'>".$this->bpas->formatMoney($payslip->pension_by_staff)."</td>
																<td></td>
															</tr>
															<tr>
																<td class='text_left'>".lang('tax')." កាត់ពន្ធប្រាក់ខែ</td>
																<td class='text_right'>".$this->bpas->formatMoney($payslip->tax_payment)."</td>
																<td></td>
															</tr>
															<tr>
																<td colspan='2' class='text_left'><b>".lang('net_salary')." ប្រាក់ខែទទួលបាន</b></td>
																<td class='text_right'><b>".$this->bpas->formatMoney($payslip->net_salary)."</b></td>
															</tr>
														</tbody>
														<tbody id='tfooter'>
														</tbody>
													</table>
												</td>
											</tr>
										</tbody>
										<tfoot>
											<tr class='tr_print'>
												<td>
													<table style='margin-top:".$margin_signature."px;'>
														<tr>
															<td class='text_center' style='width:33%'>".lang('preparer').' '. lang('signature')."</td>
															<td class='text_center' style='width:33%'>".lang('employee').' '. lang('signature')."</td>
															<td class='text_center' style='width:33%'>".lang('approver') .' '. lang('signature')."</td>

														</tr>
														<tr>
															<td class='text_center' style='width:33%; padding-top:40px'>______________________</td>
															<td class='text_center' style='width:33%; padding-top:40px'>______________________</td>
															<td class='text_center' style='width:33%; padding-top:40px'>______________________</td>
														</tr>
													</table>
												</td>
											</tr>
										</tfoot>
									</table>
								</div>";
			$i++;					
		}
	}
	if($payslip_tables){
		echo "<div style='width:100%;clear:both;background:#ffffff;'>".$payslip_tables."</div>";
	}else{
		echo "<div class='col-lg-12'>".lang('sEmptyTable')."</div>";
	}
	
?>

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
	.td_print{
		border:none !important;
		border-left:1px solid black !important;
		border-right:1px solid black !important;
	}
	.modal-dialog{
		background-color:white !important;
		padding-left:12px; !important;
		padding-right:12px; !important;
		padding-top:12px; !important;
	}
	.hr_title{
		border:3px double #7daaf2 !important;
		margin-bottom:<?= $margin ?>px !important;
		margin-top:<?= $margin ?>px !important;
	}
	.table_item{
		margin-left: 0px;
	}
	.table_item th{
		border:1px solid black !important;
		background-color : #7daaf2 !important;
		text-align:center !important;
		line-height:17px !important;
	}
	.table_item td{
		border:1px solid black;
		line-height: 15px !important;
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
		border:2px solid #7daaf2 !important;
		min-height:<?= ($min_height+35) ?>px !important;
		margin-bottom : <?= $margin ?>px !important;
		padding-left : <?= $margin ?>px !important;
	}

	legend{
		width: initial !important;
		margin-bottom: initial !important;
		border: initial !important;
	}
	
	table{
		width:100% !important;
		font-size:<?= $font_size ?>px !important;
		border-collapse: collapse !important;
	}
</style>

<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
		$("#xls").click(function(e) {
			var result = "data:application/vnd.ms-excel," + encodeURIComponent( '<meta charset="UTF-8"><style> table { white-space:wrap; } table th, table td{ font-size:10px !important; }</style>' + $('.box_payslip').html());
			this.href = result;
			this.download = "payslip.xls";
			return true;			
		});
		$(document).on("change", "#biller", function () {	
			var biller_id = $(this).val();
			$.ajax({
				type: "get", 
				async: true,
				url: site.base_url + "payrolls/get_departments/",
				data : { biller_id : biller_id },
				dataType: "json",
				success: function (data) {
					var department_sel = "<select class='form-control' id='department' name='department'><option value=''><?= lang('select').' '.lang('department') ?></option>";
					if (data != false) {
						$.each(data, function () {
							department_sel += "<option value='"+this.id+"'>"+this.name+"</option>";
						});
						
					}
					department_sel += "</select>"
					$(".department_box").html(department_sel);
					$('select').select2();
				}
			});
			$.ajax({
				type: "get", 
				async: true,
				url: site.base_url + "payrolls/get_positions/",
				data : { biller_id : biller_id },
				dataType: "json",
				success: function (data) {
					var postion_sel = "<select class='form-control' id='position' name='position'><option value=''><?= lang('select').' '.lang('position') ?></option>";
					if (data != false) {
						$.each(data, function () {
							postion_sel += "<option value='"+this.id+"'>"+this.name+"</option>";
						});
						
					}
					postion_sel += "</select>"
					$(".position_box").html(postion_sel);
					$('select').select2();
				}
			});
		});
		$(document).on("change", "#department", function () {
			var department_id = $(this).val();
			$.ajax({
				type: "get", 
				async: true,
				url: site.base_url + "payrolls/get_groups/",
				data : { department_id : department_id },
				dataType: "json",
				success: function (data) {
					var group_sel = "<select class='form-control' id='group' name='group'><option value=''><?= lang('select').' '.lang('group') ?></option>";
					if (data != false) {
						$.each(data, function () {
							group_sel += "<option value='"+this.id+"'>"+this.name+"</option>";
						});
						
					}
					group_sel += "</select>"
					$(".group_box").html(group_sel);
					$('select').select2();
				}
			});
		});
    });
</script>



