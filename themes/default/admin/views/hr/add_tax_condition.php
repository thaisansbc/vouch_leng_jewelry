<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_tax_condition'); ?></h4>
        </div>
        <?=  admin_form_open_multipart("hr/add_tax_condition"); ?>
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            
			<div class="form-group">
                <?php echo lang('min_salary', 'min_salary'); ?> *
                <div class="controls">
                    <input type="text" class="form-control" name="min_salary" required="required"/>
                </div>
            </div>
			
			<div class="form-group">
                <?php echo lang('max_salary', 'max_salary'); ?> *
                <div class="controls">
                    <input type="text" class="form-control" name="max_salary" required="required"/>
                </div>
            </div>
			
			<div class="form-group">
                <?php echo lang('tax_percent', 'tax_percent'); ?> *
                <div class="controls">
                    <input type="text" class="form-control" name="tax_percent" required="required"/>
                </div>
            </div>
			
			<div class="form-group">
                <?php echo lang('reduce_tax', 'reduce_tax'); ?> *
                <div class="controls">
                    <input type="text" class="form-control" name="reduce_tax" required="required"/>
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