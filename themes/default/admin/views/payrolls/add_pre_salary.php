<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_pre_salary'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
					$attrib = array('data-toggle' => 'validator', 'role' => 'form');
					echo admin_form_open_multipart("payrolls/add_pre_salary", $attrib);
                ?>
                <div class="row">
					<div class="col-md-12">
						<div class="panel panel-warning">
							<div class="panel-heading"><?= lang('please_select_these_before_adding_employee') ?></div>
							<div class="panel-body" style="padding: 5px;">
								<?php if ($Owner || $Admin || $GP['payrolls-salaries_date']) { ?>
									<div class="col-md-3">
										<div class="form-group">
											<?= lang("date", "date"); ?>
											<?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="date" required="required"'); ?>
										</div>
									</div>
								<?php } ?>
								<div class="col-md-3">
									<div class="form-group">
										<?= lang("month", "month"); ?>
										<?php echo form_input('month', (isset($_POST['month']) ? $_POST['month'] : date("m/Y")), 'class="form-control month" required="required" id="month"'); ?>
									</div>
								</div>
								<div class="col-md-3">
									<?= lang("biller", "biller"); ?>
									<div class="form-group">
										<?php
										$bl[""] = "";
										foreach ($billers as $biller) {
											$bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
										}
										echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'id="biller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
								
								<div class="col-md-3">
									<?= lang("department", "department"); ?>
									<div class="department_box form-group">
										<?php
											$dp[""] = lang("select")." ".lang("department");
											echo form_dropdown('department', $dp, (isset($_POST['department']) ? $_POST['department'] : ''), 'id="department" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("department") . '"  class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
								<div class="col-md-3">
									<?= lang("group", "group"); ?>
									<div class="group_box form-group">
										<?php
											$gp[""] = lang("select")." ".lang("group");
											echo form_dropdown('group', $gp, (isset($_POST['group']) ? $_POST['group'] : ''), 'id="group" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("group") . '"  class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
								
								<div class="col-md-3">
									<?= lang("position", "position"); ?>
									<div class="position_box form-group">
										<?php
											$ps[""] = lang("select")." ".lang("position");
											echo form_dropdown('position', $ps, (isset($_POST['position']) ? $_POST['position'] : ''), 'id="position" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("position") . '"  class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
								<div class="col-md-3">
									<?= lang("type", "type"); ?>
									<div class="form-group">
										<?php
											$type["half"] = lang("half");
											$type["full"] = lang("full");

											echo form_dropdown('type', $type, (isset($_POST['type']) ? $_POST['position'] : ''), 'id="type" class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
							</div>
						</div>
                    </div>
                    <div class="col-lg-12">
                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("employee"); ?> *</label>
                                <div class="controls table-controls">
                                    <table id="expTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                       <thead>
											<tr>
												<th><?= lang("code") ?></th>
												<th><?= lang("name") ?></th>
												<th><?= lang("department") ?></th>
												<th><?= lang("group") ?></th>
												<th><?= lang("position") ?></th>
												<th><?= lang("employee_date") ?></th>
												<th><?= lang("basic_salary") ?></th>
												<th><?= lang("present") ?></th>
												<th><?= lang("gross_salary") ?></th>
												<th style="width: 30px !important; text-align: center;"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
											</tr>
										</thead>
                                        <tbody id="dataEmp"></tbody>
                                        <tfoot>
											<tr>
												<th colspan="8" class="text-right"><?= lang("total") ?></th>
												<th class="text-right ttotal"></th>
												<th style="width: 30px !important; text-align: center;"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
											</tr>
										</tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
                            </div>
                        </div>
                        <div class="row" id="bt">
                            <div class="col-sm-12">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <?= lang("note", "note"); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="note" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="fprom-group">
								<?php echo form_submit('add_pre_salary', $this->lang->line("submit"), 'id="add_pre_salary" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
								<?php echo form_submit('add_pre_salary_next', lang("submit_and_next"), 'id="add_pre_salary_next" class="btn btn-info" style="padding: 6px 15px; margin:15px 0;"'); ?>
							</div>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		<?php if ($Owner || $Admin || $GP['payrolls-salaries_date']) { ?>
			$("#date").datetimepicker({
				<?= ($Settings->date_with_time == 0 ? 'format: site.dateFormats.js_sdate, minView: 2' : 'format: site.dateFormats.js_ldate') ?>,
				fontAwesome: true,
				language: 'bpas',
				weekStart: 1,
				todayBtn: 1,
				autoclose: 1,
				todayHighlight: 1,
				startView: 2,
				forceParse: 0
			}).datetimepicker('update', new Date());
		<?php } ?>
		function getSalaryEmployee(){
			var biller_id = $("#biller").val();
			var position_id = $("#position").val();
			var department_id = $("#department").val();
			var group_id = $("#group").val();
			var month = $("#month").val();
			var type = $("#type").val();
			if(biller_id && month){
				$.ajax({
					type: "get", 
					async: true,
					url: site.base_url + "payrolls/get_pre_salary_employees/",
					data : { 
							biller_id : biller_id,
							position_id : position_id,
							department_id : department_id,
							group_id : group_id,
							month : month,
							type : type
					},
					dataType: "json",
					success: function (data) {
						var dataEmp = "";
						var ttotal = 0;
						if (data != false) {
							$.each(data, function () {
								ttotal += parseFloat(this.gross_salary);
								var employee_id = this.employee_id;
								dataEmp += "<tr>";
									dataEmp += "<td><input name='employee_id[]' value='"+employee_id+"' type='hidden'/>"+this.empcode+"</td>";
									dataEmp += "<td><input name='att_id[]' value='"+this.id+"' type='hidden'/>"+this.lastname+" "+this.firstname+"</td>";
									dataEmp += "<td><input name='holiday[]' value='"+this.holiday+"' type='hidden'/>"+this.department+"</td>";
									dataEmp += "<td><input name='annual_leave[]' value='"+this.annual_leave+"' type='hidden'/>"+this.group+"</td>";
									dataEmp += "<td><input name='sick_leave[]' value='"+this.sick_leave+"' type='hidden'/>"+this.position+"</td>";
									dataEmp += "<td><input name='special_leave[]' value='"+this.special_leave+"' type='hidden'/>"+fsd(this.employee_date)+"</td>";
									dataEmp += "<td class='text-right'><input name='basic_salary[]' value='"+this.basic_salary+"' type='hidden'/>"+formatMoney(this.basic_salary)+"</td>";
									dataEmp += "<td class='text-center'><input name='present[]' value='"+this.present+"' type='hidden'/>"+this.present+"</td>";
									dataEmp += "<td class='text-right'><input class='gross_salary' name='gross_salary[]' value='"+this.gross_salary+"' type='hidden'/>"+formatMoney(this.gross_salary)+"</td>";
									dataEmp += "<td class='text-center'><i class='fa fa-times tip pointer del' title='Remove' style='cursor:pointer'></i></td>";	
								dataEmp += "</tr>";
							});
						}
						$("#dataEmp").html(dataEmp);
						$(".ttotal").html(formatMoney(ttotal));
					}
				});
			}
			
		}
		
		
		$(document).on("change", "#biller,#type", function () {	
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
			getSalaryEmployee();
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
					getSalaryEmployee();
				}
			});
		});
		$(document).on("change", "#group, #position, #month, #times,#type", function () {	
			getSalaryEmployee();
		});
		
		$(document).on("click", ".del", function () {		
			var row = $(this).closest('tr');
			row.remove();
			loadItem();
		});

	
		function loadItem(){
			var ttotal = 0;
			$(".gross_salary").each(function(){
				ttotal += $(this).val() - 0;
			});
			$(".ttotal").html(formatMoney(ttotal));
		}

	});
</script>