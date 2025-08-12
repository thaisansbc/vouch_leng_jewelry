<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php echo admin_form_open("attendances/approve_ot", ' id="form-submit" '); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-calendar"></i><?= lang('approve_ot').' ('.(isset($sel_warehouse) ? $sel_warehouse->name : lang('all_warehouses')).')'; ?></h2>
		
        <div class="box-icon">
            <ul class="btn-tasks">
			
				<li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang("approve_ot") ?></p>
				
				<div id="form">
					<div class="row">	
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
								<?php
                                $bl[""] = lang('all').' '.lang('biller');
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
						
						
						<div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="department"><?= lang("department"); ?></label>
								<?php
								$department_opt = array(lang('all')." ".lang('department'));
								echo form_dropdown('department', $department_opt, isset($_POST['department'])? $_POST['department'] : '', 'id="department" class="form-control"');
								?>
                            </div>
                        </div>
						<div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="group"><?= lang("group"); ?></label>
								<?php
								$group_opt = array(lang('all')." ".lang('group'));
								echo form_dropdown('group', $group_opt, isset($_POST['group'])? $_POST['group']: '', 'id="group" class="form-control"');
								?>
                            </div>
                        </div>
						
						<div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="position"><?= lang("position"); ?></label>
								<?php
								$position_opt = array(lang('all')." ".lang('position'));
								echo form_dropdown('position', $position_opt, (isset($_POST['position']) ? $_POST['position'] : ""), 'id="position" class="form-control"');
								?>
                            </div>
                        </div>
						
						<div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : date("d/m/Y")), 'class="form-control date" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : date("d/m/Y")), 'class="form-control date" id="end_date"'); ?>
                            </div>
                        </div>
						
					</div>
					
					<div class="form-group">
                        <div class="controls" style="float:left"> 
							<?php echo form_submit('submit_report', $this->lang->line("search"), 'class="btn btn-primary"'); ?> 
							<?php echo form_submit('approve', $this->lang->line("approve"), 'class="btn btn-success" id="approve_ot"'); ?>
						</div>
						<div style="clear:both"></div>
                    </div>
					
				</div>
				
                <div class="table-responsive">
                    <table class="table table-bordered table-striped dfTable reports-table">
                        <thead>
							<tr>
								<th style="min-width:30px; width: 30px; text-align: center;">
									<input class="checkbox checkft" type="checkbox" name="check"/>
								</th>
								<th><?= lang("code") ?></th>
								<th><?= lang("name") ?></th>
								<th><?= lang("position") ?></th>
								<th><?= lang("department") ?></th>
								<th><?= lang("group") ?></th>
								<th><?= lang("date") ?></th>
								<th><?= lang("ot_policy") ?></th>
								<th><?= lang("ot_time") ?></th>
								<th><?= lang("check_time") ?></th>
								<th><?= lang("ot") ?></th>
							</tr>
                        </thead>
                        <tbody>
							<?php
								$employee_ots = $this->attendances_model->getEmployeeOT();
								$tbody = '';

								if($employee_ots){
									foreach($employee_ots as $employee_ot){
										$ot_duration = '';
										$ot_time = '';
										$ot = 0;
										$check_times = explode(',',$employee_ot->check_time);
										$start_time = 0;
										$end_time = 0;
										
										if($check_times){
											foreach($check_times as $check_time){
												if($start_time==0){
													$start_time = $check_time;
												}else if($start_time > $check_time && $end_time < $start_time){
													$end_time = $start_time;
													$start_time = $check_time;
												}else if($start_time > $check_time){
													$start_time = $check_time;
												}else if($end_time < $check_time){
													$end_time = $check_time;
												}
											}
										}
										if($start_time > 0){
											if($start_time > $employee_ot->time_in && $end_time==0){
												$end_time = $start_time;
												$start_time = $employee_ot->time_in;
											}
											if($start_time > 0){
												$ot_duration = $this->bpas->secTotime($start_time);
												$acutal_in = $this->bpas->secTotime($start_time);
											}
											if($end_time > 0){
												$ot_duration .=' - '.$this->bpas->secTotime($end_time);
												$acutal_out = $this->bpas->secTotime($end_time);
											}
											
											if($start_time < $employee_ot->time_in){
												$start_time = $employee_ot->time_in;
											}
											if($end_time > $employee_ot->time_out){
												$end_time = $employee_ot->time_out;
											}
											
											$ot = $end_time - $start_time;
											$ot = $this->bpas->round_time($employee_ot->round_min,$employee_ot->minimum_min,$ot);

											if($ot > $employee_ot->minimum_min){
												$ot_time = $this->bpas->secTotime($ot);
												$tbody .='<tr>
															<td><input value="'.$employee_ot->id.'" class="checkbox multi-select input-xs" type="checkbox" name="val[]"/>
																<input class="if_check" disabled type="hidden" name="employee_id[]" value="'.$employee_ot->id.'"/>
																<input class="if_check" disabled type="hidden" name="date[]" value="'.$employee_ot->check_date.'"/>
																<input class="if_check" disabled type="hidden" name="policy_ot_id[]" value="'.$employee_ot->policy_ot_id.'"/>
																<input class="if_check" disabled type="hidden" name="check_in[]" value="'.$acutal_in.'"/>
																<input class="if_check" disabled type="hidden" name="check_out[]" value="'.$acutal_out.'"/>
																<input class="if_check" disabled type="hidden" name="ot[]" value="'.$ot_time.'"/>
																<input class="if_check" disabled type="hidden" name="type[]" value="'.$employee_ot->type.'"/>
															</td>
															<td>'.$employee_ot->empcode.'</td>
															<td>'.$employee_ot->firstname.' '.$employee_ot->lastname.'</td>
															<td>'.$employee_ot->position.'</td>
															<td>'.$employee_ot->department.'</td>
															<td>'.$employee_ot->group.'</td>
															<td class="text-center">'.$this->bpas->hrsd($employee_ot->check_date).'</td>
															<td>'.$employee_ot->ot_policy.'</td>
															<td class="text-center">'.$this->bpas->secTotime($employee_ot->time_in).' - '.$this->bpas->secTotime($employee_ot->time_out).'</td>
															<td class="text-center">'.$ot_duration.'</td>
															<td class="text-center">'.$ot_time.'</td>
														</tr>';
											}	
										}
										
									}
								}else{
									$tbody = '<tr><td colspan="11" class="dataTables_empty">'.lang('sEmptyTable').'</td></tr>';
								}
								echo $tbody; 
							?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
    
		
		$('#form').slideDown();
		
		$('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
		
		$('.checkft').live('ifChecked',function(){
			$('.if_check').attr('disabled',false);
		});
		$('.checkft').live('ifUnchecked',function(){
			$('.if_check').attr('disabled',true);
		});
		
		
		$('.multi-select').live('ifChecked',function(){
			var parent = $(this).parent().parent();
			parent.find('.if_check').attr('disabled',false);
		});
		$('.multi-select').live('ifUnchecked',function(){
			var parent = $(this).parent().parent();
			parent.find('.if_check').attr('disabled',true);
		});
		


		getDepPos();
		$('#biller').on('change',function(){
			getDepPos();
		});
		$('#department').on('change',function(){
			getGroup();
		});
		
		function getDepPos(){
			var biller = $("#biller").val();
			$.ajax({
				url : "<?= admin_url("hr/get_dep_pos") ?>",
				type : "GET",
				dataType : "JSON",
				data : { biller : biller },
				success : function(data){
					$("#department").html(data.department_opt);
					$("#department").select2();
					$("#position").html(data.position_opt);
					$("#position").select2();
					getGroup();
				}
			});
		}

		function getGroup(){
			var department = $("#department").val();
			$.ajax({
				url : "<?= admin_url("hr/get_group") ?>",
				type : "GET",
				dataType : "JSON",
				data : { department_id : department },
				success : function(data){
					$("#group").html(data.group_opt);
					$("#group").select2();
				}
			});
		}
		

		
    });
</script>
