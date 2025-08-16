<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_assignation'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("installments/add_assignation/".$id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <?= lang('assign_date', 'assign_date'); ?>
                <?= form_input('assign_date', set_value('assign_date', $this->bpas->hrsd(date("Y-m-d"))), 'class="form-control date" id="assign_date" required="required" autocomplete="off" '); ?>
            </div>
			<div class="form-group">
				<?= lang("customer", "customer"); ?>
				<select name="customer" class="form-control" id="customer" data-placeholder="<?= lang("select") . ' ' . lang("customer") ?>" class="form-control input-tip select" style="width:100%;">
					<?php
						$cts = array();
						foreach ($customers as $customer) {
							if($installment->customer_id == $customer->id){
								echo '<option value="'.$customer->id.'" disabled="disabled">'. ($customer->company != '-' ? $customer->company : $customer->name).'</option>';
							}else{
								echo '<option value="'.$customer->id.'">'. ($customer->company != '-' ? $customer->company : $customer->name).'</option>';
							}
						}
					?>
				</select>
			</div>
			<div class="form-group">
				<?= lang("note", "note"); ?>
				<?php echo form_textarea('description', set_value('note'), 'class="form-control" id="note" style="margin-top: 10px; height: 100px;"'); ?>
			</div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_assignation', lang('add_assignation'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>