<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_cash_account'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open("system_settings/edit_cash_account/".$cash_account->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <?= lang('code', 'code'); ?>
                <?= form_input('code', set_value('code', $cash_account->code), 'class="form-control tip" id="code" required="required"'); ?>
            </div>
            <div class="form-group">
                <?= lang('name', 'name'); ?>
                <?= form_input('name', set_value('name', $cash_account->name), 'class="form-control tip" id="name" required="required"'); ?>
            </div>
			<?php if($Settings->module_account == 1){ 
                $acc[''] = lang('please_selected');
                foreach ($accounts as $account) {
                    $acc[$account->accountcode] = $account->accountcode.' '.$account->accountname;
                }
                ?>
				<div class="form-group">
					<?= lang("account_code", "account_code"); ?>
                    <?php 
                    echo form_dropdown('account_code', $acc,$cash_account->account_code, 'class="form-control select" id="account_code" style="width:100%;" required="required"');
                    ?>
				</div>
			<?php } ?>
			<div class="form-group">
                <?= lang('type', 'type'); ?>
				<?php
					$type_opt["cash"] = lang("cash");
					$type_opt["bank"] = lang("bank");
					$type_opt["cheque"] = lang("cheque");
				?>
				<?= form_dropdown('type',$type_opt, $cash_account->type, 'class="form-control tip" id="type"'); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_cash_account', lang('edit_cash_account'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
