<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_custom_field'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('system_settings/edit_custom_field/' . $category->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('update_info'); ?></p>

            <div class="form-group">
                <?= lang('name', 'name'); ?>
                <?= form_input('name', $category->name, 'class="form-control" id="name" required="required"'); ?>
            </div>
            <div class="form-group">
                <?= lang('description', 'description'); ?>
                <?= form_input('description', $category->description, 'class="form-control" '); ?>
            </div>
             <div class="form-group">
                <?= lang('discount', 'discount'); ?>
                <?= form_input('discount', $category->discount, 'class="form-control" id="discount"'); ?>
            </div>
            <div class="form-group">
                <?= lang('parent_category', 'parent') ?>
                <?php
                $cat[''] = lang('select') . ' ' . lang('parent_category');
                foreach ($expenses as $pcat) {
                    $cat[$pcat->id] = $pcat->name;
                }
                echo form_dropdown('parent', $cat, (isset($_POST['parent']) ? $_POST['parent'] : $category->parent_id), 'class="form-control select" id="parent" style="width:100%"')
                ?>
            </div>
            <?php echo form_hidden('id', $category->id); ?>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_custom_field', lang('edit_custom_field'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript">
    $(document).ready(function(e){
        var old_discount;
        $('#discount')
            .focus(function() {
                old_discount = $(this).val();
            })
            .change(function(e) {
                var new_discount = $(this).val() ? ($(this).val()) : 0;
                if (!is_valid_discount($(this).val())) {
                    $(this).val(old_discount);
                    bootbox.alert(lang.unexpected_value);
                    return;
                } else {
                    localStorage.setItem('discount', new_discount);
                    $('#discount').val(new_discount);
                }
            });
    });
</script>
<?= $modal_js ?>