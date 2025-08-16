<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_document'); ?></h4>
		
        </div>
		<?php  
		$attrib = array('data-toggle' => 'validator', 'role' => 'form');
		echo admin_form_open_multipart("hr/edit_document/".$id, $attrib); ?>
        
		<div class="modal-body">
			<p><?= lang('enter_info'); ?></p>
			<div class="row">
				<div class="col-sm-12">
					<div class="form-group">
						<?php echo lang('title', 'd_name'); ?>
						<div class="controls">
							<input type="text" id="d_name" value="<?= $row->name ?>" required name="d_name" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('document', 'document'); ?>
						<div class="controls">
							<input type="file" id="d_document" name="d_document" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('expired_date', 'expired_date'); ?>
						<div class="controls">
							<input type="text" value="<?= ($row->expired_date !='') ? $this->bpas->hrsd($row->expired_date):''; ?>" name="expired_date" class="form-control date"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('description', 'd_description'); ?>
						<div class="controls">
							<textarea id="d_description" name="d_description" class="form-control"><?= $row->description ?></textarea>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="modal-footer">
			<?php echo form_submit('edit_document', lang('edit_document'), 'class="btn btn-primary"'); ?>
		</div>

    	<?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>