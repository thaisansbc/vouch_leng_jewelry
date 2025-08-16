<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_contribution_payment_condition'); ?></h4>
        </div>
        <?=  admin_form_open_multipart("hr/edit_con_payment/".$id); ?>
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            
			<div class="form-group">
                <?php echo lang('min_salary', 'min_salary'); ?> *
                <div class="controls">
                    <input type="text" value="<?= $row->min_salary ?>" class="form-control" name="min_salary" required="required"/>
                </div>
            </div>
			
			<div class="form-group">
                <?php echo lang('max_salary', 'max_salary'); ?> *
                <div class="controls">
                    <input type="text" value="<?= $row->max_salary ?>" class="form-control" name="max_salary" required="required"/>
                </div>
            </div>
			<div class="form-group">
                <?php echo lang('reduce_tax', 'reduce_tax'); ?> *
                <div class="controls">
                    <input type="text" value="<?= $row->contributory_wage ?>" class="form-control" name="reduce_tax" required="required"/>
                </div>
            </div>
			<div class="form-group">
                <?php echo lang('tax_percent', 'tax_percent'); ?> *
                <div class="controls">
                    <input type="text" value="<?= $row->or_rate ?>" class="form-control" name="tax_percent" required="required"/>
                </div>
            </div>
            <div class="form-group">
                <?php echo lang('hc_rate', 'tax_percent'); ?> *
                <div class="controls">
                    <input type="text" value="<?= $row->hc_rate ?>" class="form-control" name="hc_rate" required="required"/>
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