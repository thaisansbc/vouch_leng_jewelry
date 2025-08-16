<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_kpi_type'); ?></h4>
        </div>
        <?=  admin_form_open_multipart("hr/edit_kpi_type/".$id); ?>
        
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

			<div class="form-group">
                <?php echo lang('code', 'code'); ?>
                <div class="controls">
                    <input type="text" class="form-control" value="<?= $row->code ?>" name="code" />
                </div>
            </div>
			
			<div class="form-group">
                <?php echo lang('name', 'name'); ?>
                <div class="controls">
                    <input type="text" class="form-control" value="<?= $row->name ?>" name="name" />
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