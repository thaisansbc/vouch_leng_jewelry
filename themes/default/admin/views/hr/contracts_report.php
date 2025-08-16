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
	if ($this->input->post('position')) {
		$v .= "&position=" . $this->input->post('position');
	}
	if ($this->input->post('employee')) {
		$v .= "&employee=" . $this->input->post('employee');
	}
	if ($this->input->post('gender')) {
		$v .= "&gender=" . $this->input->post('gender');
	}
	if ($this->input->post('policy')) {
		$v .= "&policy=" . $this->input->post('policy');
	}
	if ($this->input->post('employee_type')) {
		$v .= "&employee_type=" . $this->input->post('employee_type');
	}
	if ($this->input->post('month')) {
		$v .= "&month=" . $this->input->post('month');
	}
	if ($this->input->post('status')) {
		$v .= "&status=" . $this->input->post('status');
	}
	if($alert){
		$v .= "&alert=" . $alert;
	}

?>

<script>
    $(document).ready(function () {
        oTable = $('#REM').dataTable({
            "aaSorting": [[0, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('hr/getContractsReport/?v=1' . $v); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
			'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[11];
                nRow.className = "hr_contract_link "+aData[12];
                return nRow;
            },
			"aoColumns": [
							{"mRender": fsd},
							null,
							null,
							null,
							null,
							null,
							{"mRender": fsd, "sClass":"center"},
							{"mRender": fsd, "sClass":"center"},
							{"mRender": fsd, "sClass":"center"},
							{"mRender": row_status, "sClass":"no_print", "bSearchable" : false},
							{"sClass":"print_td"},
						],
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('code');?>]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('name_kh');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('gender');?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('group');?>]", filter_type: "text", data: []},
			{column_number: 6, filter_default_label: "[<?=lang('employee_date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
			{column_number: 7, filter_default_label: "[<?=lang('start_date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
			{column_number: 8, filter_default_label: "[<?=lang('end_date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
			{column_number: 9, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
			{column_number: 10, filter_default_label: "[<?=lang('signature');?>]", filter_type: "text", data: []},
        ], "footer");

    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= lang('contracts_report'); ?></h2>
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
				<li class="dropdown">
					<a href="#" onclick="window.print(); return false;" id="print" class="tip" title="<?= lang('print') ?>">
						<i class="icon fa fa-print"></i>
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

                    <?php echo admin_form_open("hr/contracts_report"); ?>
                    <div class="row">
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
						<div class="col-md-4">
							<label class="control-label" for="gender"><?= lang("gender"); ?></label>
							<div class="form-group">
								<?php
									$gd[""] = lang("select")." ".lang("gender");
									$gd["male"] = lang("male");
									$gd["female"] = lang("female");
									echo form_dropdown('gender', $gd, (isset($_POST['gender']) ? $_POST['gender'] : ""), 'id="gender" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("gender") . '"  class="form-control input-tip select" style="width:100%;"');
								?>
							</div>
						</div>
						<div class="col-md-4">
							<label class="control-label" for="policy"><?= lang("policy"); ?></label>
							<div class="form-group">
								<?php
									$pl[""] = lang("select")." ".lang("policy");
									if(isset($policies) && $policies){
										foreach ($policies as $policy) {
											$pl[$policy->id] = $policy->policy;
										}
									}
									echo form_dropdown('policy', $pl, (isset($_POST['policy']) ? $_POST['policy'] : ""), 'id="policy" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("policy") . '"  class="form-control input-tip select" style="width:100%;"');
								?>
							</div>
						</div>
						<div class="col-md-4">
							<label class="control-label" for="employee_type"><?= lang("employee_type"); ?></label>
							<div class="form-group">
								<?php
									$et[""] = lang("select")." ".lang("employee_type");
									if(isset($employee_types) && $employee_types){
										foreach ($employee_types as $employee_type) {
											$et[$employee_type->id] = $employee_type->name;
										}
									}
									echo form_dropdown('employee_type', $et, (isset($_POST['employee_type']) ? $_POST['employee_type'] : ""), 'id="employee_type" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("employee_type") . '"  class="form-control input-tip select" style="width:100%;"');
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
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label" for="month"><?= lang("month"); ?></label>
								<?php echo form_input('month', (isset($_POST['month']) ? $_POST['month'] : ""), 'class="form-control month" id="month"'); ?>
							</div>
						</div>
						<div class="col-md-4">
							<label class="control-label" for="status"><?= lang("status"); ?></label>
							<div class="form-group">
								<?php
									$status[""] = lang("all");
									$status["active"] = lang("active");
									$status["inactive"] = lang("inactive");
									echo form_dropdown('status', $status, (isset($_POST['status']) ? $_POST['status'] : ""), 'id="status" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("status") . '"  class="form-control input-tip select" style="width:100%;"');
								?>
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
				<table class="print_only" style="width:100%; margin-bottom: 10px">
					<?php
						$print_filter = "";
						$p = 1;
						if($this->input->post('biller')){
							$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
							$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("biller").": ".$bl[$this->input->post('biller')]."</td>";
						}
						if($this->input->post('month')){
							$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
							$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("month").": ".$this->input->post('month')."</td>";
						}
						$p++; $td_class="text-right"; if($p % 2 == 0){ $td_class="text-left"; $print_filter .= ($p > 2 ? "</tr>" : "")."<tr>";}
						$print_filter .= "<td class=".$td_class." style='width:50%'>".lang("printing_date").": ".$this->bpas->hrsd(date("Y-m-d"))."</td></tr>";
					?>
					<tr>
						<td colspan="2" class="text-center" style="font-size:18px; font-family:Khmer OS Muol Light !important;"><?= $this->Settings->other_site_name ?></td>
					</tr>
					<tr>
						<th colspan="2" class="text-center" style="font-size:16px"><?= $this->Settings->site_name ?></th>
					</tr>
					<tr>
						<th colspan="2" class="text-center"><u><?= lang('contracts_report'); ?></u></th>
					</tr>

					<?= $print_filter ?>
				</table>	
                <div class="table-responsive">
                    <table id="REM" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped">
                        <thead>
							<tr class="active">
								<th><?= lang("date"); ?></th>
								<th><?= lang("code"); ?></th>
								<th><?= lang("name_kh"); ?></th>
								<th><?= lang("name"); ?></th>
								<th><?= lang("gender"); ?></th>
								<th><?= lang("group"); ?></th>
								<th><?= lang("employee_date"); ?></th>
								<th><?= lang("start_date"); ?></th>
								<th><?= lang("end_date"); ?></th>
								<th class="no_print"><?= lang("status"); ?></th>
								<th class="print_td"><?= lang("signature"); ?></th>
							</tr>
                        </thead>
                        <tbody>
							<tr>
								<td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
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
								<th class="no_print"></th>
								<th class="print_td"></th>
							</tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
	@media print{    
		.dtFilter{
			display: table-footer-group !important;
		}
		#form{
			display:none !important;
		}
		.print_only{
			display:table !important;
		}
		.print_td{
			display:table-cell !important;
		}
		.no_print{
			display:none !important;
		}
		table .td_biller{ 
			display:none; !important
		} 
		.exportExcel tr th{
			background-color : #428BCA !important;
			color : white !important;
		}
		@page{
			margin: 5mm; 
		}
		body {
			-webkit-print-color-adjust: exact !important;  
			color-adjust: exact !important;         
		}
		
	}
	.print_only{
		display:none;
	}
	.print_td{
		display:none;
	}
	#table_sinature{
		width:100%;
		margin-top:15px
	}
	#table_sinature td{
		border:1px solid black;
	}
</style>
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
            window.location.href = "<?=admin_url('hr/getContractsReport/xls/?v=1'.$v)?>";
            return false;
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
		});
		$(document).on("change", "#department", function () {
			var department_id = $(this).val();
			$.ajax({
				type: "get", 
				async: true,
				url: site.base_url + "hr/get_groups_positions/",
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



