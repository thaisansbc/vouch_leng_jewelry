<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
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
        <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= lang('pre_payslip_forms_report'); ?></h2>
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
                <div id="form">

                    <?php echo admin_form_open("payrolls/pre_payslip_forms_report"); ?>
                    <div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label" for="month"><?= lang("month"); ?></label>
								<?php echo form_input('month', (isset($_POST['month']) ? $_POST['month'] : date("m/Y")), 'class="form-control month" id="month"'); ?>
							</div>
						</div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
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
	$font_size = 10;
	$td_line_height = $font_size + 15;
	$min_height = $font_size * 6; 
	$margin = $font_size - 5;
	$margin_signature = $font_size * 2;
	$payslip_tables = "";
	if($payslips){
		$i = 1;
		foreach($payslips as $payslip){
			$payslip_tables .= "<div style='margin-bottom:20px; margin-top:20px'>
									<table>
										<thead>
											<tr>
												<th style='line-height:30px !important'>
													".$payslip->name." (បញ្ជីប្រាក់បៀរវត្សលើកទី១សំរាប់ប្រចាំខែ".$this->bms->numberToKhmerMonth(sprintf("%02s", $payslip->month))." ឆ្នាំ".$this->bms->numberToKhmer($payslip->year).")  (ក្រុម:".$payslip->group.")
												</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td>
													<table class='table_item'>
														<thead>
															<tr>
																<th rowspan='2'>".lang('ល-រ')."</th>
																<th rowspan='2'>".lang('អត្តលេខ')."</th>
																<th rowspan='2'>".lang('ឈ្មោះ')."</th>
																<th rowspan='2'>".lang('ភេទ')."</th>
																<th rowspan='2'>".lang('តួរនាទី')."</th>
																<th>".lang('ថ្ងៃចូល')."</th>
																<th>".lang('ប្រាក់')."</th>
																<th>".lang('ចំនួន')."</th>
																<th>".lang('ប្រាក់')."</th>
																<th colspan='3'>".lang('សរុបប្រាក់')."</th>
																<th class='no-print' colspan='4'>".lang('ដុល្លារ')."</th>
																<th class='no-print' colspan='7'>".lang('រៀល')."</th>
															</tr>
															<tr class='sub_theader'>
																<th>".lang('ធ្វើការ')."</th>
																<th>".lang('គោល')."</th>
																<th>".lang('ថ្ងៃ')."</th>
																<th>".lang('ធ្វើបាន')."</th>
																<th>".lang('ត្រូវបើក')."</th>
																<th>".lang('ប្រាក់ដុល្លារ')."</th>
																<th>".lang('ប្រាក់រៀល')."</th>
																<th class='no-print'>".lang('100')."</th>
																<th class='no-print'>".lang('50')."</th>
																<th class='no-print'>".lang('20')."</th>
																<th class='no-print'>".lang('10')."</th>
																<th class='no-print'>".lang('20000')."</th>
																<th class='no-print'>".lang('10000')."</th>
																<th class='no-print'>".lang('5000')."</th>
																<th class='no-print'>".lang('2000')."</th>
																<th class='no-print'>".lang('1000')."</th>
																<th class='no-print'>".lang('500')."</th>
																<th class='no-print'>".lang('100')."</th>
															</tr>
														</thead>
														<tbody id='tbody'>
															<tr>
																<td class='text_center'>".$i."</td>
																<td class='text_left'>".$payslip->empcode."</td>
																<td class='text_left'>".$payslip->lastname." ".$payslip->firstname."</td>
																<td class='text_left'>".($payslip->gender == "male" ? "M" : "F")."</td>
																<td class='text_left'>".$payslip->position."</td>
																<td class='text_center'>".$this->bms->hrsd($payslip->employee_date)."</td>
																<td class='text_right'>".$this->bms->formatMoney($payslip->basic_salary)."</td>
																<td class='text_center'>".($payslip->present+$payslip->holiday+$payslip->annual_leave+$payslip->special_leave+$payslip->sick_leave)."</td>
																<td class='text_right'>".$this->bms->formatMoney(($payslip->present+$payslip->holiday+$payslip->annual_leave+$payslip->special_leave+$payslip->sick_leave) * ($payslip->basic_salary/$payslip->working_day))."</td>
																<td class='text_right'>".$this->bms->formatMoney($payslip->gross_salary)."</td>
																<td class='text_right'>".$this->bms->formatMoney($payslip->total_usd)."</td>
																<td class='text_right'>".$this->bms->formatMoneyKH($payslip->total_khr)."</td>
																<td class='text_center no-print'>".$payslip->usd_100."</td>
																<td class='text_center no-print'>".$payslip->usd_50."</td>
																<td class='text_center no-print'>".$payslip->usd_20."</td>
																<td class='text_center no-print'>".$payslip->usd_10."</td>
																<td class='text_center no-print'>".$payslip->khr_20000."</td>
																<td class='text_center no-print'>".$payslip->khr_10000."</td>
																<td class='text_center no-print'>".$payslip->khr_5000."</td>
																<td class='text_center no-print'>".$payslip->khr_2000."</td>
																<td class='text_center no-print'>".$payslip->khr_1000."</td>
																<td class='text_center no-print'>".$payslip->khr_500."</td>
																<td class='text_center no-print'>".$payslip->khr_100."</td>
															</tr>
														</tbody>
									
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</div>";
			$i++;	
		}
	}
	if($payslip_tables){
		echo "<div class='box_payslip'>".$payslip_tables."</div>";
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
	.table_item th{
		border:1px solid black !important;
		background-color : #7daaf2 !important;
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

		});
		$(document).on("change", "#department", function () {
			var department_id = $(this).val();
			$.ajax({
				type: "get", 
				async: true,
				url: site.base_url + "payrolls/get_groups_positions/",
				data : { department_id : department_id },
				dataType: "json",
				success: function (data) {
					var group_sel = "<select class='form-control' id='group' name='group'><option value=''><?= lang('select').' '.lang('group') ?></option>";
					if (data.groups != false) {
						$.each(data.groups, function () {
							group_sel += "<option value='"+this.id+"'>"+this.name+"</option>";
						});
						
					}
					group_sel += "</select>"
		
					var postion_sel = "<select class='form-control' id='position' name='position'><option value=''><?= lang('select').' '.lang('position') ?></option>";
					if (data.positions != false) {
						$.each(data.positions, function () {
							postion_sel += "<option value='"+this.id+"'>"+this.name+"</option>";
						});
						
					}
					postion_sel += "</select>"
					$(".group_box").html(group_sel);
					$(".position_box").html(postion_sel);
					$('select').select2();
				}
			});
		});
    });
</script>



