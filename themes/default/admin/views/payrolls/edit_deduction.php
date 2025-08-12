<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_deduction'); ?></h4>
        </div>
        <?php
            $attrib = array('data-toggle' => 'validator', 'role' => 'form');
            echo admin_form_open("payrolls/edit_deduction/".$id, $attrib); 
        ?>
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			<div class="form-group">
                <?php echo lang('name', 'name'); ?> *
                <div class="controls">
                    <input type="text" class="form-control" value="<?= $row->name ?>" name="name" required="required"/>
                </div>
            </div>
			<div class="form-group">
                <?php echo lang('amount', 'value'); ?> *
                <div class="controls">
                    <input type="text" class="form-control" value="<?= $row->value ?>" name="value" required="required"/>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_deduction', lang('update_deduction'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>