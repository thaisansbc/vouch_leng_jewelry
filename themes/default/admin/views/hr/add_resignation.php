<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_resignation'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
					$attrib = array('data-toggle' => 'validator', 'role' => 'form');
					echo admin_form_open_multipart("hr/add_resignation", $attrib);
                ?>
                <div class="row">
					<div class="col-md-12">
						<div class="panel panel-warning">
							<div class="panel-heading"><?= lang('please_select_these_before_adding_employee') ?></div>
							<div class="panel-body" style="padding: 5px;">
								<?php if ($Owner || $Admin || $GP['hr-id_cards_date']) { ?>
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("date", "date"); ?>
											<?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="date" required="required"'); ?>
										</div>
									</div>
								<?php } ?>


						
								
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
								<div class="col-md-4">
									<?= lang("employee", "employee"); ?>
									<div class="employee_box form-group">
										<?php
											$em[""] = lang("select")." ".lang("employee");
											echo form_dropdown('employee', $em, (isset($_POST['employee']) ? $_POST['employee'] : ''), 'id="employee" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("employee") . '"  class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<?= lang("notice_date", "notice_date"); ?>
										<?php echo form_input('notice_date', (isset($_POST['notice_date']) ? $_POST['notice_date'] : ""), 'class="form-control input-tip date" required="required"'); ?>
									</div>
								</div>
								
								<div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('last_day', 'last_day'); ?>
                                        <?php echo form_input('resignation_date', (isset($_POST['resignation_date']) ? $_POST['resignation_date'] : ""), 'class="form-control input-tip date" required="required"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                	<div class="form-group">
				            			<?php echo lang('returned_company_asset', 'assets'); ?>
				                        <div class="row">
				                            <div class="col-xs-6 col-sm-2">
				                                <input type="radio" class="checkbox type" value="1" name="returned_asset" <?= $this->input->post('returned_asset')==1 ? 'checked="checked"' : ''; ?> required="required">
				                                <label for="full" class="padding05">
				                                    <?= lang('yes'); ?>
				                                </label>
				                            </div>
				                            <div class="col-xs-6 col-sm-2">
				                                <input type="radio" class="checkbox type" value="0" name="returned_asset" <?= $this->input->post('returned_asset')==0 ? 'checked="checked"' : ''; ?>>
				                                <label for="partial" class="padding05">
				                                    <?= lang('no'); ?>
				                                </label>
				                            </div>
				                        </div>
				                    </div>
                                </div>
							</div>
						</div>
                    </div>
                    <div class="col-lg-12">
                   
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
								<?php echo form_submit('add_id_card', $this->lang->line("submit"), 'id="add_id_card" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
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
		<?php if ($Owner || $Admin || $GP['hr-id_cards_date']) { ?>
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
	
		
		function translateGender(x){
			if(x == "male"){
				return "<?= lang('male') ?>";
			}else{
				return "<?= lang('female') ?>";
			}
		}
		
		function getNameCardEmployee(){
			var biller_id = $("#biller").val();
			var position_id = $("#position").val();
			var department_id = $("#department").val();
			var group_id = $("#group").val();
			var employee_id = $("#employee").val();
			$.ajax({
				type: "get", 
				async: true,
				url: site.base_url + "hr/get_employees/",
				data : { 
						biller_id : biller_id,
						position_id : position_id,
						department_id : department_id,
						group_id : group_id,
				},
				dataType: "json",
				success: function (data) {
					var dataEmp = "";
					var employee_sel = "<select class='form-control' id='employee' name='employee'><option value=''><?= lang('select').' '.lang('employee') ?></option>";
					if (data != false) {
						$.each(data, function () {
							employee_sel += "<option value='"+this.id+"'>"+this.lastname+" "+this.firstname+" ("+this.empcode+")</option>";
						});
								
						$.each(data, function () {
							
							if(employee_id == null || employee_id == "" || employee_id == this.id){
								dataEmp += "<tr>";
									dataEmp += "<td><input name='employee_id[]' value='"+this.id+"' type='hidden'/>"+this.empcode+"</td>";
									dataEmp += "<td>"+this.lastname+" "+this.firstname+"</td>";
									dataEmp += "<td>"+translateGender(this.gender)+"</td>";
								dataEmp += "<td class='text-center'><i class='fa fa-times tip pointer del' title='Remove' style='cursor:pointer'></i></td>";	
								dataEmp += "</tr>";
							}
						});
						employee_sel += "</select>"
					}
					$("#dataEmp").html(dataEmp);
					$(".employee_box").html(employee_sel);
					$("#employee").val(employee_id);
					$('select').select2();
				}
			});
		}
		
		
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
			getNameCardEmployee();
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
			getNameCardEmployee();
		});
		$(document).on("change", "#group, #position, #employee", function () {	
			getNameCardEmployee();
		});
		
		$(document).on("click", ".del", function () {		
			var row = $(this).closest('tr');
			row.remove();
		});
		
	});
</script>