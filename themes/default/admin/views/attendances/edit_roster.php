<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_roster'); ?></h4>
        </div>
        <?=  admin_form_open_multipart("attendances/edit_roster/".$policy->id); ?>
        
		<div class="modal-body">
			<div class="form-group">
                <?= lang("employee", "employee"); ?>
                <?php
                $emp[""] = "";
                if($employees){
                    foreach ($employees as $employee) {
                        $emp[$employee->id] = $employee->lastname.' '.$employee->firstname;
                    }
                }
                echo form_dropdown('employee', $emp,$policy->employee_id, 'id="candidate" data-placeholder="' . lang("select") . ' ' . lang("employee") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                ?>
            </div>
            <div class="form-group">
				<?php echo lang('working_day', 'working_day'); ?> *
				<div class="controls">
					<input type="text" value="<?= $this->bpas->hrsd($policy->working_day) ?>" class="form-control date" name="working_day" required="required"/>
				</div>
			</div>
			<div class="form-group">
                <?php echo lang('policy', 'policy_id'); ?>
                <div class="controls policy">
                    <?php
                    $policy_opt = array(lang('select')." - ".lang('policy'));
                    if($policies){
                        foreach($policies as $pol){
                            $policy_opt[$pol->id] = $pol->code.' '.$pol->policy;
                        }   
                    }
                    echo form_dropdown('policy', $policy_opt, $policy->policy_id, 'id="policy_id" class="form-control policy" required="required"');
                    ?>
                </div>
            </div>
			<div class="row">
                <div class="col-md-6">
					<div class="form-group">
                        <?= lang('first_half', 'first_half'); ?>
                        <?php $first_half = ['1' => 'Yes', '0' => 'No']; ?>
                        <?= form_dropdown('first_half', $first_half, $policy->time_one, 'class="form-control" id="first_half" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('second_half', 'second_half'); ?>
                        <?php $first_half = ['1' => 'Yes', '0' => 'No']; ?>
                        <?= form_dropdown('second_half', $first_half, $policy->time_two, 'class="form-control" id="second_half" required="required"'); ?>
                    </div>
                </div>
            </div>
			<div class="form-group hide">
                <?php echo lang('note', 'note'); ?>
                <div class="controls">
                    <textarea name="note" class="form-control"><?= $policy->note ?></textarea>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('submit', lang('submit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>
<script type="text/javascript">
	$(function(){
		$('.timepicker').datetimepicker({
			format: 'hh:ii:ss',
			fontAwesome: true,
			autoclose: 1,
			startView: 0,
			todayBtn: 1
		});	
	});
</script>