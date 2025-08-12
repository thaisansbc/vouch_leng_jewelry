<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_kpi_question'); ?></h4>
        </div>
        <?=  admin_form_open_multipart("hr/add_kpi_question"); ?>
        
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			<div class="form-group">
                <?php echo lang('type', 'type'); ?> *
                <div class="controls">
					<input type="hidden" name="kpi_type" value="<?= $kpi_type ?>" />
					<select class="form-control" name="type">
						<option value="0"><?= lang('general') ?></option>
						<option value="1"><?= lang('employee') ?></option>
					</select>
                </div>
            </div>
			<div class="form-group">
                <?php echo lang('question', 'question'); ?> *
                <div class="controls">
                    <textarea name="question" class="form-control"  required="required"></textarea>
                </div>
            </div>
			<div class="form-group">
                <?php echo lang('question_kh', 'question_kh'); ?>
                <div class="controls">
                    <textarea name="question_kh" class="form-control"></textarea>
                </div>
            </div>

			<div class="form-group">
                <?php echo lang('value_percentage', 'value_percentage'); ?> (<?= lang('no_%') ?>)
                <div class="controls">
                    <?php echo form_input('value_percentage', 0, 'class="form-control input-tip value_percentage"'); ?>
                </div>
            </div>
			
			<div class="form-group">
                <?php echo lang('min_rate', 'min_rate'); ?>
                <div class="controls">
                    <?php echo form_input('min_rate', 0, 'class="form-control input-tip min_rate"'); ?>
                </div>
            </div>
			
			<div class="form-group">
                <?php echo lang('max_rate', 'max_rate'); ?>
                <div class="controls">
                    <?php echo form_input('max_rate', 0, 'class="form-control input-tip max_rate"'); ?>
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
		$(document).on("focus", '.min_percentage, .max_percentage, .value_percentage', function () {
			old_value = $(this).val();
		}).on("change", '.min_percentage, .max_percentage, .value_percentage', function () {
			if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
				$(this).val(old_value);
				bootbox.alert(lang.unexpected_value);
				return;
			}
		});   
    });
</script>