<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('edit_payment'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
					$attrib = array('data-toggle' => 'validator', 'role' => 'form');
					echo admin_form_open_multipart("payrolls/edit_payment/".$payment->id, $attrib);
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
											<?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->bpas->hrld($payment->date)), 'class="form-control input-tip datetime" id="date" required="required"'); ?>
										</div>
									</div>
								 <?php } ?>
								<div class="col-md-4">
									<div class="form-group">
										<?= lang("month", "month"); ?>
										<?php echo form_input('month', (isset($_POST['month']) ? $_POST['month'] : $payment->month."/".$payment->year), 'class="form-control month" required="required" id="month"'); ?>
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
										echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $payment->biller_id), 'id="biller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
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
											echo form_dropdown('position', $ps, (isset($_POST['position']) ? $_POST['position'] : $payment->position_id), 'id="position" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("position") . '"  class="form-control input-tip select" style="width:100%;"');
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
											echo form_dropdown('department', $dp, (isset($_POST['department']) ? $_POST['department'] : $payment->department_id), 'id="department" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("department") . '"  class="form-control input-tip select" style="width:100%;"');
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
											echo form_dropdown('group', $gp, (isset($_POST['group']) ? $_POST['group'] : $payment->group_id), 'id="group" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("group") . '"  class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
								<?php if($Settings->accounting == 1){ ?>
									<div class="col-md-4">
										<?= lang("paying_from", "paying_from"); ?>
										<div class="form-group">
											<select name="paying_from" class="form-control select" id="paying_from" style="width:100%">
												<?= $cash_account ?>
											</select>
										</div>
									</div>
								<?php } ?>
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
												<th><?= lang("tax_payment") ?></th>
												<th><?= lang("net_salary") ?></th>
												<th><?= lang("pay_tax") ?></th>
												<th><?= lang("pay_salary") ?></th>
												<th style="width: 30px !important; text-align: center;"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
											</tr>
                                        </thead>
                                        <tbody id="dataEmp">
											<?php
												$dataEmp = "";
												if($payment_items){
													foreach($payment_items as $payment_item){
														$dataEmp .= "<tr>
																		<td><input name='employee_id[]' value='".$payment_item->employee_id."' type='hidden'/>".$payment_item->empcode."</td>
																		<td>".$payment_item->lastname." ".$payment_item->firstname."</td>
																		<td class='text-right'><input class='tax_payment' value='".$this->bpas->formatDecimal($payment_item->tax_payment + $payment_item->tax_paid)."' type='hidden'/>".$this->bpas->formatDecimal($payment_item->tax_payment + $payment_item->tax_paid)."</td>
																		<td class='text-right'><input class='net_salary' value='".$this->bpas->formatDecimal($payment_item->net_salary + $payment_item->salary_paid)."' type='hidden'/>".$this->bpas->formatDecimal($payment_item->net_salary + $payment_item->salary_paid)."</td>
																		<td class='text-center'><input class='form-control text-right pay_tax' name='pay_tax[]' value='".$this->bpas->formatDecimal($payment_item->tax_paid)."' type='text'/></td>
																		<td class='text-center'><input class='form-control text-right pay_salary' name='pay_salary[]' value='".$this->bpas->formatDecimal($payment_item->salary_paid)."' type='text'/></td>
																		<td class='text-center'><i class='fa fa-times tip pointer del' title='Remove' style='cursor:pointer'></i></td>
																	<tr>";	
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
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $payment->note), 'class="form-control" id="note" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="fprom-group">
								<?php echo form_submit('edit_payment', $this->lang->line("submit"), 'id="edit_payment" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
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