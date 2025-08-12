
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('edit_expiry'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
          echo admin_form_open('purchases/edit_expiry/' . $inv->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <?= lang('name', 'name'); ?>
                <?php echo form_input('name', $inv->product_name." - ". $inv->product_code .($inv->expiry !="" ? " (". $inv->expiry.") " : ""), 'class="form-control tip" id="name" data-bv-notempty="true" disabled'); ?>
            </div>
            <div class="form-group">
                <?= lang('quantity', 'quantity'); ?>
                <?php echo form_input('quantity_balance', (int)($inv->quantity_balance), 'class="form-control tip" type ="number" id="quantity_balance"'); ?>
            </div>
            <div class="form-group">
                <?= lang('expiry', 'expiry'); ?>
                <!-- $this->bpas->hrsd( -->
                <?php echo form_input('expiry', $inv->expiry, 'class="form-control tip date" id="expiry"'); ?>
            </div>
        </div>
        <!-- <?php echo form_input('fix_quantity_balance', $inv->quantity_balance, 'class="form-control tip" id="fix_quantity_balance"'); ?> -->
        <input type="hidden" value = "<?= (int)($inv->quantity_balance) ?>" id ="fix_quantity_balance">
        <div class="modal-footer">
            <?php echo form_submit('edit_expiry', lang('edit_expiry'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>
<?= $modal_js ?>
<script>
$(document).ready(function () {
        $('#quantity_balance').change(function() {
                    var quantity_balance = $("#quantity_balance").val();
                    var fix_quantity_balance = $("#fix_quantity_balance").val();
                     var slsh = $(this).val() ? $(this).val() : 0;
                    if (!is_numeric(slsh)) {
                        bootbox.alert(lang.unexpected_value);
                        document.getElementById("quantity_balance").value = $('#fix_quantity_balance').val();
                        return;
                    }
                    if (parseInt(quantity_balance) > parseInt(fix_quantity_balance)) {
                        bootbox.alert('<?= lang('this_item_has_only') ?>'+$('#fix_quantity_balance').val());
                        document.getElementById("quantity_balance").value = $('#fix_quantity_balance').val();
                        return true;
                    }
                });
});
</script>