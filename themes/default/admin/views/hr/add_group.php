<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_group'); ?></h4>
        </div>
        <?=  admin_form_open_multipart("hr/add_group"); ?>
        
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			
			<div class="form-group">
				<?php echo lang('department', 'department'); ?>
				<div class="controls">
					<?php
					$department_ops= array();
					foreach ($departments as $department) {
					   $department_ops[$department->id] = $department->name; 
					}
					echo form_dropdown('department', $department_ops, 0, 'id="department" class="form-control department" ');
					?>
				</div>
			</div>
			
			<div class="form-group">
                <?php echo lang('code', 'code'); ?> *
                <div class="controls">
                    <input type="text" class="form-control" name="code" required="required"/>
                </div>
            </div>
			
            <div class="form-group">
                <?php echo lang('name', 'name'); ?> *
                <div class="controls">
                    <input type="text" class="form-control" name="name" required="required"/>
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