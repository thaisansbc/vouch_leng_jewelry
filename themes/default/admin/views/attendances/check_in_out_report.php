<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-calendar"></i><?= lang('check_in_out_report') ?></h2>
		
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
                <p class="introtext"><?= lang("check_in_out_report") ?></p>
				<?php echo admin_form_open("attendances/check_in_out_report", ' id="form-submit" '); ?>
				<div id="form">
					<div class="row">	
                        
						<div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
								<?php
                                $bl[""] = lang('all').' '.lang('biller');
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
						
						<div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="department"><?= lang("department"); ?></label>
								<?php
								$department_opt = array(lang('all')." ".lang('department'));
								echo form_dropdown('department', $department_opt, '', 'id="department" class="form-control"');
								?>
                            </div>
                        </div>
						
						<div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="group"><?= lang("group"); ?></label>
								<?php
								$group_opt = array(lang('all')." ".lang('group'));
								echo form_dropdown('group', $group_opt, '', 'id="group" class="form-control"');
								?>
                            </div>
                        </div>
						
						<div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="position"><?= lang("position"); ?></label>
								<?php
								$position_opt = array(lang('all')." ".lang('position'));
								echo form_dropdown('position', $position_opt, '', 'id="position" class="form-control"');
								?>
                            </div>
                        </div>
						
						<div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="group"><?= lang("employee"); ?></label>
								<input type="text" name="employee_id" id="suggest_employee" value="<?= set_value('employee_id') ?>" class="form-control ui-autocomplete-input" />
								<input type="hidden" name="employee" value="<?= set_value('employee') ?>" id="suggest_employee_id">
							</div>
                        </div>
						
						<div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : date("d/m/Y")), 'class="form-control date" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : date("d/m/Y")), 'class="form-control date" id="end_date"'); ?>
                            </div>
                        </div>
						
					</div>
					
					<div class="form-group">
                        <div class="controls" style="float:left"> 
							<?php echo form_submit('submit_report', $this->lang->line("search"), 'class="btn btn-primary"'); ?> 
						</div>
						<div style="clear:both"></div>
                    </div>
					
				</div>
				
                <div class="table-responsive">
                    <table border="1" class="table table-bordered table-striped dfTable reports-table">
                        <thead>
							<tr>
								<th><?= lang("code") ?></th>
								<th><?= lang("name") ?></th>
								<th><?= lang("position") ?></th>
								<th><?= lang("department") ?></th>
								<th><?= lang("group") ?></th>
								<th><?= lang("date") ?></th>
								<th><?= lang("check_in") ?></th>
								<th><?= lang("check_out") ?></th>
								<th><?= lang("check_in") ?></th>
								<th><?= lang("check_out") ?></th>
								<th><?= lang("coming_late") ?></th>
								<th><?= lang("leave_early") ?></th>
							</tr>
                        </thead>
                        <tbody>
							<?php
								$biller_id = isset($_POST['biller'])? $_POST['biller'] : '';
								$department_id = isset($_POST['department'])? $_POST['department'] : '';
								$group_id = isset($_POST['group'])? $_POST['group'] : '';
								$position_id = isset($_POST['position'])? $_POST['position'] : '';
								$employee_id = isset($_POST['employee'])? $_POST['employee'] : '';
								
								$start_date = isset($_POST['start_date'])? $this->bpas->fsd($_POST['start_date']) : '';
								$end_date = isset($_POST['end_date'])? $this->bpas->fsd($_POST['end_date']) : '';
								
								if(!isset($_POST['start_date'])){
									$start_date = date('Y-m-d');
								}
								if(!isset($_POST['end_date'])){
									$end_date = date('Y-m-d');
								}
								
								$employees = $this->attendances_model->getEmployeeWorkingInfo($employee_id,$biller_id,$position_id,$department_id,$group_id);
								
								$attendances 			= $this->attendances_model->getEmployeeAttedance($employee_id,$start_date,$end_date);
								$attedance_array 		= array();
								$attedance_late_early 	= array();
								if($attendances){
									foreach($attendances as $attendance){
										$attedance_date = date('Y-m-d',strtotime($attendance->check_time));
										$attedance_array[$attendance->employee_id][$attedance_date][$attendance->timeshift][$attendance->check_type] = $attendance->time_only;
										if($attendance->check_type=='in' && $attendance->after_time > 0){
											$attedance_late_early[$attendance->employee_id][$attedance_date]['late'] = $attedance_late_early[$attedance_date]['late'] + $attendance->after_time;
										}else if($attendance->check_type=='out' && $attendance->before_time > 0){
											$attedance_late_early[$attendance->employee_id][$attedance_date]['early'] = $attedance_late_early[$attedance_date]['early'] + $attendance->before_time;
										}
									}
								}
								$tbody = '';
								if(count($employees) > 0){
									$begin = new DateTime($start_date);
									$end   = new DateTime($end_date);
									for($i = $begin; $i <= $end; $i->modify('+1 day')){
										$date 		= $i->format("Y-m-d");
										foreach($employees as $employee){
										
											if($employee->status != 'inactive' || $employee->resigned_date > $date){
												if (array_key_exists($employee->employee_id, $attedance_array)) {
													$one_in 	= $attedance_array[$employee->employee_id][$date]['one']['in'];
													$one_out 	= $attedance_array[$employee->employee_id][$date]['one']['out'];
													$two_in 	= $attedance_array[$employee->employee_id][$date]['two']['in'];
													$two_out 	= $attedance_array[$employee->employee_id][$date]['two']['out'];
												}else{
													$one_in 	= '';
													$one_out 	= '';
													$two_in 	= '';
													$two_out 	= '';
												}

												if (array_key_exists($employee->employee_id, $attedance_late_early)) {
													$att_late 	= $this->bpas->secTotime($attedance_late_early[$employee->employee_id][$date]['late']);
													$att_early 	= $this->bpas->secTotime($attedance_late_early[$employee->employee_id][$date]['early']);
												}else{
													$att_late 	= '';
													$att_early 	= '';
												}

												$tbody .='<tr>
														<td>'.$employee->empcode.'</td>
														<td>'.$employee->firstname.' '.$employee->lastname.'</td>
														<td>'.$employee->position.'</td>
														<td>'.$employee->department.'</td>
														<td>'.$employee->group.'</td>
														<td class="text-center">'.$this->bpas->hrsd($date).'</td>
														<td class="text-center">'.$one_in.'</td>
														<td class="text-center">'.$one_out.'</td>
														<td class="text-center">'.$two_in.'</td>
														<td class="text-center">'.$two_out.'</td>
														<td class="text-center">'.$att_late.'</td>
														<td class="text-center">'.$att_early.'</td>
													</tr>';
											}
											
										}
									}
									
									
								}else{
									$tbody = '<tr><td colspan="12" class="dataTables_empty">'.lang('sEmptyTable').'</td></tr>';
								}
								echo $tbody; 
							?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
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
		
		$("#xls").click(function(e) {
			var result = "data:application/vnd.ms-excel," + encodeURIComponent( '<meta charset="UTF-8"><style> table { white-space:wrap; } table th, table td{ font-size:12px !important; border:1px solid black !important }</style>' + $('.table-responsive').html());
			this.href = result;
			this.download = "check_in_out_report.xls";
			return true;			
		});
		
		getDepPos();
		
		$('#biller').on('change',function(){
			getDepPos();
		});
		$('#department').on('change',function(){
			getGroup();
		});
		
		function getDepPos(){
			var biller = $("#biller").val();
			var department  = "<?= (isset($_POST['department'])?$_POST['department']:0) ?>";
			var position  = "<?= (isset($_POST['position'])?$_POST['position']:0) ?>";
			
			$.ajax({
				url : "<?= admin_url("hr/get_dep_pos") ?>",
				type : "GET",
				dataType : "JSON",
				data : { biller : biller, department : department, position : position},
				success : function(data){
					$("#department").html(data.department_opt);
					$("#department").select2();
					$("#position").html(data.position_opt);
					$("#position").select2();
					getGroup();
				}
			});
		}

		function getGroup(){
			var department = $("#department").val();
			var group  = "<?= (isset($_POST['group'])?$_POST['group']:0) ?>";
			$.ajax({
				url : "<?= admin_url("hr/get_group") ?>",
				type : "GET",
				dataType : "JSON",
				data : { department_id : department, group : group },
				success : function(data){
					$("#group").html(data.group_opt);
					$("#group").select2();
				}
			});
		}

    });
</script>
