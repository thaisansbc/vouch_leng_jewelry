<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('import_by_excel'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("attendances/import_roster", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="well well-small">
                <a href="<?php echo base_url(); ?>assets/excel/import_roster.xlsx" class="btn btn-primary pull-right"><i
                        class="fa fa-download"></i> Download Sample File</a>
                <span class="text-warning"><?= lang("csv1"); ?></span><br/><?= lang("csv2"); ?> <span class="text-info">(<?= lang("code").', '.lang("check_time") ?>)  
                </span> <?= lang("csv3"); ?><br>
            </div>
            <div class="form-group">
                <?= lang("upload_file", "excel_file") ?>
                <input id="excel_file" type="file" data-browse-label="<?= lang('browse'); ?>" name="excel_file" data-bv-notempty="true" data-show-upload="false" data-show-preview="false" class="form-control file" accept=".xls, .xlsx">
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('import', lang('import'), 'class="btn btn-primary"'); ?>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>