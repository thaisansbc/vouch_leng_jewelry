<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_family_info'); ?></h4>
        </div>
		<?php  
			$attrib = array('data-toggle' => 'validator', 'role' => 'form');
			echo admin_form_open("hr/add_family_info/".$employee_id, $attrib); 
		?>
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			<div class="row">
				<div class="col-lg-12">	
					<div class="form-group">
						<?php echo lang('first_name', 'f_first_name'); ?>
						<div class="controls">
							<input type="text" id="f_first_name" name="f_first_name" required class="form-control"/>
						</div>
					</div>	
					<div class="form-group">
						<?php echo lang('last_name', 'f_last_name'); ?>
						<div class="controls">
							<input type="text" id="f_last_name" name="f_last_name" required class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('relationship', 'f_relationship'); ?>
						<div class="controls">
							<?php
								$relationships_= array();
								foreach ($relationships as $relationship) {
									$relationships_[$relationship->id] = $relationship->name; 
								}
								echo form_dropdown('f_relationship', $relationships_, 0, 'id="f_relationship" required class="form-control f_relationship"');
							?>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('dob', 'dob'); ?>
						<div class="controls">
							<input type="text" id="f_dob" name="f_dob" class="form-control date"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('occupation', 'occupation'); ?>
						<div class="controls">
							<input type="text" id="f_occupation" name="f_occupation" class="form-control"/>
						</div>
					</div>	
					<div class="form-group">
						<?php echo lang('phone', 'phone'); ?>
						<div class="controls">
							<input type="text" id="f_telephone" name="f_telephone" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('pob', 'pob'); ?>
						<div class="controls">
							<input type="text" id="f_pob" name="f_pob" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('address', 'address'); ?>
						<div class="controls">
							<input type="text" id="f_address" name="f_address" class="form-control"/>
						</div>
					</div>

				</div>
			</div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_family_info', lang('add_family_info'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>