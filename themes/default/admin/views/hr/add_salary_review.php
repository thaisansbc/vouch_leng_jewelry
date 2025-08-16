<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_salary_review'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
					$attrib = array('data-toggle' => 'validator', 'role' => 'form');
					echo admin_form_open_multipart("hr/add_salary_review", $attrib);
                ?>
                <div class="row">
					<div class="col-md-12">
						<div class="panel panel-warning">
							<div class="panel-heading"><?= lang('please_select_these_before_adding_employee') ?></div>
							<div class="panel-body" style="padding: 5px;">
								<?php if ($Owner || $Admin || $GP['hr-salary_reviews_date']) { ?>
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("date", "date"); ?>
											<?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="date" required="required"'); ?>
										</div>
									</div>
								<?php } ?>
								<div class="col-sm-4">
									<div class="form-group">
										<?= lang("month", "month"); ?>
										<?php echo form_input('month', (isset($_POST['kpi_month']) ? $_POST['kpi_month'] : date("m/Y")), 'class="form-control month" id="month"  required="required"'); ?>
									</div>
								</div>
								<div class="col-md-4">
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
								<div class="col-md-4">
									<?= lang("position", "position"); ?>
									<div class="position_box form-group">
										<?php
											$ps[""] = lang("select")." ".lang("position");
											echo form_dropdown('position', $ps, (isset($_POST['position']) ? $_POST['position'] : ''), 'id="position" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("position") . '"  class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
								<div class="col-md-4">
									<?= lang("department", "department"); ?>
									<div class="department_box form-group">
										<?php
											$dp[""] = lang("select")." ".lang("department");
											echo form_dropdown('department', $dp, (isset($_POST['department']) ? $_POST['department'] : ''), 'id="department" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("department") . '"  class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
								<div class="col-md-4">
									<?= lang("group", "group"); ?>
									<div class="group_box form-group">
										<?php
											$gp[""] = lang("select")." ".lang("group");
											echo form_dropdown('group', $gp, (isset($_POST['group']) ? $_POST['group'] : ''), 'id="group" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("group") . '"  class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
							</div>
						</div>
                    </div>
					<?php
						$colspan = 1;
						$td_addition = "";
						if($additions){
							foreach($additions as $addition){
								$td_addition .= '<td style="border-bottom:1px solid #3B84C3; border-right:1px solid #3B84C3;">'.$addition->name.'</td>';
								$colspan++;
							}
						}
					
					?>
					
                    <div class="col-lg-12">
                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("employee"); ?> *</label>
                                <div class="controls table-controls">
                                    <table id="expTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
										<thead>
											<tr>
												<th rowspan="2"><?= lang("code") ?></th>
												<th rowspan="2"><?= lang("name") ?></th>
												<th colspan="<?= $colspan ?>"><?= lang("old_salary") ?></th>
												<th colspan="2"><?= lang("kpi") ?></th>
												<th colspan="<?= $colspan ?>"><?= lang("new_salary") ?></th>
												<th rowspan="2"><?= lang("gross_salary") ?></th>
												<th rowspan="2" style="width: 30px !important; text-align: center;"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
											</tr>
											<tr style="background:#428BCA; color:#FFFFFF; text-align:center; font-weight:bold;">
												<td style="border-bottom:1px solid #3B84C3; border-right:1px solid #3B84C3;"><?= lang("basic") ?></td>
												<?= $td_addition ?>
												<td style="border-bottom:1px solid #3B84C3; border-right:1px solid #3B84C3;"><?= lang("result") ?></td>
												<td style="border-bottom:1px solid #3B84C3; border-right:1px solid #3B84C3;"><?= lang("increase_salary") ?></td>
												<td style="border-bottom:1px solid #3B84C3; border-right:1px solid #3B84C3;"><?= lang("basic") ?></td>
												<?= $td_addition ?>
											</tr>
										</thead>
                                        <tbody id="dataEmp"></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                       data-show-preview="false" class="form-control file">
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
								<?php echo form_submit('add_salary_review', $this->lang->line("submit"), 'id="add_salary_review" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
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
		<?php if ($Owner || $Admin || $GP['hr-salary_reviews_date']) { ?>
			$("#date").datetimepicker({
				<?= ($Settings->date_with_time == 0 ? 'format: site.dateFormats.js_sdate, minView: 2' : 'format: site.dateFormats.js_ldate') ?>,
				fontAwesome: true,
				language: 'bms',
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
			$.ajax({
				type: "get", 
				async: true,
				url: site.base_url + "hr/get_salary_review_employee/",
				data : { 
						biller_id : biller_id,
						position_id : position_id,
						department_id : department_id,
						group_id : group_id,
						month : month
				},
				dataType: "json",
				success: function (data) {
					var dataEmp = "";
					if (data != false) {
						$.each(data, function () {
							var employee_id = this.employee_id;
							dataEmp += "<tr>";
								dataEmp += "<td><input class='employee_id' name='employee_id[]' value='"+employee_id+"' type='hidden'/>"+this.empcode+"</td>";
								dataEmp += "<td><input type='hidden' value='"+this.additions+"' name='old_addition[]'/>"+this.lastname+" "+this.firstname+"</td>";
								dataEmp += "<td class='text-right'><input class='old_salary' name='old_salary[]' value='"+this.net_salary+"' type='hidden'/>"+formatMoney(this.net_salary)+"</td>";
								if(this.emp_addtions){
									$.each(this.emp_addtions, function(){
										dataEmp += "<td class='text-right'>"+(this.value)+"</td>";
									});
								}	
								dataEmp += "<td class='text-right'><input type='hidden' value='"+this.result+"' name='result[]'/>"+formatDecimal(this.result)+"%</td>";
								dataEmp += "<td><input name='increase_salary[]' class='form-control text-right increase_salary' type='text' value='"+this.increase_salary+"'/></td>";
								dataEmp += "<td><input type='hidden' name='gross_salary[]' class='gross_salary'/><input name='new_salary[]' class='form-control text-right new_salary' type='text' value='0'/></td>";
								if(this.emp_addtions){
									$.each(this.emp_addtions, function(){
										dataEmp += "<td><input name='new_addition["+employee_id+"]["+this.id+"]' class='form-control text-right addition' type='text' value='"+(this.value)+"'/></td>";
									});
								}
								dataEmp += "<td class='text-right lgross_salary'></td>";
								dataEmp += "<td class='text-center'><i class='fa fa-times tip pointer del' title='Remove' style='cursor:pointer'></i></td>";	
							dataEmp += "</tr>";
						});
					}
					$("#dataEmp").html(dataEmp);
					calculateSalary();
				}
			});
		}
		
		function calculateSalary(){
			$(".employee_id").each(function(){
				var row = $(this).closest('tr');
				var old_salary = row.find(".old_salary").val() - 0;
				var increase_salary = row.find(".increase_salary").val();
				var basic_increase = 0
				if (increase_salary.indexOf("%") !== -1) {
					var pds = increase_salary.split("%");
					if (!isNaN(pds[0])) {
						basic_increase = (old_salary * parseFloat(pds[0])) / 100;
					} else {
						basic_increase = increase_salary - 0;
					}
				} else {
					basic_increase = increase_salary - 0;
				}
				var new_salary = formatDecimal(old_salary + basic_increase);
				var addition = 0;
				if(row.find(".addition")){
					$.each(row.find(".addition"), function(){
						if ($(this).val().indexOf("%") !== -1) {
							var pds = $(this).val().split("%");
							if (!isNaN(pds[0])) {
								addition += (new_salary * parseFloat(pds[0])) / 100;
							} else {
								addition += $(this).val() - 0;
							}
						} else {
							addition += $(this).val() - 0;
						}
					});
				}
				row.find(".new_salary").val(new_salary);
				row.find(".gross_salary").val(new_salary + addition);
				row.find(".lgross_salary").html(formatMoney(new_salary + addition));
			});
		}
		
		var old_new_salary;
		$(document).on("focus", '.new_salary', function () {
			old_new_salary = $(this).val();
		}).on("change", '.new_salary', function () {
			if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
				$(this).val(old_new_salary);
				bootbox.alert(lang.unexpected_value);
				return;
			}
			var row = $(this).closest('tr');
			var new_salary = $(this).val() - 0;
			var old_salary = row.find(".old_salary").val() - 0;
			var increase_salary = new_salary - old_salary;
			row.find(".increase_salary").val(increase_salary);
			calculateSalary();
		});
		
		var old_value;
		$(document).on("focus", '.increase_salary, .addition', function () {
			old_value = $(this).val();
		}).on("change", '.increase_salary, .addition', function () {
			var new_value = $(this).val() ? $(this).val() : '0';
			if (is_valid_discount(new_value)) {
				calculateSalary();
			} else {
				$(this).val(old_value);
				bootbox.alert(lang.unexpected_value);
				return;
			}
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
			$.ajax({
				type: "get", 
				async: true,
				url: site.base_url + "hr/get_positions/",
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
			getSalaryEmployee();
		});
		
		$(document).on("change", "#department", function () {
			var department_id = $(this).val();
			$.ajax({
				type: "get", 
				async: true,
				url: site.base_url + "hr/get_groups/",
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
			getSalaryEmployee();
		});
		
		$(document).on("change", "#group, #position, #month", function () {	
			getSalaryEmployee();
		});
		
		$(document).on("click", ".del", function () {		
			var row = $(this).closest('tr');
			row.remove();
		});

	});
</script>