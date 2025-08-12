<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-calendar"></i><?= lang('attendance_department_report') ?></h2>
		
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
                <p class="introtext"><?= lang("attendance_department_report") ?></p>
				<?php echo admin_form_open("attendances/attendance_department_report", ' id="form-submit" '); ?>
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
                                <?= lang("date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : date("d/m/Y")), 'class="form-control date" id="start_date"'); ?>
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
				<?php
					if(isset($_POST['start_date'])){
						$date = $this->bpas->fsd($this->input->post('start_date'));
					}else{
						$date = date('Y-m-d');
					}
				
				
					if($groups){
						foreach($groups as $group){
							$array_group[$group->department_id][] = $group;
						}
					}
					//TODO Check below loop again
					$working_department = (array) null;
					$present_department = (array) null;
					$permission_department = (array) null;
					$absent_department = (array) null;
					$attendace_group = (array) null;

					if($attendances){
						foreach($attendances as $attendance){
							if (!array_key_exists($attendance->department_id, $working_department)) {
								$working_department[$attendance->department_id] = $attendance->working_day;
							}else{
								$working_department[$attendance->department_id] = $working_department[$attendance->department_id] + $attendance->working_day;
							}
							
							if (!array_key_exists($attendance->department_id, $present_department)) {
								$present_department[$attendance->department_id] = $attendance->present;
							}else{
								$present_department[$attendance->department_id] = $present_department[$attendance->department_id] + $attendance->present;
							}

							if (!array_key_exists($attendance->department_id, $permission_department)) {
								$permission_department[$attendance->department_id] = $attendance->permission;
							}else{
								$permission_department[$attendance->department_id] = $permission_department[$attendance->department_id] + $attendance->permission;
							}

							if (!array_key_exists($attendance->department_id, $absent_department)) {
								$absent_department[$attendance->department_id] = $attendance->absent;
							}else{
								$absent_department[$attendance->department_id] = $absent_department[$attendance->department_id] +$attendance->absent;
							}

							$attendace_group[$attendance->department_id][$attendance->group_id] = $attendance;
						}
					}

					$tbody = '';
					if($departments){
						foreach($departments as $department){
							if (array_key_exists($department->id, $working_department)) {
								$show_working_department = $working_department[$department->id];
							}else{
								$show_working_department = "";
							}

							if (array_key_exists($department->id, $present_department)) {
								$show_present_department = $present_department[$department->id];
							}else{
								$show_present_department = "";
							}

							if (array_key_exists($department->id, $permission_department)) {
								$show_permission_department = $permission_department[$department->id];
							}else{
								$show_permission_department = "";
							}

							if (array_key_exists($department->id, $absent_department)) {
								$show_absent_department = $absent_department[$department->id];
							}else{
								$show_absent_department = "";
							}

							$tbody .= '<tr class="department_time_card_link" id="'.$date.'/'.$department->id.'">
											<td style="font-weight:bold">'.$department->name.'</td>
											<td class="text-right" style="font-weight:bold">'.$show_working_department.'</td>
											<td class="text-right" style="font-weight:bold">'.$show_present_department.'</td>
											<td class="text-right" style="font-weight:bold">'.$show_permission_department.'</td>
											<td class="text-right" style="font-weight:bold">'.$show_absent_department.'</td>
										</tr>';
							if(isset($array_group[$department->id])){
								
								foreach($array_group[$department->id] as $group){

									if (array_key_exists($department->id, $attendace_group)) {
										$att_group_working = isset($attendace_group[$department->id][$group->id]->working_day)? $attendace_group[$department->id][$group->id]->working_day: 0;
										$att_group_present = isset($attendace_group[$department->id][$group->id]->present)? $attendace_group[$department->id][$group->id]->present: 0;
										$att_group_permission = isset($attendace_group[$department->id][$group->id]->permission)? $attendace_group[$department->id][$group->id]->permission: 0;
										$att_group_absent = isset($attendace_group[$department->id][$group->id]->absent)? $attendace_group[$department->id][$group->id]->absent: 0;
									}else{
										$att_group_working = "";
										$att_group_present = "";
										$att_group_permission = "";
										$att_group_absent = "";
									}

									$tbody .= '<tr class="department_time_card_link" id="'.$date.'/'.$department->id.'/'.$group->id.'">
													<td style="padding-left:3%">'.$group->name.'</td>
													<td class="text-right">'.$att_group_working.'</td>
													<td class="text-right">'.$att_group_present.'</td>
													<td class="text-right">'.$att_group_permission.'</td>
													<td class="text-right">'.$att_group_absent.'</td>
												</tr>';
								}
							}			
						}
					}else{
						$tbody = '<tr><td colspan="5" class="dataTables_empty">'.lang('sEmptyTable').'</td></tr>';
					}
				?>

				
                <div class="table-responsive">
                    <table border="1" class="table table-bordered table-striped dfTable reports-table">
                        <thead>
							<tr>
								<th><?= lang("department") ?> <i class="fa fa-angle-double-right" aria-hidden="true"></i> <?= lang("group") ?></th>
								<th><?= lang("working_day") ?></th>
								<th><?= lang("present") ?></th>
								<th><?= lang("permission") ?></th>
								<th><?= lang("absent") ?></th>
							</tr>
                        </thead>
                        <tbody>
							<?= $tbody ?>
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
			this.download = "attendance_department_report.xls";
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
