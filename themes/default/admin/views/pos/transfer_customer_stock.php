<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('transfer_customer_stock'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("pos/transfer_customer_stock/" . $id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <?php if ($Owner || $Admin || $GP['sales-date']) { ?>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <?= lang("date", "date"); ?>
                            <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control datetime" id="date" required="required"'); ?>
                        </div>
                    </div>
                <?php } ?>
				
                <div class="col-sm-6">
                    <div class="form-group">
                        <?= lang("reference_no", "reference_no"); ?>
                        <?= form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $customer_stock->reference_no), 'class="form-control tip" id="reference_no" required="required"'); ?>
                    </div>
                </div>
				
				<div class="col-sm-12">
                    <div class="form-group">
                        <label><?= lang("table"); ?> / <?= lang("room"); ?></label>
                        <?php
							$bls = array( "" => lang("select").' '.lang("table").' / '.lang('room'));
							foreach($suspend_bills as $bill){
								$bls[$bill->table_id] = $bill->table_name;
							}
							echo form_dropdown("table", $bls, (isset($_GET['table'])? $_GET['table'] :""), " class='form-control select' required='required' ");
						?>
                    </div>
                </div>
				
            </div>
			
            <div class="clearfix"></div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('transfer_customer_stock', lang('submit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
        $("#date").datetimepicker({
            format: site.dateFormats.js_ldate,
            fontAwesome: true,
            language: 'bpas',
            weekStart: 1,
            todayBtn: 1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            forceParse: 0
        }).datetimepicker('update', new Date());
    });
</script>