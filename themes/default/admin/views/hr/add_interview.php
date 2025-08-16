<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_interview'); ?></h4>
        </div>
        <?=  admin_form_open_multipart("hr/add_interview"); ?>
        
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			<div class="form-group">
                <?php echo lang('date', 'date'); ?>
                <div class="controls">
                    <input type="text" class="form-control date" value="<?= date('d/m/Y');?>" name="date" required="required"/>
                </div>
            </div>
			<div class="form-group">
                <?= lang("shortlist_name", "shortlist_name"); ?>
                <?php
                $shl[""] = "";
                if($shortlists){
                    foreach ($shortlists as $shortlist) {
                        $shl[$shortlist->id] = $shortlist->lastname.' '.$shortlist->firstname;
                    }
                }
                echo form_dropdown('candidate', $shl, (isset($shortlist_id->candidate_id) ? $shortlist_id->candidate_id : ''), 'id="candidate" data-placeholder="' . lang("select") . ' ' . lang("candidate") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                ?>
            </div>
            <div class="form-group">
                <?php echo lang('interviewer', 'interviewer'); ?>
                
                <?php
                $emp[""] = "";
                if($employees){
                    foreach ($employees as $employee) {
                        $emp[$employee->id] = $employee->lastname.' '.$employee->firstname;
                    }
                }
                echo form_dropdown('employee', $emp, (isset($_POST['employee']) ? $_POST['employee'] : ''), 'id="employee" data-placeholder="' . lang("select") . ' ' . lang("employee") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                ?>
            </div>
			
            <div class="form-group">
                <?php echo lang('total_mark', 'total_mark'); ?>
                <div class="controls">
                    <input type="text" class="form-control" name="total_mark" required="required"/>
                </div>
            </div>
			<div class="form-group">
                <?php echo lang('selection', 'selection'); ?>
                <div class="controls">
                    <?php
                    $selection = [
                        ''            => lang('please_select'),
                        'select'      => lang('selected'),
                        'deselected'  => lang('deselected'),
                    ];
                    echo form_dropdown('selection', $selection,'', 'class="form-control tip" required="required" style="width:100%;"');
                    ?>
                </div>
            </div>
			<div class="form-group">
                <?php echo lang('description', 'description'); ?>
                <div class="controls">
                    <textarea name="description" class="form-control"></textarea>
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