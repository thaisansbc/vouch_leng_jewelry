<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-calendar"></i><?= lang('employee_leave_report') ?></h2>
		
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
		<div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang("employee_leave_report") ?></p>
				<?php echo admin_form_open("attendances/employee_leave_report", ' id="form-submit" '); ?>
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
								echo form_dropdown('department', $department_opt, '', 'id="department" class="form-control"');
								?>
                            </div>
                        </div>
						
						<div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="group"><?= lang("group"); ?></label>
								<?php
								$group_opt = array(lang('all')." ".lang('group'));
								echo form_dropdown('group', $group_opt, '', 'id="group" class="form-control"');
								?>
                            </div>
                        </div>
						
						<div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="position"><?= lang("position"); ?></label>
								<?php
								$position_opt = array(lang('all')." ".lang('position'));
								echo form_dropdown('position', $position_opt, '', 'id="position" class="form-control"');
								?>
                            </div>
                        </div>
						
						<div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="group"><?= lang("employee"); ?></label>
								<input type="text" name="employee_id" id="suggest_employee" value="<?= set_value('employee_id') ?>" class="form-control ui-autocomplete-input" />
								<input type="hidden" name="employee" value="<?= set_value('employee') ?>" id="suggest_employee_id">
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
						</div>
						<div style="clear:both"></div>
                    </div>
					
				</div>
				<?php
					if(isset($_POST['start_date'])){
						$start_date = $this->bpas->fsd($_POST['start_date']);
					}else{
						$start_date = date('Y-m-d');
					}
					if(isset($_POST['end_date'])){
						$end_date = $this->bpas->fsd($_POST['end_date']);
					}else{
						$end_date = date('Y-m-d');
					}
					
					$tbody = '';
					if($employee_leave_categories){
						$total_categories = array();
						foreach($employee_leave_categories as $employee_leave_categorie){
							$tbody .='<tr class="employee_leave_link" id="'.$employee_leave_categorie->employee_id.'date'.$start_date.'date'.$end_date.'">
										<td>'.$employee_leave_categorie->empcode.'</td>
										<td>'.$employee_leave_categorie->firstname.' '.$employee_leave_categorie->lastname.'</td>
										<td>'.$employee_leave_categorie->position.'</td>
										<td>'.$employee_leave_categorie->department.'</td>
										<td>'.$employee_leave_categorie->group.'</td>';
							if($leave_categories){
								foreach($leave_categories as $leave_category){ 
									if($leave_category->id == $employee_leave_categorie->leave_category_id){
										if (array_key_exists($leave_category->id, $total_categories)) {
											$total_categories[$leave_category->id] = ($total_categories[$leave_category->id] + $employee_leave_categorie->total_leave)-0;
										}else{
											$total_categories[$leave_category->id] = ($employee_leave_categorie->total_leave)-0;
										}

										
										$tbody.= '<td class="text-center">'.($employee_leave_categorie->total_leave-0).'</td>';
									}else{
										$tbody.= '<td class="text-center">0</td>';
									}
								}
							}	
							$tbody .='</tr>';
						}
					}else{
						$tbody = '<tr><td colspan="12" class="dataTables_empty">'.lang('sEmptyTable').'</td></tr>';
					}
				?>
				
				<?php
					$header_leave_category = '';
					$footer_leave_category = '';
					if($leave_categories){
						foreach($leave_categories as $leave_category){ 
							$header_leave_category.='<th>'.lang($leave_category->name).'</th>';
							$footer_leave_category.='<td class="text-center" style="font-weight:bold">'.(isset($total_categories[$leave_category->id]) ? $total_categories[$leave_category->id] : 0).'</td>';
						}
					}
				?>
				
				
                <div class="table-responsive">
                    <table border="1" class="table table-bordered table-striped dfTable reports-table">
                        <thead>
							<tr>
								<th><?= lang("code") ?></th>
								<th><?= lang("name") ?></th>
								<th><?= lang("position") ?></th>
								<th><?= lang("department") ?></th>
								<th><?= lang("group") ?></th>
								<?= $header_leave_category ?>
							</tr>
                        </thead>
                        <tbody>
							<?= $tbody ?>
                        </tbody>
						<tfoot>
							<td style="font-weight:bold" class="text-right" colspan="5"><?= lang('total') ?></td>
							<?= $footer_leave_category ?>
						</tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
    
		
		$('#form').hide();
		$('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
		
		$("#xls").click(function(e) {
			var result = "data:application/vnd.ms-excel," + encodeURIComponent( '<meta charset="UTF-8"><style> table { white-space:wrap; } table th, table td{ font-size:12px !important; border:1px solid black !important }</style>' + $('.table-responsive').html());
			this.href = result;
			this.download = "employee_leave_report.xls";
			return true;			
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
			var department  = "<?= (isset($_POST['department'])?$_POST['department']:0) ?>";
			var position  = "<?= (isset($_POST['position'])?$_POST['position']:0) ?>";
			
			$.ajax({
				url : "<?= admin_url("hr/get_dep_pos") ?>",
				type : "GET",
				dataType : "JSON",
				data : { biller : biller, department : department, position : position},
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
			var group  = "<?= (isset($_POST['group'])?$_POST['group']:0) ?>";
			$.ajax({
				url : "<?= admin_url("hr/get_group") ?>",
				type : "GET",
				dataType : "JSON",
				data : { department_id : department, group : group },
				success : function(data){
					$("#group").html(data.group_opt);
					$("#group").select2();
				}
			});
		}
		
    });
</script>
