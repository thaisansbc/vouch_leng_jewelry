<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_kpi_question'); ?></h4>
        </div>
        <?=  admin_form_open_multipart("hr/edit_kpi_question/".$id); ?>
        
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			<div class="form-group">
                <?php echo lang('type', 'type'); ?> *
                <div class="controls">
					<select class="form-control" name="type">
						<option <?= ($row->type == 0 ? 'selected' : '') ?> value="0"><?= lang('general') ?></option>
						<option <?= ($row->type == 1 ? 'selected' : '') ?> value="1"><?= lang('employee') ?></option>
					</select>
                </div>
            </div>
			<div class="form-group">
                <?php echo lang('question', 'question'); ?> *
                <div class="controls">
                    <textarea name="question" class="form-control"  required="required"><?= $row->question ?></textarea>
                </div>
            </div>
			<div class="form-group">
                <?php echo lang('question_kh', 'question_kh'); ?>
                <div class="controls">
                    <textarea name="question_kh" class="form-control"><?= $row->question_kh ?></textarea>
                </div>
            </div>

			<div class="form-group">
                <?php echo lang('value_percentage', 'value_percentage'); ?> (<?= lang('no_%') ?>)
                <div class="controls">
                    <?php echo form_input('value_percentage', $row->value_percentage, 'class="form-control input-tip value_percentage"'); ?>
                </div>
            </div>
			
			<div class="form-group">
                <?php echo lang('min_rate', 'min_rate'); ?>
                <div class="controls">
                    <?php echo form_input('min_rate', $row->min_rate, 'class="form-control input-tip min_rate"'); ?>
                </div>
            </div>
			
			<div class="form-group">
                <?php echo lang('max_rate', 'max_rate'); ?>
                <div class="controls">
                    <?php echo form_input('max_rate', $row->max_rate, 'class="form-control input-tip max_rate"'); ?>
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