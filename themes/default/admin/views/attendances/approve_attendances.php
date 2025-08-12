<?php defined('BASEPATH') OR exit('No direct script access allowed'); 

$v = "";

if ($this->input->post('biller')) {
    $v .= "&biller=" . $this->input->post('biller');
}
if ($this->input->post('department')) {
    $v .= "&department=" . $this->input->post('department');
}
if ($this->input->post('position')) {
    $v .= "&position=" . $this->input->post('position');
}
if ($this->input->post('group')) {
    $v .= "&group=" . $this->input->post('group');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}

?>
<script>
    $(document).ready(function () {
		
		function working_input(x){
			return '<input type="text" disabled min="0" value="'+x+'" name="working_day[]" class="working_day" style="width:100px; padding:3px; text-align:center;" />';
		}
		function present_input(x){
			return '<input type="text" disabled min="0" value="'+x+'" name="present[]" class="present" style="width:100px; padding:3px; text-align:center;" />';
		}
		function permission_input(x){
			return '<input type="text" disabled min="0" value="'+x+'" name="permission[]" class="permission" style="width:100px; padding:3px; text-align:center;" />';
		}
		function absent_input(x){
			return '<input type="text" disabled min="0" value="'+x+'" name="absent[]" class="absent" style="width:100px; padding:3px; text-align:center;" />';
		}
		function late_input(x){
			return '<input type="text" disabled min="0" value="'+secToHour(x)+'" class="late" style="width:100px; padding:3px; text-align:center;" name="late[]" />';
		}
		function leave_input(x){
			return '<input type="text" disabled min="0" value="'+secToHour(x)+'" class="leave_early" style="width:100px; padding:3px; text-align:center;" name="leave_early[]" />';
		}
		function normal_ot_input(x){
			return '<input type="text" disabled min="0" value="'+secToHour(x)+'" class="normal_ot" style="width:100px; padding:3px; text-align:center;" name="normal_ot[]" />';
		}
		function weekend_ot_input(x){
			return '<input type="text" disabled min="0" value="'+secToHour(x)+'" class="weekend_ot" style="width:100px; padding:3px; text-align:center;" name="weekend_ot[]" />';
		}
		function holiday_ot_input(x){
			return '<input type="text" disabled min="0" value="'+secToHour(x)+'" class="holiday_ot" style="width:100px; padding:3px; text-align:center;" name="holiday_ot[]" />';
		}
		
        'use strict';
        var oTable = $('#ATTable').dataTable({
            "aaSorting": [[1, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('attendances/getApproveAttendances?v=1'.$v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
			{
                "bSortable": false,
                "mRender": checkbox,
            }, 
			{"sClass" : "left","sClass":"center"}, 
			{"sClass" : "left"}, 
			{"sClass" : "left"},
			{"sClass" : "left"},
			{"sClass" : "left", "bSortable" : false, "sClass":"center" , "mRender" : working_input},
			{"sClass" : "left", "bSortable" : false, "sClass":"center" , "mRender" : present_input},
			{"sClass" : "left", "bSortable" : false, "sClass":"center" , "mRender" : permission_input},
			{"sClass" : "left", "bSortable" : false, "sClass":"center" , "mRender" : absent_input},
			{"sClass" : "left", "bSortable" : false, "sClass":"center" , "mRender" : late_input},
			{"sClass" : "left", "bSortable" : false, "sClass":"center" , "mRender" : leave_input},
			{"sClass" : "left", "bSortable" : false, "sClass":"center" , "mRender" : normal_ot_input},
			{"sClass" : "left", "bSortable" : false, "sClass":"center" , "mRender" : weekend_ot_input},
			{"sClass" : "left", "bSortable" : false, "sClass":"center" , "mRender" : holiday_ot_input}]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?= lang('code') ?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?= lang('name') ?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?= lang('position') ?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?= lang('department') ?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?= lang('working_day') ?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?= lang('present') ?>]", filter_type: "text", data: []},
			{column_number: 7, filter_default_label: "[<?= lang('permission') ?>]", filter_type: "text", data: []},
            {column_number: 8, filter_default_label: "[<?= lang('absent') ?>]", filter_type: "text", data: []},
			{column_number: 9, filter_default_label: "[<?= lang('late') ?>]", filter_type: "text", data: []},
			{column_number: 10,filter_default_label: "[<?= lang('leave_early') ?>]", filter_type: "text", data: []},
			{column_number: 11,filter_default_label: "[<?= lang('normal_ot') ?>]", filter_type: "text", data: []},
			{column_number: 12,filter_default_label: "[<?= lang('weekend_ot') ?>]", filter_type: "text", data: []},
			{column_number: 13,filter_default_label: "[<?= lang('holiday_ot') ?>]", filter_type: "text", data: []},
        ], "footer");
		
		function presentCal(){
			$('.working_day').each(function(){
				var parent = $(this).parent().parent();
				var working = $(this).val()-0;
				var present = parent.find('.present').val()-0;
				var permission = parent.find('.permission').val()-0;
				var absent = parent.find('.absent').val()-0;
				var absent_balance = working - (present+permission);

				if(absent_balance > 0){
					parent.find('.absent').val(absent_balance);
				}else{
					parent.find('.absent').val(0);
					var permission_balance = working - present;
					if(permission_balance > 0){
						parent.find('.permission').val(permission_balance);
					}else{
						parent.find('.permission').val(0);
					}
				}
			});
		}
		
		function permissionCal(){
			$('.working_day').each(function(){
				var parent = $(this).parent().parent();
				var working = $(this).val()-0;
				var present = parent.find('.present').val()-0;
				var permission = parent.find('.permission').val()-0;
				var absent = parent.find('.absent').val()-0;
				var absent_balance = working - (present+permission);

				if(absent_balance > 0){
					parent.find('.absent').val(absent_balance);
				}else{
					parent.find('.absent').val(0);
					var present_balance = working - permission;
					if(present_balance > 0){
						parent.find('.present').val(present_balance);
					}else{
						parent.find('.present').val(0);
					}
				}
			});
		}
		
		function absentCal(){
			$('.working_day').each(function(){
				var parent = $(this).parent().parent();
				var working = $(this).val()-0;
				var present = parent.find('.present').val()-0;
				var permission = parent.find('.permission').val()-0;
				var absent = parent.find('.absent').val()-0;
				var present_balance = working - (absent+permission);

				if(present_balance > 0){
					parent.find('.present').val(present_balance);
				}else{
					parent.find('.present').val(0);
					var permission_balance = working - absent;
					if(permission_balance > 0){
						parent.find('.permission').val(permission_balance);
					}else{
						parent.find('.permission').val(0);
					}
				}
			});
		}
		
		$('#add_all_present').click(function(){
			$('.working_day').each(function(){
				var parent = $(this).parent().parent();
				var check_employee = parent.find('.multi-select');
				if ((check_employee).is(':checked')) {
					var working = $(this).val()-0;
					var present = parent.find('.present').val()-0;
					var new_present = present + 1;
					if(new_present > working){
						parent.find('.present').val(working);
					}else{
						parent.find('.present').val(new_present);
					}
				}
				
			});
			presentCal();
		});
		
		$('#sub_all_present').click(function(){
			$('.working_day').each(function(){
				var parent = $(this).parent().parent();
				var check_employee = parent.find('.multi-select');
				if ((check_employee).is(':checked')) {
					var present = parent.find('.present').val()-0;
					var new_present = present - 1;
					if(new_present > 0){
						parent.find('.present').val(new_present);
					}else{
						parent.find('.present').val(0);
					}
				}
				
			});
			presentCal();
		});
		
		$('#add_all_permission').click(function(){
			$('.working_day').each(function(){
				var parent = $(this).parent().parent();
				var check_employee = parent.find('.multi-select');
				if ((check_employee).is(':checked')) {
					var working = $(this).val()-0;
					var permission = parent.find('.permission').val()-0;
					var new_permission = permission + 1;
					if(new_permission > working){
						parent.find('.permission').val(working);
					}else{
						parent.find('.permission').val(new_permission);
					}
				}
			});
			permissionCal();
		});
		
		$('#sub_all_permission').click(function(){
			$('.working_day').each(function(){
				var parent = $(this).parent().parent();
				var check_employee = parent.find('.multi-select');
				if ((check_employee).is(':checked')) {
					var permission = parent.find('.permission').val()-0;
					var new_permission = permission - 1;
					if(new_permission > 0){
						parent.find('.permission').val(new_permission);
					}else{
						parent.find('.permission').val(0);
					}
				}
				
			});
			permissionCal();
		});
		
		$('#add_all_absent').click(function(){
			$('.working_day').each(function(){
				var parent = $(this).parent().parent();
				var check_employee = parent.find('.multi-select');
				if ((check_employee).is(':checked')) {
					var working = $(this).val()-0;
					var absent = parent.find('.absent').val()-0;
					var new_absent = absent + 1;
					if(new_absent > working){
						parent.find('.absent').val(working);
					}else{
						parent.find('.absent').val(new_absent);
					}
				}
			});
			absentCal();
		});
		
		$('#sub_all_absent').click(function(){
			$('.working_day').each(function(){
				var parent = $(this).parent().parent();
				var check_employee = parent.find('.multi-select');
				if ((check_employee).is(':checked')) {
					var absent = parent.find('.absent').val()-0;
					var new_absentn = absent - 1;
					if(new_absentn > 0){
						parent.find('.absent').val(new_absentn);
					}else{
						parent.find('.absent').val(0);
					}
				}
				
			});
			absentCal();
		});
		
		$('.present').live('change',function(){
			var parent = $(this).parent().parent();
			var working = parent.find('.working_day').val()-0;
			var present = $(this).val()-0;
			if(present > 0){
				if(present > working){
					var new_present = working;
				}else{
					var new_present = present;
				}
			}else{
				var new_present = 0;
			}
			$(this).val(new_present);
			
		
			var present = new_present;
			var permission = parent.find('.permission').val()-0;
			var absent = parent.find('.absent').val()-0;
			var absent_balance = working - (present+permission);
			if(absent_balance > 0){
				parent.find('.absent').val(absent_balance);
			}else{
				parent.find('.absent').val(0);
				var permission_balance = working - present;
				if(permission_balance > 0){
					parent.find('.permission').val(permission_balance);
				}else{
					parent.find('.permission').val(0);
				}
			}
			
		});
		
		$('.permission').live('change',function(){
			var parent = $(this).parent().parent();
			var working = parent.find('.working_day').val()-0;
			var permission = $(this).val()-0;
			if(permission > 0){
				if(permission > working){
					var new_permission = working;
				}else{
					var new_permission = permission;
				}
			}else{
				var new_permission = 0;
			}
			$(this).val(new_permission);
			
			var present = parent.find('.present').val()-0;
			var permission = new_permission;
			var absent = parent.find('.absent').val()-0;
			var absent_balance = working - (present+permission);

			if(absent_balance > 0){
				parent.find('.absent').val(absent_balance);
			}else{
				parent.find('.absent').val(0);
				var present_balance = working - permission;
				if(present_balance > 0){
					parent.find('.present').val(present_balance);
				}else{
					parent.find('.present').val(0);
				}
			}
			
		});
		
		$('.absent').live('change',function(){
			var parent = $(this).parent().parent();
			var working = parent.find('.working_day').val()-0;
			var absent = $(this).val()-0;
			if(absent > 0){
				if(absent > working){
					var new_absent = working;
				}else{
					var new_absent = absent;
				}
			}else{
				var new_absent = 0;
			}
			$(this).val(new_absent);
			
			var present = parent.find('.present').val()-0;
			var permission = parent.find('.permission').val()-0;
			var absent = new_absent;
			var present_balance = working - (absent+permission);
			if(present_balance > 0){
				parent.find('.present').val(present_balance);
			}else{
				parent.find('.present').val(0);
				var permission_balance = working - absent;
				if(permission_balance > 0){
					parent.find('.permission').val(permission_balance);
				}else{
					parent.find('.permission').val(0);
				}
			}
		});
		
		$('.multi-select').live('ifChecked',function(){
			var parent =  $(this).closest('tr');
			parent.find('.present').prop("disabled", false);
			parent.find('.permission').prop("disabled", false);
			parent.find('.absent').prop("disabled", false);
			parent.find('.late').prop("disabled", false);
			parent.find('.leave_early').prop("disabled", false);
			parent.find('.normal_ot').prop("disabled", false);
			parent.find('.weekend_ot').prop("disabled", false);
			parent.find('.holiday_ot').prop("disabled", false);
			parent.find('.employee_id').prop("disabled", false);
			parent.find('.working_day').prop("disabled", false);
		});
		
		$('.multi-select').live('ifUnchecked',function(){
			var parent =  $(this).closest('tr');
			parent.find('.present').prop("disabled", true);
			parent.find('.permission').prop("disabled", true);
			parent.find('.absent').prop("disabled", true);
			parent.find('.late').prop("disabled", true);
			parent.find('.leave_early').prop("disabled", true);
			parent.find('.normal_ot').prop("disabled", true);
			parent.find('.weekend_ot').prop("disabled", true);
			parent.find('.holiday_ot').prop("disabled", true);
			parent.find('.employee_id').prop("disabled", true);
			parent.find('.working_day').prop("disabled", true);
		});
		
    });
</script>
<div class="breadcrumb-header">
        <h2 class="#428BCA"><i class="fa-fw fa fa-calendar"></i><?= lang('approve_attendances'); ?></h2>
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
    </div>
<div class="box">
    
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
				
				<?php echo admin_form_open("attendances/approve_attendances"); ?>
                
				<p class="introtext"><?= lang('list_results'); ?></p>
				
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
						<div class="col-lg-3">
							<div class="form-group">
								<?php echo lang('policy', 'policy_id'); ?>
								<div class="controls policy">
									<?php
									$policy_opt = array(lang('select')." ".lang('policy'));
									if($policies){
										foreach($policies as $policy){
											$policy_opt[$policy->id] = $policy->policy;
										}	
									}
									echo form_dropdown('policy_id', $policy_opt,(isset($_POST['policy_id']) ? $_POST['policy_id'] :''), 'id="policy_id" class="form-control policy"');
									?>
								</div>
							</div>
						</div>
                        
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("month", "month"); ?>
                                <?php echo form_input('approve_month', (isset($_POST['approve_month']) ? $_POST['approve_month'] : date("m/Y")), 'class="form-control month" '); ?>
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
						<?php if($Settings->project == 1){ ?>
							<div class="col-md-3">
								<div class="form-group">
									<?= lang("project", "project"); ?>
									<?php
									$pj[''] = lang('select');
									if($projects){
										foreach ($projects as $project) {
											$pj[$project->project_id] = $project->project_name;
										}
									}
									echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] :''), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
									?>
								</div>
							</div>
						<?php } ?>
					</div>
					
					<div class="form-group">
                        <div class="controls" style="float:left"> 
							<?php echo form_submit('submit_report', $this->lang->line("search"), 'class="btn btn-primary"'); ?> 
							<?php echo form_submit('approve', $this->lang->line("approve"), 'class="btn btn-success" id="approve_attendance"'); ?>
						</div>
						<div style="clear:both"></div>
                    </div>
					
				</div>
				
                <div class="table-responsive">
                    <table id="ATTable" class="table table-bordered table-striped table-hover table-condensed accountings-table dataTable">
                        <thead>
							<tr>
								<th rowspan="2" style="min-width:30px; width: 30px; text-align: center;">
									<input class="checkbox checkth" type="checkbox" name="check"/>
								</th>
								<th rowspan="2"><?= lang("code") ?></th>
								<th rowspan="2"><?= lang("name") ?></th>
								<th rowspan="2"><?= lang("position") ?></th>
								<th rowspan="2"><?= lang("department") ?></th>
								<th colspan="4"><?= lang("day") ?></th>
								<th colspan="5"><?= lang("hour") ?></th>
							</tr>	
							<tr style="background:#428BCA; color:#FFFFFF; text-align:center; font-weight:bold;">
								<th style="border-bottom:1px solid #3B84C3; border-right:1px solid #3B84C3;"><?= lang("working_day") ?></th>
								<th style="border-bottom:1px solid #3B84C3; border-right:1px solid #3B84C3;">
									<input type="button" class="btn btn-xs btn-success" id="add_all_present" style="width:20px" value="+"> 
									<?= lang("present") ?> 
									<input type="button" class="btn btn-xs btn-danger" id="sub_all_present" style="width:20px" value="-">
								</th>
								<th style="border-bottom:1px solid #3B84C3; border-right:1px solid #3B84C3;">
									<input type="button" class="btn btn-xs btn-success" id="add_all_permission" style="width:20px" value="+"> 
									<?= lang("permission") ?> 
									<input type="button" class="btn btn-xs btn-danger"  id="sub_all_permission" style="width:20px" value="-">
								</th>
								<th style="border-bottom:1px solid #3B84C3; border-right:1px solid #3B84C3;">
									<input type="button" class="btn btn-xs btn-success" id="add_all_absent" style="width:20px" value="+"> 
									<?= lang("absent") ?> 
									<input type="button" class="btn btn-xs btn-danger"  id="sub_all_absent" style="width:20px" value="-">
								</th>
								<th style="border-bottom:1px solid #3B84C3; border-right:1px solid #3B84C3;"><?= lang("late") ?></th>
								<th style="border-bottom:1px solid #3B84C3; border-right:1px solid #3B84C3;"><?= lang("leave_early") ?></th>
								<th style="border-bottom:1px solid #3B84C3; border-right:1px solid #3B84C3;"><?= lang("normal_ot") ?></th>
								<th style="border-bottom:1px solid #3B84C3; border-right:1px solid #3B84C3;"><?= lang("weekend_ot") ?></th>
								<th style="border-bottom:1px solid #3B84C3; border-right:1px solid #3B84C3;"><?= lang("holiday_ot") ?></th>
							</tr>
                        </thead>
						<tbody></tbody>
						<tfoot>
							<tr>
								<th><input class="checkbox checkth" type="checkbox" name="check"/></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
							</tr>
						</tfoot>
                    </table>
                </div>
				<?php echo form_close(); ?>
            </div>
			
        </div>
    </div>
</div>

<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
		
		$("#form").slideDown();
		
		$('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
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
			$.ajax({
				type: "get", 
				async: true,
				url: site.base_url + "projects/get_projects/",
				data : { biller_id : biller },
				dataType: "json",
				success: function (data) {
					var project_sel = "<select class='form-control' id='poproject' name='project'><option value=''><?= lang('select').' '.lang('project') ?></option>";
					if (data != false) {
						$.each(data, function () {
							project_sel += "<option value='"+this.project_id+"'>"+this.project_name+"</option>";
						});
						
					}
					project_sel += "</select>"
					$(".project_box").html(project_sel);
					$('select').select2();
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
