<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('add_assign'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open("table/add_assign", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <?= lang('date', 'sldate'); ?>
                <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : date('d/m/Y H:i:s')), 'class="form-control input-tip datetime" required="required"'); ?>
            </div>

            <div class="form-group">
                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                <div class="controls"> 
                    <?php
                    foreach ($customers as $customer) {
                        $cus[$customer->id] = $customer->name;
                    }
                    echo form_dropdown('customer', $cus,'', 'class="form-control tip" id="customer" required="required" style="width:100%;"');
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
                    echo form_dropdown('bed', $tab,'', 'class="form-control tip" id="bed" required="required" style="width:100%;"');
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?= lang('assign_date', 'assign_date'); ?>
                <?php echo form_input('assign_date', (isset($_POST['assign_date']) ? $_POST['assign_date'] : date('d/m/Y H:i:s')), 'class="form-control input-tip datetime" required="required"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="address"><?php echo $this->lang->line("description"); ?></label>
                <?php echo form_textarea('description', '', 'class="form-control" id="description"'); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?= form_submit('add_assign', lang('add_assign'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>
<?= $modal_js ?>
