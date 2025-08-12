<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('edit_assign'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open("table/edit_assign/".$id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <?= lang('date', 'sldate'); ?>
                <?php echo form_input('date',$this->bpas->hrld($rooms->date), 'class="form-control input-tip datetime" required="required"'); ?>
            </div>

            <div class="form-group">
                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                <div class="controls"> 
                    <?php
                    foreach ($customers as $customer) {
                        $cus[$customer->id] = $customer->name;
                    }
                    echo form_dropdown('customer', $cus,$rooms->patient_id, 'class="form-control tip" id="customer" required="required" style="width:100%;"');
                    ?>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label" for="bed"><?= lang("bed"); ?></label>
                <div class="controls"> 
                    <?php
                    foreach ($tables as $table) {
                        $tab[$table->id] = $table->name;
                    }
                    echo form_dropdown('bed', $tab,$rooms->bed, 'class="form-control tip" id="bed" required="required" style="width:100%;"');
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?= lang('assign_date', 'assign_date'); ?>
                <?php echo form_input('assign_date',$this->bpas->hrld($rooms->assign_date), 'class="form-control input-tip datetime" required="required"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="address"><?php echo $this->lang->line("description"); ?></label>
                <?php echo form_textarea('description', $rooms->description, 'class="form-control" id="description"'); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?= form_submit('edit_assign', lang('edit_assign'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>
<?= $modal_js ?>
