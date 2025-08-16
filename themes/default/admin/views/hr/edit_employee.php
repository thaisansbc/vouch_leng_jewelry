<?php
	$biller_id 		= '';
	$department_id 	= '';
	$group_id 		= '';
	$position_id 	= '';
	$policy_id 		= '';
	$employee_type_id = '';
	$working_info_status = '';
	$currency 		= '';
	$working_info_self_tax = '';
	$net_salary 	= '';
	$net_salary 	= '';
	$tax_rate 		= '';
	$salary_tax 	= '';
	$monthly_rate 	= '';
	$tax_rate 		= '';
	$absent_rate 		= '100%';
	$permission_rate 	= '100%';
	$late_early_rate 	= '100%';
	$normal_ot 			= '150%';
	$holiday_ot 		= '200%';
	$weekend_ot 		= '200%';
	$resigned_date 	= '';
	$employee_date 	= date("Y-m-d");
	$emp_addtions 	= false;
	$emp_deductions = false;
	$employee_kpi_type = '';
	$annual_leave 	= 0;
	$special_leave 	= 0;
	$sick_leave 	= 0;
	$other_leave 	= 0;
	$no_seniority 	= 0;
	$attendance_bonus = 10;
	$special_bonus = 2; 
	$tra_acc_allowance = 7;
	$union = 0;
	$pension = 2;
	$payment_type = "monthly";

	if (isset($working_info) && $working_info != false) {
		$biller_id 		= $working_info->biller_id;
		$department_id 	= $working_info->department_id;
		$group_id 		= $working_info->group_id;
		$position_id 	= $working_info->position_id;
		$policy_id 		= $working_info->policy_id;
		$employee_type_id = $working_info->employee_type_id;
		$working_info_status = $working_info->status;
		$currency 		= $working_info->currency;
		$working_info_self_tax = $working_info->self_tax;
		$net_salary 		= $working_info->net_salary;
		$tax_rate 			= $working_info->tax_rate;
		$salary_tax 		= $working_info->salary_tax;
		$monthly_rate 		= $working_info->monthly_rate;
		$tax_rate 			= $working_info->tax_rate;
		$absent_rate 		= $working_info->absent_rate;
		$permission_rate = $working_info->permission_rate;
		$late_early_rate = $working_info->late_early_rate;
		$normal_ot = $working_info->normal_ot_rate;
		$holiday_ot = $working_info->holiday_ot_rate;
		$weekend_ot = $working_info->weekend_ot_rate;
		$resigned_date = $working_info->resigned_date;
		$employee_date = $working_info->employee_date;
		$employee_kpi_type = $working_info->kpi_type;
		$annual_leave 	= $working_info->annual_leave;
		$special_leave 	= $working_info->special_leave;
		$sick_leave 	= $working_info->sick_leave;
		$other_leave 	= $working_info->other_leave;
		$no_seniority 	= $working_info->no_seniority;
		$attendance_bonus = $working_info->attendance_bonus;
		$special_bonus 	= $working_info->special_bonus;
		$tra_acc_allowance = $working_info->tra_acc_allowance;
		$union 			= $working_info->union;
		$pension 		= $working_info->pension;
		$payment_type 	= $working_info->payment_type;
		if(json_decode($working_info->additions)){
			foreach(json_decode($working_info->additions) as $index => $emp_addtion){
				$emp_addtions[$index] = $emp_addtion;
			}
		}
		if(json_decode($working_info->deductions)){
			foreach(json_decode($working_info->deductions) as $index => $emp_deduction){
				$emp_deductions[$index] = $emp_deduction;
			}
		}
		$current_year = date("Y");
		$employee_date_year = ($employee_date ? date("Y", strtotime($employee_date)) : $current_year);  
		if($annual_leave == "" || $annual_leave == 0){
			$annual = $this->hr_model->leaveCategoryByCode("annual_leave");
			if($current_year==$employee_date_year){
				$annual_leave = ($annual->days / 12) * (13 - ($employee_date ? date("m", strtotime($employee_date)) : date("m")));
			}else{
				$annual_leave = $annual->days;
			}
		}
		if($special_leave == "" || $special_leave == 0){
			$special = $this->hr_model->leaveCategoryByCode("special_leave");
			if($current_year==$employee_date_year){
				$special_leave = ($special->days / 12) * (13 - ($employee_date ? date("m", strtotime($employee_date)) : date("m")));
			}else{
				$special_leave = $special->days;
			}
		}
		if($sick_leave == "" || $sick_leave == 0){
			$sick = $this->hr_model->leaveCategoryByCode("sick_leave");
			if($current_year==$employee_date_year){
				$sick_leave = ($sick->days / 12) * (13 - ($employee_date ? date("m", strtotime($employee_date)) : date("m")));
			}else{
				$sick_leave = $sick->days;
			}
		}
		if($other_leave == "" || $other_leave == 0){
			$other = $this->hr_model->leaveCategoryByCode("other_leave");
			if($current_year==$employee_date_year){
				$other_leave = ($other->days / 12) * (13 - ($employee_date ? date("m", strtotime($employee_date)) : date("m")));
			}else{
				$other_leave = $other->days;
			}
		}
		
	}
?>
<ul id="myTab" class="nav nav-tabs">
	<li><a href="#basic" class="tab-grey"  ><?= lang('basic_information') ?></a></li>
	<li><a href="#working" class="tab-grey" ><?= lang('working_information') ?></a></li>
	<li><a href="#on_boarding" class="tab-grey"><?= lang('on_boarding') ?></a></li>
	<li><a href="#login_account" class="tab-grey"><?= lang('login_account') ?></a></li>
	<li><a href="#contract" class="tab-grey"><?= lang('contract') ?></a></li>
	<li><a href="#family" class="tab-grey" ><?= lang('family_information') ?></a></li>
	<li><a href="#qualification" class="tab-grey"><?= lang('qualification') ?></a></li>
	<li><a href="#work" class="tab-grey"><?= lang('work_experience') ?></a></li>
	<li><a href="#bank" class="tab-grey"><?= lang('bank_account') ?></a></li>
	<li><a href="#promotion" class="tab-grey"><?= lang('promotions') ?></a></li>
	<li><a href="#emergency" class="tab-grey"><?= lang('emergency_contacts') ?></a></li>
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
							echo admin_form_open_multipart("hr/edit_employee/".$id, $attrib); 
						?>
						<div class="col-lg-10">
							<?php
								if(isset($last_employee) && $last_employee->empcode){
									$tmp_number = 1;
									$number = preg_replace('/[^0-9]/', '', $last_employee->empcode);
									$letter = preg_replace('/[^^!<>@&\/\sA-Za-z_]/', '', $last_employee->empcode);
									$number = ($tmp_number.$number)+1;
									$number = substr($number, 1);
									$new_empcode = $letter.($number);
								}else{
									$new_empcode = '';
								}
							?>
							<div class="row">
								<div class="col-lg-6">
									<div class="form-group">
										<?php echo lang('empcode', 'empcode'); ?>
										<div class="controls">
											<?php echo form_input('empcode', $row->empcode, 'class="form-control" id="empcode" required="required"'); ?>
										</div>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<?php echo lang('finger_id', 'finger_id'); ?>
												<div class="controls">
													<?php echo form_input('finger_id', $row->finger_id, 'class="form-control" id="finger_id"'); ?>
												</div>
											</div>
										</div>
										<div class="col-lg-6">
											<div class="form-group">
												<?php echo lang('nric_no', 'nric_no'); ?>
												<div class="controls">
													<?php echo form_input('nric_no', $row->nric_no, 'class="form-control" id="nric_no"'); ?>
												</div>
											</div>
										</div>
									</div>
								</div>
						
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
										<?php echo lang('retirement', 'retirement'); ?>
										<div class="controls">
											<?php echo form_input('retirement', $this->bpas->hrsd($row->retirement), 'class="form-control date" id="retirement" required="required"'); ?>
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
					
								<div class="col-lg-6">
									<div class="form-group">
										<?php echo lang('non_resident', 'non_resident'); ?>
										<div class="controls">
											<select class="form-control" name="non_resident" id="non_resident" required="required">
												<option value="0" <?= ($row->non_resident==0?"selected":""); ?> ><?= lang("no") ?></option>
												<option value="1" <?= ($row->non_resident==1?"selected":""); ?>  ><?= lang("yes") ?></option>
											</select>
										</div>
									</div>
								</div>
								
								<div class="col-lg-2">
										<div class="form-group">
											<?php echo lang('book_type', 'book_type'); ?>
											<div class="controls">
												<select class="form-control book_type" name="book_type" id="book_type" >
													<option value="workbook"    <?= ($row->book_type=="workbook"?"selected":"") ?> ><?= lang("workbook") ?></option>
													<option value="work_permit" <?= ($row->book_type=="work_permit"?"selected":"") ?> ><?= lang("work_permit") ?></option>
												</select>
											</div>
										</div>
									</div>
									<div class="col-lg-4">
					                  <div class="form-group workbook_number" >
					                    <?php echo lang('workbook_number', 'workbook_number'); ?>
					                    
											<?php 
											  echo form_input('workbook_number',$row->workbook_number, 'class="form-control" id="workbook_number"'); ?>
									
					                  </div>
					                  <div class="form-group work_permit__number">
					                    <?php echo lang('work_permit_number', 'work_permit_number'); ?>
											<?php echo form_input('work_permit_number',$row->work_permit_number, 'class="form-control" id="work_permit"'); ?>
										
					                  </div>
					            </div>
								<div class="col-lg-2">
				                  <div class="form-group ">
				                    <?php echo lang('nssf', 'nssf'); ?>
				                    <div class="controls">
				                      <select class="form-control" name="nssf" id="nssf" required="required">
				                        <option value="0" <?= ($row->nssf==0?"selected":""); ?> ><?= lang("no") ?></option>
				                        <option value="1" <?= ($row->nssf==1?"selected":""); ?>  ><?= lang("yes") ?></option>
				                      </select>
				                    </div>
				                  </div>
				                </div>
				                <div class="col-lg-4">
				                  <div class="form-group nssf_number">
				                    <?php echo lang('nssf_number', 'nssf_number'); ?>
				                    <div class="controls">
										<?php echo form_input('nssf_number',$row->nssf_number, 'class="form-control" id="nssf_number"'); ?>
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
				                <div class="col-lg-6">		
										<div class="form-group">
											<?php echo lang('type', 'type'); ?>
											<div class="controls">
												<select class="form-control" name="type">
													<option <?= ($row->type == "Worker" ? "selected" : "") ?> value="Worker"><?= lang("Worker") ?></option>
													<option <?= ($row->type == "Staff" ? "selected" : "") ?> value="Staff"><?= lang("Staff") ?></option>
												</select>
											</div>
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
	<div id="on_boarding" class="tab-pane fade">
		<div class="box">
			<div class="box-header">
				<h2 class="blue">
					<i class="fa-fw fa fa-newspaper-o"></i>
					<?= lang('on_boarding'); ?>
				</h2>
				<div class="box-icon">
					<ul class="btn-tasks">
						<li class="dropdown">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip"  data-placement="left" title="<?= lang("actions") ?>"></i></a>
							<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
								<li><a href="<?php echo admin_url('hr/add_on_boarding/'.$id); ?>" data-toggle="modal" data-target="#myModal" ><i class="fa fa-plus-circle"></i> <?= lang('add_on_boarding') ?></a></li>
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
							var oTable = $('#onb').dataTable({
								"aaSorting": [[1, "asc"], [3, "asc"]],
								"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
								"iDisplayLength": <?= $Settings->rows_per_page ?>,
								'bProcessing': true, 
								'bServerSide': true,
								'sAjaxSource': '<?= admin_url('hr/getOnBoarding?employee_id='.$id) ?>',
								'fnServerData': function (sSource, aoData, fnCallback) {
									aoData.push({
										"name": "<?= $this->security->get_csrf_token_name() ?>",
										"value": "<?= $this->security->get_csrf_hash() ?>"
									});
									$.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
								},
								"aoColumns": [
								{"sClass" : "left", "mRender" : fsd},
								null, 
								{"sClass" : "left", "mRender" : fsd},
								{"sClass" : "left", "mRender" : yesno_status}, 
								{"sClass" : "left", "mRender" : decode_html}, 
								{"sClass" : "left", "mRender" : attachment},
								{"bSortable": false, "sClass" : "left"}]
							}).fnSetFilteringDelay().dtFilter([
								{column_number: 0, filter_default_label: "[<?=lang('joining_date');?>]", filter_type: "text", data: []},
								{column_number: 1, filter_default_label: "[<?=lang('probation_end_date');?>]", filter_type: "text", data: []},
								{column_number: 2, filter_default_label: "[<?=lang('description');?>]", filter_type: "text", data: []},
								{column_number: 3, filter_default_label: "[<?=lang('experience_etter');?>]", filter_type: "text", data: []},
								{column_number: 4, filter_default_label: "[<?=lang('resume');?>]", filter_type: "text", data: []},
							], "footer");
						});
					</script>
					<div class="col-lg-12">
						<div class="table-responsive">
							<table id="onb" cellpadding="0" cellspacing="0" border="0"
								   class="table table-bordered table-hover table-striped dataTable">
								<thead>
								<tr>
									<th style="width:150px;"><?php echo lang('joining_date'); ?></th>
									<th style="width:150px;"><?php echo lang('probation_periods'); ?></th>
									<th style="width:150px;"><?php echo lang('probation_end_date'); ?></th>
									<th style="width:150px;"><?php echo lang('received_company_asset'); ?></th>
									<th style="width:150px;"><?php echo lang('description'); ?></th>
									<th style="width:250px;"><i class="fa fa-chain"></i> <?php echo lang('attachment'); ?></th>
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
	<div id="login_account" class="tab-pane fade in">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-edit nb"></i><?= lang('login_account').' <span style="color:red;">'.(isset($emp_user->id)? 'Already Created':'').'</span>'; ?></h2>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <?php $attrib = ['class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form'];
                        echo admin_form_open_multipart('hr/edit_user_emp/' . $row->id, $attrib);
                        ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-5">
								<?php
								echo '<input type="hidden" name="user_id" value="'.(isset($emp_user->id)? $emp_user->id:'').'"> ';
								echo '<input type="hidden" name="emp_id" value="' . $row->id . '"> ';

								?>      <div class="form-group">
                                            <?php echo lang('username', 'username'); ?>
                                            <input type="text" name="username" class="form-control" id="username" value="<?= $row->empcode;?>" required="required" readonly/>
                                        </div>
                                        <div class="row">
                                            <div class="panel panel-warning">
                                                <div class="panel-heading"><?= lang('if_you_need_to_rest_password_for_user') ?></div>
                                                <div class="panel-body" style="padding: 5px;">
                                                    <div class="col-md-12">
                                                        <div class="col-md-12">
															  <div class="form-group">
																<?php echo lang('password', 'password'); ?>
																<div class="controls">
																	<?php echo form_password('password', '', 'class="form-control tip" id="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" data-bv-regexp-message="' . lang('pasword_hint') . '"'); ?>
																	<span class="help-block"></span>
																</div>
															</div>
															<div class="form-group">
																<?php echo lang('confirm_password', 'confirm_password'); ?>
																<div class="controls">
																	<?php echo form_password('confirm_password', '', 'class="form-control" id="confirm_password" data-bv-identical="true" data-bv-identical-field="password" data-bv-identical-message="' . lang('pw_not_same') . '"'); ?>
																</div>
															</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                              
                                </div>
                            </div>
                        </div>
                        <p><?php echo form_submit('update', lang('update'), 'class="btn btn-primary"'); ?></p>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<div id="working" class="tab-pane fade">
		<div class="box">
			<div class="box-header">
				<h2 class="blue">
					<i class="fa-fw fa fa-newspaper-o"></i>
					<?= lang('working_information'); ?>
				</h2>
			</div>
			<div class="box-content">
				<div class="row">
					<div class="col-lg-12">
						<p class="introtext"><?= lang('enter_info'); ?></p>
						<?php 
							$attrib = array('data-toggle' => 'validator', 'role' => 'form');
							echo admin_form_open_multipart("hr/edit_employee/".$id."#working", $attrib); 
							echo form_hidden("working_info_id",1);
						?>
						<div class="col-lg-12">
							<div class="panel panel-warning">
								<div class="panel-heading"><?= lang("working_settings") ?></div>
								<div class="panel-body" style="padding: 5px;">
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('biller', 'biller_id'); ?>
											<div class="controls">
												<?php
												$companies_= array();
												foreach ($companies as $company) {
												   $companies_[$company->id] = $company->name; 
												}
												echo form_dropdown('biller_id', $companies_, $biller_id, 'id="biller_id" class="form-control company" required="required"');
												?>
											</div>
										</div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('department', 'department_id'); ?>
											<div class="controls">
												<?php
													$department_opt = array(lang('select')." ".lang('department'));
													echo form_dropdown('department_id', $department_opt, $department_id, ' id="department_id" class="form-control department"');
												?>
											</div>
										</div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('group', 'group'); ?>
											<div class="controls">
												<?php
													$group_opt = array(lang('select')." ".lang('group'));
													echo form_dropdown('group_id', $group_opt, $group_id, 'id="group_id" class="form-control group"');
												?>
											</div>
										</div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('position', 'position_id'); ?>
											<div class="controls position">
												<?php
												$position_opt = array(lang('select')." ".lang('position'));
												echo form_dropdown('position_id', $position_opt, $position_id, 'id="position_id" class="form-control position"');
												?>
											</div>
										</div>
									</div>
									<?php if ($this->Settings->project) { ?>
			                            <div class="col-md-4">
			                            	<?= lang("project", "poproject"); ?>
			                                <div class="project_box form-group">
			                                    <?php
			                                    $project_id = $working_info->project_id;
			                                    $pro[""] 	= lang('select')." ".lang('project');
			                                    foreach ($projects as $project) {
			                                        $pro[$project->project_id] = $project->project_name;
			                                    }
			                                    echo form_dropdown('project', $pro, (isset($_POST['project']) ? $_POST['project'] : $project_id), 'id="poproject" data-placeholder="' . lang("select") . ' ' . lang("project") . '" class="form-control input-tip select" style="width:100%;"');
			                                    ?>
			                                </div>
			                            </div>
			                        <?php } ?>
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('policy', 'policy_id'); ?>
											<div class="controls policy">
												<?php
												$policy_opt = array(lang('select')." ".lang('policy'));
												if($policies){
													foreach($policies as $policy){
														$policy_opt[$policy->id] = $policy->policy;
													}	
												}
												echo form_dropdown('policy_id', $policy_opt, $policy_id, 'id="policy_id" class="form-control policy"');
												?>
											</div>
										</div>
									</div>	
									<div class="col-lg-4">	
										<div class="form-group">
											<?php echo lang('employee_date', 'employee_date'); ?>
											<div class="controls">
												<input type="text" id="employee_date" value="<?= $this->bpas->hrsd($employee_date) ?>" name="employee_date" class="form-control date" required="required"/>
											</div>
										</div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('employee_type', 'employee_type'); ?>
											<div class="controls">
												<?php
												$tp[] = lang("select") . " " .lang("employee_type");
												if($types){
													foreach($types as $type){
														$tp[$type->id] = $type->name;
													}
												}			
												echo form_dropdown('employee_type', $tp, $employee_type_id, 'id="employee_type" class="form-control" required="required"');
												?>
											</div>
										</div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('kpi_type', 'kpi_type'); ?>
											<div class="controls">
												<?php
												$kpip[] = lang("select") . " " .lang("kpi_type");
												if($kpi_types){
													foreach($kpi_types as $kpi_type){
														$kpip[$kpi_type->id] = $kpi_type->name;
													}
												}			
												echo form_dropdown('kpi_type', $kpip, $employee_kpi_type, 'id="kpi_type" class="form-control"');
												?>
											</div>
										</div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('status', 'status'); ?>
											<div class="controls">
												<?php
												$status= array("active" => lang("active"),"inactive"=> lang("inactive"));							
												echo form_dropdown('status', $status, $working_info_status, 'id="status" class="form-control" required="required"');
												?>
											</div>
										</div>
									</div>
	                                <div class="col-md-4">
	                                    <div class="form-group all">
	                                        <?= lang('employee_level', 'employee_level'); ?>
	                                        <div class="input-group" style="width: 100%">
	                                            <?php 
	                                            $form_category = null;
	                                            function formMultiLevelCategory($data, $n, $str = '', $p_category_id)
	                                            {
	                                                $form_category = ($n ? '<select id="employee_level" name="employee_level" class="form-control select" style="width: 100%" placeholder="' . lang('select') . ' ' . lang('category') . '" required="required"><option value="" selected>' . lang('select') . ' ' . lang('category') . '</option>' : '');
	                                                foreach ($data as $key => $categories) {
	                                                    if (!empty($categories->children)) {
	                                                        $form_category .= '<option disabled>' . $str . $categories->name . '</option>';
	                                                        $form_category .= formMultiLevelCategory($categories->children, 0, ($str.'&emsp;&emsp;'), $p_category_id);
	                                                    } else {
	                                                        if ($p_category_id == $categories->id) 
	                                                            $form_category .= ('<option value="' . $categories->id . '" selected>' . $str . $categories->name . '</option>');
	                                                        else 
	                                                            $form_category .= ('<option value="' . $categories->id . '">' . $str . $categories->name . '</option>');
	                                                    }
	                                                }

	                                                $form_category .= ($n ? '</select>' : '');
	                                                return $form_category;
	                                            }
	                                            echo formMultiLevelCategory($employee_levels, 1, '', $working_info->employee_level); ?>
	                                        </div>
	                                    </div>
	                                </div>
								</div>
							</div>
							
							<div class="panel panel-warning">
								<div class="panel-heading">
									<?= lang("salary_settings") ?>
								</div>
								<div class="panel-body" style="padding: 5px;">
									<div class="col-lg-4 hide">
										<div class="form-group">
											<?= lang("currency", "currency"); ?>
											<?php
											$cur[""] = "";
											foreach ($currencies as $currency) {
												$cur[$currency->code] = $currency->name;
											}
											echo form_dropdown('currency', $cur, (isset($_POST['?']) ? $_POST['currency'] : 'USD'), 'id="currency" data-placeholder="' . lang("select") . ' ' . lang("currency") . '" class="form-control input-tip select" disabled  required style="width:100%;"');
											?>
										</div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('basic_salary', 'basic_salary'); ?> 
											<span id="deduct_net_salary" style="color:red;" class="hide">
												<?php 
													if($working_info_self_tax==1){
														echo " ( " . $this->bpas->formatDecimal($net_salary - $tax_rate) ." ) ";
													}else{
														echo " ( " . $this->bpas->formatDecimal($net_salary) ." ) ";
													} 
												?>
											</span>
											<div class="controls">
												<input type="text" id="net_salary" value="<?= $net_salary ?>" name="net_salary" class="form-control" required/>
											</div>
										</div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('salary_tax', 'salary_tax'); ?>
											<div class="controls">
												<input type="text" id="salary_tax" value="<?= $salary_tax ?>" name="salary_tax" class="form-control" required/>
											</div>
										</div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('self_tax', 'self_tax'); ?>
											<div class="controls">
												<?php
												$self_tax= array('0' => lang("no"),'1'=> lang("yes"));
												echo form_dropdown('self_tax', $self_tax, $working_info_self_tax, 'id="self_tax" class="form-control" required');
												?>
											</div>
										</div>
									</div>
									
									<div class="col-lg-4 hide">
										<div class="form-group">
											<?php echo lang('monthly_rate', 'monthly_rate'); ?>
											<div class="controls">
												<input type="text" id="monthly_rate" value="<?= $this->bpas->formatDecimal($monthly_rate) ?>" name="monthly_rate" class="form-control" required/>
											</div>
										</div>
									</div>
									<div class="col-lg-4 hide">
										<div class="form-group">
											<?php echo lang('tax_rate', 'tax_rate'); ?>
											<div class="controls">
												<input type="text" id="tax_rate" value="<?= $this->bpas->formatDecimal($tax_rate) ?>" name="tax_rate" readonly class="form-control"/>
											</div>
										</div>
									</div>
									<div class="col-lg-4 hide">
										<div class="form-group">
											<?php echo lang('salary_two_time', 'salary_two_time'); ?> (<?php echo lang('per_month'); ?>)
											<div class="controls">
												<?php
												
												$salary2_opt["0"] = lang("no") ;
												$salary2_opt["1"] = lang("yes") ;		
												echo form_dropdown('salary_two_time', $salary2_opt, $working_info->salary_two_time, 'id="salary_two_time" class="form-control"');
												?>
											</div>
										</div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('absent_rate', 'absent_rate'); ?> 
											<small style="font-weight:bold;">(Day)</small>
											<div class="controls">
												<input type="text" id="absent_rate" value="<?= $absent_rate ?>" name="absent_rate" class="form-control"/>
											</div>
										</div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('permission_rate', 'permission_rate'); ?> 
											<small style="font-weight:bold;">(Day)</small>
											<div class="controls">
												<input type="text" id="permission_rate" value="<?= $permission_rate ?>" name="permission_rate" class="form-control"/>
											</div>
										</div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('late_early', 'late_early'); ?>
											<small style="font-weight:bold;">(Hour)</small>
											<div class="controls">
												<input type="text" id="late_early" value="<?= $late_early_rate ?>" name="late_early" class="form-control"/>
											</div>
										</div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('normal_ot', 'normal_ot'); ?>
											<small style="font-weight:bold;">(Hour)</small>
											<div class="controls">
												<input type="text" id="normal_ot" value="<?= ($normal_ot?$normal_ot:0) ?>" name="normal_ot" class="form-control"/>
											</div>
										</div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('holiday_ot', 'holiday_ot'); ?>
											<small style="font-weight:bold;">(Hour)</small>
											<div class="controls">
												<input type="text" id="holiday_ot" value="<?= ($holiday_ot?$holiday_ot:0) ?>" name="holiday_ot" class="form-control"/>
											</div>
										</div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('weekend_ot', 'weekend_ot'); ?>
											<small style="font-weight:bold;">(Hour)</small>
											<div class="controls">
												<input type="text" id="weekend_ot" value="<?= ($weekend_ot?$weekend_ot:0) ?>" name="weekend_ot" class="form-control"/>
											</div>
										</div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('seniority', 'no_seniority'); ?>
											<div class="controls">
												<?php
												$seniority_opt[0] = lang("yes") ;
												$seniority_opt[1] = lang("no") ;		
												echo form_dropdown('no_seniority', $seniority_opt, $no_seniority, 'id="no_seniority" class="form-control"');
												?>
											</div>
										</div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('pension', 'union'); ?> <?= lang("without_%") ?>
											<div class="controls">
												<input type="text" id="pension" value="<?= $pension ?>" name="pension" class="form-control"/>
											</div>
										</div>
									</div>
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('attendance_bonus', 'attendance_bonus'); ?>
											<div class="controls">
												<input type="text" id="attendance_bonus" value="<?= $attendance_bonus ?>" name="attendance_bonus"  class="form-control"/>
											</div>
										</div>
									</div>
									
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('special_bonus', 'special_bonus'); ?>
											<div class="controls">
												<input type="text" id="special_bonus" value="<?= $special_bonus ?>" name="special_bonus"  class="form-control"/>
											</div>
										</div>
									</div>
									
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('tra_acc_allowance', 'tra_acc_allowance'); ?>
											<div class="controls">
												<input type="text" id="tra_acc_allowance" value="<?= $tra_acc_allowance ?>" name="tra_acc_allowance" class="form-control"/>
											</div>
										</div>
									</div>
									
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('union', 'union'); ?>
											<div class="controls">
												<input type="text" id="union" value="<?= $union ?>" name="union" class="form-control"/>
											</div>
										</div>
									</div>
									
									<div class="col-lg-4">
										<div class="form-group">
											<?php echo lang('payment_type', 'payment_type'); ?>
											<div class="controls">
												<?php
												
												$pt_opt["monthly"] = lang("monthly") ;
												$pt_opt["daily"] = lang("daily") ;		
												echo form_dropdown('payment_type', $pt_opt, $payment_type, 'id="payment_type" class="form-control"');
												?>
											</div>
										</div>
									</div>
									<div class="col-lg-6">
										<div class="form-group text-center">
											<?php echo lang('addition', 'addition'); ?>
											<table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
												<thead>
													<tr>
														<th><?= lang("description") ?></th>
														<th><?= lang("amount") ?></th>
													</tr>
												</thead>
												<?php
													$td_addition = "";
													$additions = $this->hr_model->getAllAdditions();
													if($additions){
														foreach($additions as $addition){
															$amount = $addition->value;
															if(isset($emp_addtions[$addition->id])){
																$amount = $emp_addtions[$addition->id];
															}
															$td_addition .= "<tr>
																				<td class='text-left'>".$addition->name."</td>
																				<td><input name='addition[".$addition->id."]' type='text' value='".$amount."' class='form-control text-right'/></td>
																			</tr>";
														}
													}
												?>
												<tbody>
													<?= $td_addition ?>
												</tbody>
											</table>
										</div>
									</div>
									<div class="col-lg-6">
										<div class="form-group text-center">
											<?php echo lang('deduction', 'deduction'); ?>
											<table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
												<thead>
													<tr>
														<th><?= lang("description") ?></th>
														<th><?= lang("amount") ?></th>
													</tr>
												</thead>
												<?php
													$td_deduction = "";
													$deductions = $this->hr_model->getAllDeductions();
													if($deductions){
														foreach($deductions as $deduction){
															$amount = $deduction->value;
															if(isset($emp_deductions[$deduction->id])){
																$amount = $emp_deductions[$deduction->id];
															}
															$td_deduction .= "<tr>
																				<td class='text-left'>".$deduction->name."</td>
																				<td><input name='deduction[".$deduction->id."]' type='text' value='".$amount."' class='form-control text-right'/></td>
																			</tr>";
														}
													}
												?>
												<tbody>
													<?= $td_deduction ?>
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
							<div class="panel panel-warning">
								<div class="panel-heading"><?= lang("leave_settings") ?></div>
								<div class="panel-body" style="padding: 5px;">
									<div class="col-lg-3">
										<div class="form-group">
											<?php echo lang('annual_leave', 'annual_leave'); ?>
											<div class="controls">
												<div class="controls">
													<?php echo form_input('annual_leave', $annual_leave, 'class="form-control text-right" id="annual_leave"'); ?>
												</div>
											</div>
										</div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
											<?php echo lang('special_leave', 'special_leave'); ?>
											<div class="controls">
												<div class="controls">
													<?php echo form_input('special_leave', $special_leave, 'class="form-control text-right" id="special_leave"'); ?>
												</div>
											</div>
										</div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
											<?php echo lang('sick_leave', 'sick_leave'); ?>
											<div class="controls">
												<div class="controls">
													<?php echo form_input('sick_leave', $sick_leave, 'class="form-control text-right" id="sick_leave"'); ?>
												</div>
											</div>
										</div>
									</div>
									<div class="col-lg-3">
										<div class="form-group">
											<?php echo lang('other_leave', 'other_leave'); ?>
											<div class="controls">
												<div class="controls">
													<?php echo form_input('other_leave', $other_leave, 'class="form-control text-right" id="other_leave"'); ?>
												</div>
											</div>
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
						<?php echo form_close(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div id="family" class="tab-pane fade"><form></form>
		<div class="box">
			<div class="box-header">
				<h2 class="blue">
					<i class="fa-fw fa fa-newspaper-o"></i>
					<?= lang('family_information'); ?>
				</h2>
				<div class="box-icon">
					<ul class="btn-tasks">
						<li class="dropdown">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip"  data-placement="left" title="<?= lang("actions") ?>"></i></a>
							<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
								<li><a href="<?php echo admin_url('hr/add_family_info/'.$id); ?>" data-toggle="modal" data-target="#myModal" ><i class="fa fa-plus-circle"></i> <?= lang('add_family_info') ?></a></li>
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
							var oTable = $('#familyTable').dataTable({
								"aaSorting": [[1, "asc"], [3, "asc"]],
								"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
								"iDisplayLength": <?= $Settings->rows_per_page ?>,
								'bProcessing': true, 
								'bServerSide': true,
								'sAjaxSource': '<?= admin_url('hr/getFamilyInfo?employee_id='.$id) ?>',
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
								{"sClass" : "center"},
								{"sClass" : "left"},
								{"sClass" : "left"},
								{"sClass" : "left"},
							
								{"bSortable": false, "sClass" : "center"}]
							}).fnSetFilteringDelay().dtFilter([
								{column_number: 0, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
								{column_number: 1, filter_default_label: "[<?=lang('occupation');?>]", filter_type: "text", data: []},
								{column_number: 2, filter_default_label: "[<?=lang('dob');?>]", filter_type: "text", data: []},
								{column_number: 3, filter_default_label: "[<?=lang('relationship');?>]", filter_type: "text", data: []},
								{column_number: 4, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
								{column_number: 5, filter_default_label: "[<?=lang('pob');?>]", filter_type: "text", data: []},
								{column_number: 6, filter_default_label: "[<?=lang('address');?>]", filter_type: "text", data: []},
							], "footer");
						
						});
					</script>
					<div class="col-lg-12">
						<div class="table-responsive">
							<table id="familyTable" cellpadding="0" cellspacing="0" border="0"
								   class="table table-bordered table-hover table-striped dataTable">
								<thead>
								<tr>
									<th style="width:150px;"><?php echo lang('name'); ?></th>
									<th style="width:150px;"><?php echo lang('occupation'); ?></th>
									<th style="width:150px;"><?php echo lang('dob'); ?></th>
									<th style="width:150px;"><?php echo lang('relationship'); ?></th>
									<th style="width:150px;"><?php echo lang('phone'); ?></th>
									<th style="width:250px;"><?php echo lang('pob'); ?></th>
									<th style="width:250px;"><?php echo lang('address'); ?></th>
									<th style="width:100px;"><?php echo lang('action'); ?></th>
								</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
									</tr>
								</tfoot>
							</table>
						</div>
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
	
	<div id="promotion" class="tab-pane fade">
		
		<div class="box">
			<div class="box-header">
				<h2 class="blue">
					<i class="fa-fw fa fa-newspaper-o"></i>
					<?= lang('promotion'); ?>
				</h2>
				<div class="box-icon">
					<ul class="btn-tasks">
						<li class="dropdown">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip"  data-placement="left" title="<?= lang("actions") ?>"></i></a>
							<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
								<li><a href="<?php echo admin_url('hr/add_promotion/'.$id); ?>" data-toggle="modal" data-target="#myModal" ><i class="fa fa-plus-circle"></i> <?= lang('add_promotion') ?></a></li>
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
							var oTable = $('#promotionTable').dataTable({
					            "aaSorting": [[1, "asc"], [3, "asc"]],
					            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
					            "iDisplayLength": <?= $Settings->rows_per_page ?>,
					            'bProcessing': true, 
					            'bServerSide': true,
					            'sAjaxSource': '<?= admin_url('hr/getPromotions') ?>',
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
					            {"sClass" : "left"},
					            {"sClass" : "center", "mRender" : fsd},
					            {"sClass" : "center", "mRender" : fsd},
					            {"sClass" : "left"},
					            {"bSortable": false, "sClass" : "center"}]
					        }).fnSetFilteringDelay().dtFilter([
					            {column_number: 0, filter_default_label: "[<?=lang('code');?>]", filter_type: "text", data: []},
					            {column_number: 1, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
					            {column_number: 2, filter_default_label: "[<?=lang('position');?>]", filter_type: "text", data: []},
					            {column_number: 3, filter_default_label: "[<?=lang('employee_level');?>]", filter_type: "text", data: []},
					            {column_number: 4, filter_default_label: "[<?=lang('promoted_date');?>]", filter_type: "text", data: []},
					            {column_number: 5, filter_default_label: "[<?=lang('official_promote');?>]", filter_type: "text", data: []},
					            {column_number: 6, filter_default_label: "[<?=lang('promoted_by');?>]", filter_type: "text", data: []},
					            
					        ], "footer");
						});
					</script>
					<div class="col-lg-12">
						<div class="table-responsive">
							<table id="promotionTable" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped dataTable">
		                        <thead>
		                        <tr>
		                            <th style="width:100px;"><?php echo lang('code'); ?></th>
		                            <th style="width:150px;"><?php echo lang('name'); ?></th>
		                            <th style="width:150px;"><?php echo lang('position'); ?></th>
		                            <th style="width:150px;"><?php echo lang('employee_level'); ?></th>
		                            <th style="width:150px;"><?php echo lang('promoted_date'); ?></th>
		                            <th style="width:250px;"><?php echo lang('official_promote'); ?></th>
		                            <th style="width:150px;"><?php echo lang('promoted_by'); ?></th>
		                            
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
		                                <th class="text-center"><?= lang("actions"); ?></th>
		                            </tr>
		                        </tfoot>
		                    </table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div id="contract" class="tab-pane fade">
		<div class="box">
			<div class="box-header">
				<h2 class="blue">
					<i class="fa-fw fa fa-newspaper-o"></i>
					<?= lang('contract'); ?>
				</h2>
				<div class="box-icon">
					<ul class="btn-tasks">
						<li class="dropdown">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip"  data-placement="left" title="<?= lang("actions") ?>"></i></a>
							<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
								<li><a href="<?php echo admin_url('hr/add_contract/'.$id); ?>" data-toggle="modal" data-target="#myModal" ><i class="fa fa-plus-circle"></i> <?= lang('add_contract') ?></a></li>
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
					<div class="col-lg-12">
						<script type="text/javascript">
							$(document).ready(function () {
								'use strict';
								var oTable = $('#contractTable').dataTable({
									"aaSorting": [[1, "asc"], [3, "asc"]],
									"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
									"iDisplayLength": <?= $Settings->rows_per_page ?>,
									'bProcessing': true, 
									'bServerSide': true,
									'sAjaxSource': '<?= admin_url('hr/getContract?employee_id='.$id) ?>',
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
									{column_number: 0, filter_default_label: "[<?=lang('title');?>]", filter_type: "text", data: []},
									{column_number: 1, filter_default_label: "[<?=lang('type');?>]", filter_type: "text", data: []},
									{column_number: 2, filter_default_label: "[<?=lang('start_date');?>]", filter_type: "text", data: []},
									{column_number: 3, filter_default_label: "[<?=lang('end_date');?>]", filter_type: "text", data: []},
									{column_number: 4, filter_default_label: "[<?=lang('description');?>]", filter_type: "text", data: []},
								], "footer");
							
							});
						</script>
						<div class="row">
							<div class="col-lg-12">
								<div class="table-responsive">
									<table id="contractTable" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped dataTable">
										<thead>
										<tr>
											<th style="width:150px;"><?php echo lang('title'); ?></th>
											<th style="width:150px;"><?php echo lang('type'); ?></th>
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
		</div>
	</div>
	
	<div id="emergency" class="tab-pane fade">
		<div class="box">
			<div class="box-header">
				<h2 class="blue">
					<i class="fa-fw fa fa-newspaper-o"></i>
					<?= lang('emergency_contacts'); ?>
				</h2>
				<div class="box-icon">
					<ul class="btn-tasks">
						<li class="dropdown">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip"  data-placement="left" title="<?= lang("actions") ?>"></i></a>
							<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
								<li><a href="<?php echo admin_url('hr/add_emergency_contact/'.$id); ?>" data-toggle="modal" data-target="#myModal" ><i class="fa fa-plus-circle"></i> <?= lang('add_emergency_contact') ?></a></li>
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
					<div class="col-lg-12">
						<script type="text/javascript">
							$(document).ready(function () {
								'use strict';
								var oTable = $('#emergencyTable').dataTable({
									"aaSorting": [[1, "asc"], [3, "asc"]],
									"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
									"iDisplayLength": <?= $Settings->rows_per_page ?>,
									'bProcessing': true, 
									'bServerSide': true,
									'sAjaxSource': '<?= admin_url('hr/getEmergencyContact?employee_id='.$id) ?>',
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
									{"bSortable": false, "sClass" : "center"}]
								}).fnSetFilteringDelay().dtFilter([
									{column_number: 0, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
									{column_number: 1, filter_default_label: "[<?=lang('relationship');?>]", filter_type: "text", data: []},
									{column_number: 2, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
								], "footer");
							});
						</script>
						<div class="row">
							<div class="col-lg-12">
								<div class="table-responsive">
									<table id="emergencyTable" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-striped dataTable">
										<thead>
										<tr>
											<th style="width:150px;"><?php echo lang('name'); ?></th>
											<th style="width:150px;"><?php echo lang('relationship'); ?></th>
											<th style="width:150px;"><?php echo lang('phone'); ?></th>
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
	
	<div id="bank" class="tab-pane fade">
		<div class="box">
			<div class="box-header">
				<h2 class="blue">
					<i class="fa-fw fa fa-newspaper-o"></i>
					<?= lang('bank_account'); ?>
				</h2>
				<div class="box-icon">
					<ul class="btn-tasks">
						<li class="dropdown">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip"  data-placement="left" title="<?= lang("actions") ?>"></i></a>
							<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
								<li><a href="<?php echo admin_url('hr/add_bank_account/'.$id); ?>" data-toggle="modal" data-target="#myModal" ><i class="fa fa-plus-circle"></i> <?= lang('add_bank_account') ?></a></li>
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
							var oTable = $('#bankTable').dataTable({
								"aaSorting": [[1, "asc"], [3, "asc"]],
								"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
								"iDisplayLength": <?= $Settings->rows_per_page ?>,
								'bProcessing': true, 
								'bServerSide': true,
								'sAjaxSource': '<?= admin_url('hr/getBankAccounts?employee_id='.$id) ?>',
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
								{"sClass" : "left"},
								{"sClass" : "center"},
								{"sClass" : "center", "mRender" : fsd}, 
								{"sClass" : "center", "mRender" : fsd},
								{"sClass" : "left"}, 
								{"bSortable": false, "sClass" : "center"}]
							}).fnSetFilteringDelay().dtFilter([
								{column_number: 0, filter_default_label: "[<?=lang('account');?>]", filter_type: "text", data: []},
								{column_number: 1, filter_default_label: "[<?=lang('account_no');?>]", filter_type: "text", data: []},
								{column_number: 2, filter_default_label: "[<?=lang('account_name');?>]", filter_type: "text", data: []},
								{column_number: 3, filter_default_label: "[<?=lang('account_type');?>]", filter_type: "text", data: []},
								{column_number: 4, filter_default_label: "[<?=lang('currency');?>]", filter_type: "text", data: []},
								{column_number: 5, filter_default_label: "[<?=lang('date_opened');?>]", filter_type: "text", data: []},
								{column_number: 6, filter_default_label: "[<?=lang('date_issued');?>]", filter_type: "text", data: []},
								{column_number: 7, filter_default_label: "[<?=lang('description');?>]", filter_type: "text", data: []},
							], "footer");
						
						});
					</script>
					
					<div class="col-lg-12">
						<div class="table-responsive">
							<table id="bankTable" cellpadding="0" cellspacing="0" border="0"
								   class="table table-bordered table-hover table-striped dataTable">
								<thead>
								<tr>
									<th style="width:150px;"><?php echo lang('account'); ?></th>
									<th style="width:150px;"><?php echo lang('account_no'); ?></th>
									<th style="width:150px;"><?php echo lang('account_name'); ?></th>
									<th style="width:150px;"><?php echo lang('account_type'); ?></th>
									<th style="width:150px;"><?php echo lang('currency'); ?></th>
									<th style="width:150px;"><?php echo lang('date_opened'); ?></th>
									<th style="width:150px;"><?php echo lang('date_issued'); ?></th>
									<th style="width:250px;"><?php echo lang('description'); ?></th>
									<th style="width:80px;"><?php echo lang('action'); ?></th>
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
    	<?php if($row->nssf_number ==0){
    	?>
    	$(".nssf_number").hide();
    	<?php }?>
    	$('#nssf').change(function(event) {
            var nssf = $(this).val();
			if(nssf == 0){
				$(".nssf_number").hide();
			}else{
				$(".nssf_number").show();
			}	
        });
    });
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

<?php if ($Owner && $id != $this->session->userdata('user_id')) { ?>
        <script type="text/javascript" charset="utf-8">
            $(document).ready(function() {
                $('#group').change(function(event) {
                    var group = $(this).val();
                    if (group == 1) {
                        $('.no').slideUp();
                    } else {
                        $('.no').slideDown();
                    }
                });
                // var group = <?= $user->group_id ?>;
                // if (group == 1) {
                //     $('.no').slideUp();
                // } else {
                //     $('.no').slideDown();
                // }
            });
        </script>
    <?php } ?>
<script type="text/javascript" charset="utf-8">
    $(document).ready(function() {
        if ($('#group').val() != 1) {
            $('.no').slideDown();
        } else {
            $('.no').slideUp();
        }
        $('#group').change(function(event) {
            var group = $(this).val();
            if (group == 1) {
                $('.no').slideUp();
            } else {
                $('.no').slideDown();
            }
        });
    });
</script>



