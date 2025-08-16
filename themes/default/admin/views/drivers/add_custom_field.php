<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('add_route'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open('drivers/add_route', $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <?= lang('name', 'name'); ?>
                <?= form_input('name', '', 'class="form-control" id="name" required="required"'); ?>
            </div>
            <div class="form-group">
                <?= lang('fare', 'fare'); ?>
                <?= form_input('price', '', 'class="form-control" id="fare" required="required"'); ?>
            </div>
            <div class="form-group">
                <?= lang('description', 'description'); ?>
                <?php echo form_textarea('description', '', 'class="form-control skip" id="invoice_footer" style="height:100px;"'); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?= form_submit('add_route', lang('add_route'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>
<?= $modal_js ?>