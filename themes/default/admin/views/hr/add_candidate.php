
<ul id="myTab" class="nav nav-tabs">
	<li class=""><a href="#basic" class="tab-grey"><?= lang('basic_information') ?></a></li>
</ul>

<div class="tab-content">

	<div id="basic" class="tab-pane fade in">
	
		<div class="box">
		
			<div class="box-header">
				<h2 class="blue">
					<i class="fa-fw fa fa-plus"></i>
					<?= lang('add_candidate'); ?>
				</h2>
			</div>
			
			<div class="box-content">
			
				<div class="row">
				
					<div class="col-lg-12">

						<p class="introtext"><?= lang('enter_info'); ?></p>
						<?php 
							//$attrib = array('data-toggle' => 'validator', 'role' => 'form');  
							echo admin_form_open_multipart("hr/add_candidate", isset($attrib)? $attrib: array()); 
						?>
						
						<div class="col-lg-12">
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
									<!-- <div class="col-lg-6">		
										<div class="form-group">
											<?php echo lang('current_job', 'current_job'); ?>
											<div class="controls">
												<?php echo form_input('current_job', '', 'class="form-control" id="current_job"'); ?>
											</div>
										</div>
									</div>
									<div class="col-lg-6">		
										<div class="form-group">
											<?php echo lang('current_salary', 'current_salary'); ?>
											<div class="controls">
												<?php echo form_input('current_salary', '', 'class="form-control" id="current_salary"'); ?>
											</div>
										</div>
									</div>
									<div class="col-lg-6">		
										<div class="form-group">
											<?php echo lang('expect_salary', 'expect_salary'); ?>
											<div class="controls">
												<?php echo form_input('expect_salary', '', 'class="form-control" id="expect_salary"'); ?>
											</div>
										</div>
									</div>
									<div class="col-lg-6">		
										<div class="form-group">
											<?php echo lang('working_period', 'working_period'); ?>
											<div class="controls">
												<?php echo form_input('working_period', '', 'class="form-control" id="working_period"'); ?>
											</div>
										</div>
									</div> -->
								
								</div>
								
								
								<div class="row">
									<div class="col-lg-6">
										<div class="form-group">
											<?php echo lang('nric_no', 'nric_no'); ?>
											<div class="controls">
												<?php echo form_input('nric_no', '', 'class="form-control" id="nric_no"'); ?>
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


