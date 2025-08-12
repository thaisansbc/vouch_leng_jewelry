<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_floor'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('system_settings/edit_floor/' . $floor->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('update_info'); ?></p>
            <div class="form-group">
                <?= lang('name', 'name'); ?>
                <?= form_input('name', $floor->name, 'class="form-control gen_slug" id="name" required="required"'); ?>
            </div>
            <div class="form-group all">
                <?= lang('description', 'description'); ?>
                <?= form_input('description', set_value('description', $floor->description), 'class="form-control tip" id="description" required="required"'); ?>
            </div>
            <?php echo form_hidden('id', $floor->id); ?>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_floor', lang('edit_floor'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>
<script>
    $(document).ready(function() {
        $('.gen_slug').change(function(e) {
            getSlug($(this).val(), 'floor');
        });
    });
</script>
