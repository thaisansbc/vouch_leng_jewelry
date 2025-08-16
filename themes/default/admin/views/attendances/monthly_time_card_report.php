<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
	$biller_id = $this->input->post("biller") ? $this->input->post("biller") : false;
	$position_id = $this->input->post("position") ? $this->input->post("position") : false;
	$department_id = $this->input->post("department") ? $this->input->post("department") : false;
	$group_id = $this->input->post("group") ? $this->input->post("group") : false;
	$employee_id = $this->input->post("employee") ? $this->input->post("employee") : false;
	$month = $this->input->post("month") ? $this->input->post("month") : date("m/Y");
	$attendances = $this->attendances_model->getMonthlyAttendances($biller_id,$position_id,$department_id,$group_id,$employee_id,$month);
	$employee_attendances = $this->attendances_model->getIndexEmployeeAttedances($biller_id,$position_id,$department_id,$group_id,$employee_id,$month);
	$ot_check_in_outs = $this->attendances_model->getIndexEmployeeOTAttedances($biller_id,$position_id,$department_id,$group_id,$employee_id,$month);
	$check_in_outs = $employee_attendances["check_in_out"];
	$daily_attendances = $employee_attendances["attendances"];
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= lang('monthly_time_card_report'); ?></h2>
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

                    <?php echo admin_form_open("attendances/monthly_time_card_report"); ?>
                    <div class="row">
						
						<div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
								<?php
                                $opt_biller[""] = lang('select').' '.lang('biller');
                                foreach ($billers as $biller) {
                                    $opt_biller[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                }
                                echo form_dropdown('biller', $opt_biller, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
						<div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="department"><?= lang("department"); ?></label>
								<?php
								$dep_opt = array(lang('select')." ".lang('department'));
								if($departments){
									foreach($departments as $department){
										$dep_opt[$department->id] = $department->name;
									}
								}
								echo form_dropdown('department', $dep_opt, (isset($_POST['department']) ? $_POST['department'] : ""), 'id="department" class="form-control"');
								?>
                            </div>
                        </div>
						
						<div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="group"><?= lang("group"); ?></label>
								<?php
								$group_opt = array(lang('select')." ".lang('group'));
								if($groups){
									foreach($groups as $group){
										$group_opt[$group->id] = $group->name;
									}
								}
								echo form_dropdown('group', $group_opt, (isset($_POST['group']) ? $_POST['group'] : ""), 'id="group" class="form-control"');
								?>
                            </div>
                        </div>
						
						<div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="position"><?= lang("position"); ?></label>
								<?php
								$pos_opt = array(lang('select')." ".lang('position'));
								if($positions){
									foreach($positions as $position){
										$pos_opt[$position->id] = $position->name;
									}
								}
								echo form_dropdown('position', $pos_opt, (isset($_POST['position']) ? $_POST['position'] : ""), 'id="position" class="form-control"');
								?>
                            </div>
                        </div>
						
						<div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="suggest_employee"><?= lang("employee"); ?></label>
								<input type="text" name="employee_id" id="suggest_employee" value="<?= set_value('employee_id') ?>" class="form-control ui-autocomplete-input" />
								<input type="hidden" name="employee" value="<?= set_value('employee') ?>" id="suggest_employee_id">
							</div>
                        </div>
						
						<div class="col-md-3">
							<div class="form-group">
								<label class="control-label" for="month"><?= lang("month"); ?></label>
								<?php echo form_input('month', (isset($_POST['month']) ? $_POST['month'] : date("m/Y")), 'class="form-control month" id="month"'); ?>
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
	$font_size = $this->config->item('font_size') - 0.001;
	$td_line_height = $font_size + 10;
	$min_height = $font_size * 4.5; 
	$margin = $font_size - 5;
	$margin_signature = $font_size;
	$attendance_tables = "";
	$split_month = explode('/',$month);
	$split_month = $split_month[1].'-'.$split_month[0];
	if($attendances){
		foreach($attendances as $attendance){
			$tbody = '<tr>
						<td class="text_left" colspan="18"><b>'.$attendance->lastname.' '.$attendance->firstname.', '.$attendance->empcode.', '.$attendance->department.'</b></td>
					</tr>';
			$b = 1;
			$tlate = 0;
			$tleave_early = 0;
			$tpresent = 0;
			$tnormal_ot = 0;
			$tweekend_ot = 0;
			$tholiday_ot = 0;
			$tabsent = 0;
			$tother_leave = 0;
			$tsick_leave = 0;
			$tannual_leave = 0;
			$tspecial_leave = 0;
			$thalf_pay_leave = 0;
			$begin = new DateTime($split_month.'-01');
			$end = new DateTime(date("Y-m-t", strtotime($split_month.'-01')));
			
			for($i = $begin; $i <= $end; $i->modify('+1 day')){
				$date = $i->format("Y-m-d");
				$one_in = (isset($check_in_outs[$attendance->id][$date]['one']['in']) ? $check_in_outs[$attendance->id][$date]['one']['in'] : "");
				$one_out = (isset($check_in_outs[$attendance->id][$date]['one']['out']) ? $check_in_outs[$attendance->id][$date]['one']['out'] : "");
				$two_in = (isset($check_in_outs[$attendance->id][$date]['two']['in']) ? $check_in_outs[$attendance->id][$date]['two']['in'] : "");
				$two_out = (isset($check_in_outs[$attendance->id][$date]['two']['out']) ? $check_in_outs[$attendance->id][$date]['two']['out'] : "");
				$late = "";
				$leave_early = "";
				$present = "";
				$normal_ot = "";
				$weekend_ot = "";
				$holiday_ot = ""; 
				$absent = "";
				$other_leave = "";
				$sick_leave = "";
				$annual_leave = "";
				$special_leave = "";
				$half_pay_leave = "";
				
				if(isset($daily_attendances[$attendance->id][$date])){
					if($daily_attendances[$attendance->id][$date]->weekend > 0){
						if($one_in == "" || $one_out == ""){
							$one_in = "SUN";
							$one_out = "SUN";
						}
						if($two_in == "" || $two_out == ""){
							$two_in = "SUN";
							$two_out = "SUN";
						}
						
					}else if($daily_attendances[$attendance->id][$date]->holiday > 0){
						if($one_in == "" || $one_out == ""){
							$one_in = "HOL";
							$one_out = "HOL";
						}
						if($two_in == "" || $two_out == ""){
							$two_in = "HOL";
							$two_out = "HOL";
						}
					}
					
					if(isset($policy_hour[$attendance->policy_id])){
						if($daily_attendances[$attendance->id][$date]->present > 0){
							$present = $this->bms->formatDecimal($daily_attendances[$attendance->id][$date]->present * $policy_hour[$attendance->policy_id],0);
							$tpresent += $present;
						}
						if($daily_attendances[$attendance->id][$date]->absent > 0){
							$absent = $this->bms->formatDecimal($daily_attendances[$attendance->id][$date]->absent * $policy_hour[$attendance->policy_id],0);
							$tabsent += $absent;
						}
						if($daily_attendances[$attendance->id][$date]->other_leave > 0){
							$other_leave = $this->bms->formatDecimal($daily_attendances[$attendance->id][$date]->other_leave * $policy_hour[$attendance->policy_id],0);
							$tother_leave += $other_leave;
						}
						if($daily_attendances[$attendance->id][$date]->sick_leave > 0){
							$sick_leave = $this->bms->formatDecimal($daily_attendances[$attendance->id][$date]->sick_leave * $policy_hour[$attendance->policy_id],0);
							$tsick_leave += $sick_leave;
						}
						if($daily_attendances[$attendance->id][$date]->annual_leave > 0){
							$annual_leave = $this->bms->formatDecimal($daily_attendances[$attendance->id][$date]->annual_leave * $policy_hour[$attendance->policy_id],0);
							$tannual_leave += $annual_leave;
						}
						if($daily_attendances[$attendance->id][$date]->special_leave > 0){
							$special_leave = $this->bms->formatDecimal($daily_attendances[$attendance->id][$date]->special_leave * $policy_hour[$attendance->policy_id],0);
							$tspecial_leave += $special_leave;
						}
						if($daily_attendances[$attendance->id][$date]->half_pay_leave > 0){
							$half_pay_leave = $this->bms->formatDecimal($daily_attendances[$attendance->id][$date]->half_pay_leave * $policy_hour[$attendance->policy_id],0);
							$thalf_pay_leave += $half_pay_leave;
						}
					}
					if($daily_attendances[$attendance->id][$date]->late > 0){
						$late = $this->bms->formatDecimal(($daily_attendances[$attendance->id][$date]->late / 60),0);
						$tlate += $late;
					}
					if($daily_attendances[$attendance->id][$date]->leave_early > 0){
						$leave_early = $this->bms->formatDecimal(($daily_attendances[$attendance->id][$date]->leave_early / 60),0);
						$tleave_early += $leave_early;
					}
					if($daily_attendances[$attendance->id][$date]->normal_ot > 0){
						$normal_ot = $this->bms->formatDecimal(($daily_attendances[$attendance->id][$date]->normal_ot / 3600),0);
						$tnormal_ot += $normal_ot;
					}
					if($daily_attendances[$attendance->id][$date]->weekend_ot > 0){
						$weekend_ot = $this->bms->formatDecimal(($daily_attendances[$attendance->id][$date]->weekend_ot / 3600),0);
						$tweekend_ot += $weekend_ot;
						if(isset($ot_check_in_outs[$attendance->id][$date]['morning'])){
							$one_in = $ot_check_in_outs[$attendance->id][$date]['morning']->check_in;
							$one_out = $ot_check_in_outs[$attendance->id][$date]['morning']->check_out;
						}
						if(isset($ot_check_in_outs[$attendance->id][$date]['afternoon'])){
							$two_in = $ot_check_in_outs[$attendance->id][$date]['afternoon']->check_in;
							$two_out = $ot_check_in_outs[$attendance->id][$date]['afternoon']->check_out;
						}
						
					}
					if($daily_attendances[$attendance->id][$date]->holiday_ot > 0){
						$holiday_ot = $this->bms->formatDecimal(($daily_attendances[$attendance->id][$date]->holiday_ot / 3600),0);
						$tholiday_ot += $holiday_ot;
						if(isset($ot_check_in_outs[$attendance->id][$date]['morning'])){
							$one_in = $ot_check_in_outs[$attendance->id][$date]['morning']->check_in;
							$one_out = $ot_check_in_outs[$attendance->id][$date]['morning']->check_out;
						}
						if(isset($ot_check_in_outs[$attendance->id][$date]['afternoon'])){
							$two_in = $ot_check_in_outs[$attendance->id][$date]['afternoon']->check_in;
							$two_out = $ot_check_in_outs[$attendance->id][$date]['afternoon']->check_out;
						}
					}
				}
				
				
				$tbody .='<tr>
							<td class="text_center">'.$b++.'</td>
							<td class="text_center">'.$this->bms->hrsd($date).'</td>
							<td class="text_center">'.$one_in.'</td>
							<td class="text_center">'.$one_out.'</td>
							<td class="text_center">'.$two_in.'</td>
							<td class="text_center">'.$two_out.'</td>
							<td class="text_center">'.$late.'</td>
							<td class="text_center">'.$leave_early.'</td>
							<td class="text_center">'.$present.'</td>
							<td class="text_center">'.$normal_ot.'</td>
							<td class="text_center">'.$weekend_ot.'</td>
							<td class="text_center">'.$holiday_ot.'</td>
							<td class="text_center">'.$absent.'</td>
							<td class="text_center">'.$other_leave.'</td>
							<td class="text_center">'.$sick_leave.'</td>
							<td class="text_center">'.$annual_leave.'</td>
							<td class="text_center">'.$special_leave.'</td>
							<td class="text_center">'.$half_pay_leave.'</td>
						</tr>';		
			}
			
			$tbody .='<tr>
						<td colspan="6"></td>
						<td class="text_center">'.($tlate > 0 ? $tlate : '').'</td>
						<td class="text_center">'.($tleave_early > 0 ? $tleave_early : '').'</td>
						<td class="text_center">'.($tpresent > 0 ? $tpresent : '').'</td>
						<td class="text_center">'.($tnormal_ot > 0 ? $tnormal_ot : '').'</td>
						<td class="text_center">'.($tweekend_ot > 0 ? $tweekend_ot : '').'</td>
						<td class="text_center">'.($tholiday_ot > 0 ? $tholiday_ot : '').'</td>
						<td class="text_center">'.($tabsent > 0 ? $tabsent : '').'</td>
						<td class="text_center">'.($tother_leave > 0 ? $tother_leave : '').'</td>
						<td class="text_center">'.($tsick_leave > 0 ? $tsick_leave : '').'</td>
						<td class="text_center">'.($tannual_leave > 0 ? $tannual_leave : '').'</td>
						<td class="text_center">'.($tspecial_leave > 0 ? $tspecial_leave : '').'</td>
						<td class="text_center">'.($thalf_pay_leave > 0 ? $thalf_pay_leave : '').'</td>
					</tr>';
			

			$attendance_tables .= "<div class='modal-dialog modal-lg' style='width:70%'>
									<table>
										<tbody>
											<tr>
												<th>
													<table>
														<tr>
															<td class='text_center' style='width:60%'>
																<div style='font-size:".($font_size+15)."px'><b>".$attendance->name."</b></div>
																<div style='font-size:".($font_size+5)."px'>".lang("employee_monthly_time_in_out_report")."</div>
															</td>
														</tr>
														<tr>
															<td class='text_left' style='font-size:'".($font_size+2)."px'>
																".$this->bms->hrsd($split_month.'-01').' - '.$this->bms->hrsd(date("Y-m-t", strtotime($split_month.'-01')))."
															</td>
														</tr>
													</table>
												</th>
											</tr>
											<tr>
												<td>
													<table class='table_item'>
														<thead>
															<tr>
																<th>".lang('ល.រ')."</th>
																<th>".lang('កាលបរិច្ឆេទ')."</th>
																<th>".lang('ម៉ោងចូល')."</th>
																<th>".lang('ម៉ោងចេញ')."</th>
																<th>".lang('ម៉ោងចូល')."</th>
																<th>".lang('ម៉ោងចេញ')."</th>
																<th>".lang('មកយឹត')."</th>
																<th>".lang('ចេញមុន')."</th>
																<th>".lang('ម៉ោងធ្វើការ')."</th>
																<th>".lang('ម៉ោងថែម')."</th>
																<th>".lang('ម៉ោងថែម')."</th>
																<th>".lang('ម៉ោងថែម')."</th>
																<th>".lang('អវត្តអត់ច្បាប់')."</th>
																<th>".lang('អវត្តមានមានច្បាប់')."</th>
																<th>".lang('ច្បាប់ឈឺ')."</th>
																<th>".lang('ឈប់ប្រចាំឆ្នាំ')."</th>
																<th>".lang('ច្បាប់ពិសេស')."</th>
																<th>".lang('ច្បាប់សំរាល')."</th>
															</tr>
															<tr class='sub_header'>
																<th>".lang("Nº")."</th>
																<th>".lang("Date")."</th>
																<th>".lang("Time In")."</th>
																<th>".lang("Time Out")."</th>
																<th>".lang("Time In")."</th>
																<th>".lang("Time Out")."</th>
																<th>".lang("Late")."</th>
																<th>".lang("Early")."</th>
																<th>".lang("WH")."</th>
																<th>".lang("OT")."</th>
																<th>".lang("OT Sunday")."</th>
																<th>".lang("OT PH")."</th>
																<th>".lang("AB")."</th>
																<th>".lang("AW")."</th>
																<th>".lang("Sick")."</th>
																<th>".lang("ANL")."</th>
																<th>".lang("SP")."</th>
																<th>".lang("Maternity")."</th>
															</tr>
														</thead>
														<tbody id='tbody'>
															".$tbody."
														</tbody>
														<tbody id='tfooter'>
														</tbody>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</div>";	
		}
	}
	if($attendance_tables){
		echo "<div class='box_attendance'>".$attendance_tables."</div>";
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
		.modal-dialog{
			page-break-after: always !important;
			width:100% !important;
		}
		@page{
			margin: 5mm; 
			size: landscape;
		}
		body {
			-webkit-print-color-adjust: exact !important;  
			color-adjust: exact !important;         
			zoom: 77% !important;	
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

<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
		$('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('attendances/monthly_time_card_excel/xls/?v=1'.$v)?>";
            return false;
        });
		
		
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



