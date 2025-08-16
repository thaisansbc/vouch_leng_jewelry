<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_contribution_payment'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
					$attrib = array('data-toggle' => 'validator', 'role' => 'form');
					echo admin_form_open_multipart("payrolls/add_contribution_payment/".$salary->id, $attrib);
                ?>
                <div class="row">
					<div class="col-md-12">
						<div class="panel panel-warning">
							<div class="panel-heading"><?= lang('please_select_these_before_adding_employee') ?></div>
							<div class="panel-body" style="padding: 5px;">
								<?php if ($Owner || $Admin || $GP['payrolls-payments_date']) { ?>
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("date", "date"); ?>
											<?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="date" required="required"'); ?>
										</div>
									</div>
								<?php } ?>
								<div class="col-md-4">
									<div class="form-group">
										<?= lang("month", "month"); ?>
										<?php echo form_input('month', (isset($_POST['month']) ? $_POST['month'] : (isset($salary) && $salary ? $salary->month.'/'.$salary->year : date("m/Y"))), 'class="form-control" required="required" readonly'); ?>
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
										echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : (isset($salary) && $salary ? $salary->biller_id : '')), 'id="biller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
								<div class="col-md-4 hide">
									<?= lang("position", "position"); ?>
									<div class="position_box form-group">
										<?php
											$ps[""] = lang("select")." ".lang("position");
											if(isset($positions) && $positions){		
												foreach ($positions as $position) {
													$ps[$position->id] = $position->name;
												}
											}
											echo form_dropdown('position', $ps, (isset($_POST['position']) ? $_POST['position'] : (isset($salary) && $salary ? $salary->position_id : '')), 'id="position" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("position") . '"  class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
								<div class="col-md-4">
									<?= lang("department", "department"); ?>
									<div class="department_box form-group">
										<?php
											$dp[""] = lang("select")." ".lang("department");
											if(isset($departments) && $departments){	
												foreach ($departments as $department) {
													$dp[$department->id] = $department->name;
												}
											}
											echo form_dropdown('department', $dp, (isset($_POST['department']) ? $_POST['department'] : (isset($salary) && $salary ? $salary->department_id : '')), 'id="department" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("department") . '"  class="form-control input-tip select" style="width:100%;"');
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
											echo form_dropdown('group', $gp, (isset($_POST['group']) ? $_POST['group'] : (isset($salary) && $salary ? $salary->group_id : '')), 'id="group" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("group") . '"  class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
								<div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang("nssf_rate", "nssf_rate"); ?>
                                        <div class="input-group">
                                            <?php
                                            $data = $this->site->getcustomfieldByName('nssf_rate');
                                            $rate = $data->description;
                                            echo form_input('nssf_rate',$rate, 'id="nssf_rate" required="required" class="form-control" readonly');
                                            ?>
                                            <div class="input-group-addon no-print" style="padding: 2px 5px;">
                                            	<a href="<?= admin_url('system_settings/edit_custom_field/'.$data->id); ?>" id="add-customer"
                                                    class="external" data-toggle="modal" data-target="#myModal"><i
                                                        class="fa fa-pencil" id="addIcon"></i></a>
                                            </div>
                                        </div>
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
												<th class="hide"><?= lang("tax_payment") ?></th>
												<th><?= lang("net_salary") ?></th>
												<th><?= lang("salary") ?> (Riel)</th>
												<th class="hide"><?= lang("pay_salary") ?></th>

												<th><?= lang("contributory_wage") ?></th>
												<th><?= lang("O.R_scheme") ?></th>
												<th><?= lang("H.C_scheme") ?></th>
												<th><?= lang("total_payment") ?></th>
												<th style="width: 30px !important; text-align: center;"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
											</tr>
										</thead>
                                        <tbody id="dataEmp">
										<?php if(isset($salary_items) && $salary_items){
											$dataEmp = "";
											foreach($salary_items as $salary_item){
												if($salary_item->nssf){
													$tax_payment = $this->bpas->formatDecimal($salary_item->tax_payment - $salary_item->tax_paid);
													$net_salary = $this->bpas->formatDecimal($salary_item->net_salary - $salary_item->salary_paid);

													$net_salary_riel = $net_salary*$rate;

													$nssf_payment =$this->site->getNSSFPayment($salary_item->employee_id,$net_salary_riel);
													$total_conpayment = $nssf_payment['contributory_or']+$nssf_payment['contributory_hc'];
													$dataEmp .= "<tr>
														<td><input name='employee_id[]' value='".$salary_item->employee_id."' type='hidden'/>".$salary_item->empcode."</td>
														<td>".$salary_item->lastname." ".$salary_item->firstname."</td>
														<td class='text-right hide'><input class='tax_payment' value='".$tax_payment."' type='hidden'/>".$tax_payment."</td>
														<td class='text-right'><input class='net_salary' value='".$net_salary."' type='hidden'/>".$net_salary."</td>
														<td class='text-center hide'>".$tax_payment."</td>
														<td class='text-center hide'>".$net_salary."</td>
														<td class='text-center'>".$this->bpas->formatMoney($net_salary_riel)."</td>

														<td class='text-center'>
															<input name='contributory_wage[]' value='".$nssf_payment['contributory_wage']."' type='hidden'/>
															".$this->bpas->formatMoney($nssf_payment['contributory_wage'])."</td>
														<td class='text-center'>
															<input name='contributory_or[]' value='".$nssf_payment['contributory_or']."' type='hidden'/>
														".$this->bpas->formatMoney($nssf_payment['contributory_or'])."</td>
														<td class='text-center'>
															<input name='contributory_hc[]' value='".$nssf_payment['contributory_hc']."' type='hidden'/>
															".$this->bpas->formatMoney($nssf_payment['contributory_hc'])."</td>
														<td class='text-center'>".$this->bpas->formatMoney($total_conpayment)."</td>
														<td class='text-center'><i class='fa fa-times tip pointer del' title='Remove' style='cursor:pointer'></i></td>
													<tr>";	
												}
											}
											echo $dataEmp;
										} ?>
										
										</tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 hide">
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
								<?php echo form_submit('add_payment', $this->lang->line("submit"), 'id="add_payment" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
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
		$('#biller,#position,#department,#group').attr("style", "pointer-events: none;");

		<?php if ($Owner || $Admin || $GP['payrolls-payments_date']) { ?>
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
		function getPaymentEmployee(){
			var biller_id = $("#biller").val();
			var position_id = $("#position").val();
			var department_id = $("#department").val();
			var group_id = $("#group").val();
			var month = $("#month").val();
			$.ajax({
				type: "get", 
				async: true,
				url: site.base_url + "payrolls/get_payment_employees/",
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
								dataEmp += "<td><input name='employee_id[]' value='"+employee_id+"' type='hidden'/>"+this.empcode+"</td>";
								dataEmp += "<td>"+this.lastname+" "+this.firstname+"</td>";
								dataEmp += "<td class='text-right'><input class='tax_payment' value='"+formatDecimal(this.tax_payment)+"' type='hidden'/>"+formatMoney(this.tax_payment)+"</td>";
								dataEmp += "<td class='text-right'><input class='net_salary' value='"+formatDecimal(this.net_salary)+"' type='hidden'/>"+formatMoney(this.net_salary)+"</td>";
								dataEmp += "<td class='text-center'><input class='form-control text-right pay_tax' name='pay_tax[]' value='"+formatDecimal(this.tax_payment)+"' type='text'/></td>";
								dataEmp += "<td class='text-center'><input class='form-control text-right pay_salary' name='pay_salary[]' value='"+formatDecimal(this.net_salary)+"' type='text'/></td>";
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
			getPaymentEmployee();
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
			getPaymentEmployee();
		});
		$(document).on("change", "#group, #position, #month", function () {	
			getPaymentEmployee();
		});
		
		$(document).on("click", ".del", function () {		
			var row = $(this).closest('tr');
			row.remove();
		});
		
		var old_value;
		$(document).on("focus", '.pay_tax, .pay_salary', function () {
			old_value = $(this).val();
		}).on("change", '.pay_tax, .pay_salary', function () {
			var row = $(this).closest('tr');
			var tax_payment = row.find('.tax_payment').val() - 0;
			var net_salary = row.find('.net_salary').val() - 0;
			var pay_tax = row.find('.pay_tax').val() - 0;
			var pay_salary = row.find('.pay_salary').val() - 0;
			if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0 || pay_tax > tax_payment || pay_salary > net_salary) {
				$(this).val(old_value);
				bootbox.alert(lang.unexpected_value);
				return;
			}
		});

	});
</script>