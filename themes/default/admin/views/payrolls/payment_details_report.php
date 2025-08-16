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

<script>
    $(document).ready(function () {
        function attachment(x) {
            if (x != null) {
                return '<a href="' + site.base_url + 'assets/uploads/' + x + '" target="_blank"><i class="fa fa-chain"></i></a>';
            }
            return x;
        }

        oTable = $('#RPMD').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('payrolls/getPaymentDetailsReport/?v=1' . $v); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[10];
                nRow.className = "payroll_payment_link";
                return nRow;
            },
			"aoColumns": [{"mRender": fld},null,null,null,null,null,null,{"mRender": currencyFormat},{"mRender": currencyFormat},{"mRender": currencyFormat}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total_tax_paid = 0, total_salary_paid = 0, total_paid = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total_tax_paid += parseFloat(aaData[aiDisplay[i]][7]);
					total_salary_paid += parseFloat(aaData[aiDisplay[i]][8]);
					total_paid += parseFloat(aaData[aiDisplay[i]][9]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[7].innerHTML = currencyFormat(total_tax_paid);
				nCells[8].innerHTML = currencyFormat(total_salary_paid);
				nCells[9].innerHTML = currencyFormat(total_paid);
            }
        }).fnSetFilteringDelay().dtFilter([
			{column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
			{column_number: 1, filter_default_label: "[<?=lang('month');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('code');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('position');?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('department');?>]", filter_type: "text", data: []},
			{column_number: 6, filter_default_label: "[<?=lang('group');?>]", filter_type: "text", data: []},
        ], "footer");

    });

</script>
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
        <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= lang('payment_details_report'); ?></h2>
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

                    <?php echo admin_form_open("payrolls/payment_details_report"); ?>
                    <div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label" for="month"><?= lang("month"); ?></label>
								<?php echo form_input('month', (isset($_POST['month']) ? $_POST['month'] : ""), 'class="form-control month" id="month"'); ?>
							</div>
						</div>
						<div class="col-sm-4">
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
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="suggest_employee"><?= lang("employee"); ?></label>
								<input type="text" name="employee_id" id="suggest_employee" value="<?= set_value('employee_id') ?>" class="form-control ui-autocomplete-input" />
								<input type="hidden" name="employee" value="<?= set_value('employee') ?>" id="suggest_employee_id">
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
                    <table id="RPMD" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped">
                        <thead>
							<tr class="active">
								<th><?= lang("date"); ?></th>
								<th><?= lang("month"); ?></th>
								<th><?= lang("code"); ?></th>
								<th><?= lang("name"); ?></th>
								<th><?= lang("position"); ?></th>
								<th><?= lang("department"); ?></th>
								<th><?= lang("group"); ?></th>
								<th><?= lang("tax_paid"); ?></th>
								<th><?= lang("salary_paid"); ?></th>
								<th><?= lang("total_paid"); ?></th>
							</tr>
                        </thead>
                        <tbody>
							<tr>
								<td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
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
		$('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('payrolls/getPaymentDetailsReport/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('payrolls/getPaymentDetailsReport/0/xls/?v=1'.$v)?>";
            return false;
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
			$.ajax({
				type: "get", 
				async: true,
				url: site.base_url + "payrolls/get_positions/",
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
				url: site.base_url + "payrolls/get_groups/",
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



