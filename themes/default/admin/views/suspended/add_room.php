<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('add_room'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open("table/add_room", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <?= lang('room', 'room'); ?>
                <?= form_input('room', set_value('room'), 'class="form-control tip" id="room" required="required"'); ?>
            </div>
            <div class="form-group">
                <?= lang('pos_type', 'type'); ?>
                <?php
                $type = [ 'table' => lang('table'), 
                        'room' => lang('room'),
                        'bungalow' => lang('bungalow'),
                    ];
                echo form_dropdown('type', $type, (isset($_POST['type']) ? $_POST['type'] : ''), 'id="type" class="form-control select" required="required" style="width:100%;" ');
                ?>
            </div>
            <div class="form-group">
                <?= lang('type', 'type'); ?>
                <?php 
                $get_fields = $this->site->getcustomfield('suspended_note');
                $field ['']='';
                if (!empty($get_fields)) {
                    foreach ($get_fields as $field_id) {
                        $field[$field_id->id] = $field_id->description;
                    }
                }
                echo form_dropdown('suspend_type',$field, '', 'class="form-control select" id="type"'); ?>
            </div>
            <div class="form-group">
				<?= lang("warehouse", "warehouse"); ?>
				<?php
				$wh[''] = lang('select').' '.lang('warehouse');
				foreach ($warehouses as $warehouse) {
					$wh[$warehouse->id] = $warehouse->name;
				}
				echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ''), 'id="warehouse" class="form-control select" required="required" style="width:100%;" ');
				?>
			</div>
            <div class="form-group">
				<?= lang("floor", "floor"); ?>
				<?php
				$fl[''] = lang('select').' '.lang('floor');
				foreach ($floors as $floor) {
					$fl[$floor->id] = $floor->name;
				}
				echo form_dropdown('floor', $fl, (isset($_POST['floor']) ? $_POST['floor'] : ''), 'id="floor" class="form-control select" required="required" style="width:100%;" ');
				?>
			</div>
            <div class="form-group">
                <?= lang('bed', 'bed'); ?>
                <?php
                $bed = [ '1' => 1, '2' => 2,'3' => 3,];
                echo form_dropdown('bed', $bed, (isset($_POST['bed']) ? $_POST['bed'] : ''), 'id="bed" class="form-control select" style="width:100%;" ');
                ?>
            </div>
            <?php if ($Settings->module_hotel_apartment && !empty($options)) { ?>
                <div class="col-xs-6">
                    <div class="form-group" style="margin-bottom: 0 !important;">
                        <label for="option"><?php echo lang("option"); ?></label>
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="form-group" style="margin-bottom: 0 !important;">
                        <label for="price"><?php echo lang("price"); ?></label>
                    </div>
                </div>
                <?php foreach ($options as $opt) { ?>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <input type="hidden" name="custom_field[]" value="<?= $opt->id; ?>">
                            <?php echo form_input('option[]', $opt->name, 'class="form-control option" id="option_' . $opt->id . '"'); ?>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?php echo form_input('price[]', '', 'class="form-control price" id="price_' . $opt->id . '"'); ?>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
    			<div class="form-group">
                    <label for="price"><?php echo lang("price"); ?></label>
    				<?php echo form_input('price', '', 'class="form-control" id="price"'); ?>
                </div>
            <?php } ?>
			<div class="form-group">
                <label for="amount"><?php echo lang("discount_amount"); ?></label>
				<?php echo form_input('amount', '', 'class="form-control" id="amount"'); ?>
            </div>
            <div class="form-group">
                <?= lang('item_code', 'item_code'); ?>
                <?= form_input('item_code', set_value('item_code'), 'class="form-control tip"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="address"><?php echo $this->lang->line("description"); ?></label>
                <?php echo form_textarea('description', '', 'class="form-control" id="description"'); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?= form_submit('add_room', lang('add_room'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>
<?= $modal_js ?>