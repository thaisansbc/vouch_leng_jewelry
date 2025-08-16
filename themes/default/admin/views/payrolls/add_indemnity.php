<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_indemnity'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
					$attrib = array('data-toggle' => 'validator', 'role' => 'form');
					echo admin_form_open_multipart("payrolls/add_indemnity", $attrib);
                ?>
                <div class="row">
					<div class="col-md-12">
						<div class="panel panel-warning">
							<div class="panel-heading"><?= lang('please_select_these_before_adding_employee') ?></div>
							<div class="panel-body" style="padding: 5px;">
								<div class="col-md-3">
									<?= lang("biller", "biller"); ?>
									<div class="form-group">
										<?php
										$bl[""] = "";
										foreach ($billers as $biller) {
											$bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
										}
										echo form_dropdown('biller', $bl, $biller_id, 'id="biller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
								
								<div class="col-md-3">
									<div class="form-group">
										<?= lang("date", "date"); ?>
										<?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="date" required="required"'); ?>
									</div>
								</div>
								
								<div class="col-md-3">
									<div class="form-group">
										<?= lang("year", "year"); ?>
										<?php echo form_input('year', (isset($_POST['year']) ? $_POST['year'] : date("Y")), 'class="form-control year" required="required" id="year"'); ?>
									</div>
								</div>

								
								<div class="col-md-3">
									<?= lang("department", "department"); ?>
									<div class="department_box form-group">
										<?php
											$dp[""] = lang("select")." ".lang("department");
											if($departments){
												foreach ($departments as $department) {
													$dp[$department->id] = $department->name;
												}
											}
											echo form_dropdown('department', $dp, (isset($_POST['department']) ? $_POST['department'] : ""), 'id="department" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("department") . '"  class="form-control input-tip select" style="width:100%;"');
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
											echo form_dropdown('position', $ps, (isset($_POST['position']) ? $_POST['position'] : ""), 'id="position" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("position") . '"  class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
								
								<div class="col-sm-3">
									<div class="form-group">
										<?= lang("paying_by", "paid_by_1"); ?>
										<select name="paid_by" id="paid_by_1" class="form-control paid_by">
											<?= $this->bpas->cash_opts(false,true,false,true); ?>
										</select>
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
												<th><?= lang('empcode') ?></th>
												<th><?= lang("name"); ?></th>
												<th><?= lang("position"); ?></th>
												<th><?= lang("department"); ?></th>
												<th><?= lang("group"); ?></th>
												<th><?= lang("employee_date"); ?></th>
												<th><?= lang("basic_salary"); ?></th>
												<th><?= lang("al_day"); ?></th>
												<th><?= lang("al_amount"); ?></th>
												<th style="width: 30px !important; text-align: center;"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
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
								<?php echo form_submit('add_al_compensate', $this->lang->line("submit"), 'id="add_al_compensate" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
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
		
		function get_al_compensate_employee(){
			var biller_id = $("#biller").val();
			var position_id = $("#position").val();
			var department_id = $("#department").val();
			var group_id = $("#group").val();
			var year = $("#year").val();
			if(biller_id && year){
				$.ajax({
					type: "get", 
					async: true,
					url: site.base_url + "payrolls/get_al_compensate_employees/",
					data : { 
							biller_id : biller_id,
							position_id : position_id,
							department_id : department_id,
							group_id : group_id,
							year : year,
					},
					dataType: "json",
					success: function (data) {
						var dataEmp = "";
						if (data != false) {
							$.each(data, function () {
								if(this.al_amount > 0){
									var employee_id = this.employee_id;
									dataEmp += "<tr>";
										dataEmp += "<td><input name='employee_id[]' value='"+employee_id+"' type='hidden'/>"+this.empcode+"</td>";
										dataEmp += "<td>"+this.lastname+" "+this.firstname+"</td>";
										dataEmp += "<td><input name='position_id[]' value='"+this.position_id+"' type='hidden'/>"+this.position+"</td>";
										dataEmp += "<td><input name='department_id[]' value='"+this.department_id+"' type='hidden'/>"+this.department+"</td>";
										dataEmp += "<td><input name='group_id[]' value='"+this.group_id+"' type='hidden'/>"+this.group+"</td>";
										dataEmp += "<td class='text-center'><input name='employee_date[]' value='"+this.employee_date+"' type='hidden'/>"+fsd(this.employee_date)+"</td>";
										dataEmp += "<td class='text-right'><input name='basic_salary[]' value='"+this.basic_salary+"' type='hidden'/>"+currencyFormat(this.basic_salary)+"</td>";	
										dataEmp += "<td class='text-right'><input name='al_day[]' value='"+this.al_day+"' type='hidden'/>"+formatDecimal(this.al_day)+"</td>";
										dataEmp += "<td class='text-right'><input name='al_amount[]' value='"+this.al_amount+"' type='hidden'/>"+currencyFormat(this.al_amount)+"</td>";	
									dataEmp += "<td class='text-center'><i class='fa fa-times tip pointer del' title='Remove' style='cursor:pointer'></i></td>";	
									dataEmp += "</tr>";
								}
							});
						}
						$("#dataEmp").html(dataEmp);
					}
				});
			}
		}
		
		get_al_compensate_employee();
		
		$(document).on("change", "#biller", function () {	
			var biller_id = $(this).val();
			if(biller_id){
				window.location = "<?= admin_url("payrolls/add_indemnity/") ?>"+biller_id;
			}
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
					get_al_compensate_employee();
				}
			});
			
		});
		
		$(document).on("change", "#group, #position, #year", function () {	
			get_al_compensate_employee();
		});

		
		$(document).on("click", ".del", function () {		
			var row = $(this).closest('tr');
			row.remove();
		});
		
		

	});
</script>