<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('add_custom_field'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open('system_settings/add_custom_field', $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <?php 
            if($code){
            ?>
            <div class="form-group">
                <?= lang('code', 'code'); ?>
                <?= form_input('code',$code, 'class="form-control" id="code" readonly required="required"'); ?>
            </div>
            <?php
            }
            ?>
            <div class="form-group">
                <?= lang('name', 'name'); ?>
                <?= form_input('name', '', 'class="form-control" id="name" required="required"'); ?>
            </div>

            <div class="form-group">
                <?= lang('description', 'description'); ?>
                <?= form_input('description', '', 'class="form-control" '); ?>
            </div>
             <div class="form-group">
                <?= lang('discount', 'discount'); ?>
                <?= form_input('discount', '', 'class="form-control" id="discount"'); ?>
            </div>
            <div class="form-group">
                <?= lang('parent_category', 'parent') ?>
                <?php
                $cat[''] = lang('select') . ' ' . lang('parent_category');
                foreach ($expenses as $pcat) {
                    $cat[$pcat->id] = $pcat->name;
                }
                echo form_dropdown('parent', $cat, (isset($_POST['parent']) ? $_POST['parent'] : ''), 'class="form-control select" id="parent" style="width:100%"')
                ?>
            </div>
        </div>
        <div class="modal-footer">
            <?= form_submit('add_custom_field', lang('add_custom_field'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
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