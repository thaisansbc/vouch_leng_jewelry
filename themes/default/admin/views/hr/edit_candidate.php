<ul id="myTab" class="nav nav-tabs">
	<li><a href="#basic" class="tab-grey"  ><?= lang('basic_information') ?></a></li>
	<!-- <li><a href="#working" class="tab-grey" ><?= lang('working_information') ?></a></li> -->
	<li><a href="#qualification" class="tab-grey"><?= lang('qualification') ?></a></li>
	<li><a href="#work" class="tab-grey"><?= lang('work_experience') ?></a></li>
	<li><a href="#document" class="tab-grey"><?= lang('document') ?></a></li> 
</ul>
<div class="tab-content">
	<div id="basic" class="tab-pane fade in">
		<div class="box">
			<div class="box-header">
				<h2 class="blue">
					<i class="fa-fw fa fa-user"></i>
					<?= lang('basic_information'); ?>
				</h2>
			</div>
			<div class="box-content">
				<div class="row">
					<div class="col-lg-12">
						<p class="introtext"><?= lang('enter_info'); ?></p>
						<?php 
							$attrib = array('data-toggle' => 'validator', 'role' => 'form');
							echo admin_form_open_multipart("hr/edit_candidate/".$id, $attrib); 
						?>
						<div class="col-lg-10">
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<?php echo lang('first_name_kh', 'first_name_kh'); ?>
										<div class="controls">
											<?php echo form_input('first_name_kh', $row->firstname_kh, 'class="form-control" id="first_name_kh"'); ?>
										</div>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<?php echo lang('last_name_kh', 'last_name_kh'); ?>
										<div class="controls">
											<?php echo form_input('last_name_kh', $row->lastname_kh, 'class="form-control" id="last_name_kh" '); ?>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<?php echo lang('first_name', 'first_name'); ?>
										<div class="controls">
											<?php echo form_input('first_name', $row->firstname, 'class="form-control" id="first_name" required="required""'); ?>
										</div>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<?php echo lang('last_name', 'last_name'); ?>
										<div class="controls">
											<?php echo form_input('last_name', $row->lastname, 'class="form-control" id="last_name" required="required"'); ?>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<?php echo lang('dob', 'dob'); ?>
										<div class="controls">
											<?php echo form_input('dob', $this->bpas->hrsd($row->dob), 'class="form-control date" id="dob" required="required"'); ?>
										</div>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
										<?php echo lang('gender', 'gender'); ?>
										<div class="controls">
											<select class="form-control" name="gender" id="gender" required>
												<option value="male" <?= ($row->gender=="male"?"selected":"") ?> ><?= lang("male") ?></option>
												<option value="female" <?= ($row->gender=="female"?"selected":"") ?> ><?= lang("female") ?></option>
											</select>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<?php echo lang('phone', 'phone'); ?>
										<div class="controls">
											<?php echo form_input('phone', $row->phone, 'class="form-control" id="phone"'); ?>
										</div>
									</div>
								</div>
								
								<div class="col-lg-6">
									<div class="form-group">
										<?php echo lang('email', 'email'); ?>
										<div class="controls">
											<input type="email" id="email" value="<?= $row->email ?>" name="email" class="form-control"/>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-6">		
									<div class="form-group">
										<?php echo lang('nationality', 'nationality'); ?>
										<div class="controls">
											<?php echo form_input('nationality', $row->nationality, 'class="form-control" id="nationality"'); ?>
										</div>
									</div>
								</div>
								<div class="col-lg-6">		
									<div class="form-group">
										<?php echo lang('marital_status', 'marital_status'); ?>
										<div class="controls">
											<select class="form-control" name="marital_status" id="marital_status" required="required">
												<option value="single" <?= ($row->marital_status=="single"?"selected":""); ?> ><?= lang("single") ?></option>
												<option value="married" <?= ($row->marital_status=="married"?"selected":""); ?>  ><?= lang("married") ?></option>
												<option value="widowed" <?= ($row->marital_status=="widowed"?"selected":""); ?> ><?= lang("widowed") ?></option>
												<option value="divoiced" <?= ($row->marital_status=="divoiced"?"selected":""); ?> ><?= lang("divoiced_or_separated") ?></option>
											</select>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<?php echo lang('nric_no', 'nric_no'); ?>
										<div class="controls">
											<?php echo form_input('nric_no', $row->nric_no, 'class="form-control" id="nric_no"'); ?>
										</div>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="form-group">
									  <?= lang("photo", "photo"); ?>
									  <input type="file" data-browse-label="<?= lang('browse'); ?>" name="photo" id="photo" 
										data-show-upload="false" data-show-preview="false" accept="image/*"
										class="form-control file"/>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<?php echo lang('address', 'address'); ?>
										<div class="controls">
											<textarea class="form-control" name="address"><?= $row->address ?></textarea>
										</div>
									</div>
								</div>	
								<div class="col-lg-6">
									<div class="form-group">
										<?php echo lang('note', 'note'); ?>
										<div class="controls">
											<textarea class="form-control" name="note"><?= $row->note ?></textarea>
										</div>
									</div>
								</div>	
							</div>
							<div class="form-group">
								<div class="controls">
									<input type="submit" value="<?= lang("update") ?>" name="update" class="btn btn-success" />
									<input type="submit" value="<?= lang("update_close") ?>" name="update_close" class="btn btn-danger" />
								</div>
							</div>
						</div>
						<div class="col-lg-2">
							<div style="margin-top: 30px;">
								<?php if(!$row->photo){ ?>
									<img alt="" src="<?= base_url('assets/images/male.png') ?>" class="avatar">	                
								<?php }else{
									echo "<img src='".base_url('assets/uploads/'.$row->photo)."' class='avatar' />";
								} ?>
							</div>		
						</div>
						<?php echo form_close(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="qualification" class="tab-pane fade">
		<div class="box">
			<div class="box-header">
				<h2 class="blue">
					<i class="fa-fw fa fa-newspaper-o"></i>
					<?= lang('qualification'); ?>
				</h2>
				<div class="box-icon">
					<ul class="btn-tasks">
						<li class="dropdown">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip"  data-placement="left" title="<?= lang("actions") ?>"></i></a>
							<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
								<li><a href="<?php echo admin_url('hr/add_qualification/'.$id); ?>" data-toggle="modal" data-target="#myModal" ><i class="fa fa-plus-circle"></i> <?= lang('add_qualification') ?></a></li>
						   </ul>
						</li>
					</ul>
				</div>
			</div>
			
			<div class="box-content">
				<div class="row">
					<div class="col-lg-12">
						<p class="introtext"><?= lang('enter_info'); ?></p>
					</div>
					<script type="text/javascript">
						$(document).ready(function () {
							'use strict';
							var oTable = $('#qualificationTable').dataTable({
								"aaSorting": [[1, "asc"], [3, "asc"]],
								"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
								"iDisplayLength": <?= $Settings->rows_per_page ?>,
								'bProcessing': true, 
								'bServerSide': true,
								'sAjaxSource': '<?= admin_url('hr/getQualification?employee_id='.$id) ?>',
								'fnServerData': function (sSource, aoData, fnCallback) {
									aoData.push({
										"name": "<?= $this->security->get_csrf_token_name() ?>",
										"value": "<?= $this->security->get_csrf_hash() ?>"
									});
									$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
								},
								"aoColumns": [
								{"sClass" : "left"}, 
								{"sClass" : "left"}, 
								{"sClass" : "left"}, 
								{"sClass" : "cenleftter"},
								{"sClass" : "center", "mRender" : fsd},
								{"sClass" : "center", "mRender" : fsd},
								{"sClass" : "left"},
								{"sClass" : "left"},
								{"sClass" : "center", "mRender" : attachment},
								{"bSortable": false, "sClass" : "center"}]
							}).fnSetFilteringDelay().dtFilter([
								{column_number: 0, filter_default_label: "[<?=lang('certificate');?>]", filter_type: "text", data: []},
								{column_number: 1, filter_default_label: "[<?=lang('major');?>]", filter_type: "text", data: []},
								{column_number: 2, filter_default_label: "[<?=lang('school');?>]", filter_type: "text", data: []},
								{column_number: 3, filter_default_label: "[<?=lang('degree');?>]", filter_type: "text", data: []},
								{column_number: 4, filter_default_label: "[<?=lang('start_date');?>]", filter_type: "text", data: []},
								{column_number: 5, filter_default_label: "[<?=lang('end_date');?>]", filter_type: "text", data: []},
								{column_number: 6, filter_default_label: "[<?=lang('language');?>]", filter_type: "text", data: []},
								{column_number: 7, filter_default_label: "[<?=lang('description');?>]", filter_type: "text", data: []},
								{column_number: 8, filter_default_label: "[<?=lang('attachment');?>]", filter_type: "text", data: []},
							], "footer");
						
						});
					</script>
					<div class="col-lg-12">
						<div class="table-responsive">
							<table id="qualificationTable" cellpadding="0" cellspacing="0" border="0"
								   class="table table-bordered table-hover table-striped dataTable">
								<thead>
								<tr>
									<th style="width:150px;"><?php echo lang('certificate'); ?></th>
									<th style="width:150px;"><?php echo lang('major'); ?></th>
									<th style="width:150px;"><?php echo lang('school'); ?></th>
									<th style="width:150px;"><?php echo lang('degree'); ?></th>
									<th style="width:150px;"><?php echo lang('start_date'); ?></th>
									<th style="width:150px;"><?php echo lang('end_date'); ?></th>
									<th style="width:150px;"><?php echo lang('language'); ?></th>
									<th style="width:250px;"><?php echo lang('description'); ?></th>
									<th style="width:60px;"><i class="fa fa-chain"></i></th>
									<th style="width:60px;"><?php echo lang('action'); ?></th>
								</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
										<th class="center"><i class="fa fa-chain"></i></th>
										<th></th>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div id="work" class="tab-pane fade">
		<div class="box">
			<div class="box-header">
				<h2 class="blue">
					<i class="fa-fw fa fa-newspaper-o"></i>
					<?= lang('work_experience'); ?>
				</h2>
				<div class="box-icon">
					<ul class="btn-tasks">
						<li class="dropdown">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip"  data-placement="left" title="<?= lang("actions") ?>"></i></a>
							<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
								<li><a href="<?php echo admin_url('hr/add_working_history/'.$id); ?>" data-toggle="modal" data-target="#myModal" ><i class="fa fa-plus-circle"></i> <?= lang('add_working_history') ?></a></li>
						   </ul>
						</li>
					</ul>
				</div>
			</div>
			
			<div class="box-content">
				<div class="row">
					<div class="col-lg-12">
						<p class="introtext"><?= lang('enter_info'); ?></p>
					</div>
					<script type="text/javascript">
						$(document).ready(function () {
							'use strict';
							var oTable = $('#workTable').dataTable({
								"aaSorting": [[1, "asc"], [3, "asc"]],
								"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
								"iDisplayLength": <?= $Settings->rows_per_page ?>,
								'bProcessing': true, 
								'bServerSide': true,
								'sAjaxSource': '<?= admin_url('hr/getWorkingHistory?employee_id='.$id) ?>',
								'fnServerData': function (sSource, aoData, fnCallback) {
									aoData.push({
										"name": "<?= $this->security->get_csrf_token_name() ?>",
										"value": "<?= $this->security->get_csrf_hash() ?>"
									});
									$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
								},
								"aoColumns": [
								{"sClass" : "left"}, 
								{"sClass" : "left"}, 
								{"sClass" : "center", "mRender" : fsd},
								{"sClass" : "center", "mRender" : fsd},
								{"sClass" : "left"},
								{"sClass" : "center", "mRender" : attachment},
								{"bSortable": false, "sClass" : "center"}]
							}).fnSetFilteringDelay().dtFilter([
								{column_number: 0, filter_default_label: "[<?=lang('company');?>]", filter_type: "text", data: []},
								{column_number: 1, filter_default_label: "[<?=lang('position');?>]", filter_type: "text", data: []},
								{column_number: 2, filter_default_label: "[<?=lang('start_date');?>]", filter_type: "text", data: []},
								{column_number: 3, filter_default_label: "[<?=lang('end_date');?>]", filter_type: "text", data: []},
								{column_number: 4, filter_default_label: "[<?=lang('description');?>]", filter_type: "text", data: []},
							], "footer");
						});
					</script>
					<div class="col-lg-12">
						<div class="table-responsive">
							<table id="workTable" cellpadding="0" cellspacing="0" border="0"
								   class="table table-bordered table-hover table-striped dataTable">
								<thead>
								<tr>
									<th style="width:150px;"><?php echo lang('company'); ?></th>
									<th style="width:150px;"><?php echo lang('position'); ?></th>
									<th style="width:150px;"><?php echo lang('start_date'); ?></th>
									<th style="width:150px;"><?php echo lang('end_date'); ?></th>
									<th style="width:250px;"><?php echo lang('description'); ?></th>
									<th style="width:60px;"><i class="fa fa-chain"></i></th>
									<th style="width:60px;"><?php echo lang('action'); ?></th>
								</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
									</tr>
								</tbody>
								<tfoot class="dtFilter">
									<tr class="active">
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th class="center"><i class="fa fa-chain"></i></th>
										<th></th>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	
	<div id="document" class="tab-pane fade">
		<div class="box">
			<div class="box-header">
				<h2 class="blue">
					<i class="fa-fw fa fa-newspaper-o"></i>
					<?= lang('document'); ?>
				</h2>
				<div class="box-icon">
					<ul class="btn-tasks">
						<li class="dropdown">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip"  data-placement="left" title="<?= lang("actions") ?>"></i></a>
							<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
								<li><a href="<?php echo admin_url('hr/add_document/'.$id); ?>" data-toggle="modal" data-target="#myModal" ><i class="fa fa-plus-circle"></i> <?= lang('add_document') ?></a></li>
						   </ul>
						</li>
					</ul>
				</div>
			</div>
			<div class="box-content">
				<div class="row">
					<div class="col-lg-12">
						<p class="introtext"><?= lang('enter_info'); ?></p>
					</div>
					<script type="text/javascript">
						$(document).ready(function () {
							'use strict';
							var oTable = $('#documentTable').dataTable({
								"aaSorting": [[1, "asc"], [3, "asc"]],
								"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
								"iDisplayLength": <?= $Settings->rows_per_page ?>,
								'bProcessing': true, 
								'bServerSide': true,
								'sAjaxSource': '<?= admin_url('hr/getDocuments?employee_id='.$id) ?>',
								'fnServerData': function (sSource, aoData, fnCallback) {
									aoData.push({
										"name": "<?= $this->security->get_csrf_token_name() ?>",
										"value": "<?= $this->security->get_csrf_hash() ?>"
									});
									$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
								},
								"aoColumns": [
								{"sClass" : "center"}, 
								{"sClass" : "center"},
								
								{"sClass" : "center", "mRender" : fld},
								{"sClass" : "center", "mRender" : fsd},
								{"sClass" : "center"},
								{"sClass" : "center", "mRender" : attachment}, 
								{"bSortable": false, "sClass" : "center"}]
							}).fnSetFilteringDelay().dtFilter([
								{column_number: 0, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
								{column_number: 1, filter_default_label: "[<?=lang('description');?>]", filter_type: "text", data: []},
								{column_number: 2, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
								{column_number: 3, filter_default_label: "[<?=lang('created_date');?>]", filter_type: "text", data: []},
							], "footer");
						});
					</script>
					<div class="col-lg-12">
						<div class="table-responsive">
							<table id="documentTable" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped dataTable">
								<thead>
								<tr>
									<th style="width:200px;"><?php echo lang('name'); ?></th>
									<th style="width:250px;"><?php echo lang('description'); ?></th>
									<th style="width:250px;"><?php echo lang('created_date'); ?></th>
									<th style="width:250px;"><?php echo lang('expired_date'); ?></th>
									<th style="width:250px;"><?php echo lang('created_by'); ?></th>
									<th style="width:20px;"><i class="fa fa-chain"></i></th>
									<th style="width:60px;"><?php echo lang('action'); ?></th>
								</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="7" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
</div>
<script type="text/javascript">
    $(function(){
		
		$('#update').click(function () {
            $(window).unbind('beforeunload');            
        });
		
		$('.timepicker').datetimepicker({
			format: 'hh:ii',
			startView: 0
		});
		
		getDepPos();
		
		$('#biller_id').on('change',function(){
			getDepPos();
		});
		$('#department_id').on('change',function(){
			getGroup();
		});
		
		function getDepPos(){
			var biller = $("#biller_id").val();
			$.ajax({
				url : "<?= admin_url("hr/get_dep_pos") ?>",
				type : "GET",
				dataType : "JSON",
				data : { 
					biller : biller,
					department : "<?= $working_info->department_id ?>",
					position : "<?= $working_info->position_id ?>"
				},
				success : function(data){
					$("#department_id").html(data.department_opt);
					$("#department_id").select2();
					$("#position_id").html(data.position_opt);
					$("#position_id").select2();
					getGroup();
				}
			});
		}

		function getGroup(){
			var department_id = $("#department_id").val();
			$.ajax({
				url : "<?= admin_url("hr/get_group") ?>",
				type : "GET",
				dataType : "JSON",
				data : { 
					department_id : department_id,
					group : "<?= $working_info->group_id ?>"
				},
				success : function(data){
					$("#group_id").html(data.group_opt);
					$("#group_id").select2();
				}
			});
		}
		
		resigned();
		
		function resigned(){
			var cstatus = $("#status option:selected").val();
			if(cstatus == "inactive"){
				$(".form-resigned_date").show();
			}else{
				$(".form-resigned_date").hide();
			}
		}
		$("#status").on("change",resigned);
		
		$("#net_salary, #salary_tax, #monthly_rate").on("change",function(){
			var net_salary 	 = $("#net_salary").val() - 0;
			var salary_tax 	 = $("#salary_tax").val() - 0;
			var monthly_rate = $("#monthly_rate").val() - 0;
			var currency = $("#currency option:selected").val();
			
			$.ajax({
				url : "<?= admin_url('hr/salary_tax') ?>",
				dataType : "JSON",
				type : "GET",
				data : {
					net_salary	 : net_salary,
					salary_tax 	 : salary_tax,
					monthly_rate : monthly_rate,
					employee_id  : "<?= $id ?>"
				},
				success : function(data){
					var base_salary_tax = (data.base_salary_tax?data.base_salary_tax:0);
					var tax_on_salary = (data.tax_on_salary?data.tax_on_salary:0);
					var monthly_rate = (net_salary + tax_on_salary);
			
					$("#tax_rate").val(formatDecimal(tax_on_salary));
					$("#monthly_rate").val(formatDecimal(monthly_rate));
				}
			})
		});

		$("#self_tax").on("change",function(){
			var tax_rate 	 = $("#tax_rate").val() - 0;
			var salary_tax 	 = $("#salary_tax").val() - 0;
			var self_tax = $(this).val();
			if(self_tax == 1){
				var net_salary = salary_tax - tax_rate;
				var monthly_rate = net_salary + tax_rate;
				$("#deduct_net_salary").text(" ( "+formatDecimal(net_salary)+ " ) ");
				$("#monthly_rate").val(formatDecimal(monthly_rate));
				
			}else{
				var net_salary = salary_tax;
				var monthly_rate = salary_tax + tax_rate;
				$("#deduct_net_salary").text(" ( "+formatDecimal(net_salary)+ " ) ");
				$("#monthly_rate").val(formatDecimal(monthly_rate));
			}
		});

    });
</script>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
       $("#nssf").on("click", nssf);
	   nssf();
		function nssf(){
			var nssf = $("#nssf option:selected").val();
			if(nssf == 0){
				$(".nssf_number").hide();
				
			}else{
				$(".nssf_number").show();
				
			}	
		}
    });
</script>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
       $("#book_type").on("click", book_type);
	   book_type();
		function book_type(){
			var book_type = $("#book_type option:selected").val();
	
			if(book_type == 'workbook'){
				$(".work_permit__number").hide();
				$(".workbook_number").show();
			}else{
				$(".work_permit__number").show();
				$(".workbook_number").hide();
			}
		}
    });
</script>



