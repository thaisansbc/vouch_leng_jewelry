<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_relationship'); ?></h4>
        </div>
        <?=  admin_form_open_multipart("hr/add_relationship"); ?>
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            
			<div class="form-group">
                <?php echo lang('name', 'name'); ?> *
                <div class="controls">
                    <input type="text" class="form-control" name="name" required="required"/>
                </div>
            </div>
			<div class="form-group">
                <div class="row">
                    <div class="col-xs-4">
                        <input type="radio" class="checkbox" value="" name="type">
                        <label for="none" class="padding05">
                            <?= lang('none'); ?>
                        </label>
                    </div>
                    <div class="col-xs-4">
                        <input type="radio" class="checkbox" value="spouse" name="type">
                        <label for="spouse" class="padding05">
                            <?= lang('spouse'); ?>
                        </label>
                    </div>
                    <div class="col-xs-4">
                        <input type="radio" class="checkbox" value="children" name="type">
                        <label for="children" class="padding05">
                            <?= lang('children'); ?>
                        </label>
                    </div>
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