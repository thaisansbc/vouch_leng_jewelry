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
if ($this->input->post('employee')) {
    $v .= "&employee=" . $this->input->post('employee');
}
if ($this->input->post('year')) {
    $v .= "&year=" . $this->input->post('year');
}
?>

<script>
    $(document).ready(function () {
		
		function formatDay(x) {
			return (x != null) ? '<div class="text-center">'+(x-0)+'</div>' : '';
		}
		oTable = $('#ELYRData').dataTable({
			"aaSorting": [[0, "acs"]],
			"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
			"iDisplayLength": <?= $Settings->rows_per_page ?>,
			'bProcessing': true, 'bServerSide': true,
			'sAjaxSource': '<?= admin_url('attendances/getEmployeeLeaveByYear/?v=1' . $v) ?>',
			'fnServerData': function (sSource, aoData, fnCallback) {
				aoData.push({
					"name": "<?= $this->security->get_csrf_token_name() ?>",
					"value": "<?= $this->security->get_csrf_hash() ?>"
				});
				$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
			},
			'fnRowCallback': function (nRow, aData, iDisplayIndex) {
				nRow.id = aData[11]+'date'+aData[4]; 
				nRow.className = "daily_time_card_link";
				var action = $('td:eq(9)', nRow);
				return nRow;
			},
			"aoColumns": [
			null, 
			null, 
			null, 
			null, 
			null, 
			{"mRender": formatDay},
			{"mRender": formatDay},
			{"mRender": formatDay},
			{"mRender": formatDay},
			{"mRender": formatDay},
			{"mRender": formatDay},
			{"mRender": formatDay},
			{"mRender": formatDay}],
			"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
				var total_annual = 0, used_annual = 0, total_special = 0, used_special=0, total_sick = 0, used_sick = 0, total_other = 0, used_other = 0;
				for (var i = 0; i < aaData.length; i++) {
					total_annual += parseFloat(aaData[aiDisplay[i]][5]);
					used_annual += parseFloat(aaData[aiDisplay[i]][6]);
					total_special += parseFloat(aaData[aiDisplay[i]][7]);
					used_special += parseFloat(aaData[aiDisplay[i]][8]);
					total_sick += parseFloat(aaData[aiDisplay[i]][9]);
					used_sick += parseFloat(aaData[aiDisplay[i]][10]);
					total_other += parseFloat(aaData[aiDisplay[i]][11]);
					used_other += parseFloat(aaData[aiDisplay[i]][12]);
				}
				var nCells = nRow.getElementsByTagName('th');
				nCells[5].innerHTML = formatDay((total_annual));
				nCells[6].innerHTML = formatDay((used_annual));
				nCells[7].innerHTML = formatDay((total_special));
				nCells[8].innerHTML = formatDay((used_special));
				nCells[9].innerHTML = formatDay((total_sick));
				nCells[10].innerHTML = formatDay((used_sick));
				nCells[11].innerHTML = formatDay((total_other));
				nCells[12].innerHTML = formatDay((used_other));
	
			}
		}).fnSetFilteringDelay().dtFilter([
			{column_number: 0, filter_default_label: "[<?=lang('code');?>]", filter_type: "text", data: []},
			{column_number: 1, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('position');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('department');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('group');?>]", filter_type: "text", data: []},
		], "footer");
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('employee_leave_by_year_report'); ?> </h2>

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

                <p class="introtext"><?= lang('employee_leave_by_year_report'); ?></p>

                <div id="form">

                    <?php echo admin_form_open("attendances/employee_leave_by_year_report"); ?>
                    
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
                                <?= lang("year", "year"); ?>
                                <?php echo form_input('year', (isset($_POST['year']) ? $_POST['year'] : date("Y")), 'class="form-control year" id="year"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table id="ELYRData"
                           class="table table-bordered table-hover table-striped table-condensed reports-table">
                        <thead>
							<tr>
								<th rowspan="2"><?= lang("code"); ?></th>
								<th rowspan="2"><?= lang("name"); ?></th>
								<th rowspan="2"><?= lang("position"); ?></th>
								<th rowspan="2"><?= lang("department"); ?></th>
								<th rowspan="2"><?= lang("group"); ?></th>
								<th colspan="2"><?= lang("annual_leave") ?></th>
								<th colspan="2"><?= lang("special_leave") ?></th>
								<th colspan="2"><?= lang("sick_leave") ?></th>
								<th colspan="2"><?= lang("other_leave") ?></th>
							</tr>
							<tr>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("total") ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("used") ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("total") ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("used") ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("total") ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("used") ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("total") ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("used") ?></th>
							</tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="13" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
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
		
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('attendances/getEmployeeLeaveByYear/pdf/?v=1'.$v)?>";
            return false;
        });
		
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('attendances/getEmployeeLeaveByYear/0/xls/?v=1'.$v)?>";
            return false;
        });
		
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    openImg(img);
                }
            });
            return false;
        });
		
    });
</script>
