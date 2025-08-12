<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_telegram'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open_multipart("system_settings/add_telegram", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <?= lang('name', 'name'); ?>
                <?= form_input('name', '', 'class="form-control" id="name" required="required"'); ?>
            </div>
			<div class="form-group">
                <?= lang('token_id', 'token_id'); ?>
                <?= form_input('token_id', '', 'class="form-control" id="token_id" required="required"'); ?>
            </div>
			<div class="form-group">
                <?= lang('chat_id', 'chat_id'); ?>
                <?= form_input('chat_id', '', 'class="form-control" id="chat_id"'); ?>
            </div>
			<div class="form-group">
				<label class="control-label" for="transaction"><?= lang("transaction"); ?></label>
				<?php
					
					if($this->config->item("quotation")){
						$tran_opt["quotation"] = lang("quotation");
					}
					if($this->config->item("saleorder")){
						$tran_opt["sale_order"] = lang("sale_order");
					}
					if($this->config->item("sale")){
						$tran_opt["sale"] = lang("sale");
						$tran_opt["payment"] = lang("payment");
					}
					if($this->config->item("deliveries")){
						$tran_opt["delivery"] = lang("delivery");
					}
					if($this->config->item("purchase")){
						$tran_opt["purchase"] = lang("purchase");
					}
					$tran_opt["expense"] = lang("expense");
					if(!$this->config->item("one_warehouse")){
						$tran_opt["transfer"] = lang("transfer");
					}
					if($this->config->item("attendance")){
						$tran_opt["take_leave"] = lang("take_leave");
					}
					if($this->config->item("truckings")){
						$tran_opt["trucking"] = lang("trucking");
						$tran_opt["cash_advance"] = lang("cash_advance");
					}
					echo form_dropdown('transaction[]', $tran_opt, '', 'class="form-control transaction" id="transaction" style="min-height:70px" multiple data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("transaction") . '"');
				?>
			</div>
			<?php if(!$this->config->item('one_biller')){ ?>
				<div class="form-group">
					<label class="control-label" for="biller"><?= lang("biller"); ?></label>
					<?php
					foreach ($billers as $biller) {
						$bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
					}
					echo form_dropdown('biller[]', $bl, '', 'class="form-control biller" id="biller" style="min-height:70px" multiple data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
					?>
				</div>
			<?php } ?>
			<div class="form-group">
				<label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
				<?php
				foreach ($warehouses as $warehouse) {
					$wh[$warehouse->id] = $warehouse->name;
				}
				echo form_dropdown('warehouse[]', $wh, '', 'class="form-control warehouse" id="warehouse" style="min-height:70px" multiple data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
				?>
			</div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_telegram', lang('add_telegram'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>
