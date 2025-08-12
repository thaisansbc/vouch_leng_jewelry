<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_shortlist'); ?></h4>
        </div>
        <?=  admin_form_open_multipart("hr/edit_shortlist/".$id); ?>
        
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			
			<div class="form-group">
                <?= lang("candidate", "candidate"); ?>
                <?php
                $emp[""] = "";
                if($employees){
                    foreach ($employees as $employee) {
                        $emp[$employee->id] = $employee->lastname.' '.$employee->firstname;
                    }
                }
                echo form_dropdown('employee', $emp,$row->candidate_id, 'id="candidate" data-placeholder="' . lang("select") . ' ' . lang("employee") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                ?>
            </div>

            
            <div class="form-group">
                <?php echo lang('position', 'position'); ?>
                <div class="controls">
                    <?php 
                    $position_opt = array('');

                    $biller_ops = array();
                    foreach ($positions as $position) {
                       $position_opt[$position->id] = $position->name; 
                    }
                    echo form_dropdown('position', $position_opt, $row->job_position_id, 'id="position" class="form-control position" required="required"');
                    ?>
                </div>
            </div>
			<div class="form-group">
                <?php echo lang('shortlist_date', 'shortlist_date'); ?>
                <div class="controls">
                    <input type="text" class="form-control date" value="<?= $this->bpas->hrsd($row->shortlist_date); ?>" name="shortlist_date" required="required"/>
                </div>
            </div>
            <div class="form-group">
                <?php echo lang('interview_date', 'interview_date'); ?>
                <div class="controls">
                    <input type="text" class="form-control date" value="<?= $this->bpas->hrsd($row->interview_date); ?>" name="interview_date" required="required"/>
                </div>
            </div>
			
			<div class="form-group">
                <?php echo lang('description', 'description'); ?>
                <div class="controls">
                    <textarea name="description" class="form-control"><?= $row->description ?></textarea>
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