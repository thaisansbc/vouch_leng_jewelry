<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php echo admin_form_open("attendances/index", ' id="form-submit" '); ?>

<div class="box">
	<div class="box-header">
		<h2 class="#428BCA"><i class="fa-fw fa fa-calendar"></i><?= lang('generate_attendances'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang("list_results") ?></p>
				
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
								echo form_dropdown('department', $department_opt, isset($_POST['department'])? $_POST['department']: '', 'id="department" class="form-control"');
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
						<?php if ($this->Settings->project) { ?>
                            <div class="col-md-3 hide">
                            	<?= lang("project", "poproject"); ?>
                                <div class="project_box form-group">
                                    <?php
                                    $project_id = '';
                                    $pro[""] 	= lang('select')." ".lang('project');
                                    /*foreach ($projects as $project) {
                                        $pro[$project->project_id] = $project->project_name;
                                    }*/
                                    echo form_dropdown('project', $pro, (isset($_POST['project']) ? $_POST['project'] : $project_id), 'id="poproject" data-placeholder="' . lang("select") . ' ' . lang("project") . '" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } ?>
						<div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("employee", "suggest_employee"); ?>
                                <?php echo form_input('employee_id', (isset($_POST['employee_id']) ? $_POST['employee_id'] : ""), 'class="form-control" id="suggest_employee"'); ?>
                                <input type="hidden" name="employee" value="<?= isset($_POST['employee']) ? $_POST['employee'] : "" ?>" id="suggest_employee_id"/>
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
							<?php echo form_submit('generate_attendance', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> 
						</div>
						<div style="clear:both"></div>
                    </div>
					
				</div>
				
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
    
         $('#pdf').click(function (event) {
            event.preventDefault();
			$("#form-submit").append("<input type='hidden' name='pdf' value=1 />")
			$("#form-submit").submit();
            return false;
        });

		$("#xls").click(function(e) {
			event.preventDefault();
			$("#form-submit").append("<input type='hidden' name='xls' value=1 />")
			$("#form-submit").submit();
			return true;			
		});
		
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
			var biller_id = $(this).val();
			$.ajax({
				type: "get", 
				async: true,
				url: site.base_url + "projects/get_projects/",
				data : { biller_id : biller_id },
				dataType: "json",
				success: function (data) {
					var project_sel = "<select class='form-control' id='poproject' name='project'><option value=''><?= lang('select').' '.lang('project') ?></option>";
					if (data != false) {
						$.each(data, function () {
							project_sel += "<option value='"+this.project_id+"'>"+this.project_name+"</option>";
						});
						
					}
					project_sel += "</select>"
					$(".project_box").html(project_sel);
					$('select').select2();
				}
			});
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
