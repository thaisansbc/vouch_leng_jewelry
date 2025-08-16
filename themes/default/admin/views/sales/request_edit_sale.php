<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('request_edit_sale')." ". $inv->reference_no; ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('sales/add_request_edit_sale/' . $inv->id, $attrib);  ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p> 
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('date', 'date'); ?>
                        <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : ''), 'class="form-control datetime" id="date" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('reference_no', 'reference_no'); ?>
                        <?= form_input('view_reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $reference_no), 'class="form-control tip" disabled="true" id="view_reference_no"'); ?>
                        <?= form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $reference_no), 'class="hide form-control tip" id="reference_no"'); ?>
                    </div> 
                    <div class="form-group">
                        <?= lang('sale_reference_no', 'sale_reference_no'); ?>
                        <?= form_input('view_sale_reference_no', (isset($_POST['sale_reference_no']) ? $_POST['sale_reference_no'] : $inv->reference_no), 'class="form-control tip" disabled="true" id="view_sale_reference_no" required="required"'); ?>
                        <?= form_input('sale_reference_no', (isset($_POST['sale_reference_no']) ? $_POST['sale_reference_no'] : $inv->reference_no), 'class="form-control tip hide" id="sale_reference_no" required="required"'); ?>
                    </div>
                    <input type="hidden" value="<?php echo $inv->id; ?>" name="sale_id"/> 
                </div> 
                <div class="col-md-6">
                    <div class="form-group hide">
                        <?= lang('status', 'status'); ?>
                        <?php $opts = ['request' => lang('request'), 'approved' => lang('approved')]; ?>
                        <?= form_dropdown('status', $opts, '', 'class="form-control" id="status" required="required" style="width:100%;"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('attachment', 'attachment') ?>
                        <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="true" data-show-preview="true" class="form-control file">
                    </div> 
                    <div class="form-group">
                        <?= lang('noted', 'noted'); ?>
                        <?php echo form_textarea('noted', (isset($_POST['noted']) ? $_POST['noted'] : ''), 'class="form-control" id="note"'); ?>
                    </div>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_request', lang('add_request'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
</script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
        $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
        $("#date").datetimepicker({
            format: site.dateFormats.js_ldate,
            fontAwesome: true,
            language: 'sma',
            weekStart: 1,
            todayBtn: 1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            forceParse: 0
        }).datetimepicker('update', new Date());

       
    });

   
</script>
