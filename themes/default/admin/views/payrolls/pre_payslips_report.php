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
        oTable = $('#RSLD').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('payrolls/getPrePayslipsReport/?v=1' . $v); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var index = iDisplayIndex +1;
				$('td:eq(0)',nRow).html(index);
				return nRow;
            },
			"aoColumns": [
				{"sClass":"text-center"},
				null,
				null,
				null,
				null,
				{"mRender": fsd},
				{"mRender": currencyFormat},
				{"sClass":"text-right"},
				{"mRender": currencyFormat},
				{"mRender": currencyFormatWithoutZero,"sClass":"bold"},
				{"mRender": currencyFormatWithoutZero,"sClass":"bold"},
				{"mRender": currencyFormatKH,"sClass":"bold"},
				{"sClass":"td_signature"},
			],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0,total_gross=0, total_usd = 0, total_khr = 0;
                for (var i = 0; i < aaData.length; i++) {
					total += parseFloat(aaData[aiDisplay[i]][8]);
					total_gross += parseFloat(aaData[aiDisplay[i]][9]);
					total_usd += parseFloat(aaData[aiDisplay[i]][10]);
					total_khr += parseFloat(aaData[aiDisplay[i]][11]);
                }
                var nCells = nRow.getElementsByTagName('th');
				nCells[8].innerHTML = currencyFormat(total);
				nCells[9].innerHTML = currencyFormatWithoutZero(total_gross);
				nCells[10].innerHTML = currencyFormatWithoutZero(total_usd);
				nCells[11].innerHTML = currencyFormatKH(total_khr);
            }
        });

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
        <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= lang('pre_payslips_report'); ?></h2>
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

                    <?php echo admin_form_open("payrolls/pre_payslips_report"); ?>
                    <div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label" for="month"><?= lang("month"); ?></label>
								<?php echo form_input('month', (isset($_POST['month']) ? $_POST['month'] : date("m/Y")), 'class="form-control month" id="month"'); ?>
							</div>
						</div>
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
				<table class="print_only" style="width:100%; font-size:22px !important; margin-bottom: 10px; font-family:Khmer OS Bokor !important">
					<?php
						if($this->input->post('month')){
							$y_month = explode("/",$this->input->post('month'));
							$month = $y_month[0];
							$year = $y_month[1];
						}else{
							$month = date("m");
							$year = date("Y");
						}
					?>
					
					<?php if(isset($_POST['biller']) && $_POST['biller']){ 
						$biller_info = $this->site->getCompanyByID($_POST['biller']);
						echo '<tr>
								<td colspan="2" class="text-center" style="font-size:18; font-family:Times New Roman !important;"><b><u style="color:#0070C0 !important">'.$biller_info->name.'</u></b></td>
							</tr>';
					}else if(!$Owner && !$Admin && $this->session->userdata('biller_id')){ 
						$biller_info = $this->site->getCompanyByID($this->session->userdata('biller_id'));
						echo '<tr>
								<td colspan="2" class="text-center" style="font-size:18; font-family:Times New Roman !important;"><b><u style="color:#0070C0 !important">'.$biller_info->name.'</u></b></td>
							</tr>';
					}else { ?>
						<tr>
							<td colspan="2" class="text-center" style="font-size:18; font-family:Times New Roman !important;"><b><u style="color:#0070C0 !important"><?= $this->Settings->site_name ?></u></b></td>
						</tr>	
					<?php } ?>
					

					<tr>
						<th colspan="2" class="text-center" style="font-size:18; font-family:Khmer OS Bokor !important;"><b><u style="color:#0070C0 !important"><?= lang('បញ្ជីប្រាក់ខែបើលើកទី១សំរាប់ ខែ').$this->bpas->numberToKhmerMonth(sprintf("%02s", $month))." ឆ្នាំ".$this->bpas->numberToKhmer($year) ?></u></b></th>
					</tr>
					
					<?php if($this->input->post('group')){ ?>
						<tr>
							<th colspan="2" class="text-center" style="font-size:18; font-family:Khmer OS Bokor !important;"><b><u style="color:#0070C0 !important"><?= lang('របស់ក្រុម')." ".$gp[$this->input->post('group')] ?></u></b></th>
						</tr>
					<?php } ?>

				</table>
                <div class="table-responsive main_div">
                    <table id="RSLD" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped">
						<thead>
							<tr class="active">
								<th rowspan="2"><?= lang("ល-រ"); ?></th>
								<th rowspan="2"><?= lang("អត្តលេខ"); ?></th>
								<th rowspan="2"><?= lang("ឈ្មោះ"); ?></th>
								<th rowspan="2"><?= lang("ភេទ"); ?></th>
								<th rowspan="2"><?= lang("ក្រុម"); ?></th>
								<th><?= lang("ថ្ងៃចូល"); ?></th>
								<th><?= lang("ប្រាក់"); ?></th>
								<th><?= lang("ចំនួន"); ?></th>
								<th><?= lang("ប្រាក់"); ?></th>
								<th colspan="3"><?= lang("សរុបប្រាក់"); ?></th>
								<th rowspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= lang("ហត្ថលេខា"); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
							</tr>
							<tr>
								<th style='border: 1px solid #357EBD; border-bottom:2px solid #357EBD; color: white; background-color:#428bca; text-align:center'><?= lang("ធ្វើការ") ?></th>
								<th style='border: 1px solid #357EBD; border-bottom:2px solid #357EBD; color: white; background-color:#428bca; text-align:center'><?= lang("គោល") ?></th>
								<th style='border: 1px solid #357EBD; border-bottom:2px solid #357EBD; color: white; background-color:#428bca; text-align:center'><?= lang("ថ្ងៃ") ?></th>
								<th style='border: 1px solid #357EBD; border-bottom:2px solid #357EBD; color: white; background-color:#428bca; text-align:center'><?= lang("ធ្វើបាន") ?></th>
								<th style='border: 1px solid #357EBD; border-bottom:2px solid #357EBD; color: white; background-color:#428bca; text-align:center'><?= lang("ត្រូវបើក") ?></th>
								<th style='border: 1px solid #357EBD; border-bottom:2px solid #357EBD; color: white; background-color:#428bca; text-align:center'><?= lang("ប្រាក់ដុល្លារ") ?></th>
								<th style='border: 1px solid #357EBD; border-bottom:2px solid #357EBD; color: white; background-color:#428bca; text-align:center'><?= lang("ប្រាក់រៀល") ?></th>
							</tr>
                        </thead>
                        <tbody>
							<tr>
								<td colspan="13" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
							</tr>
                        </tbody>
                        <tfoot class="dtFilter">
							<tr class="active">
								<th style="border:none !important"></th>
								<th style="border:none !important"></th>
								<th style="border:none !important"></th>
								<th style="border:none !important"></th>
								<th style="border:none !important"></th>
								<th style="border:none !important"></th>
								<th style="border:none !important"></th>
								<th><?= lang("total") ?></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th style="border:none !important"></th>
							</tr>
                        </tfoot>
                    </table>
                </div>
				<table class="print_only" id="table_sinature">
					<tr>
						<td class="text-center" style="width:25%">Prepared by:_____________</td>
						<td class="text-center" style="width:25%">Checked by:_____________</td>
						<td class="text-center" style="width:25%">Verified by:_____________</td>
						<td class="text-center" style="width:25%">Approved by:_____________</td>
					</tr>
				</table>
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
		table .td_signature{ 
			height:70px !important;
		} 
		.dtFilter tr th{
			height:20px !important;
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
	#table_sinature{
		width:100%;
		margin-top:80px
	}
	.main_div{
		font-family:Khmer OS Bokor !important;
	}

</style>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?= admin_url('payrolls/getPrePayslipsReport/xls/?v=1'.$v)?>";
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

		});
		$(document).on("change", "#department", function () {
			var department_id = $(this).val();
			$.ajax({
				type: "get", 
				async: true,
				url: site.base_url + "payrolls/get_groups_positions/",
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



