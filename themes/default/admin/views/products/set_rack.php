<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>ul.ui-autocomplete {
        z-index: 1100;
    }</style>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= $product->name; ?></h4>
        </div>
        <?php echo admin_form_open("products/set_rack/" . $product->id . '/' . $warehouse_id); ?>
        <div class="modal-body">
            <input type="hidden" name="product_id" value="<?= $product->id;?>">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <?= lang('warehouse', 'qawarehouse'); ?>
                <div class="input-group" style="width:100%">
                    <?php
                    $wh[''] = '';
                    foreach ($warehouses as $warehouse) {
                        $wh[$warehouse->id] = $warehouse->name;
                    }
                    echo form_dropdown('warehouse', $wh,$warehouse_id, 'id="qawarehouse" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('warehouse') . '" required="required" ' . ($warehouse_id ? 'readonly' : '') . ' style="width:100%;"'); ?>
                </div>
            </div>
            <div class="form-group">
                <?= lang('rack', 'rack') ?>
                <?php
                $cat_opt[''] = lang('select') . ' ' . lang('rack');
                foreach ($racks as $prack) {
                    $cat_opt[$prack->id] = $prack->name;
                }
                echo form_dropdown('rack', $cat_opt, $rack, 'class="form-control select" id="rack" style="width:100%"')
                ?>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('set_rack', lang('set_rack'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['bpas'] = <?=$dp_lang?>;
</script>
<?= $modal_js ?>
    