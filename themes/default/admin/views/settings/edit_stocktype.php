<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_stocktype'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('system_settings/edit_stocktype/' . $stocktype->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('update_info'); ?></p>
            <div class="form-group">
                <?= lang('name', 'name'); ?>
                <?= form_input('name', $stocktype->name, 'class="form-control gen_slug" id="name" required="required"'); ?>
            </div>
            <div class="form-group all">
                <?= lang('description', 'description'); ?>
                <?= form_input('description', set_value('description', $stocktype->description), 'class="form-control tip" id="description"'); ?>
            </div>
            <?php echo form_hidden('id', $stocktype->id); ?>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_stocktype', lang('edit_stocktype'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>

