<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_document'); ?></h4>
		
        </div>
		<?php  
		
		$attrib = array('data-toggle' => 'validator', 'role' => 'form');
		echo admin_form_open_multipart("hr/add_document/".$employee_id, $attrib); ?>
        
			<div class="modal-body">
				<p><?= lang('enter_info'); ?></p>
				<div class="row">
					<div class="col-sm-12">
						<div class="form-group">
							<?php echo lang('title', 'd_name'); ?>
							<div class="controls">
								<input type="text" id="d_name" name="d_name" required class="form-control"/>
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
							<input type="text" name="expired_date" class="form-control date"/>
						</div>
					</div>
						<div class="form-group">
							<?php echo lang('description', 'd_description'); ?>
							<div class="controls">
								<textarea id="d_description" name="d_description" class="form-control"></textarea>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<?php echo form_submit('add_document', lang('add_document'), 'class="btn btn-primary"'); ?>
			</div>
			
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>