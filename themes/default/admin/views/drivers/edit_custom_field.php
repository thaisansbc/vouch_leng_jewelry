<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_route'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('drivers/edit_route/' . $category->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('update_info'); ?></p>

            <div class="form-group">
                <?= lang('name', 'name'); ?>
                <?= form_input('name', $category->name, 'class="form-control" id="name" required="required"'); ?>
            </div>
            <div class="form-group">
                <?= lang('fare', 'fare'); ?>
                <?= form_input('price', $category->price, 'class="form-control" id="fare" required="required"'); ?>
            </div>
            <div class="form-group">
                <?= lang('description', 'description'); ?>
                <?php echo form_textarea('description', $category->description, 'class="form-control skip" style="height:100px;"'); ?>
            </div>
            <?php echo form_hidden('id', $category->id); ?>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_route', lang('edit_route'), 'class="btn btn-primary"'); ?>
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