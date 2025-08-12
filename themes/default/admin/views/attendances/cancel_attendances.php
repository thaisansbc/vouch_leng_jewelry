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
if ($this->input->post('employee')) {
    $v .= "&employee=" . $this->input->post('employee');
}
if ($this->input->post('approve_month')) {
    $v .= "&approve_month=" . $this->input->post('approve_month');
}
?>
<script>
    $(document).ready(function () {
        'use strict';
        var oTable = $('#ATTable').dataTable({
            "aaSorting": [[1, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('attendances/getCancelAttendances?v=1'.$v) ?>',
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
			{"sClass" : "left"},
			{"sClass" : "left","sClass":"center"},
			{"sClass" : "left","sClass":"center"},
			{"sClass" : "left","sClass":"center"},
			{"sClass" : "left","sClass":"center"},
			{"sClass" : "left","sClass":"center", "mRender" : secTotime},
			{"sClass" : "left","sClass":"center", "mRender" : secTotime},
			{"sClass" : "left","sClass":"center"},
			{"sClass" : "left","sClass":"center"},
			{"sClass" : "left","sClass":"center"}]
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
    });
</script>
    <div class="breadcrumb-header">
        <h2 class="#428BCA"><i class="fa-fw fa fa-calendar"></i><?= lang('cancel_attendances'); ?></h2>
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
				
				<?php echo admin_form_open("attendances/cancel_attendances", ' '); ?>
                
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
						
						<div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="group"><?= lang("employee"); ?></label>
								<input type="text" name="employee_id" id="suggest_employee" value="<?= set_value('employee_id') ?>" class="form-control ui-autocomplete-input" />
								<input type="hidden" name="employee" value="<?= set_value('employee') ?>" id="suggest_employee_id">
							</div>
                        </div>
						
						<div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("month", "month"); ?>
                                <?php echo form_input('approve_month', (isset($_POST['approve_month']) ? $_POST['approve_month'] : date("m/Y")), 'class="form-control month" '); ?>
                            </div>
                        </div>
						
					</div>
					
					<div class="form-group">
                        <div class="controls"> 
							<?php echo form_submit('search', $this->lang->line("search"), 'class="btn btn-primary"'); ?>
							<?php echo form_submit('cancel', $this->lang->line("cancel"), 'class="btn btn-danger"'); ?>
						</div>
                    </div>
					
				</div>
				
                <div class="table-responsive">
                    <table id="ATTable" class="table table-bordered table-striped table-hover table-condensed accountings-table dataTable">
                        <thead>
							<th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
							<th width="100"><?= lang("code") ?></th>
							<th><?= lang("name") ?></th>
							<th><?= lang("position") ?></th>
							<th><?= lang("department") ?></th>
							<th><?= lang("project") ?></th>
							<th><?= lang("working_day") ?></th>
							<th><?= lang("present") ?></th>
							<th><?= lang("permission") ?></th>
							<th><?= lang("absent") ?></th>
							<th><?= lang("late") ?></th>
							<th><?= lang("leave_early") ?></th>
							<th><?= lang("normal_ot") ?></th>
							<th><?= lang("weekend_ot") ?></th>
							<th><?= lang("holiday_ot") ?></th>
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
