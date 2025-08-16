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
	if ($this->input->post('kpi_type')) {
		$v .= "&kpi_type=" . $this->input->post('kpi_type');
	}
	
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-print"></i><?= lang('print_kpi'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="pdf" onclick="window.print()" class="tip" title="<?= lang('print') ?>">
                        <i class="icon fa fa-print"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12 no-print">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">
                    <?php echo admin_form_open("hr/print_kpi"); ?>
                    <div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label class="control-label" for="month"><?= lang("month"); ?></label>
								<?php echo form_input('month', (isset($_POST['month']) ? $_POST['month'] : ""), 'class="form-control month" id="month"'); ?>
							</div>
						</div>
						<div class="col-sm-3">
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
						
						<div class="col-md-3">
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
						<div class="col-md-3">
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
						<div class="col-md-3">
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
						<div class="col-md-3">
							<label class="control-label" for="kpi_type"><?= lang("kpi_type"); ?></label>
							<div class="form-group">
								<?php
									$kp[""] = lang("select")." ".lang("kpi_type");
									if(isset($kpi_types) && $kpi_types){
										foreach ($kpi_types as $kpi_type) {
											$kp[$kpi_type->id] = $kpi_type->name;
										}
									}
									echo form_dropdown('kpi_type', $kp, (isset($_POST['kpi_type']) ? $_POST['kpi_type'] : ""), 'id="kpi_type" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("kpi_type") . '"  class="form-control input-tip select" style="width:100%;"');
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
	$td_line_height = $font_size + 15;
	$min_height = $font_size * 6; 
	$margin = $font_size - 5;
	$margin_signature = $font_size * 2;
	$employee_tables = "";
	$questions = false;
	if($employees){
		if($kpi_questions){
			foreach($kpi_questions as $kpi_question){
				$questions[$kpi_question->type][$kpi_question->kpi_type][] = $kpi_question;
			}
		}
		
		$i = 1;
		foreach($employees as $employee){
			if($a4 || ($i % 2) == 0){
				$page_break = "page-break-after: always !important";
			}else{
				$page_break = "";
			}
			
			$g_tbody = "";
			$e_tbody = "";
			if(isset($questions[0][$employee->kpi_type])){
				$u = 1 ;
				foreach($questions[0][$employee->kpi_type] as $question){
					$g_tbody .= "<tr>
									<td class='text_center'>".$u++."</td>
									<td class='text_left'>".$this->bpas->remove_tag($question->question_kh)."<br>".$this->bpas->remove_tag($question->question)."</td>
									<td></td>
									<td></td>
									<td></td>
								</tr>";
				}
			}
			if(isset($questions[1][$employee->kpi_type])){
				$u = 1 ;
				foreach($questions[1][$employee->kpi_type] as $question){
					$e_tbody .= "<tr>
									<td class='text_center'>".$u++."</td>
									<td colspan='4' class='text_left'>
										".$this->bpas->remove_tag($question->question_kh)." - ".$this->bpas->remove_tag($question->question)."
										<br>ចម្លើយ៖<br> &nbsp;
									</td>
								</tr>";
				}
			}
			
			$employee_tables .= "<div style='".$page_break."' class='modal-dialog modal-lg'>
									<table>
										<tbody>
											<tr>
												<th>
													<table>
														<tr>
															<td class='text_center' style='width:20%'>
																<img src='".base_url()."assets/uploads/logos/" .$employee->logo."' alt='".$employee->name."'>'
															</td>
															<td class='text_center' style='width:60%'>
																<div style='font-size:".($font_size+15)."px'><b>".$employee->name."</b></div>
																<div>".$employee->address.$employee->city."</div>
																<div>".lang('tel').' : '. $employee->phone."</div>	
																<div>".lang('email').' : '. $employee->email."</div>	
															</td>
															<td class='text_center' style='width:20%'>
																".$this->bpas->qrcode('link', urlencode(admin_url('hr/modal_view_salary/' . $employee->id)), 2)."
															</td>
														</tr>
													</table>
												</th>
											</tr>
											<tr>
												<th>
													<table>
														<tr>
															<td valign='bottom' style='width:45%'><hr class='hr_title'></td>
															<td class='text_center' style='width:15%'><span style='font-size:".($font_size+5)."px'><b><i>".lang('kpi')."</i></b></span></td>
															<td valign='bottom' style='width:40%'><hr class='hr_title'></td>
														</tr>
													</table>
												</th>
											</tr>
											<tr>
												<th>
													<table>
														<tr>
															<td style='width:60%'>
																<fieldset>
																	<legend style='font-size:".$font_size."px'><b><i>".lang('employee')."</i></b></legend>
																	<table style='width:100%'>
																		<tr>
																			<td class='text_left' style='width:15%'>".lang('code')."</td>
																			<td class='text_left'> : ".$employee->empcode."</td>
																		</tr>
																		<tr>
																			<td class='text_left'>".lang('name')."</td>
																			<td class='text_left'> : ".$employee->lastname.' '.$employee->firstname."</td>
																		</tr>
																		<tr>
																			<td class='text_left'>".lang('position')."</td>
																			<td class='text_left'> : ".$employee->position."</td>
																		</tr>
																		<tr>
																			<td class='text_left'>".lang('department')."</td>
																			<td class='text_left'> : ".$employee->department."</td>
																		</tr>
																		<tr>
																			<td class='text_left'>".lang('group')."</td>
																			<td class='text_left'> : ".$employee->group."</td>
																		</tr>
																	</table>
																</fieldset>
															</td>
															<td style='width:0%'>
																<fieldset style='margin-left:5px !important'>
																	<legend style='font-size:".$font_size."px'><b><i>".lang('information')."</i></b></legend>
																	<table style='width:100%'>
																		<tr>
																			<td class='text_left' style='width:15%'>".lang('month')."</td>
																			<td class='text_left'> : ".(isset($_POST['month']) ? $_POST['month'] : "")." </td>
																		</tr>
																	</table>
																</fieldset>
															</td>
														</tr>
													</table>
												</th>
											</tr>
											<tr>
												<th>
													<table>
														<tr>
															<th colspan='4'>
																* ព័ត៌មានផ្ទាល់ខ្លួន (ត្រូវបំពេញដោយផ្នែកធនធានមនុស្ស)៖
															</th>
														</tr>
														<tr>
															<th style='padding-left:30px'>មកធ្វើការយឺត</th>
															<th>: ".$employee->late." ម៉ោង</th>
															<th>អវត្តមាន</th>
															<th>: ".$employee->absent." ថ្ងៃ</th>
														</tr>
														<tr>
															<th style='padding-left:30px'>ការពិន័យ</th>
															<th>: ...........ដង</th>
															<th>មកធ្វើការគ្មានប្រាក់ឈ្នួល</th>
															<th>: ...........ថ្ងៃ</th>
														</tr>
														<tr>
															<th colspan='4'>
																* ពិន្ទុនៃការវាយតម្លៃការងារ ត្រូវបានបែងចែកដូចខាងក្រោម៖
															</th>
															
														</tr>
														<tr>
															<th style='padding-left:30px' colspan='4'>
																ពិន្ទុ ១ ដល់ ២ : ការអនុវន្តការងារនៅក្រោមស្តង់ដារ ត្រូវខិតខំបន្ថែមទៀត។
															</th>
														</tr>
														<tr>
															<th style='padding-left:30px' colspan='4'>
																ពិន្ទុ ៣ ដល់ ៦ : ការអនុវន្តការងារអាចទទួលយកបាន សមនឹងតួនាទី។
															</th>
														</tr>
														<tr>
															<th style='padding-left:30px' colspan='4'>
																ពិន្ទុ ៧ ដល់ ៨ : ការអនុវន្តការងារល្អមានស្តង់ដារ។
															</th>
														</tr>
														<tr>
															<th style='padding-left:30px' colspan='4'>
																ពិន្ទុ ៩ ដល់ ១០ : ការអនុវន្តការងារហ្មត់ចត់ ល្អឥតខ្ចោះ ឆ្នើមដូចអ្វីដែលរំពឹងទុកក្នុងមុខតំណែង។
															</th>
														</tr>
													</table>
													
												</th>
											</tr>
											<tr>
												<td>
													<table class='table_item' style='margin-top:10px'>
														<thead>
															<tr>
																<th style='width:3%'>".lang('ល.រ')."</th>
																<th style='width:57%'>".lang('ការវិនិច្ឆ័យ និងវាយតម្លៃការងារ')."</th>
																<th style='width:10%'>".lang('ពិន្ទុ')." <br> (".lang('សម្រាប់បុគ្គលិក').")</th>
																<th style='width:10%'>".lang('ពិន្ទុ')." <br> (".lang('សម្រាប់ប្រធាន').")</th>
																<th style='width:20%'>".lang('មតិយោបល់បន្ថែម')."</th>
															</tr>
														</thead>
														<tbody id='g_tbody'>
															".$g_tbody."
														</tbody>
														<thead>
															<tr>
																<th>".lang('ល.រ')."</th>
																<th colspan='4'>".lang('សំនួរ (សម្រាប់បុគ្គលិក)')."</th>
															</tr>
														</thead>
														<tbody id='e_tbody'>
															".$e_tbody."
														</tbody>
													</table>
												</td>
											</tr>
											<tr>
												<th style='padding-top:10px'>* យោបល់៖
													<table class='table_item' style='margin-top:10px'>
														<thead>
															<tr>
																<th style='width:50%'>".lang('ចំណុចខ្លាំង')."</th>
																<th style='width:50%'>".lang('ចំណុចត្រូវកែតម្រូវ')."</th>
															</tr>
															<tr>
																<td style='line-height:100px !important'>&nbsp;</td>
																<td style='line-height:100px !important'>&nbsp;</td>
															</tr>
															<tr>
																<th style='width:50%'>".lang('យោបល់សមុីខ្លួន')."</th>
																<th style='width:50%'>".lang('យោបល់គណៈគ្រប់គ្រង')."</th>
															</tr>
															<tr>
																<td style='line-height:100px !important'>&nbsp;</td>
																<td style='line-height:100px !important'>&nbsp;</td>
															</tr>
														</thead>
													</table>
												</th>
											</tr>
											<tr>
												<th style='padding-top:10px'>
													<table class='table_item' style='margin-top:10px'>
														<thead>
															<tr>
																<th>".lang('ការកំណត់គោលដៅសម្រាប់ឆ្នាំថ្មី')."</th>
															</tr>
															<tr>
																<td style='line-height:100px !important'>&nbsp;</td>
															</tr>
														</thead>
													</table>
												</th>
											</tr>
											<tr>
												<th style='padding-top:10px'>* លទ្ធផល៖
													<table style='margin-top:10px'>
														<tr>
															<td class='text_left' style='width:33.33%;'><div style='float:left; margin-right:5px; border:1px solid black; width:17px; height:17px;'></div> ជាប់ការងារសាកល្បង</td>
															<td class='text_left' style='width:33.34%'><div style='float:left; margin-right:5px; border:1px solid black; width:17px; height:17px;'></div> បរាជ័យការងារសាកល្បង</td>
															<td class='text_left' style='width:33.33%'><div style='float:left; margin-right:5px; border:1px solid black; width:17px; height:17px;'></div> ពន្យាពេលការងារសាកល្បង..............ខែ</td>
														</tr>
														<tr>
															<td colspan='3'>&nbsp;</td>
														</tr>
														<tr>
															<td class='text_left' style='width:33.33%'><div style='float:left; margin-right:5px; border:1px solid black; width:17px; height:17px;'></div> វាយតម្លៃប្រចាំឆ្នាំ</td>
															<td class='text_left' style='width:33.34%'><div style='float:left; margin-right:5px; border:1px solid black; width:17px; height:17px;'></div> តម្លើងឋានៈ</td>
															<td class='text_left' style='width:33.33%'><div style='float:left; margin-right:5px; border:1px solid black; width:17px; height:17px;'></div> អនុសាសន៏តម្លើងប្រាក់ខែ.....................</td>
														</tr>
														<tr>
															<td style='font-weight: normal !important; padding-top:10px' colspan='3'>តម្លើងឋានៈពីតួនាទី........................................................កម្រិត........................................................ផ្នែក........................................................<td>
														</tr>
														<tr>
															<td style='font-weight: normal !important; padding-top:10px' colspan='3'>ទៅតួនាទី.......................................................................កម្រិត........................................................ផ្នែក........................................................<td>
														</tr>
														<tr>
															<td style='padding-top:10px' colspan='3' class='text_left' style='width:33.33%;'><div style='float:left; margin-right:5px; border:1px solid black; width:17px; height:17px;'></div> កំណត់តម្លើងប្រាក់បៀវត្សរ៏ដោយ ផ្នែកធនធានមនុស្ស៖</td>
														</tr>
														<tr>
															<td colspan='3' style='font-weight: normal !important; padding-top:10px; padding-left:24px !important'>ការផ្ដល់អនុសាសន៏តម្លើងប្រាក់បៀវត្សរ៏ពី..............................................................ទៅ............................................................ក្នុងមួយខែ។</td>
														</tr>
														<tr>
															<td style='padding-top:10px' colspan='3' class='text_left' style='width:33.33%;'><div style='float:left; margin-right:5px; border:1px solid black; width:17px; height:17px;'></div> កំណត់តម្លើងប្រាក់បៀវត្សរ៏ដោយ អគ្គនាយកក្រុមហ៑ុន៖</td>
														</tr>
														<tr>
															<td colspan='3' style='font-weight: normal !important; padding-top:10px; padding-left:24px !important'>ការផ្ដល់អនុសាសន៏តម្លើងប្រាក់បៀវត្សរ៏ពី..............................................................ទៅ............................................................ក្នុងមួយខែ។</td>
														</tr>
														<tr>
															<td colspan='3' style='font-weight: normal !important; padding-top:10px; padding-left:5px'>&#10003; មានប្រសិទ្ធភាព / Effective Date : .....................................</td>
														</tr>
													</table>
												</th>
											</tr>
											<tr>
												<td style='padding-top:20px'>
													<table style='width:100%'>
														<tr>
															<th ​style='text-align:left !important; width:50%' >ទទួលស្គាល់ដោយ / Acknowledged By : ..........................................</th>
															<th style='text-align:left !important; width:50%' >វាយតម្លៃដោយ / Appraiser By : ..........................................................</th>
														</tr>
														<tr>
															<td ​style='font-weight: normal !important; text-align:left !important'><br>ហត្ថលេខាសាមុីខ្លួន ឈ្មោះ និងកាលបរិច្ឆេទ : ............./............./............</td>
															<td ​style='font-weight: normal !important; text-align:left !important'><br>ហត្ថលេខាគណៈគ្រប់គ្រង ឈ្មោះ និងកាលបរិច្ឆេទ : ........./........../..........</td>
														</tr>
													</table>
												</td>
											</tr>
											<tr>
												<td style='line-height:100px; !important'>&nbsp;</td>
											</tr>
											<tr>
												<td style='padding-top:20px'>
													<table style='width:100%'>
														<tr>
															<th ​style='text-align:left !important; width:50%' >បានឃើញ និងឯកភាពដោយ / Verified By : ........................................</th>
															<th style='text-align:left !important; width:50%'>បានឃើញ និងអនុម័តដោយ /  Approved By : .......................................</th>
														</tr>
														<tr>
															<td ​style='font-weight: normal !important; text-align:left !important'><br>ហត្ថលេខាផ្នែកធនធានមនុស្ស និងកាលបរិច្ឆេទ : ........./........../.............</td>
															<td ​style='text-align:left !important'><span style='font-size:14px !important; font-family: Khmer OS Muol Light !important;'><br>អគ្គនាយកក្រុមហ៑ុន</span> (Managing Director)</td>
														</tr>
														<tr>
															<td colspan='2' style='text-align:right !important'>កាលបរិច្ឆេទ : ............/............./...............</td>
														</tr>
													</table>
												</td>
											</tr>
											<tr>
												<td style='line-height:50px; !important'>&nbsp;</td>
											</tr>
										</tbody>
									</table>
								</div>";
			$i++;					
		}
	}
	if($employee_tables){
		echo "<div class='box_employee'>".$employee_tables."</div>";
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
		border:3px solid; #7daaf2 !important;
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
			var result = "data:application/vnd.ms-excel," + encodeURIComponent( '<meta charset="UTF-8"><style> table { white-space:wrap; } table th, table td{ font-size:10px !important; }</style>' + $('.box_employee').html());
			this.href = result;
			this.download = "employee.xls";
			return true;			
		});
		$(document).on("change", "#biller", function () {	
			var biller_id = $(this).val();
			$.ajax({
				type: "get", 
				async: true,
				url: site.base_url + "hr/get_departments/",
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
				url: site.base_url + "hr/get_positions/",
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
				url: site.base_url + "hr/get_groups/",
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



