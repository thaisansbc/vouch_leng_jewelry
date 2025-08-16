<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_kpi_measure'); ?></h4>
        </div>
        <?=  admin_form_open_multipart("hr/add_kpi_measure"); ?>
        
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

			<div class="form-group">
                <?php echo lang('name', 'name'); ?> * 
                <div class="controls">
					<input type="hidden" name="kpi_type" value="<?= $kpi_type ?>" />
                    <input type="text" class="form-control" name="name"  required="required"/>
                </div>
            </div>
			<div class="form-group">
                <?php echo lang('name_kh', 'name_kh'); ?>
                <div class="controls">
                    <input type="text" class="form-control" name="name_kh"/>
                </div>
            </div>
			
			<div class="form-group">
                <?php echo lang('min_percentage', 'min_percentage'); ?> (<?= lang('no_%') ?>)
                <div class="controls">
                    <?php echo form_input('min_percentage', 0, 'class="form-control input-tip min_percentage"'); ?>
                </div>
            </div>
			
			<div class="form-group">
                <?php echo lang('max_percentage', 'max_percentage'); ?> (<?= lang('no_%') ?>)
                <div class="controls">
                    <?php echo form_input('max_percentage', 0, 'class="form-control input-tip max_percentage"'); ?>
                </div>
            </div>
			<div class="form-group">
                <?php echo lang('increase_salary', 'increase_salary'); ?> 
                <div class="controls">
                    <?php echo form_input('increase_salary', 0, 'class="form-control input-tip increase_salary"'); ?>
                </div>
            </div>
			
			<div class="form-group">
				<?php echo lang('color', 'color'); ?>
				<div class="controls">
				  <input type="color" name="color">
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

<script type="text/javascript">
    $(document).ready(function() {
		var old_value;
		$(document).on("focus", '.min_percentage, .max_percentage', function () {
			old_value = $(this).val();
		}).on("change", '.min_percentage, .max_percentage', function () {
			if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
				$(this).val(old_value);
				bootbox.alert(lang.unexpected_value);
				return;
			}
		});   
    });
</script>