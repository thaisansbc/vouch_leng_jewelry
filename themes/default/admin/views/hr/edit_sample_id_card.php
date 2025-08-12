<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_sample_id_card'); ?></h4>
        </div>
        <?=  admin_form_open_multipart("hr/edit_sample_id_card/".$id); ?>
        
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			<div class="form-group">
                <?php echo lang('name', 'name'); ?> *
                <div class="controls">
                    <input type="text" class="form-control" value="<?= $row->name ?>" name="name"  required="required" />
                </div>
            </div>
			<div class="form-group">
                <?php echo lang('width', 'width'); ?>(Pixel) *
                <div class="controls">
                    <input type="text" class="form-control" value="<?= $row->width ?>" name="width" required="required"/>
                </div>
            </div>
			<div class="form-group">
                <?php echo lang('height', 'height'); ?>(Pixel) *
                <div class="controls">
                    <input type="text" class="form-control" value="<?= $row->height ?>" name="height" required="required"/>
                </div>
            </div>
			<div class="form-group">
                <?= lang("front_card", "front_card") ?>
				<div class="controls">
					<input id="front_card" type="file" data-browse-label="<?= lang('browse'); ?>" name="front_card" data-show-upload="false" data-show-preview="false" class="form-control file">
				</div>
            </div>
			<div class="form-group">
                <?= lang("back_card", "back_card") ?>
				<div class="controls">
					<input id="back_card" type="file" data-browse-label="<?= lang('browse'); ?>" name="back_card" data-show-upload="false" data-show-preview="false" class="form-control file">
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