<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('edit_room'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open("suspended_note/edit_room/".$id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <?= lang('room', 'room'); ?>
                <?= form_input('room', set_value('room', $rooms->name), 'class="form-control tip" id="room" required="required"'); ?>
            </div>

            <div class="form-group">
                <?= lang('type', 'type'); ?>
                <?= form_input('type', set_value('type', $rooms->type), 'class="form-control tip" id="type"'); ?>
            </div>

            <div class="form-group">
				<?= lang("warehouse", "warehouse"); ?>
				<?php
				$wh[''] = lang('select').' '.lang('warehouse');
				foreach ($warehouses as $warehouse) {
					$wh[$warehouse->id] = $warehouse->name;
				}
				echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $rooms->warehouse_id), 'id="warehouse" class="form-control select" required="required" style="width:100%;" ');
				?>
			</div>
			<div class="form-group">
                <label for="percent"><?php echo lang("Price Per Hour"); ?></label>
				<?php echo form_input('price', $rooms->price, 'class="form-control" id="percent"'); ?>
            </div>
			<div class="form-group">
                <label for="percent"><?php echo lang("discount_amount"); ?></label>
				<?php echo form_input('amount', $rooms->amount, 'class="form-control" id="percent"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="address"><?php echo $this->lang->line("description"); ?></label>
                <?php echo form_textarea('description', $rooms->description, 'class="form-control" id="description"'); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?= form_submit('edit_room', lang('edit_room'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>
<?= $modal_js ?>
