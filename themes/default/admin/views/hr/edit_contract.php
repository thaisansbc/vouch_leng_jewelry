<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_contract'); ?></h4>
		
        </div>
		<?php  
			$attrib = array('data-toggle' => 'validator', 'role' => 'form');
			echo admin_form_open_multipart("hr/edit_contract/".$id, $attrib);
		 ?>
        
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			
			<div class="row">
				<div class="col-lg-12">	

					<div class="form-group">
						<?php echo lang('employee_type', 'c_type'); ?>
						<div class="controls">
							<?php
								$tp[''] = lang("select") . " " .lang("employee_type");
								foreach($types as $type){
									$tp[$type->id] = $type->name;
								}
								echo form_dropdown('c_type', $tp, $row->employee_type_id, 'id="c_type" class="form-control" ');
							?>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('contract_type', 'contract_type'); ?>
						<div class="controls">
							<?php
								$con[''] = lang("select") . " " .lang("contract_type");
								$con['udc'] = lang("udc");
								$con['fdc'] = lang("fdc");
								echo form_dropdown('contract_type', $con, $row->contract_type, 'id="contract_type" required class="form-control" ');
							?>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('contract_title', 'c_title'); ?>
						<div class="controls">
							<input type="text" id="c_title" value="<?= $row->contract_title ?>" name="c_title" class="form-control"/>
						</div>
					</div>

					<div class="form-group">
						<?php echo lang('start_date', 'start_date'); ?>
						<div class="controls">
							<input type="text" id="c_start_date" value="<?= $this->bpas->hrsd($row->start_date) ?>" name="c_start_date" class="form-control date"/>
						</div>
					</div>	

					<div class="form-group">
						<?php echo lang('end_date', 'end_date'); ?>
						<div class="controls">
							<input type="text" id="c_end_date" value="<?= $this->bpas->hrsd($row->end_date) ?>" name="c_end_date" class="form-control date"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('basic_salary', 'c_basic_salary'); ?>
						<div class="controls">
							<input type="text" id="c_basic_salary" value="<?= $row->basic_salary ?>" name="c_basic_salary" required class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('severance', 'c_severance'); ?>
						<div class="controls">
							<input type="text" id="c_severance" value="<?= $row->severance ?>" name="c_severance" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<?php echo lang('document', 'document'); ?>
						<div class="controls">
							<input type="file" id="c_document" value="<?= isset($row->document)? $row->document: '' ?>" name="c_document" class="form-control"/>
						</div>
					</div>

					<div class="form-group">
						<?php echo lang('description', 'description'); ?>
						<div class="controls">
							<textarea id="c_description" name="c_description" class="form-control"><?= $row->description ?></textarea>
						</div>
					</div>
					
				</div>
			</div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_contract', lang('update_contract'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>