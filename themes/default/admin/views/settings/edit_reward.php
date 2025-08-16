<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style> ul.ui-autocomplete { z-index: 1100; } </style>
<script type="text/javascript">
    $(".exchange_product:not(.ui-autocomplete-input)").live("focus", function(event) {
        $(this).autocomplete({
            source: '<?= admin_url('products/suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function(event, ui) {
                if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
            },
            select: function(event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var parent = $(this).parent();
                    parent.find(".exchange_product_id").val(ui.item.id);
                    $(this).val(ui.item.label);
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
    });
    $(".receive_product:not(.ui-autocomplete-input)").live("focus", function(event) {
        $(this).autocomplete({
            source: '<?= admin_url('products/suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function(event, ui) {
                if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
            },
            select: function(event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var parent = $(this).parent();
                    parent.find(".receive_product_id").val(ui.item.id);
                    $(this).val(ui.item.label);
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
    });
</script>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_reward'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open('system_settings/edit_reward/' . $category . '/' . $type . '/' . $reward->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <input type="hidden" name="category" value="<?= $category; ?>">
            <input type="hidden" name="type" value="<?= $type; ?>">
            <div class="form-group">
                <?= lang('exchange_product', 'exchange_product'); ?>
                <?php echo form_input('exchange_product', (isset($_POST['exchange_product']) ? $_POST['exchange_product'] : $reward->exchange_label), 'class="exchange_product form-control input-tip" id="exchange_product" required="required"'); ?>
                <?php echo form_input('exchange_product_id', $reward->exchange_product_id, 'class="exchange_product_id form-control input-tip hide" id="exchange_product_id"'); ?>
            </div>
            <div class="form-group">
                <?= lang('exchange_quantity', 'exchange_quantity'); ?>
                <?= form_input('exchange_quantity', set_value('exchange_quantity', $reward->exchange_quantity), 'class="form-control tip" id="exchange_quantity" required="required"'); ?>
            </div>
            <div class="form-group">
                <?= lang('amount', 'amount'); ?>
                <?= form_input('amount', set_value('amount', $reward->amount), 'class="form-control tip" id="amount" required="required"'); ?>
            </div>
            <?php if ($type == 'product') { ?>
            <div class="form-group">
                <?= lang('receive_product', 'receive_product'); ?>
                <?php echo form_input('receive_product', (isset($_POST['receive_product']) ? $_POST['receive_product'] : $reward->receive_label), 'class="receive_product form-control input-tip" id="receive_product" required="required"'); ?>
                <?php echo form_input('receive_product_id', $reward->receive_product_id, 'class="receive_product_id form-control input-tip hide" id="receive_product_id"'); ?>
            </div>
            <div class="form-group">
                <?= lang('receive_quantity', 'receive_quantity'); ?>
                <?= form_input('receive_quantity', set_value('receive_quantity', $reward->receive_quantity), 'class="form-control tip" id="receive_quantity" required="required"'); ?>
            </div>
            <?php } else { ?>
            <div class="form-group">
                <?= lang('interest (without %)', 'interest'); ?>
                <?= form_input('interest', set_value('interest', $reward->interest), 'class="form-control tip" id="interest" required="required"'); ?>
            </div>
            <?php } ?>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_reward', lang('edit_reward'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>