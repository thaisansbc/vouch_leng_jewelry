<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_truck'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("drivers/edit_vehicle/".$truck->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			<div class="form-group">
				<label class="control-label" for="code"><?php echo $this->lang->line("plate"); ?></label>
				<?php echo form_input('code', $truck->code, 'class="form-control" id="code" required="required"'); ?>
			</div>
			<div class="form-group">
				<label class="control-label" for="model"><?php echo $this->lang->line("model"); ?></label>
				<?php echo form_input('model', $truck->model, 'class="form-control" id="model" required="required"'); ?>
			</div>
			<div class="form-group">
				<?php echo lang('driver', 'driver_id'); ?>
				<div class="controls">
					<?php
					$dr[""] = lang("select")." ".lang("driver");
					foreach ($drivers as $driver) {
					   $dr[$driver->id] = $driver->name; 
					}
					echo form_dropdown('driver_id', $dr, $truck->driver_id, 'id="driver_id" class="form-control driver_id" ');
					?>
				</div>
			</div>
			<div class="form-group">
				<?= lang("status", "status"); ?>
				<?php
				$yn = array('active' => lang('active'),'inactive' => lang('inactive'));
				echo form_dropdown('status', $yn, $truck->status, 'id="status" class="form-control tip"');
				?>
			</div>
			<div class="form-group">
				<?= lang("attachment", "attachment") ?>
				<input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="attachment" data-show-upload="false"data-show-preview="false" class="form-control file">
			</div>
			<div class="form-group">
                <label class="control-label" for="note"><?php echo $this->lang->line("note"); ?></label>
                <?php echo form_textarea('note', $truck->note, 'class="form-control" id="note"'); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_vehicle', lang('edit_vehicle'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>

