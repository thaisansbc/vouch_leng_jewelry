
<ul id="myTab" class="nav nav-tabs">
	<li class=""><a href="#basic" class="tab-grey"><?= lang('basic_information') ?></a></li>
</ul>

<div class="tab-content">

	<div id="basic" class="tab-pane fade in">
	
		<div class="box">
		
			<div class="box-header">
				<h2 class="blue">
					<i class="fa-fw fa fa-plus"></i>
					<?= lang('basic_information'); ?>
				</h2>
			</div>
			
			<div class="box-content">
			
				<div class="row">
				
					<div class="col-lg-12">

						<p class="introtext"><?= lang('enter_info'); ?></p>
						<?php 
							//$attrib = array('data-toggle' => 'validator', 'role' => 'form');  
							echo admin_form_open_multipart("hr/add_employee", isset($attrib)? $attrib: array()); 
						?>
						
						<div class="col-lg-12">
						
								<?php
									if($last_employee->empcode){
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
											<?php echo lang('code', 'empcode'); ?>
											<div class="controls">
												<?php echo form_input('empcode', $new_empcode, 'class="form-control" id="empcode" required="required"'); ?>
											</div>
										</div>
									</div>
									<div class="col-lg-6">
										<div class="row">
											<div class="col-lg-6">
												<div class="form-group">
													<?php echo lang('finger_id', 'finger_id'); ?>
													<div class="controls">
														<?php echo form_input('finger_id', '', 'class="form-control" id="finger_id"'); ?>
													</div>
												</div>
											</div>
											<div class="col-lg-6">
												<div class="form-group">
													<?php echo lang('nric_no', 'nric_no'); ?>
													<div class="controls">
														<?php echo form_input('nric_no', '', 'class="form-control" id="nric_no"'); ?>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<div class="row">
									<div class="col-lg-6">
										<div class="form-group">
											<?php echo lang('first_name_kh', 'first_name_kh'); ?>
											<div class="controls">
												<?php echo form_input('first_name_kh', '', 'class="form-control" id="first_name_kh"'); ?>
											</div>
										</div>
									</div>
									<div class="col-lg-6">
										<div class="form-group">
											<?php echo lang('last_name_kh', 'last_name_kh'); ?>
											<div class="controls">
												<?php echo form_input('last_name_kh', '', 'class="form-control" id="last_name_kh" '); ?>
											</div>
										</div>
									</div>
								</div>
								
								<div class="row">
									<div class="col-lg-6">
										<div class="form-group">
											<?php echo lang('first_name', 'first_name'); ?>
											<div class="controls">
												<?php echo form_input('first_name', '', 'class="form-control" id="first_name" required="required""'); ?>
											</div>
										</div>
									</div>
									<div class="col-lg-6">
										<div class="form-group">
											<?php echo lang('last_name', 'last_name'); ?>
											<div class="controls">
												<?php echo form_input('last_name', '', 'class="form-control" id="last_name" required="required"'); ?>
											</div>
										</div>
									</div>
								</div>
								
								<div class="row">
									<div class="col-lg-6">
										<div class="form-group">
											<?php echo lang('dob', 'dob'); ?>
											<div class="controls">
												<?php echo form_input('dob', '', 'class="form-control date" id="dob" required="required"'); ?>
											</div>
										</div>
									</div>
									<div class="col-lg-6">
										<div class="form-group">
											<?php echo lang('gender', 'gender'); ?>
											<div class="controls">
												<select class="form-control" name="gender">
													<option value="male"><?= lang("male") ?></option>
													<option value="female"><?= lang("female") ?></option>
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
												<?php echo form_input('phone', '', 'class="form-control" id="phone"'); ?>
											</div>
										</div>
									</div>
									
									<div class="col-lg-6">
										<div class="form-group">
											<?php echo lang('email', 'email'); ?>
											<div class="controls">
												<input type="email" id="email" name="email" class="form-control"/>
											</div>
										</div>
									</div>
								</div>

								<div class="row">
									<div class="col-lg-6">		
										<div class="form-group">
											<?php echo lang('nationality', 'nationality'); ?>
											<div class="controls">
												<?php echo form_input('nationality', '', 'class="form-control" id="nationality"'); ?>
											</div>
										</div>
									</div>
									<div class="col-lg-6">		
										<div class="form-group">
											<?php echo lang('marital_status', 'marital_status'); ?>
											<div class="controls">
												<select class="form-control" name="marital_status">
													<option value="single"><?= lang("single") ?></option>
													<option value="married"><?= lang("married") ?></option>
													<option value="widowed"><?= lang("widowed") ?></option>
													<option value="widowed"><?= lang("divoiced_or_separated") ?></option>
												</select>
											</div>
										</div>
									</div>
								</div>
								
								
								<div class="row">
									<div class="col-lg-6">
										<div class="form-group">
											<?php echo lang('non_resident', 'non_resident'); ?>
											<div class="controls">
												<select class="form-control" name="non_resident">
													<option value="0" <?= (isset($row) && $row->non_resident==0?"selected":""); ?> ><?= lang("no") ?></option>
													<option value="1" <?= (isset($row) && $row->non_resident==1?"selected":""); ?>  ><?= lang("yes") ?></option>
												</select>
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
									<div class="col-lg-2">
										<div class="form-group">
											<?php echo lang('book_type', 'book_type'); ?>
											<div class="controls">
												<select class="form-control" name="book_type" id="book_type" required="required">
													<option value="workbook"><?= lang("workbook") ?></option>
													<option value="work_permit"><?= lang("work_permit") ?></option>
												</select>
											</div>
										</div>
									</div>
									<div class="col-lg-4">
					                  <div class="form-group workbook_number" >
					                    <?php echo lang('workbook_number', 'workbook_number'); ?>
					                    <div class="controls">
											<?php echo form_input('workbook_number','', 'class="form-control" id="workbook_number"'); ?>
										</div>
					                  </div>
					             
					                  <div class="form-group work_permit">
					                    <?php echo lang('work_permit_number', 'work_permit_number'); ?>
					                    <div class="controls">
											<?php echo form_input('work_permit_number','', 'class="form-control" id="work_permit"'); ?>
										</div>
					                  </div>
					                </div>
									<div class="col-lg-2">
										<div class="form-group">
											<?php echo lang('nssf', 'nssf'); ?>
											<div class="controls">
												<select class="form-control" name="nssf" id="nssf" required="required">
													<option value="0"><?= lang("no") ?></option>
													<option value="1"><?= lang("yes") ?></option>
												</select>
											</div>
										</div>
									</div>
									<div class="col-lg-4">
					                  <div class="form-group nssf_number">
					                    <?php echo lang('nssf_number', 'nssf_number'); ?>
					                    <div class="controls">
											<?php echo form_input('nssf_number','', 'class="form-control" id="nssf_number"'); ?>
										</div>
					                  </div>
					                </div>
									<div class="col-lg-6">		
										<div class="form-group">
											<?php echo lang('type', 'type'); ?>
											<div class="controls">
												<select class="form-control" name="type">
													<option value="Staff"><?= lang("Staff") ?></option>
													<option value="Worker"><?= lang("Worker") ?></option>
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
												<textarea class="form-control" name="address"></textarea>
											</div>
										</div>
									</div>	
									<div class="col-lg-6">
										<div class="form-group">
											<?php echo lang('note', 'note'); ?>
											<div class="controls">
												<textarea class="form-control" name="note"></textarea>
											</div>
										</div>
									</div>	
								</div>	
								
								
								<div class="form-group">
									<div class="controls">
										<input type="submit" name="submit" class="btn btn-success" />
									</div>
								</div>
								
							
						</div>
				
						<?php echo form_close(); ?>
						
					</div>
					
				</div>
				
			</div>
			
		</div>  
		
	</div>
	
	
</div>

<script type="text/javascript">
    $(function(){
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
				data : { biller : biller },
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
				data : { department_id : department_id },
				success : function(data){
					$("#group_id").html(data.group_opt);
					$("#group_id").select2();
				}
			});
		}
		
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
				$(".nssf").show();
			}else{
				$(".nssf_number").show();
				$(".nssf").hide();
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
				$(".work_permit").hide();
				$(".workbook_number").show();
			}else{
				$(".work_permit").show();
				$(".workbook_number").hide();
			}
		}
    });
</script>


