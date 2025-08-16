<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_emergency_contact'); ?></h4>
		
        </div>
		<?php  
		$attrib = array('data-toggle' => 'validator', 'role' => 'form');
		echo admin_form_open_multipart("hr/edit_emergency_contact/".$id, $attrib); ?>
        
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			
			<div class="row">
				
				<div class="col-sm-12">
				
					<div class="form-group">
						<?php echo lang('relationship', 'e_relationship'); ?>
						<div class="controls">
							<?php
								$rs['']= lang('select').' '.lang('relationship');
								foreach ($relationships as $relationship) {
									$rs[$relationship->id] = $relationship->name; 
								}
								echo form_dropdown('e_relationship', $rs, $row->relationship, 'id="e_relationship" required class="form-control f_relationship"');
							?>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('name', 'e_name'); ?>
						<div class="controls">
							<input type="text" id="e_name" value="<?= $row->name ?>" name="e_name" required class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('phone', 'e_phone'); ?>
						<div class="controls">
							<input type="text" id="e_phone" value="<?= $row->telephone ?>" required name="e_phone" class="form-control"/>
						</div>
					</div>
				
				</div>
				
			</div>
			  
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_emergency_contact', lang('update_emergency_contact'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>