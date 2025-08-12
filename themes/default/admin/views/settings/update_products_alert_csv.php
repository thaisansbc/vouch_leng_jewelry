<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('update_products_alert_csv'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('system_settings/update_product_qty_alert_csv', $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-md-12">
                    <div class="well well-small">
                        <a href="<?php echo admin_url('system_settings/sample_products_alert'); ?>" class="btn btn-primary pull-right">
                            <i class="fa fa-download"></i> <?= lang('download_sample_file') ?>
                        </a>
                        <span class="text-warning"><?= lang('csv1'); ?></span><br/><?= lang('csv2'); ?> 

                        <?php 
                            $str = "";
                            if ($warehouses) {
                                foreach ($warehouses as $key => $warehouse) {
                                    $str .= ((count($warehouses) - 1 != $key) ? ($warehouse->name . ', ') : $warehouse->name);
                                }
                            }
                        ?>

                        <span class="text-info">(<?= lang('product_code') . ', ' . lang('product_price') . ', ' . $str; ?>)</span>
                        <?= lang('csv3'); ?>
                    </div>
                    <div class="form-group">
                        <label for="csv_file"><?= lang('upload_file'); ?></label>
                        <input type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" class="form-control file" data-show-upload="false" data-show-preview="false" id="csv_file" required="required"/>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('update_products_alert', lang('update_products_alert'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>