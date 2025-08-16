<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_penalty'); ?></h4>
        </div>
        <?=  admin_form_open_multipart("installments/add_penalty"); ?>
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <?php //if ($Settings->installment_penalty_option == 1) { ?>
			<div class="form-group">
                <?php echo lang('from_day', 'from_day'); ?> *
                <div class="controls">
                    <input type="text" class="form-control" name="from_day" required="required"/>
                </div>
            </div>
			<div class="form-group">
                <?php echo lang('to_day', 'to_day'); ?> *
                <div class="controls">
                    <input type="text" class="form-control" name="to_day" required="required"/>
                </div>
            </div>
            <?php //} ?>
			<div class="form-group">
                <?php echo lang('type', 'type'); ?> *
                <div class="controls">
                    <?php $type = ['' => 'fixed_amount', '%' => 'percentage']; ?>
                    <?= form_dropdown('type', $type,'', 'class="form-control tip"'); ?>
                </div>
            </div>
			<div class="form-group">
                <?php echo lang('amount', 'amount'); ?> *
                <div class="controls">
                    <input type="text" class="form-control" name="amount" required="required"/>
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