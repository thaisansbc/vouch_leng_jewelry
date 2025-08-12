<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_working_history'); ?></h4>
		
        </div>
		<?php 
			$attrib = array('data-toggle' => 'validator', 'role' => 'form');
			echo admin_form_open_multipart("hr/edit_working_history/".$id, $attrib); 
		?>
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			<div class="row">
				<div class="col-lg-12">
					<div class="form-group">
						<?php echo lang('company', 'w_company'); ?>
						<div class="controls">
							<input type="text" id="w_company" required value="<?= $row->company ?>" name="w_company" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('position', 'w_position'); ?>
						<div class="controls">
							<input type="text" id="w_position" required value="<?= $row->position ?>" name="w_position" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('start_date', 'start_date'); ?>
						<div class="controls">
							<input type="text" id="w_start_date" value="<?= $this->bpas->hrsd($row->start_date) ?>" name="w_start_date" class="form-control date"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('end_date', 'end_date'); ?>
						<div class="controls">
							<input type="text" id="w_end_date" value="<?= $this->bpas->hrsd($row->end_date) ?>" name="w_end_date" class="form-control date"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('document', 'document'); ?>
						<div class="controls">
							<input type="file" id="w_document" name="w_document" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('description', 'description'); ?>
						<div class="controls">
							<textarea id="w_description" name="w_description" class="form-control"><?= $row->description ?></textarea>
						</div>
					</div>
				</div>
			</div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_working_history', lang('update_working_history'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>