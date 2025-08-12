<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_tank'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("system_settings/add_tank", $attrib); ?>
		<div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			<div class="form-group">
				<?= lang("code", "code"); ?>
				<?php echo form_input('code', '', 'class="form-control" id="code" required="required"'); ?>
			</div>
			<div class="form-group">
				<?= lang("name", "name"); ?>
				<?php echo form_input('name', '', 'class="form-control" id="name" required="required"'); ?>
			</div>
			<div class="form-group hide">
				<?= lang("warehouse", "warehouse") ?>
				<?php
				$wh['']	= lang('selected');           
				foreach($warehouses as $warehouse){
					$wh[$warehouse->id] = $warehouse->name;
				}
				echo form_dropdown('warehouse', $wh, '', 'class="form-control select" id="warehouse" style="width:100%"')
				?>
			</div>
		</div>
	
		<div class="modal-footer">
			<?php echo form_submit('add_tank', lang('add_tank'), 'class="btn btn-primary"'); ?>
		</div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
