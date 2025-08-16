<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('edit_benefit'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
					$attrib = array('data-toggle' => 'validator', 'role' => 'form');
					echo admin_form_open_multipart("payrolls/edit_benefit/".$benefit->id, $attrib);
                ?>
                <div class="row">
					<div class="col-md-12">
						<div class="panel panel-warning">
							<div class="panel-heading"><?= lang('please_select_these_before_adding_employee') ?></div>
							<div class="panel-body" style="padding: 5px;">
								<?php if ($Owner || $Admin || $GP['payrolls-benefits_date']) { ?>
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("date", "date"); ?>
											<?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->bpas->hrld($benefit->date)), 'class="form-control input-tip datetime" id="date" required="required"'); ?>
										</div>
									</div>
								 <?php } ?>
								<div class="col-md-4">
									<div class="form-group">
										<?= lang("month", "month"); ?>
										<?php echo form_input('month', (isset($_POST['month']) ? $_POST['month'] : $benefit->month."/".$benefit->year), 'class="form-control month" required="required" id="month"'); ?>
									</div>
								</div>
								<div class="col-md-4">
									<?= lang("biller", "biller"); ?>
									<div class="form-group">
										<?php
										$bl[""] = "";
										if($billers){
											foreach ($billers as $biller) {
												$bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
											}
										}
										echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $benefit->biller_id), 'id="biller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
								<div class="col-md-4">
									<?= lang("position", "position"); ?>
									<div class="position_box form-group">
										<?php
											$ps[""] = lang("select")." ".lang("position");
											if($positions){
												foreach ($positions as $position) {
													$ps[$position->id] = $position->name;
												}
											}
											echo form_dropdown('position', $ps, (isset($_POST['position']) ? $_POST['position'] : $benefit->position_id), 'id="position" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("position") . '"  class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
								<div class="col-md-4">
									<?= lang("department", "department"); ?>
									<div class="department_box form-group">
										<?php
											$dp[""] = lang("select")." ".lang("department");
											if($departments){
												foreach ($departments as $department) {
													$dp[$department->id] = $department->name;
												}
											}
											echo form_dropdown('department', $dp, (isset($_POST['department']) ? $_POST['department'] : $benefit->department_id), 'id="department" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("department") . '"  class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
								<div class="col-md-4">
									<?= lang("group", "group"); ?>
									<div class="group_box form-group">
										<?php
											$gp[""] = lang("select")." ".lang("group");
											if(isset($groups) && $groups){
												foreach ($groups as $group) {
													$gp[$group->id] = $group->name;
												}
											}
											echo form_dropdown('group', $gp, (isset($_POST['group']) ? $_POST['group'] : $benefit->group_id), 'id="group" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("group") . '"  class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
							</div>
						</div>
                    </div>
                    <div class="col-lg-12">
						<?php
							$th_additions = "";
							$th_deductions = "";
							if($additions){
								foreach($additions as $addition){
									$th_additions .= "<th style='border: 2px solid #357EBD; color: white; background-color:#428bca; text-align:center'>".$addition->name."</th>";
								}
							}
							if($deductions){
								foreach($deductions as $deduction){
									$th_deductions .= "<th style='border: 2px solid #357EBD; color: white; background-color:#428bca; text-align:center'>".$deduction->name."</th>";
								}
							}
						?>
                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("employee"); ?> *</label>
                                <div class="controls table-controls">
                                    <table id="expTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
											<tr>
												<th rowspan="2"><?= lang('empcode') ?></th>
												<th rowspan="2"><?= lang("name"); ?></th>
												<th rowspan="2"><?= lang("cash_advanced"); ?></th>
												<th colspan="<?= ($additions ? count($additions) : 1)  ?>"><?= lang("addition") ?></th>
												<th colspan="<?= ($deductions ? count($deductions) : 1) ?>"><?= lang("deduction") ?></th>
												<th rowspan="2" style="width: 30px !important; text-align: center;"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
											</tr>
											<tr>
												<?= $th_additions ?>
												<?= $th_deductions ?>
											</tr>
                                        </thead>
                                        <tbody id="dataEmp">
											<?php
												$dataEmp = "";
												if($benefit_items){
													foreach($benefit_items as $benefit_item){
														$base_cash_advanced = 0;
														if($cash_advance = $this->payrolls_model->getEmployeeCashAdvanced($benefit_item->employee_id,'approved')){
															$base_cash_advanced = $cash_advance->cash_advanced;
														}
														$td_addition = "";
														if($additions){
															$emp_additions = false;
															if(json_decode($benefit_item->additions)){
																foreach(json_decode($benefit_item->additions) as $value){
																	$emp_additions[$value->id] = $value->value;
																}
															}
															foreach($additions as $addition){
																$amount = 0;
																if(isset($emp_additions[$addition->id]) && $emp_additions[$addition->id]){
																	$amount = $emp_additions[$addition->id];
																}
																$td_addition .="<td>
																					<input name='addition[".$benefit_item->employee_id."][".$addition->id."]' value='".$amount."' type='text' class='form-control text-right addition'/>
																					<input name='addition_name[".$benefit_item->employee_id."][".$addition->id."]' value='".$addition->name."' type='hidden'/>
																			</td>";

				
															}
														}
														$td_deduction = "";
														if($deductions){
															$emp_duductions = false;
															if(json_decode($benefit_item->deductions)){
																foreach(json_decode($benefit_item->deductions) as $value){
																	$emp_duductions[$value->id] = $value->value;
																}
															}
															foreach($deductions as $deduction){
																$amount = 0;
																if(isset($emp_duductions[$deduction->id]) && $emp_duductions[$deduction->id]){
																	$amount = $emp_duductions[$deduction->id];
																}
																$td_deduction .="<td>
																					<input name='deduction[".$benefit_item->employee_id."][".$deduction->id."]' value='".$amount."' type='text' class='form-control text-right deduction'/>
																					<input name='deduction_name[".$benefit_item->employee_id."][".$deduction->id."]' value='".$deduction->name."' type='hidden'/>
																			</td>";

				
															}
														}
														$dataEmp .= "<tr>
																		<td><input name='employee_id[]' value='".$benefit_item->employee_id."' type='hidden'/>".$benefit_item->empcode."</td>
																		<td>".$benefit_item->lastname." ".$benefit_item->firstname."</td>
																		<td><input value='".$base_cash_advanced."' type='hidden' class='base_cash_advanced'/><input value='".$benefit_item->cash_advanced."' type='text' name='cash_advanced[]' class='form-control cash_advanced text-right'/></td>
																		".$td_addition."
																		".$td_deduction."
																		<td class='text-center'><i class='fa fa-times tip pointer del' title='Remove' style='cursor:pointer'></i></td>
																	</tr>";
													}
												}
												echo $dataEmp;
											?>
										
										</tbody>
                                        <tfoot></tfoot>
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
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $benefit->note), 'class="form-control" id="note" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="fprom-group">
								<?php echo form_submit('edit_benefit', $this->lang->line("submit"), 'id="edit_benefit" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
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
		function getBenefitEmployee(){
			var biller_id = $("#biller").val();
			var position_id = $("#position").val();
			var department_id = $("#department").val();
			var group_id = $("#group").val();
			var month = $("#month").val();
			$.ajax({
				type: "get", 
				async: true,
				url: site.base_url + "payrolls/get_benefit_employees/",
				data : { 
						biller_id : biller_id,
						position_id : position_id,
						department_id : department_id,
						group_id : group_id,
						month : month,
						edit_id : "<?= $benefit->id ?>"
				},
				dataType: "json",
				success: function (data) {
					var dataEmp = "";
					if (data != false) {
						$.each(data, function () {
							var employee_id = this.employee_id;
							dataEmp += "<tr>";
								dataEmp += "<td><input name='employee_id[]' value='"+employee_id+"' type='hidden'/>"+this.empcode+"</td>";
								dataEmp += "<td>"+this.lastname+" "+this.firstname+"</td>";
								dataEmp += "<td><input value='"+this.cash_advanced+"' type='hidden' class='base_cash_advanced'/><input name='cash_advanced[]' value='"+this.cash_advanced+"' type='text' class='form-control cash_advanced text-right'/></td>";
								if(this.approve_additions){
									$.each(this.approve_additions, function () {
										dataEmp += "<td><input name='addition["+employee_id+"]["+this.id+"]' value='"+this.value+"' type='text' class='form-control text-right addition'/><input name='addition_name["+employee_id+"]["+this.id+"]' value='"+this.name+"' type='hidden'/></td>";
									});
								}
								if(this.approve_deductions){
									$.each(this.approve_deductions, function () {
										dataEmp += "<td><input name='deduction["+employee_id+"]["+this.id+"]' value='"+this.value+"' type='text' class='form-control text-right deduction'/><input name='deduction_name["+employee_id+"]["+this.id+"]' value='"+this.name+"' type='hidden'/></td>";
									});
								}
							dataEmp += "<td class='text-center'><i class='fa fa-times tip pointer del' title='Remove' style='cursor:pointer'></i></td>";	
							dataEmp += "</tr>";
						});
					}
					$("#dataEmp").html(dataEmp);
				}
			});
		}
		
		
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
			getBenefitEmployee();
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
			getBenefitEmployee();
		});
		$(document).on("change", "#group, #position, #month", function () {	
			getBenefitEmployee();
		});
		
		$(document).on("click", ".del", function () {		
			var row = $(this).closest('tr');
			row.remove();
		});
		
		
		var old_value;
		$(document).on("focus", '.cash_advanced', function () {
			old_value = $(this).val();
		}).on("change", '.cash_advanced', function () {
			var row = $(this).closest('tr');
			var base_cash_advanced = row.find(".base_cash_advanced").val();
			if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0 || parseFloat($(this).val()) > base_cash_advanced) {
				$(this).val(old_value);
				bootbox.alert(lang.unexpected_value);
				return;
			}
		});  
		
		var old_value;
		$(document).on("focus", '.addition, .deduction', function () {
			old_value = $(this).val();
		}).on("change", '.addition, .deduction', function () {
			var row = $(this).closest('tr');
			if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
				$(this).val(old_value);
				bootbox.alert(lang.unexpected_value);
				return;
			}
		});

	});
</script>