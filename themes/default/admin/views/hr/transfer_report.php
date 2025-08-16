<?php defined('BASEPATH') OR exit('No direct script access allowed');

	$v = "";
	if ($this->input->post('biller')) {
		$v .= "&biller=" . $this->input->post('biller');
	}
	if ($this->input->post('employee')) {
		$v .= "&employee=" . $this->input->post('employee');
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
        oTable = $('#REM').dataTable({
            "aaSorting": [[0, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('hr/getTransferReport/?v=1' . $v); ?>',
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
				{"sClass" : "center", "mRender" :fld},
                null, 
                null,
                null, 
                {"mRender": row_status},
            ]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('code');?>]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('place');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('start_date');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('end_date');?>]", filter_type: "text", data: []},
        ], "footer");

    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= lang('transfer_report'); ?></h2>
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

                    <?php echo admin_form_open("hr/transfer_report"); ?>
                    <div class="row">
						<div class="col-sm-3">
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
						<div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="suggest_employee"><?= lang("employee"); ?></label>
								<input type="text" name="employee_id" id="suggest_employee" value="<?= set_value('employee_id') ?>" class="form-control ui-autocomplete-input" />
								<input type="hidden" name="employee" value="<?= set_value('employee') ?>" id="suggest_employee_id">
							</div>
                        </div>
                        <div class="col-sm-3">
	                        <div class="form-group">
								<?php echo lang('start_date', 'start_date'); ?>
								<div class="controls">
									<input type="text" value="<?= (isset($_POST['start_date']) ? $_POST['start_date'] : date('d/m/Y')); ?>" name="start_date" class="form-control date"/>
								</div>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group">
								<?php echo lang('end_date', 'end_date'); ?>
								<div class="controls">
									<input type="text" name="end_date" value="<?= (isset($_POST['end_date']) ? $_POST['end_date'] :date('d/m/Y')); ?>" class="form-control date"/>
								</div>
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
								<th width="200"><?= lang("date"); ?></th>	
                                <th><?= lang("from_department"); ?></th>
                                <th><?= lang("to_department"); ?></th>
								<th><?= lang("note"); ?></th>
								<th><?= lang("status"); ?></th>
							</tr>
                        </thead>
                        <tbody>
							<tr>
								<td colspan="4" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
							</tr>
                        </tbody>
                        <tfoot class="dtFilter">
							<tr class="active">
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
            window.location.href = "<?=admin_url('hr/getRetirementReport/xls/?v=1'.$v)?>";
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



