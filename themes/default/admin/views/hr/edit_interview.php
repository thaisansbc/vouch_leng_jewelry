<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_interview'); ?></h4>
        </div>
        <?=  admin_form_open_multipart("hr/edit_interview/".$id); ?>
        
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			
			<div class="form-group">
                <?php echo lang('date', 'date'); ?>
                <div class="controls">
                    <input type="text" class="form-control date" value="<?= $this->bpas->hrsd($row->interviewer_id);?>" name="date" required="required"/>
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
                echo form_dropdown('candidate', $shl, $row->shortlist_id, 'id="candidate" data-placeholder="' . lang("select") . ' ' . lang("candidate") . '" required="required" class="form-control input-tip select" style="width:100%;"');
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
                echo form_dropdown('employee', $emp,$row->interviewer_id, 'id="employee" data-placeholder="' . lang("select") . ' ' . lang("employee") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                ?>
            </div>
            
            <div class="form-group">
                <?php echo lang('total_mark', 'total_mark'); ?>
                <div class="controls">
                    <input type="text" class="form-control" value="<?= $row->total_mark;?>" name="total_mark" required="required"/>
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
                    echo form_dropdown('selection', $selection,$row->selection, 'class="form-control tip" required="required" style="width:100%;"');
                    ?>
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