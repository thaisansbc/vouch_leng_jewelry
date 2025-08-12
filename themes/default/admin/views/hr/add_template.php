<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_template'); ?></h4>
        </div>
        <?=  admin_form_open_multipart("hr/add_template"); ?>
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			
			<div class="form-group">
				<?php echo lang('biller', 'biller'); ?>
				<div class="controls">
					<?php
					$biller_ops= array();
					foreach ($billers as $biller) {
					   $biller_ops[$biller->id] = $biller->company; 
					}
					echo form_dropdown('biller_id', $biller_ops, 0, 'id="biller_id" class="form-control biller_id" ');
					?>
				</div>
			</div>
			<div class="form-group">
                <?php echo lang('name', 'name'); ?>
                <div class="controls">
                    <input type="text" class="form-control" name="name" id="name" required="required"/>
                </div>
            </div>
			<div class="form-group">
				<?php echo lang('type', 'type'); ?>
				<div class="controls">
					<?php
					$type_opt["Contract"] = lang("contract");
					$type_opt["Resignation"] = lang("resignation");
					echo form_dropdown('type', $type_opt, "", 'id="type" class="form-control type" ');
					?>
				</div>
			</div>
			<div class="form-group">
				<?php echo lang('employee_type', 'employee_type'); ?>
				<div class="controls">
					<?php
					$e_type_opt["Worker"] = lang("Worker");
					$e_type_opt["Staff"] = lang("Staff");
					echo form_dropdown('employee_type', $e_type_opt, "", 'id="employee_type" class="form-control employee_type"');
					?>
				</div>
			</div>
			<div class="form-group">
                <?php echo lang('template', 'template'); ?>
                <div class="controls">
					<?php echo form_textarea('template', '', 'class="form-control" id="template"'); ?>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_template', lang('submit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>