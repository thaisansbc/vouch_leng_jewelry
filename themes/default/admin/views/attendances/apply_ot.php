<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('apply_ot'); ?></h4>
        </div>
        <?=  admin_form_open_multipart("attendances/add_ot"); ?>
        
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            
            <div class="form-group">
                <?= lang("employee", "employee"); ?>
                <?php
                $emp[""] = "";
                if($employees){
                    foreach ($employees as $employee) {
                        $emp[$employee->id] = $employee->empcode.' | '.$employee->lastname.' '.$employee->firstname;
                    }
                }
                echo form_dropdown('employee', $emp, (isset($_POST['employee']) ? $_POST['employee'] : $id), 'id="candidate" data-placeholder="' . lang("select") . ' ' . lang("employee") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                ?>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <?php echo lang('from_time', 'from_time'); ?>
                    <div class="controls">
                        <input type="text" class="form-control datetime" name="from_time" required="required"/>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <?php echo lang('to_date', 'to_date'); ?>
                    <div class="controls">
                        <input type="text" class="form-control datetime" name="to_time" required="required"/>
                    </div>
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