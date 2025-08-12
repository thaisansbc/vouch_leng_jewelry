<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_qualification'); ?></h4>
        </div>
		<?php  
			$attrib = array('data-toggle' => 'validator', 'role' => 'form');
			echo admin_form_open_multipart("hr/add_qualification/".$employee_id, $attrib); 
		?>
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			<div class="row">
				<div class="col-lg-12">	
					<div class="form-group">
						<?php echo lang('certificate', 'q_certificate'); ?>
						<div class="controls">
							<input type="text" id="q_certificate" required name="q_certificate" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('major', 'q_major'); ?>
						<div class="controls">
							<input type="text" id="q_major" required name="q_major" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('school', 'q_school'); ?>
						<div class="controls">
							<input type="text" id="q_school" required name="q_school" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('level', 'level'); ?>
						<div class="controls">
							<input type="text" id="q_level" name="q_level" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('start_date', 'start_date'); ?>
						<div class="controls">
							<input type="text" id="q_start_date" name="q_start_date" class="form-control date"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('end_date', 'end_date'); ?>
						<div class="controls">
							<input type="text" id="q_end_date" name="q_end_date" class="form-control date"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('language', 'language'); ?>
						<div class="controls">
							<input type="text" id="q_language" name="q_language" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('document', 'document'); ?>
						<div class="controls">
							<input type="file" id="q_field" name="q_document" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('description', 'description'); ?>
						<div class="controls">
							<textarea class="q_description" name="q_description"></textarea>
						</div>
					</div>
				</div>
			</div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_qualification', lang('add_qualification'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>