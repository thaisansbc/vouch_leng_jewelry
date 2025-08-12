<?php defined('BASEPATH') OR exit('No direct script access allowed');
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

	if ($this->input->post('approve_month')) {
		$v .= "&approve_month=" . $this->input->post('approve_month');
	}
?>

<script>
    $(document).ready(function () {
		oTable = $('#MlRData').dataTable({
			"aaSorting": [[0, "acs"]],
			"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
			"iDisplayLength": <?= $Settings->rows_per_page ?>,
			'bProcessing': true, 'bServerSide': true,
			'sAjaxSource': '<?= admin_url('attendances/getMonthlyAttedanceSummary/?v=1' . $v) ?>',
			'fnServerData': function (sSource, aoData, fnCallback) {
				aoData.push({
					"name": "<?= $this->security->get_csrf_token_name() ?>",
					"value": "<?= $this->security->get_csrf_hash() ?>"
				});
				$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
			},
			'fnRowCallback': function (nRow, aData, iDisplayIndex) {
				nRow.id = aData[11]+'date'+aData[12]; 
				nRow.className = "montly_time_card_link";
				var action = $('td:eq(9)', nRow);
				return nRow;
			},
			"aoColumns": [
			null, 
			null, 
			null, 
			null, 
			null, 
			{"mRender": text_right,"bSearchable": false},
			{"mRender": text_right,"bSearchable": false},
			{"mRender": text_right,"bSearchable": false},
			{"mRender": text_right,"bSearchable": false},
			{"mRender": d_secTotime,"bSearchable": false},			
			{"mRender": d_secTotime,"bSearchable": false}],
			"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
				var working_day = 0, present = 0, permission = 0, absent=0, leave_early = 0, late = 0;
				for (var i = 0; i < aaData.length; i++) {
					working_day += parseFloat(aaData[aiDisplay[i]][5]);
					present += parseFloat(aaData[aiDisplay[i]][6]);
					permission += parseFloat(aaData[aiDisplay[i]][7]);
					absent += parseFloat(aaData[aiDisplay[i]][8]);
					leave_early += parseFloat(aaData[aiDisplay[i]][9]);
					late += parseFloat(aaData[aiDisplay[i]][10]);
				}
				var nCells = nRow.getElementsByTagName('th');
				nCells[5].innerHTML = text_right((working_day));
				nCells[6].innerHTML = text_right((present));
				nCells[7].innerHTML = text_right((permission));
				nCells[8].innerHTML = text_right((absent));
				nCells[9].innerHTML = d_secTotime(parseFloat(leave_early));
				nCells[10].innerHTML = d_secTotime(parseFloat(late));
			}
		}).fnSetFilteringDelay().dtFilter([
			{column_number: 0, filter_default_label: "[<?=lang('code');?>]", filter_type: "text", data: []},
			{column_number: 1, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('department');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('group');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('month');?>]", filter_type: "text", data: []},
		], "footer");
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('montly_attendance_summary_report'); ?> </h2>
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
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">
                    <?php echo admin_form_open("attendances/montly_attendance_summary_report"); ?>
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
                                <label class="control-label" for="group"><?= lang("employee"); ?></label>
								<input type="text" name="employee_id" id="suggest_employee" value="<?= set_value('employee_id') ?>" class="form-control ui-autocomplete-input" />
								<input type="hidden" name="employee" value="<?= set_value('employee') ?>" id="suggest_employee_id">
							</div>
                        </div>
						
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("month", "month"); ?>
                                <?php echo form_input('approve_month', (isset($_POST['approve_month']) ? $_POST['approve_month'] : date("m/Y")), 'class="form-control month" '); ?>
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
                    <table id="MlRData"
                           class="table table-bordered table-hover table-striped table-condensed reports-table">
                        <thead>
                        <tr>
							<th><?= lang("code"); ?></th>
                            <th><?= lang("name"); ?></th>
                            <th><?= lang("department"); ?></th>
                            <th><?= lang("group"); ?></th>
                            <th><?= lang("month"); ?></th>
							<th><?= lang("working_day"); ?></th>
                            <th><?= lang("present"); ?></th>
							<th><?= lang("permission"); ?></th>
							<th><?= lang("absent"); ?></th>
							<th><?= lang("coming_late") ?></th>
							<th><?= lang("leave_early") ?></th>
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
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('attendances/getMonthlyAttedanceSummary/xls/?v=1'.$v)?>";
            return false;
        });
		
    });
</script>
