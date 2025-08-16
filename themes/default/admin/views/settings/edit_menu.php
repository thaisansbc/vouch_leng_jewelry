<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_category'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('system_settings/edit_menu/' . $menu->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('update_info'); ?></p>

            <div class="form-group all">
                <?= lang('selected_name', 'selected_name'); ?>
                <?= form_input('selected_name', set_value('selected_name', $menu->selected_name), 'class="form-control tip"'); ?>
            </div>
            
            <div class="form-group">
                <?= lang('name', 'name'); ?>
                <?= form_input('name', set_value('name', $menu->name), 'class="form-control gen_slug" id="name" required="required"'); ?>
            </div>
            <div class="form-group all">
                <?= lang('slug', 'slug'); ?>
                <?= form_input('slug', set_value('slug', $menu->slug), 'class="form-control tip" '); ?>
            </div>
            <div class="form-group">
                <?= lang('icon', 'icon') ?>
                <input id="image" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false" class="form-control file">
            </div>

            <div class="form-group">
                <?= lang('parent_category', 'parent') ?>
                <?php
                $cat[''] = lang('select') . ' ' . lang('parent_category');
                foreach ($menus as $pcat) {
                    $cat[$pcat->id] = ($pcat->parent_id ? '&nbsp;&nbsp;&nbsp;':'').$pcat->name;
                }
                echo form_dropdown('parent', $cat, (isset($_POST['parent']) ? $_POST['parent'] : $menu->parent_id), 'class="form-control select" id="parent" style="width:100%"')
                ?>
            </div>
            <div class="form-group all">
                <?= lang('permission', 'permission'); ?>
                <?= form_input('permission', set_value('permission',$menu->permission), 'class="form-control tip" '); ?>
            </div>
            <div class="form-group all">
                <?= lang('module', 'module'); ?>
                <?php 
                foreach ($modules as $mod) {
                    $module[$mod->name] = lang($mod->name);
                }
                echo form_dropdown('module', $module,$menu->module, 'class="form-control select" style="width:100%"');
                ?>
            </div>
           
            <div class="form-group all">
                <?= lang('favicon', 'favicon'); ?>
                <?= form_input('favicon', set_value('favicon',$menu->icon), 'class="form-control tip"'); ?>
            </div>
            <div class="form-group">
                <?= lang('status', 'status'); ?>
                <?php $status = ['1' => 'Yes', '0' => 'No']; ?>
                <?= form_dropdown('status', $status, $menu->status, 'class="form-control tip" required="required"'); ?>
            </div>
            <div class="form-group">
                <?= lang('order', 'order'); ?>
                <?= form_input('order', set_value('order',$menu->order_number), 'class="form-control" '); ?>
            </div>
         
            <div class="form-group">
                <?= lang('is_modal', 'is_modal'); ?>
                <?php $is_modal = ['1' => 'Yes', '0' => 'No']; ?>
                <?= form_dropdown('is_modal', $is_modal, $menu->is_modal, 'class="form-control tip" required="required"'); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_menu', lang('edit_menu'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>
<script>
    $(document).ready(function() {
        $('.gen_slug').change(function(e) {
            getSlug($(this).val(), 'category');
        });
    });
</script>
