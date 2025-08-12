<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('edit_language'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open('system_settings/edit_language/' . $id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <?= lang('currency_code', 'code'); ?>
                <?= form_input('code', set_value('code', $currency->code), 'class="form-control tip" id="code" required="required"'); ?>
            </div>
            <div class="form-group">
                <?= lang('khmer', 'name'); ?>
                <?= form_input('khmer', set_value('khmer', $currency->khmer), 'class="form-control tip" required="required"'); ?>
            </div>

            <div class="form-group">
                <?= lang('english', 'english'); ?>
                <?= form_input('english', set_value('english', $currency->english), 'class="form-control tip" required="required"'); ?>
            </div>
            <div class="form-group">
                <?= lang('chinese', 'chinese'); ?>
                <?= form_input('chinese', set_value('chinese', $currency->chinese), 'class="form-control tip"'); ?>
            </div>
            <div class="form-group">
                <?= lang('thai', 'thai'); ?>
                <?= form_input('thai', set_value('thai', $currency->thai), 'class="form-control tip"'); ?>
            </div>
            <div class="form-group">
                <?= lang('vietnamese', 'vietnamese'); ?>
                <?= form_input('vietnamese', set_value('vietnamese', $currency->vietnamese), 'class="form-control tip"'); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?= form_submit('edit_currency', lang('edit_language'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>
<?= $modal_js ?>
