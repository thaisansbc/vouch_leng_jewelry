<?php defined('BASEPATH') or exit('No direct script access allowed'); 
$bgs = glob( 'assets/uploads/slides/*.jpg');
?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_slide'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('system_settings/add_slide', $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <p class="text-primary"><?= lang('slide_image_tip'); ?></p>
                <div class="form-group">
                    <?= lang('add_slide', 'add_slide') ?>
                    <input id="add_slide" type="file" data-browse-label="<?= lang('browse'); ?>" name="add_slide" data-show-upload="false"
                        data-show-preview="false" class="form-control file">
                    <small class="help-block"><?= lang('add_slide_tip'); ?></small>
                </div>  
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_slide', lang('add_slide'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?> 
