<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
			</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_tank'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("system_settings/edit_tank/".$id, $attrib); ?>
        
		<div class="modal-body">
            <p><?= lang('update_info'); ?></p>
			<div class="form-group">
				<?= lang("code", "code"); ?>
				<?php echo form_input('code', $row->code, 'class="form-control" id="code" required="required"'); ?>
			</div>
			<div class="form-group">
				<?= lang("name", "name"); ?>
				<?php echo form_input('name', $row->name, 'class="form-control" id="name" required="required"'); ?>
			</div>
			<div class="form-group hide">
				<?= lang("warehouse", "warehouse") ?>
				<?php  
				$wh['']	= lang('selected');                
				foreach($warehouses as $warehouse){
					$wh[$warehouse->id] = $warehouse->name;
				}
				echo form_dropdown('warehouse', $wh, $row->warehouse_id, 'class="form-control select" id="warehouse" style="width:100%"')
				?>
			</div>
			<div class="form-group">
				<?= lang("inactive", "inactive") ?>
				<?php                
					$it[0] = lang('no');
					$it[1] = lang('yes');
				echo form_dropdown('inactive', $it, $row->inactive, 'class="form-control select" id="inactive" style="width:100%"')
				?>
			</div>
		</div>
	
		<div class="modal-footer">
			<?php echo form_submit('edit_tank', lang('edit_tank'), 'class="btn btn-primary"'); ?>
		</div>
	</div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
